<?php

namespace App\Http\Controllers\ShomriddhiFdr;

use App\Http\Controllers\Controller;
use App\Models\BranchManagement\SisterConcern;
use App\Models\ShomriddhiFdr\FdrEncashmentRequest;
use App\Services\ShomriddhiFdr\FdrAccountService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class EncashmentRequestController extends Controller
{
    public function index(Request $r)
    {
        $this->filters($r);

        return view('shomriddhi_fdr.encashment_requests');
    }

    private function filters(Request $r): array
    {
        return $r->validate(['status' => 'nullable|in:Pending,Approved,Rejected', 'start_date' => 'nullable|date', 'end_date' => array_merge(['nullable', 'date'], $r->filled('start_date') ? ['after_or_equal:start_date'] : [])]);
    }

    public function dataTable(Request $r)
    {
        $d = $this->filters($r);
        $r->merge(['length' => min(100, max(1, (int) $r->input('length', 10)))]);
        $requests = FdrEncashmentRequest::with(['account', 'customer'])->where('origin_sister_concern_id', auth()->user()->activeConcernId());
        if ($r->filled('status')) {
            $requests->where('status', $d['status']);
        }
        if ($r->filled('start_date')) {
            $requests->whereDate('created_at', '>=', $d['start_date']);
        }
        if ($r->filled('end_date')) {
            $requests->whereDate('created_at', '<=', $d['end_date']);
        }
        $date = SisterConcern::findOrFail(auth()->user()->activeConcernId())->business_date;

        return DataTables::of($requests)
            ->addIndexColumn()
            ->editColumn('created_at', fn ($row) => $row->created_at?->format('d M Y, h:i A'))
            ->addColumn('member', fn ($row) => e($row->customer?->name).'<br>'.e($row->account?->fdr_no))
            ->filterColumn('member', function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->whereHas('customer', fn ($customer) => $customer->where('name', 'like', '%'.$keyword.'%')->orWhere('mobile_no', 'like', '%'.$keyword.'%'))
                        ->orWhereHas('account', fn ($account) => $account->where('fdr_no', 'like', '%'.$keyword.'%'));
                });
            })
            ->addColumn('settlement', function ($row) use ($date) {
                if ($row->status === 'Pending') {
                    try {
                        $row->current_preview = app(FdrAccountService::class)->settlement($row->account, $date);
                    } catch (\Throwable $e) {
                        $row->preview_error = $e->getMessage();
                    }
                }

                return view('shomriddhi_fdr.partials.encashment_preview', ['r' => $row])->render();
            })
            ->editColumn('status', fn ($row) => e($row->status).'<br>'.e($row->review_note))
            ->addColumn('action', fn ($row) => view('shomriddhi_fdr.partials.encashment_action', ['r' => $row])->render())
            ->rawColumns(['member', 'settlement', 'status', 'action'])
            ->make(true);
    }

    public function review(Request $r, int $id, FdrAccountService $service)
    {
        $d = $r->validate(['decision' => 'required|in:approve,reject', 'review_note' => 'nullable|string|max:500']);
        $service->reviewEncash($id, $d['decision'] === 'approve', $d['review_note'] ?? null);

        return back()->with('success', 'Encashment request reviewed.');
    }
}
