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

class AgentController extends Controller
{
    private function admin(): void
    {
        abort_unless(auth()->user()->role === 'Admin', 403);
    }

    public function index(Request $r)
    {
        $this->admin();
        $r->validate(['search' => 'nullable|string|max:100']);
        $q = MembershipAgent::with('customer')->whereHas('customer', fn ($c) => $c->where('origin_sister_concern_id', auth()->user()->activeConcernId()));
        if ($r->filled('search')) {
            $q->where(function ($q) use ($r) {
                $q->where('agent_code', 'like', '%'.$r->search.'%')->orWhereHas('customer', fn ($c) => $c->where('name', 'like', '%'.$r->search.'%')->orWhere('mobile_no', 'like', '%'.$r->search.'%')->orWhere('member_no', 'like', '%'.$r->search.'%'));
            });
        }

        return view('card_management.agents.index', ['agents' => $q->latest('id')->paginate(25)->withQueryString()]);
    }

    public function store(Request $r)
    {
        $this->admin();
        $r->validate(['member' => 'required|string|max:64']);
        $member = RecipientService::resolve(new Supplier, $r->member);
        DB::transaction(function () use ($member) {
            $member = Supplier::whereKey($member->id)->lockForUpdate()->firstOrFail();
            abort_unless($member->origin_sister_concern_id == auth()->user()->activeConcernId(), 403);
            KycEligibilityService::ensureApproved($member);
            abort_unless($member->shomriddhiAccount && $member->shomriddhiAccount->status === 'Active', 422, 'An active savings account is required.');
            MembershipAgent::firstOrCreate(['customer_id' => $member->id], ['agent_code' => 'AG-'.$member->id, 'status' => 'Active', 'approved_by' => auth()->id(), 'approved_at' => now()]);
        }, 3);

        return back()->with('success', 'Member Agent saved.');
    }

    public function status(Request $r, int $id)
    {
        $this->admin();
        $r->validate(['status' => 'required|in:Active,Suspended']);
        $a = MembershipAgent::findOrFail($id);
        DB::transaction(function () use ($a, $r) {
            $member = Supplier::whereKey($a->customer_id)->lockForUpdate()->firstOrFail();
            abort_unless($member->origin_sister_concern_id == auth()->user()->activeConcernId(), 403);
            $a = MembershipAgent::whereKey($a->id)->lockForUpdate()->firstOrFail();
            if ($r->status === 'Active') {
                abort_unless($member->status == 1, 422);
                KycEligibilityService::ensureApproved($member);
            }
            $a->update(['status' => $r->status]);
        }, 3);

        return back()->with('success', 'Agent status updated.');
    }

    public function qr(int $id)
    {
        $this->admin();
        $agent = MembershipAgent::with('customer')->findOrFail($id);
        abort_unless($agent->customer?->origin_sister_concern_id == auth()->user()->activeConcernId(), 403);
        $writer = new Writer(new ImageRenderer(new RendererStyle(320), new SvgImageBackEnd));

        return response($writer->writeString($agent->agent_code), 200)->header('Content-Type', 'image/svg+xml')->header('Cache-Control', 'private, no-store');
    }

    public function transactions(Request $r)
    {
        $this->admin();
        $d = $r->validate(['type' => 'nullable|in:agent_cashin,agent_cashout', 'start_date' => 'nullable|date', 'end_date' => 'nullable|date']);
        $q = MembershipAgentTransaction::with(['agent.customer', 'customer'])->whereHas('agent.customer', fn ($c) => $c->where('origin_sister_concern_id', auth()->user()->activeConcernId()));
        if ($r->filled('type')) {
            $q->where('type', $d['type']);
        }
        if ($r->filled('start_date')) {
            $q->whereDate('completed_at', '>=', $d['start_date']);
        }
        if ($r->filled('end_date')) {
            $q->whereDate('completed_at', '<=', $d['end_date']);
        }

        return view('card_management.agents.transactions', ['records' => $q->latest('id')->paginate(25)->withQueryString()]);
    }
}
