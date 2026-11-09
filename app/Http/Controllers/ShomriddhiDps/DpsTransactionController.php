<?php

namespace App\Http\Controllers\ShomriddhiDps;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ShomriddhiDps\DpsAccount;
use App\Models\ShomriddhiDps\DpsTransaction;
use App\Models\Account\AccountHead;
use App\Services\ShomriddhiDps\DpsAccountService;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use SakibRahaman\DecimalToWords\DecimalToWords;

class DpsTransactionController extends Controller
{
    public function __construct(private DpsAccountService $service) {}

    public function index()
    {
        $accounts = DpsAccount::with('customer')
            ->where('origin_sister_concern_id', auth()->user()->activeConcernId())
            ->get();
        return view('shomriddhi_dps.transactions.index', compact('accounts'));
    }

    public function create()
    {
        $accounts = DpsAccount::with('customer')->where('status', 'Active')
            ->where('origin_sister_concern_id', auth()->user()->activeConcernId())
            ->get();
            
        $paymentModes = AccountHead::where(function ($q) {
            $q->whereNull('sister_concern_id')
              ->orWhere('sister_concern_id', auth()->user()->activeConcernId());
        })->where('payment_mode', '>', 0)->where('status', 1)->get();
        
        return view('shomriddhi_dps.transactions.create', compact('accounts', 'paymentModes'));
    }

    public function dataTable(Request $request)
    {
        $query = DpsTransaction::with('dpsAccount.customer')
            ->join('dps_accounts', 'dps_transactions.dps_account_id', '=', 'dps_accounts.id')
            ->where('dps_accounts.origin_sister_concern_id', auth()->user()->activeConcernId())
            ->select('dps_transactions.*')
            ->orderBy('dps_transactions.id', 'desc');

        if ($request->has('account_id') && $request->account_id != '') {
            $query->where('dps_transactions.dps_account_id', $request->account_id);
        }
        
        if ($request->has('status') && $request->status != '') {
            $query->where('dps_transactions.status', $request->status);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('dps_no', fn($row) => e($row->dpsAccount->dps_no ?? '-'))
            ->addColumn('customer', fn($row) => e($row->dpsAccount->customer->name ?? '-'))
            ->addColumn('installment_no', function($row) {
                if (!$row->installment_no) return '-';
                $count = $row->dpsAccount && $row->dpsAccount->installment_amount > 0 
                            ? floor($row->amount / $row->dpsAccount->installment_amount) 
                            : 1;
                if ($count > 1) {
                    $end = $row->installment_no + $count - 1;
                    return "#{$row->installment_no} - #{$end}";
                }
                return "#{$row->installment_no}";
            })
            ->addColumn('type', function ($row) {
                return match($row->type) {
                    'Installment'         => '<span class="badge badge-success">Installment</span>',
                    'Penalty'             => '<span class="badge badge-warning">Penalty</span>',
                    'Encashment'          => '<span class="badge badge-primary">Encashment</span>',
                    'Premature_Encashment'=> '<span class="badge badge-danger">Premature Encashment</span>',
                    default               => '<span class="badge badge-secondary">' . e($row->type) . '</span>',
                };
            })
            ->addColumn('amount', fn($row) => '৳ ' . number_format($row->amount, 2))
            ->addColumn('penalty_amount', fn($row) => $row->penalty_amount > 0 ? '<span class="text-danger">৳ ' . number_format($row->penalty_amount, 2) . '</span>' : '-')
            ->addColumn('date', fn($row) => Carbon::parse($row->date)->format('d-M-Y'))
            ->addColumn('status', function ($row) {
                return match($row->status) {
                    'Pending'  => '<span class="badge badge-warning">Pending</span>',
                    'Approved' => '<span class="badge badge-success">Approved</span>',
                    'Rejected' => '<span class="badge badge-danger">Rejected</span>',
                    default    => '<span class="badge badge-secondary">' . e($row->status) . '</span>',
                };
            })
            ->addColumn('action', function ($row) {
                $btn = '<a href="' . route('shomriddhi-dps.transactions.print_voucher', $row->id) . '" target="_blank" class="btn btn-info btn-sm mr-1" title="Print Voucher"><i class="fa fa-print"></i></a>';
                
                if ($row->status === 'Pending') {
                    $btn .= '<button type="button" class="btn btn-success btn-sm btn-approve" data-id="' . $row->id . '" title="Approve Transaction"><i class="fa fa-check-circle"></i></button>';
                }
                
                return $btn;
            })
            ->rawColumns(['type', 'penalty_amount', 'status', 'action'])
            ->make(true);
    }

    public function payInstallment(Request $request, DpsAccount $account)
    {
        if ($account->status !== 'Active') {
            return response()->json(['success' => false, 'message' => 'Only Active accounts can receive installment payments.']);
        }

        $request->validate([
            'payment_mode'           => 'required|exists:account_heads,id',
            'date'                   => 'required|date',
            'number_of_installments' => 'required|integer|min:1',
            'penalty_collected'      => 'nullable|numeric|min:0',
        ]);

        $result = $this->service->payInstallment(
            $account,
            (int) $request->payment_mode,
            $request->date,
            (int) $request->number_of_installments,
            (float) $request->penalty_collected,
            $request->note
        );

        return response()->json($result);
    }

    public function printVoucher(DpsTransaction $transaction)
    {
        $transaction->load(['dpsAccount.customer', 'paymentHead']);
        
        $totalAmount = $transaction->amount + $transaction->penalty_amount;
        $amount_in_word = DecimalToWords::convert($totalAmount, 'Taka', 'Poisa');

        return view('shomriddhi_dps.transactions.voucher', compact('transaction', 'amount_in_word'));
    }

    public function approve(DpsTransaction $transaction)
    {
        if ($transaction->status !== 'Pending') {
            return response()->json(['success' => false, 'message' => 'Transaction is not pending.']);
        }

        $result = $this->service->approveTransaction($transaction);
        return response()->json($result);
    }
}
