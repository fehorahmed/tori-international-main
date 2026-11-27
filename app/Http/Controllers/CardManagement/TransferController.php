<?php

namespace App\Http\Controllers\CardManagement;

use App\Http\Controllers\Controller;
use App\Models\Account\Voucher;
use App\Models\MembershipTransfer;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class TransferController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->role === 'Admin' || auth()->user()->can('shomriddhi_advance_transaction_approve'), 403);
        $branch = auth()->user()->activeConcernId();
        if (! $request->ajax()) {
            $visible = $this->branchTransfers($branch);
            $senders = Supplier::withTrashed()->whereIn('id', (clone $visible)->select('sender_id'))->orderBy('name')->get(['id', 'name', 'member_no', 'mobile_no']);
            $recipients = Supplier::withTrashed()->whereIn('id', (clone $visible)->select('recipient_id'))->orderBy('name')->get(['id', 'name', 'member_no', 'mobile_no']);
            return view('card_management.transfers.index', compact('senders', 'recipients'));
        }
        $request->validate([
            'from_date' => 'nullable|date_format:Y-m-d',
            'to_date' => 'nullable|date_format:Y-m-d',
            'sender' => 'nullable|string|max:100',
            'recipient' => 'nullable|string|max:100',
            'sender_id' => 'nullable|integer|min:1',
            'recipient_id' => 'nullable|integer|min:1',
        ]);
        if ($request->filled('from_date') && $request->filled('to_date') && $request->to_date < $request->from_date) {
            throw ValidationException::withMessages(['to_date' => 'To date must be on or after from date.']);
        }
        $query = $this->branchTransfers($branch)->select(['id', 'reference', 'created_at', 'sender_id', 'recipient_id', 'amount', 'fee', 'total'])
            ->with(['sender:id,name,member_no', 'recipient:id,name,member_no'])
            ->when($request->filled('sender_id'), fn ($q) => $q->where('sender_id', $request->integer('sender_id')))
            ->when($request->filled('recipient_id'), fn ($q) => $q->where('recipient_id', $request->integer('recipient_id')))
            ->when($request->filled('from_date'), fn ($q) => $q->where('created_at', '>=', Carbon::parse($request->from_date)->startOfDay()))
            ->when($request->filled('to_date'), fn ($q) => $q->where('created_at', '<', Carbon::parse($request->to_date)->addDay()->startOfDay()));
        foreach (['sender', 'recipient'] as $relation) {
            if ($request->filled($relation)) {
                $this->filterMember($query, $relation, trim($request->input($relation)));
            }
        }

        return DataTables::eloquent($query)
            ->addColumn('sender_label', fn ($row) => trim(($row->sender?->name ?? '').' / '.($row->sender?->member_no ?? ''), ' /'))
            ->addColumn('recipient_label', fn ($row) => trim(($row->recipient?->name ?? '').' / '.($row->recipient?->member_no ?? ''), ' /'))
            ->filterColumn('sender_label', fn ($q, $value) => $this->filterMember($q, 'sender', $value))
            ->filterColumn('recipient_label', fn ($q, $value) => $this->filterMember($q, 'recipient', $value))
            ->editColumn('created_at', fn ($row) => $row->created_at->format('Y-m-d H:i:s'))
            ->toJson();
    }

    private function branchTransfers(?int $branch)
    {
        return MembershipTransfer::where('channel', 'transfer')->where(function ($q) use ($branch) {
            $q->whereIn('voucher_id', Voucher::where('sister_concern_id', $branch)->select('id'))
                ->orWhereIn('recipient_voucher_id', Voucher::where('sister_concern_id', $branch)->select('id'));
        });
    }

    private function filterMember($query, string $relation, string $value): void
    {
        $query->whereHas($relation, fn ($q) => $q->where(fn ($member) => $member
            ->where('name', 'like', '%'.$value.'%')
            ->orWhere('member_no', 'like', '%'.$value.'%')
            ->orWhere('mobile_no', 'like', '%'.$value.'%')));
    }
}
