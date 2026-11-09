<?php

namespace App\Http\Controllers\Purchase;

use App\Enumeration\PaymentPurpose;
use App\Enumeration\TransactionType;
use App\Enumeration\VoucherType;
use App\Http\Controllers\Controller;
use App\Models\Account\AccountHead;
use App\Models\Account\Transaction;
use App\Models\Account\Voucher;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupplierReceiptController extends Controller
{
    public function create(Request $request)
    {
        $supplier = Supplier::findOrFail($request->supplier_id);
        
        $paymentModes = AccountHead::where(function($q) { 
                $q->whereNull('sister_concern_id')->orWhere('sister_concern_id', auth()->user()->activeConcernId()); 
            })
            ->where('payment_mode', '>', 0)
            ->where('status', 1)
            ->get();
            
        return view('purchase.supplier_receipt._receipt_form', compact('supplier', 'paymentModes'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('purchase_payment')) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'date' => 'required|date',
            'payment_mode' => 'required',
            'amount' => 'required|numeric|min:0.01',
        ]);

        $supplier = Supplier::findOrFail($request->supplier_id);
        $amount = (float) $request->amount;
        $date = Carbon::parse($request->date)->format('Y-m-d');
        
        $payment_mode_data = explode('|', $request->payment_mode);
        $payment_account_head_id = $payment_mode_data[0];
        $payment_mode_type = $payment_mode_data[1];

        $receiptAccountHead = AccountHead::findOrFail($payment_account_head_id);
        $supplierAccountHead = AccountHead::where('head_type', 'primary')
                                          ->where('supplier_id', $supplier->id)
                                          ->first();
        if(!$supplierAccountHead) {
            return response()->json([
                'status' => false,
                'message' => "Supplier Account Head not found!",
            ]);
        }

        DB::beginTransaction();

        try {
            $voucherNo = generateVoucherNo(VoucherType::$COLLECTION_VOUCHER);
            $voucherNoGroupSl = abs((int) filter_var($voucherNo, FILTER_SANITIZE_NUMBER_INT));

            $voucher = new Voucher();
            $voucher->sister_concern_id = auth()->user()->activeConcernId();
            $voucher->voucher_type = VoucherType::$COLLECTION_VOUCHER;
            $voucher->amount = $amount;
            $voucher->date = $date;
            $voucher->voucher_no = $voucherNo;
            $voucher->voucher_no_group_sl = $voucherNoGroupSl;
            $voucher->account_head_payee_depositor_id = $supplierAccountHead->id;
            $voucher->payment_type_id = $payment_mode_type;
            $voucher->payment_account_head_id = $payment_account_head_id;
            $voucher->cheque_no = $request->cheque_no;
            $voucher->payment_purpose = PaymentPurpose::$SUPPLIER_RECEIPT;
            $voucher->user_id = auth()->user()->id;
            $voucher->notes = $request->note ?? 'Supplier advance return or refund.';
            $voucher->save();

            // 1. Debit Cash/Bank Account (Money coming in)
            $receiptTrans = new Transaction();
            $receiptTrans->sister_concern_id = auth()->user()->activeConcernId();
            $receiptTrans->voucher_id = $voucher->id;
            $receiptTrans->account_head_id = $receiptAccountHead->id; // Debit
            $receiptTrans->transaction_type = TransactionType::$DEBIT;
            $receiptTrans->payment_purpose = PaymentPurpose::$SUPPLIER_RECEIPT;
            $receiptTrans->amount = $amount;
            $receiptTrans->date = $date;
            $receiptTrans->voucher_no = $voucherNo;
            $receiptTrans->voucher_no_group_sl = $voucherNoGroupSl;
            $receiptTrans->voucher_type = VoucherType::$COLLECTION_VOUCHER;
            $receiptTrans->notes = $request->note;
            $receiptTrans->cheque_no = $request->cheque_no;
            $receiptTrans->account_head_payee_depositor_id = $supplierAccountHead->id;
            $receiptTrans->user_id = auth()->user()->id;
            $receiptTrans->save();

            // 2. Credit Supplier Account (Reduces advance balance or increases payable)
            $supplierTrans = new Transaction();
            $supplierTrans->sister_concern_id = auth()->user()->activeConcernId();
            $supplierTrans->voucher_id = $voucher->id;
            $supplierTrans->account_head_id = $supplierAccountHead->id; // Credit
            $supplierTrans->transaction_type = TransactionType::$CREDIT;
            $supplierTrans->payment_purpose = PaymentPurpose::$SUPPLIER_RECEIPT;
            $supplierTrans->amount = $amount;
            $supplierTrans->date = $date;
            $supplierTrans->voucher_no = $voucherNo;
            $supplierTrans->voucher_no_group_sl = $voucherNoGroupSl;
            $supplierTrans->voucher_type = VoucherType::$COLLECTION_VOUCHER;
            $supplierTrans->notes = $request->note;
            $supplierTrans->account_head_payee_depositor_id = $receiptAccountHead->id;
            $supplierTrans->user_id = auth()->user()->id;
            $supplierTrans->save();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Receipt processed successfully.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong: ' . $e->getMessage(),
            ]);
        }
    }
}
