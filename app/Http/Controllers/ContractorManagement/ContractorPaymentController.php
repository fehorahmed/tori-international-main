<?php

namespace App\Http\Controllers\ContractorManagement;

use App\Http\Controllers\Controller;
use App\Models\ContractorManagement\Contractor;
use App\Models\ContractorManagement\ContractorPayment;
use App\Models\Account\AccountHead;
use App\Models\Account\AccountGroup;
use App\Models\Account\Voucher;
use App\Models\Account\Transaction;
use App\Enumeration\ReservedAccountGroup;
use App\Enumeration\TransactionType;
use App\Enumeration\VoucherType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class ContractorPaymentController extends Controller
{
    public function index()
    {
        $contractors = Contractor::where('status', 1)->where(function($q) {
            $q->whereNull('origin_sister_concern_id')->orWhere('origin_sister_concern_id', auth()->user()->activeConcernId());
        })->get();
        return view('contractor_management.payments.index', compact('contractors'));
    }

    public function dataTable(Request $request)
    {
        $query = ContractorPayment::with(['contractor', 'paymentMethod'])->where(function($q) {
            $q->whereNull('sister_concern_id')->orWhere('sister_concern_id', auth()->user()->activeConcernId());
        });

        if ($request->contractor_id) {
            $query->where('contractor_id', $request->contractor_id);
        }

        if ($request->start_date && $request->end_date) {
            $startDate = Carbon::parse($request->start_date)->format('Y-m-d');
            $endDate = Carbon::parse($request->end_date)->format('Y-m-d');
            $query->whereBetween('payment_date', [$startDate, $endDate]);
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('contractor_name', function (ContractorPayment $payment) {
                return $payment->contractor->name ?? '';
            })
            ->addColumn('payment_method_name', function (ContractorPayment $payment) {
                return $payment->paymentMethod->name ?? '';
            })
            ->addColumn('payment_type_text', function (ContractorPayment $payment) {
                return $payment->payment_type == 1 ? '<span class="badge badge-success">Payment</span>' : '<span class="badge badge-warning">Advance</span>';
            })
            ->addColumn('payment_date', function(ContractorPayment $payment) {
                return Carbon::parse($payment->payment_date)->format('d-m-Y');
            })
            ->addColumn('action', function(ContractorPayment $payment) {
                $btn = '<a href="'.route('contractor_management.payments.show', $payment->id).'" class="btn btn-info btn-sm bg-gradient-info"><i class="fa fa-eye"></i></a>';
                $btn .= ' <a role="button" data-id="' . $payment->id . '" class="btn btn-danger btn-sm bg-gradient-danger btn-delete"><i class="fa fa-trash"></i></a>';
                return dropdownMenuContainer($btn);
            })
            ->rawColumns(['action', 'payment_type_text'])
            ->toJson();
    }

    public function create()
    {
        $contractors = Contractor::where('status', 1)->where(function($q) {
            $q->whereNull('origin_sister_concern_id')->orWhere('origin_sister_concern_id', auth()->user()->activeConcernId());
        })->get();

        $cashBankGroupIds = AccountGroup::whereIn('reserved_id', [
            ReservedAccountGroup::CASH_AND_BANK,
        ])->pluck('id')->toArray();

        // Get Cash & Bank Heads
        $paymentMethods = AccountHead::whereIn('account_group_id', $cashBankGroupIds)
            ->where(function($q) {
                $q->whereNull('sister_concern_id')->orWhere('sister_concern_id', auth()->user()->activeConcernId());
            })->get();

        return view('contractor_management.payments.create', compact('contractors', 'paymentMethods'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'contractor_id' => 'required|exists:contractors,id',
            'payment_date' => 'required|date',
            'payment_type' => 'required|in:1,2',
            'payment_method_id' => 'required|exists:account_heads,id',
            'amount' => 'required|numeric|min:1',
            'note' => 'nullable|string'
        ]);

        DB::beginTransaction();

        try {
            $payment = new ContractorPayment();
            $payment->contractor_id = $request->contractor_id;
            $payment->sister_concern_id = auth()->user()->activeConcernId();
            $payment->payment_date = Carbon::parse($request->payment_date)->format('Y-m-d');
            $payment->receipt_no = 'CP-' . date('YmdHis') . rand(10, 99);
            $payment->payment_type = $request->payment_type;
            $payment->payment_method_id = $request->payment_method_id;
            $payment->amount = $request->amount;
            $payment->note = $request->note;
            $payment->created_by = auth()->id();
            $payment->save();

            // Post to GL
            $contractor = Contractor::findOrFail($request->contractor_id);
            $paymentMethod = AccountHead::findOrFail($request->payment_method_id);
            
            $voucherNo = generateVoucherNo(VoucherType::$PAYMENT_VOUCHER);
            $voucherNoGroupSl = abs((int) filter_var($voucherNo, FILTER_SANITIZE_NUMBER_INT));

            $typeText = $request->payment_type == 1 ? 'Payment' : 'Advance';

            $voucher = new Voucher();
            $voucher->sister_concern_id = auth()->user()->activeConcernId();
            $voucher->voucher_no = $voucherNo;
            $voucher->voucher_no_group_sl = $voucherNoGroupSl;
            $voucher->voucher_type = VoucherType::$PAYMENT_VOUCHER;
            $voucher->payment_type_id = $paymentMethod->payment_mode;
            $voucher->payment_account_head_id = $paymentMethod->id;
            $voucher->account_head_payee_depositor_id = $contractor->account_head_id;
            $voucher->date = $payment->payment_date;
            $voucher->amount = $payment->amount;
            $voucher->notes = "Contractor {$typeText} Receipt No: " . $payment->receipt_no;
            $voucher->user_id = auth()->id();
            $voucher->save();

            $payment->voucher_id = $voucher->id;
            $payment->save();

            // Debit Contractor (Decrease Accounts Payable)
            $tx1 = new Transaction();
            $tx1->sister_concern_id = auth()->user()->activeConcernId();
            $tx1->voucher_id = $voucher->id;
            $tx1->payment_type_id = $paymentMethod->payment_mode;
            $tx1->payment_account_head_id = $paymentMethod->id;
            $tx1->account_head_id = $contractor->account_head_id;
            $tx1->voucher_type = VoucherType::$PAYMENT_VOUCHER;
            $tx1->transaction_type = TransactionType::$DEBIT;
            $tx1->amount = $payment->amount;
            $tx1->date = $payment->payment_date;
            $tx1->voucher_no = $voucherNo;
            $tx1->voucher_no_group_sl = $voucherNoGroupSl;
            $tx1->notes = "Contractor {$typeText} Receipt No: " . $payment->receipt_no;
            $tx1->user_id = auth()->id();
            $tx1->save();

            // Credit Payment Method (Decrease Cash/Bank)
            $tx2 = new Transaction();
            $tx2->sister_concern_id = auth()->user()->activeConcernId();
            $tx2->voucher_id = $voucher->id;
            $tx2->payment_type_id = $paymentMethod->payment_mode;
            $tx2->payment_account_head_id = $paymentMethod->id;
            $tx2->account_head_id = $paymentMethod->id;
            $tx2->voucher_type = VoucherType::$PAYMENT_VOUCHER;
            $tx2->transaction_type = TransactionType::$CREDIT;
            $tx2->amount = $payment->amount;
            $tx2->date = $payment->payment_date;
            $tx2->voucher_no = $voucherNo;
            $tx2->voucher_no_group_sl = $voucherNoGroupSl;
            $tx2->account_head_payee_depositor_id = $contractor->account_head_id;
            $tx2->notes = "Contractor {$typeText} Receipt No: " . $payment->receipt_no;
            $tx2->user_id = auth()->id();
            $tx2->save();

            DB::commit();
            return redirect()->route('contractor_management.payments.index')->with('success', "Contractor {$typeText} recorded successfully.");
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }
    
    public function show($id)
    {
        $payment = ContractorPayment::with('contractor', 'paymentMethod')->findOrFail($id);
        $voucher = Voucher::find($payment->voucher_id);

        if (!$voucher) {
            // Fallback for old records
            $typeText = $payment->payment_type == 1 ? 'Payment' : 'Advance';
            $voucher = Voucher::where('notes', "Contractor {$typeText} Receipt No: " . $payment->receipt_no)->first();
        }
        
        return view('contractor_management.payments.show', compact('payment', 'voucher'));
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $payment = ContractorPayment::findOrFail($id);
            
            if ($payment->voucher_id) {
                $voucher = Voucher::find($payment->voucher_id);
                if ($voucher) {
                    Transaction::where('voucher_id', $voucher->id)->delete();
                    $voucher->delete();
                }
            } else {
                // Fallback for old records
                $typeText = $payment->payment_type == 1 ? 'Payment' : 'Advance';
                $voucher = Voucher::where('notes', "Contractor {$typeText} Receipt No: " . $payment->receipt_no)->first();
                
                if ($voucher) {
                    Transaction::where('voucher_id', $voucher->id)->delete();
                    $voucher->delete();
                }
            }
            
            $payment->delete();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Contractor payment deleted successfully.']);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
}
