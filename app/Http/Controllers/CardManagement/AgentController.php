<?php

namespace App\Http\Controllers\CardManagement;

use App\Http\Controllers\Controller;
use App\Models\MembershipAgent;
use App\Models\MembershipAgentTransaction;
use App\Models\Supplier;
use App\Services\Membership\KycEligibilityService;
use App\Services\Membership\RecipientService;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AgentController extends Controller
{
    private function admin(): void
    {
        abort_unless(auth()->user()->role === 'Admin', 403);
    }

    public function index(Request $r)
    {
        $this->admin();
        if ($r->ajax()) {
            $r->validate(['search.value' => 'nullable|string|max:100']);
            $q = MembershipAgent::select('membership_agents.*')->with('customer')->whereHas('customer', fn ($c) => $c->where('origin_sister_concern_id', auth()->user()->activeConcernId()));

            return \Yajra\DataTables\Facades\DataTables::eloquent($q)
                ->filter(function ($q) use ($r) {
                    $search = trim((string) $r->input('search.value', ''));
                    if ($search !== '') {
                        $q->where(function ($q) use ($search) {
                            $q->where('membership_agents.agent_code', 'like', '%'.$search.'%')
                                ->orWhere('membership_agents.status', 'like', '%'.$search.'%')
                                ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', '%'.$search.'%')
                                    ->orWhere('mobile_no', 'like', '%'.$search.'%')
                                    ->orWhere('member_no', 'like', '%'.$search.'%'));
                        });
                    }
                })
                ->addColumn('member_name', fn ($agent) => $agent->customer?->name)
                ->addColumn('member_no', fn ($agent) => $agent->customer?->member_no)
                ->addColumn('mobile_no', fn ($agent) => $agent->customer?->mobile_no)
                ->addColumn('action', fn ($agent) => view('card_management.agents.actions', compact('agent'))->render())
                ->removeColumn('customer')
                ->rawColumns(['action'])
                ->toJson();
        }
        $r->validate(['search' => 'nullable|string|max:100']);

        return view('card_management.agents.index');
    }

    public function store(Request $r)
    {
        $this->admin();
        $r->validate(['member' => 'required|string|max:64']);
        $member = RecipientService::resolve(new Supplier, $r->member);
        DB::transaction(function () use ($member) {
            $member = Supplier::whereKey($member->id)->lockForUpdate()->firstOrFail();
            $branch = auth()->user()->activeConcernId();
            if ($branch === null) {
                throw ValidationException::withMessages(['member' => 'Select the member’s registration branch from the branch selector before making an agent.']);
            }
            if ((int) $member->origin_sister_concern_id !== (int) $branch) {
                throw ValidationException::withMessages(['member' => 'This member does not belong to the selected branch. Select the member’s registration branch and try again.']);
            }
            if ($member->status != 1) {
                throw ValidationException::withMessages(['member' => 'Member account is inactive.']);
            }
            KycEligibilityService::ensureApproved($member);
            if (! $member->shomriddhiAccount || $member->shomriddhiAccount->status !== 'Active') {
                throw ValidationException::withMessages(['member' => 'An active savings account is required before making an agent.']);
            }
            $agent = MembershipAgent::firstOrCreate(['customer_id' => $member->id], ['agent_code' => 'AG-'.$member->id, 'status' => 'Active', 'approved_by' => auth()->id(), 'approved_at' => now()]);
            if ($agent->status === 'Removed') {
                $agent->update(['status' => 'Active', 'approved_by' => auth()->id(), 'approved_at' => now()]);
            }
        }, 3);

        return back()->with('success', 'Member Agent saved.');
    }

    public function status(Request $r, int $id)
    {
        $this->admin();
        $r->validate(['status' => 'required|in:Active,Suspended,Removed']);
        $a = MembershipAgent::findOrFail($id);
        DB::transaction(function () use ($a, $r) {
            $member = Supplier::whereKey($a->customer_id)->lockForUpdate()->firstOrFail();
            abort_unless($member->origin_sister_concern_id == auth()->user()->activeConcernId(), 403);
            $a = MembershipAgent::whereKey($a->id)->lockForUpdate()->firstOrFail();
            abort_if($a->status === 'Removed' && $r->status === 'Suspended', 422, 'Make this member an agent before suspending.');
            if ($r->status === 'Active') {
                abort_unless($member->status == 1, 422);
                KycEligibilityService::ensureApproved($member);
                abort_unless($member->shomriddhiAccount && $member->shomriddhiAccount->status === 'Active', 422, 'An active savings account is required.');
            }
            $a->update(['status' => $r->status] + ($r->status === 'Active' ? ['approved_by' => auth()->id(), 'approved_at' => now()] : []));
        }, 3);

        return back()->with('success', $r->status === 'Removed' ? 'Agent returned to regular member. Balance and history are unchanged.' : 'Agent status updated.');
    }

    public function qr(int $id)
    {
        $this->admin();
        $agent = MembershipAgent::with('customer')->findOrFail($id);
        abort_unless($agent->customer?->origin_sister_concern_id == auth()->user()->activeConcernId(), 403);
        abort_if($agent->status === 'Removed', 404);
        $writer = new Writer(new ImageRenderer(new RendererStyle(320), new SvgImageBackEnd));

        return response($writer->writeString($agent->agent_code), 200)->header('Content-Type', 'image/svg+xml')->header('Cache-Control', 'private, no-store');
    }

    public function transactions(Request $r)
    {
        $this->admin();
        $rules = ['type' => 'nullable|in:agent_cashin,agent_cashout', 'start_date' => 'nullable|date_format:Y-m-d', 'end_date' => 'nullable|date_format:Y-m-d',
            'agent_id' => 'nullable|integer|min:1', 'customer_id' => 'nullable|integer|min:1'];
        if ($r->filled('start_date')) {
            $rules['end_date'] .= '|after_or_equal:start_date';
        }
        if ($r->ajax()) {
            $rules['search.value'] = 'nullable|string|max:100';
        }
        $d = $r->validate($rules);
        $q = MembershipAgentTransaction::select('membership_agent_transactions.*')->with(['agent.customer', 'customer'])
            ->whereHas('agent.customer', fn ($c) => $c->where('origin_sister_concern_id', auth()->user()->activeConcernId()));
        if (! $r->ajax()) {
            // Include suspended/removed agents so their historical transactions remain filterable.
            $agents = MembershipAgent::with('customer')->whereHas('customer', fn ($c) => $c->where('origin_sister_concern_id', auth()->user()->activeConcernId()))
                ->orderBy('agent_code')->get();
            $customers = Supplier::withTrashed()->whereIn('id', (clone $q)->select('membership_agent_transactions.customer_id'))
                ->orderBy('name')->get(['id', 'name', 'member_no', 'mobile_no']);
            return view('card_management.agents.transactions', compact('agents', 'customers'));
        }
        if ($r->filled('agent_id')) {
            $q->where('membership_agent_transactions.agent_id', $r->integer('agent_id'));
        }
        if ($r->filled('customer_id')) {
            $q->where('membership_agent_transactions.customer_id', $r->integer('customer_id'));
        }
        if ($r->filled('type')) {
            $q->where('membership_agent_transactions.type', $d['type']);
        }
        if ($r->filled('start_date')) {
            $q->whereDate('completed_at', '>=', $d['start_date']);
        }
        if ($r->filled('end_date')) {
            $q->whereDate('completed_at', '<=', $d['end_date']);
        }

        return \Yajra\DataTables\Facades\DataTables::eloquent($q)
            ->filter(function ($q) use ($r) {
                $search = trim((string) $r->input('search.value', ''));
                if ($search !== '') {
                    $q->where(function ($q) use ($search) {
                        $q->where('reference', 'like', '%'.$search.'%')->orWhere('status', 'like', '%'.$search.'%')
                            ->orWhereHas('agent', fn ($a) => $a->where('agent_code', 'like', '%'.$search.'%'))
                            ->orWhereHas('agent.customer', fn ($c) => $c->where('name', 'like', '%'.$search.'%')->orWhere('member_no', 'like', '%'.$search.'%')->orWhere('mobile_no', 'like', '%'.$search.'%'))
                            ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', '%'.$search.'%')->orWhere('member_no', 'like', '%'.$search.'%')->orWhere('mobile_no', 'like', '%'.$search.'%'));
                    });
                }
            })
            ->addColumn('type_label', fn ($record) => $record->type === 'agent_cashin' ? 'Member Cash-in' : 'Agent Cash-out')
            ->addColumn('agent_label', fn ($record) => ($record->agent?->customer?->name ?? '—').' / '.($record->agent?->agent_code ?? '—'))
            ->addColumn('customer_label', fn ($record) => ($record->customer?->name ?? '—').' / '.($record->customer?->member_no ?? '—'))
            ->only(['id', 'completed_at', 'reference', 'type_label', 'agent_label', 'customer_label', 'amount', 'customer_charge', 'agent_commission', 'status'])
            ->toJson();
    }
}
