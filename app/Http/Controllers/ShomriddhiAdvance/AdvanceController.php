<?php

namespace App\Http\Controllers\ShomriddhiAdvance;

use App\Http\Controllers\Controller;
use App\Models\ShomriddhiAdvance\ShomriddhiTransaction;
use App\Models\Account\AccountHead;
use App\Models\Supplier;
use App\Services\ShomriddhiAdvance\ShomriddhiAdvanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use App\Models\ShomriddhiAdvance\ShomriddhiAccount;

class AdvanceController extends Controller
{
    public function __construct(protected ShomriddhiAdvanceService $service) {}

    public function index()
    {
        // Same pattern as ReceiptPaymentController — accounts with payment_mode > 0 are Cash/Bank/MFS heads
        $paymentModes = AccountHead::where(function ($q) {
            $q->whereNull('sister_concern_id')
              ->orWhere('sister_concern_id', auth()->user()->activeConcernId());
        })->where('payment_mode', '>', 0)->where('status', 1)->get();

        return view('shomriddhi_advance.index', compact('paymentModes'));
    }

    public function dataTable()
    {
        $query = ShomriddhiTransaction::withTrashed()
            ->with(['customer', 'creator'])
            ->join('shomriddhi_accounts', 'shomriddhi_transactions.customer_id', '=', 'shomriddhi_accounts.customer_id')
            ->where('shomriddhi_accounts.origin_sister_concern_id', auth()->user()->activeConcernId())
            ->select('shomriddhi_transactions.*')
            ->orderBy('shomriddhi_transactions.id', 'desc');

        if (request()->has('status') && request('status') != '') {
            if (request('status') === 'Deleted') {
                $query->whereNotNull('shomriddhi_transactions.deleted_at');
            } else {
                $query->where('shomriddhi_transactions.status', request('status'))
                      ->whereNull('shomriddhi_transactions.deleted_at');
            }
        } else {
            $query->whereNull('shomriddhi_transactions.deleted_at');
        }

        if (request()->has('type') && request('type') != '') {
            $query->where('shomriddhi_transactions.type', request('type'));
        }

        if (request()->has('start_date') && request()->has('end_date') && request('start_date') != '' && request('end_date') != '') {
            $startDate = Carbon::parse(request('start_date'))->format('Y-m-d');
            $endDate = Carbon::parse(request('end_date'))->format('Y-m-d');
            $query->whereBetween('shomriddhi_transactions.date', [$startDate, $endDate]);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('customer_name', fn($row) => $row->customer
                ? $row->customer->member_no . ' - ' . $row->customer->name
                : 'N/A')
            ->filterColumn('customer_name', function($query, $keyword) {
                $query->whereHas('customer', function($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%")
                      ->orWhere('member_no', 'like', "%{$keyword}%");
                });
            })
            ->addColumn('type_badge', function($row) {
                if ($row->type == 'deposit') return '<span class="badge badge-success"><i class="fa fa-arrow-down"></i> Deposit</span>';
                if ($row->type == 'interest_capitalization') return '<span class="badge badge-info"><i class="fa fa-star"></i> Interest</span>';
                return '<span class="badge badge-danger"><i class="fa fa-arrow-up"></i> Withdraw</span>';
            })
            ->addColumn('amount_formatted', fn($row) => '৳ ' . number_format($row->amount, 2))
            ->addColumn('status_badge', function ($row) {
                if ($row->trashed()) {
                    return '<span class="badge badge-dark">Deleted</span>';
                }
                return match($row->status) {
                    'Pending'  => '<span class="badge badge-warning">Pending</span>',
                    'Approved' => '<span class="badge badge-success">Approved</span>',
                    default    => '<span class="badge badge-danger">Rejected</span>',
                };
            })
            ->addColumn('audit_info', function ($row) {
                $created = $row->creator->name ?? 'Unknown';
                $approved = $row->approver->name ?? 'Not Approved';
                $info = "<div><small><b>Created By:</b> $created</small><br><small><b>Approved By:</b> $approved</small>";
                if ($row->trashed()) {
                    $deleter = \App\Models\User::find($row->deleted_by)->name ?? 'Unknown';
                    $info .= "<br><small class='text-danger'><b>Deleted By:</b> $deleter</small>";
                }
                return $info . "</div>";
            })
            ->addColumn('action', function ($row) {
                if ($row->trashed()) return '-';
                if ($row->status === 'Pending') {
                    $customer = e($row->customer ? $row->customer->name : 'N/A');
                    $type     = ucfirst($row->type);
                    $amount   = number_format($row->amount, 2);
                    return '
                        <button class="btn btn-xs btn-success btn-approve mr-1"
                            data-id="' . $row->id . '"
                            data-customer="' . $customer . '"
                            data-type="' . $type . '"
                            data-amount="' . $amount . '">
                            <i class="fa fa-check"></i> Approve
                        </button>
                        <button class="btn btn-xs btn-danger btn-reject" data-id="' . $row->id . '">
                            <i class="fa fa-times"></i> Reject
                        </button>';
                } elseif ($row->status === 'Approved') {
                    return '<button class="btn btn-xs btn-danger btn-delete" data-id="' . $row->id . '">
                                <i class="fa fa-trash"></i> Delete
                            </button>';
                }
                return '-';
            })
            ->rawColumns(['customer_name', 'type_badge', 'status_badge', 'audit_info', 'action'])
            ->make(true);
    }

    public function create()
    {
        $accounts = ShomriddhiAccount::with('customer')
            ->where('origin_sister_concern_id', auth()->user()->activeConcernId())
            ->get();
        
        return view('shomriddhi_advance.create', compact('accounts'));
    }

    public function getCustomerInfo(Request $request)
    {
        $customer = Supplier::find($request->customer_id);
        if (!$customer) {
            return response()->json(['success' => false]);
        }

        $shomriddhiAccount = ShomriddhiAccount::where('customer_id', $customer->id)
            ->where('origin_sister_concern_id', auth()->user()->activeConcernId())
            ->first();

        $balance = $shomriddhiAccount ? $shomriddhiAccount->principal_balance : 0;
        $accrued_interest = $shomriddhiAccount ? $shomriddhiAccount->accrued_interest : 0;

        return response()->json([
            'success'   => true,
            'member_no' => $customer->member_no,
            'mobile'    => $customer->mobile_no,
            'balance'   => $balance,
            'accrued_interest' => $accrued_interest,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:suppliers,id',
            'type'        => 'required|in:deposit,withdraw',
            'amount'      => 'required|numeric|min:1',
            'date'        => 'required|date',
            'note'        => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            ShomriddhiTransaction::create([
                'customer_id' => $request->customer_id,
                'type'        => $request->type,
                'amount'      => $request->amount,
                'date'        => Carbon::parse($request->date)->format('Y-m-d'),
                'note'        => $request->note,
                'status'      => 'Pending',
                'created_by'  => auth()->id(),
            ]);
            DB::commit();

            return redirect()->route('shomriddhi-advance.index')
                ->with('success', 'Transaction saved as Pending. Waiting for approval.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function approve(ShomriddhiTransaction $advance, Request $request)
    {
        if ($advance->status !== 'Pending') {
            return response()->json(['success' => false, 'message' => 'Transaction is not pending.']);
        }

        $request->validate(['payment_mode' => 'required']);

        [$paymentAccountHeadId] = explode('|', $request->payment_mode);

        $result = $this->service->approve($advance, (int) $paymentAccountHeadId);
        return response()->json($result);
    }

    public function reject(ShomriddhiTransaction $advance)
    {
        if ($advance->status !== 'Pending') {
            return response()->json(['success' => false, 'message' => 'Transaction is not pending.']);
        }

        $result = $this->service->reject($advance);
        return response()->json($result);
    }

    public function destroy(ShomriddhiTransaction $advance)
    {
        if ($advance->status !== 'Approved') {
            return response()->json(['success' => false, 'message' => 'Only Approved transactions can be deleted.']);
        }

        $result = $this->service->deleteTransaction($advance);
        return response()->json($result);
    }
}
