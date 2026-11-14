<?php

namespace App\Http\Controllers\API\Membership;

use App\Http\Controllers\Controller;
use App\Models\ShomriddhiAdvance\ShomriddhiAccount;
use App\Models\ShomriddhiDps\DpsAccount;
use App\Models\ShomriddhiFdr\FdrAccount;
use App\Models\ShomriddhiLoan\LoanAccount;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $customer = KycController::customer($request);

        // 1. Shomriddhi / Savings Account
        $savingsAccount = ShomriddhiAccount::where('customer_id', $customer->id)
            ->where('status', 'Active')
            ->first();

        $savingsBalance = $savingsAccount ? (float) $savingsAccount->principal_balance : 0.00;

        // 2. Loan Accounts & Active Due
        $activeLoans = LoanAccount::where('customer_id', $customer->id)
            ->whereIn('status', ['Active', 'Disbursed'])
            ->get();
        $totalLoanDue = $activeLoans->sum('total_outstanding') ?: 0.00;

        // Next upcoming loan EMI installment
        $upcomingEmi = null;
        if ($activeLoans->isNotEmpty()) {
            $firstLoan = $activeLoans->first();
            $nextInstallment = $firstLoan->installments()
                ->where('status', 'Pending')
                ->orderBy('due_date', 'asc')
                ->first();

            if ($nextInstallment) {
                $upcomingEmi = [
                    'loan_account_no' => $firstLoan->account_no,
                    'amount' => (float) $nextInstallment->installment_amount,
                    'due_date' => $nextInstallment->due_date,
                ];
            }
        }

        // 3. DPS Summary
        $dpsAccounts = DpsAccount::where('customer_id', $customer->id)
            ->where('status', 'Active')
            ->get();
        $totalDpsBalance = $dpsAccounts->sum('total_deposited') ?: 0.00;

        // 4. FDR Summary
        $fdrAccounts = FdrAccount::where('customer_id', $customer->id)
            ->where('status', 'Active')
            ->get();
        $totalFdrBalance = $fdrAccounts->sum('principal_amount') ?: 0.00;

        return response()->json([
            'success' => true,
            'data' => [
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'member_no' => $customer->member_no ?: 'CUST-' . str_pad($customer->id, 5, '0', STR_PAD_LEFT),
                    'mobile_no' => $customer->mobile_no,
                    'email' => $customer->email,
                    'image' => $customer->image ? asset($customer->image) : null,
                    'is_verified' => true,
                ],
                'summary' => [
                    'savings_balance' => (float) $savingsBalance,
                    'available_savings_balance' => $savingsAccount ? \App\Services\Membership\CashoutService::available($savingsAccount) / 100 : 0,
                    'minimum_savings_balance' => \App\Services\Membership\CashoutService::minimumBalance() / 100,
                    'held_savings_balance' => $savingsAccount ? \App\Models\MembershipCashout::held($savingsAccount->id) / 100 : 0,
                    'dps_balance' => (float) $totalDpsBalance,
                    'fdr_balance' => (float) $totalFdrBalance,
                    'total_assets' => (float) ($savingsBalance + $totalDpsBalance + $totalFdrBalance),
                    'loan_due' => (float) $totalLoanDue,
                    'active_dps_count' => $dpsAccounts->count(),
                    'active_fdr_count' => $fdrAccounts->count(),
                    'active_loans_count' => $activeLoans->count(),
                ],
                'recent_transactions' => app(\App\Services\Membership\RecentTransactionService::class)->forCustomer($customer),
                'upcoming_loan_emi' => $upcomingEmi,
            ]
        ]);
    }
}
