<?php

namespace App\Http\Controllers\ContractorManagement;

use App\Http\Controllers\Controller;
use App\Models\ContractorManagement\ContractorPayment;
use Illuminate\Http\Request;
use App\Models\ContractorManagement\Contractor;
use App\Models\ContractorManagement\ContractorBill;
use App\Models\ContractorManagement\ContractorBillDetail;
use App\Models\Account\AccountHead;
use App\Models\Account\AccountGroup;
use App\Models\Account\Transaction;
use App\Enumeration\ReservedAccountGroup;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function expenseHeads(Request $request)
    {
        $group = AccountGroup::where('reserved_id', ReservedAccountGroup::OPERATING_EXPENSES)->first();
        $expenseHeads = [];
        $start_date = null;
        $end_date = null;

        if ($request->has('search')) {
            $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);

            $start_date = date('Y-m-d', strtotime($request->start_date));
            $end_date = date('Y-m-d', strtotime($request->end_date));

            $expenseHeads = ContractorBillDetail::join('contractor_bills', 'contractor_bills.id', '=', 'contractor_bill_details.contractor_bill_id')
                ->join('account_heads', 'account_heads.id', '=', 'contractor_bill_details.expense_head_id')
                ->where('contractor_bills.sister_concern_id', auth()->user()->activeConcernId())
                ->whereBetween('contractor_bills.bill_date', [$start_date, $end_date])
                ->select('account_heads.name', 'account_heads.code', DB::raw('SUM(contractor_bill_details.amount) as total_amount'))
                ->groupBy('account_heads.id', 'account_heads.name', 'account_heads.code')
                ->get();

            return view('contractor_management.reports.expense_heads_report', compact('expenseHeads', 'start_date', 'end_date'));
        }

        return view('contractor_management.reports.expense_heads');
    }

    public function expenseHeadsList(Request $request)
    {
        $group = AccountGroup::where('reserved_id', ReservedAccountGroup::OPERATING_EXPENSES)->first();
        
        $expenseHeads = AccountHead::where('account_group_id', $group->id)
            ->where(function ($q) {
                $q->whereNull('sister_concern_id')->orWhere('sister_concern_id', auth()->user()->activeConcernId());
            })
            ->get();
            
        return view('contractor_management.reports.expense_heads_list_report', compact('expenseHeads'));
    }

    public function incomeHeadsList(Request $request)
    {
        $group = AccountGroup::where('reserved_id', ReservedAccountGroup::OTHER_INCOME)->first();
        
        $incomeHeads = AccountHead::where('account_group_id', $group->id)
            ->where(function ($q) {
                $q->whereNull('sister_concern_id')->orWhere('sister_concern_id', auth()->user()->activeConcernId());
            })
            ->get();
            
        return view('contractor_management.reports.income_heads_list_report', compact('incomeHeads'));
    }

    public function outstanding(Request $request)
    {
        if ($request->has('search')) {
            $contractors = Contractor::with(['contractorType'])
                ->withSum(['transactions as total_debit' => function ($q) {
                    $q->where('transaction_type', 1);
                }], 'amount')
                ->withSum(['transactions as total_credit' => function ($q) {
                    $q->where('transaction_type', 2);
                }], 'amount')
                ->where('origin_sister_concern_id', auth()->user()->activeConcernId())
                ->where('status', 1)
                ->get();
                
            return view('contractor_management.reports.outstanding_report', compact('contractors'));
        }
        
        return view('contractor_management.reports.outstanding');
    }
    
    public function billSummary(Request $request)
    {
        $bills = [];
        $start_date = null;
        $end_date = null;

        if ($request->has('search')) {
            $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);

            $start_date = date('Y-m-d', strtotime($request->start_date));
            $end_date = date('Y-m-d', strtotime($request->end_date));

            $bills = ContractorBill::with('contractor')
                ->where('sister_concern_id', auth()->user()->activeConcernId())
                ->whereBetween('bill_date', [$start_date, $end_date])
                ->orderBy('bill_date', 'asc')
                ->get();

            return view('contractor_management.reports.bill_summary_report', compact('bills', 'start_date', 'end_date'));
        }

        return view('contractor_management.reports.bill_summary');
    }

    public function ledger(Request $request)
    {
        $contractors = Contractor::where('status', 1)->where(function($q) {
            $q->whereNull('origin_sister_concern_id')->orWhere('origin_sister_concern_id', auth()->user()->activeConcernId());
        })->get();

        $transactions = [];
        $openingBalance = 0;
        $contractor = null;
        $start_date = null;
        $end_date = null;

        if ($request->has('search')) {
            $request->validate([
                'contractor_id' => 'required|exists:contractors,id',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);

            $contractor = Contractor::findOrFail($request->contractor_id);
            $start_date = date('Y-m-d', strtotime($request->start_date));
            $end_date = date('Y-m-d', strtotime($request->end_date));

            $previousTransactions = Transaction::where('account_head_id', $contractor->account_head_id)
                ->where(function($q) use ($start_date) {
                    $q->where('date', '<', $start_date)
                      ->orWhere('is_opening_balance', 1);
                })
                ->get();

            $prevDebit = $previousTransactions->where('transaction_type', 1)->sum('amount');
            $prevCredit = $previousTransactions->where('transaction_type', 2)->sum('amount');
            
            $openingBalance = $prevCredit - $prevDebit;

            $transactions = Transaction::where('account_head_id', $contractor->account_head_id)
                ->where('is_opening_balance', 0)
                ->whereBetween('date', [$start_date, $end_date])
                ->orderBy('date', 'asc')
                ->get();
                
            foreach ($transactions as $transaction) {
                $route = null;
                if ($transaction->voucher_id) {
                    $bill = ContractorBill::where('voucher_id', $transaction->voucher_id)->first();
                    if ($bill) {
                        $route = route('contractor_management.bills.show', $bill->id);
                    } else {
                        $payment = ContractorPayment::where('voucher_id', $transaction->voucher_id)->first();
                        if ($payment) {
                            $route = route('contractor_management.payments.show', $payment->id);
                        }
                    }
                }
                
                // Fallback for older transactions
                if (!$route && $transaction->notes) {
                    if (preg_match('/Bill No:\s*([^\s]+)/', $transaction->notes, $matches)) {
                        $bill = \App\Models\ContractorBill::where('bill_no', $matches[1])->first();
                        if ($bill) $route = route('contractor_management.bills.show', $bill->id);
                    } elseif (preg_match('/Receipt No:\s*([^\s]+)/', $transaction->notes, $matches)) {
                        $payment = \App\Models\ContractorPayment::where('receipt_no', $matches[1])->first();
                        if ($payment) $route = route('contractor_management.payments.show', $payment->id);
                    }
                }
                
                $transaction->show_url = $route;
            }
                
            return view('contractor_management.reports.ledger_report', compact('contractors', 'transactions', 'openingBalance', 'contractor', 'start_date', 'end_date'));
        }

        return view('contractor_management.reports.ledger', compact('contractors'));
    }
}
