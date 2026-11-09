<?php

namespace App\Http\Controllers\Microfinance;

use App\Enumeration\TransactionType;
use App\Enumeration\VoucherType;
use App\Http\Controllers\Controller;
use App\Models\Account\AccountHead;
use App\Models\Microfinance\MfInstallment;
use App\Models\Microfinance\MfLoan;
use App\Models\Microfinance\MfSavingsAccount;
use App\Models\Microfinance\MfSavingsTransaction;
use App\Models\Account\Transaction;
use App\Models\Account\Voucher;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DayClosingController extends Controller
{
    public function index(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_day_closing'), 403, 'Unauthorized');
        $pageTitle = 'End of Day (EOD) Day Closing';
        $scId = auth()->user()->activeConcernId();
        $date = $request->date ?? date('Y-m-d');

        // Check if already closed
        $alreadyClosed = Voucher::where('sister_concern_id', $scId)
            ->where('date', $date)
            ->where('notes', 'LIKE', 'Daily Microfinance Closing%')
            ->exists();

        // 1. Savings Deposits
        $savingsDeposits = MfSavingsTransaction::whereHas('savingsAccount', function($q) use ($scId) {
                $q->where('sister_concern_id', $scId);
            })
            ->where('date', $date)
            ->where('transaction_type', 1)
            ->sum('amount');

        // 2. Savings Withdrawals
        $savingsWithdrawals = MfSavingsTransaction::whereHas('savingsAccount', function($q) use ($scId) {
                $q->where('sister_concern_id', $scId);
            })
            ->where('date', $date)
            ->where('transaction_type', 2)
            ->sum('amount');

        // 3. Savings Interest Posted
        $savingsInterest = MfSavingsTransaction::whereHas('savingsAccount', function($q) use ($scId) {
                $q->where('sister_concern_id', $scId);
            })
            ->where('date', $date)
            ->where('transaction_type', 3)
            ->sum('amount');

        // 4. Loans Disbursed
        $loans = MfLoan::where('sister_concern_id', $scId)
            ->where('disbursement_date', $date)
            ->where('status', 3)
            ->get();

        $loansDisbursed = $loans->sum('approved_amount');
        $loanFees = $loans->sum(fn($l) => ($l->processing_fee ?? 0) + ($l->insurance_amount ?? 0));
        $netDisbursed = $loans->sum('net_disbursed_amount');

        // 5. Loan Collections
        $installments = MfInstallment::where('sister_concern_id', $scId)
            ->where('paid_date', $date)
            ->get();

        $loanCollections = $installments->sum('paid_amount');
        $collectedPrincipal = 0;
        $collectedInterest = 0;

        foreach ($installments as $ins) {
            if ($ins->total_due > 0) {
                $ratio = $ins->principal_amount / $ins->total_due;
                $collectedPrincipal += $ins->paid_amount * $ratio;
                $collectedInterest += $ins->paid_amount * (1 - $ratio);
            } else {
                $collectedPrincipal += $ins->paid_amount;
            }
        }

        // 6. Savings Account Opening Fees (Collected separately in cash for accounts approved on this date)
        $openingFeesCollected = MfSavingsAccount::where('sister_concern_id', $scId)
            ->where('status', 1) // Approved/Active
            ->whereDate('updated_at', $date) // Date approved
            ->get()
            ->sum(fn($acc) => floatval($acc->product->opening_fee) ?: 0);

        // 7. Savings Penalties & Forfeited Reversals (FDR/DPS Early Closures etc.)
        $savingsPenalties = MfSavingsTransaction::whereHas('savingsAccount', function($q) use ($scId) {
                $q->where('sister_concern_id', $scId);
            })
            ->where('date', $date)
            ->where('transaction_type', 4) // TYPE_PENALTY
            ->sum('amount');

        // Cash/Bank account heads for selection
        $cashAccounts = AccountHead::whereIn('payment_mode', [1, 2]) // Cash / Bank
            ->get();

        return view('microfinance.day_closing.index', compact(
            'pageTitle',
            'date',
            'alreadyClosed',
            'savingsDeposits',
            'savingsWithdrawals',
            'savingsInterest',
            'savingsPenalties',
            'openingFeesCollected',
            'loansDisbursed',
            'loanFees',
            'netDisbursed',
            'loanCollections',
            'collectedPrincipal',
            'collectedInterest',
            'cashAccounts'
        ));
    }

    public function store(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_day_closing_execute'), 403, 'Unauthorized');
        $scId = auth()->user()->activeConcernId();

        $request->validate([
            'date' => 'required|date',
            'cash_account_id' => [
                'required',
                \Illuminate\Validation\Rule::exists('account_heads', 'id')->where('sister_concern_id', $scId),
            ],
        ]);

        $date = $request->date;
        $cashAccountId = $request->cash_account_id;

        // Double post guard
        $exists = Voucher::where('sister_concern_id', $scId)
            ->where('date', $date)
            ->where('notes', 'LIKE', 'Daily Microfinance Closing%')
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'Day closing has already been posted for this date.');
        }

        // Fetch aggregates
        // 1. Savings Deposits
        $savingsDeposits = MfSavingsTransaction::whereHas('savingsAccount', function($q) use ($scId) {
                $q->where('sister_concern_id', $scId);
            })
            ->where('date', $date)
            ->where('transaction_type', 1)
            ->sum('amount');

        // 2. Savings Withdrawals
        $savingsWithdrawals = MfSavingsTransaction::whereHas('savingsAccount', function($q) use ($scId) {
                $q->where('sister_concern_id', $scId);
            })
            ->where('date', $date)
            ->where('transaction_type', 2)
            ->sum('amount');

        // 3. Savings Interest Posted
        $savingsInterest = MfSavingsTransaction::whereHas('savingsAccount', function($q) use ($scId) {
                $q->where('sister_concern_id', $scId);
            })
            ->where('date', $date)
            ->where('transaction_type', 3)
            ->sum('amount');

        // 4. Loans Disbursed
        $loans = MfLoan::where('sister_concern_id', $scId)
            ->where('disbursement_date', $date)
            ->where('status', 3)
            ->get();

        $loansDisbursed = $loans->sum('approved_amount');
        $loanFees = $loans->sum(fn($l) => ($l->processing_fee ?? 0) + ($l->insurance_amount ?? 0));
        $netDisbursed = $loans->sum('net_disbursed_amount');

        // 5. Loan Collections
        $installments = MfInstallment::where('sister_concern_id', $scId)
            ->where('paid_date', $date)
            ->get();

        $loanCollections = $installments->sum('paid_amount');
        $collectedPrincipal = 0;
        $collectedInterest = 0;

        foreach ($installments as $ins) {
            if ($ins->total_due > 0) {
                $ratio = $ins->principal_amount / $ins->total_due;
                $collectedPrincipal += $ins->paid_amount * $ratio;
                $collectedInterest += $ins->paid_amount * (1 - $ratio);
            } else {
                $collectedPrincipal += $ins->paid_amount;
            }
        }

        // 6. Savings Account Opening Fees (Collected separately in cash for accounts approved on this date)
        $openingFeesCollected = MfSavingsAccount::where('sister_concern_id', $scId)
            ->where('status', 1) // Approved/Active
            ->whereDate('updated_at', $date) // Date approved
            ->get()
            ->sum(fn($acc) => floatval($acc->product->opening_fee) ?: 0);

        // 7. Savings Penalties & Forfeited Reversals (FDR/DPS Early Closures etc.)
        $savingsPenalties = MfSavingsTransaction::whereHas('savingsAccount', function($q) use ($scId) {
                $q->where('sister_concern_id', $scId);
            })
            ->where('date', $date)
            ->where('transaction_type', 4) // TYPE_PENALTY
            ->sum('amount');

        // General Cash/Bank (Selected by user dynamically)
        $cashHead = AccountHead::findOrFail($cashAccountId);

        // Fetch MFI system account heads using the reserved ID system
        try {
            $loanPortfolioHead = AccountHead::findOrFail(reservedAccountHeadId(\App\Enumeration\ReservedAccountHead::MFI_LOAN_PORTFOLIO));
            $savingsLiabilityHead = AccountHead::findOrFail(reservedAccountHeadId(\App\Enumeration\ReservedAccountHead::MFI_SAVINGS_ACCOUNT));
            $mfiIncomeHead = AccountHead::findOrFail(reservedAccountHeadId(\App\Enumeration\ReservedAccountHead::MFI_SERVICE_CHARGE_INCOME));
            $savingsInterestExpenseHead = AccountHead::findOrFail(reservedAccountHeadId(\App\Enumeration\ReservedAccountHead::MFI_INTEREST_PAID_ON_SAVINGS));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Required standard Account Heads not found. Please verify that Member Savings Account, General Loan Portfolio, MFI Service Charge Income, and Interest Paid on Savings exist with correct Reserved IDs in your Chart of Accounts.');
        }

        DB::beginTransaction();
        try {
            // Generate Voucher Number
            $prefix = 'MFEOD-';
            $maxNumber = DB::table('vouchers')
                ->where('sister_concern_id', $scId)
                ->where('voucher_no', 'LIKE', "{$prefix}%")
                ->select(DB::raw("MAX(CAST(SUBSTRING(voucher_no, LENGTH('{$prefix}') + 1) AS UNSIGNED)) as max_number"))
                ->value('max_number');
            
            $newNumber = $maxNumber ? $maxNumber + 1 : 1;
            $voucherNo = $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
            $voucherNoGroupSl = abs((int)filter_var($voucherNo, FILTER_SANITIZE_NUMBER_INT));

            // Create Master Voucher
            $totalVoucherAmount = $savingsDeposits + $savingsWithdrawals + $savingsInterest + $loansDisbursed + $loanCollections + $openingFeesCollected + $savingsPenalties;
            if ($totalVoucherAmount <= 0) {
                return redirect()->back()->with('error', 'There are no microfinance transactions to post for the selected date.');
            }

            $voucher = Voucher::create([
                'sister_concern_id' => $scId,
                'voucher_type' => VoucherType::$JOURNAL_VOUCHER,
                'amount' => $totalVoucherAmount,
                'date' => $date,
                'voucher_no' => $voucherNo,
                'voucher_no_group_sl' => $voucherNoGroupSl,
                'notes' => 'Daily Microfinance Closing Journal Voucher for ' . date('d M, Y', strtotime($date)),
                'user_id' => auth()->id(),
            ]);

            // 1. Savings Deposits: Debit Cash, Credit Savings Liability
            if ($savingsDeposits > 0) {
                // Debit Cash
                Transaction::create([
                    'sister_concern_id' => $scId,
                    'voucher_id' => $voucher->id,
                    'account_head_id' => $cashHead->id,
                    'voucher_type' => VoucherType::$JOURNAL_VOUCHER,
                    'transaction_type' => TransactionType::$DEBIT,
                    'amount' => $savingsDeposits,
                    'date' => $date,
                    'user_id' => auth()->id(),
                    'notes' => 'MFI Daily Savings Deposits Summary',
                    'voucher_no' => $voucherNo,
                    'voucher_no_group_sl' => $voucherNoGroupSl,
                ]);

                // Credit Savings Liability
                Transaction::create([
                    'sister_concern_id' => $scId,
                    'voucher_id' => $voucher->id,
                    'account_head_id' => $savingsLiabilityHead->id,
                    'voucher_type' => VoucherType::$JOURNAL_VOUCHER,
                    'transaction_type' => TransactionType::$CREDIT,
                    'amount' => $savingsDeposits,
                    'date' => $date,
                    'user_id' => auth()->id(),
                    'notes' => 'MFI Daily Savings Deposits Summary',
                    'voucher_no' => $voucherNo,
                    'voucher_no_group_sl' => $voucherNoGroupSl,
                ]);

                // Sync current balance
                $cashHead->increment('current_balance', $savingsDeposits);
                $savingsLiabilityHead->increment('current_balance', $savingsDeposits);
            }

            // 2. Savings Withdrawals: Debit Savings Liability, Credit Cash
            if ($savingsWithdrawals > 0) {
                // Debit Savings Liability
                Transaction::create([
                    'sister_concern_id' => $scId,
                    'voucher_id' => $voucher->id,
                    'account_head_id' => $savingsLiabilityHead->id,
                    'voucher_type' => VoucherType::$JOURNAL_VOUCHER,
                    'transaction_type' => TransactionType::$DEBIT,
                    'amount' => $savingsWithdrawals,
                    'date' => $date,
                    'user_id' => auth()->id(),
                    'notes' => 'MFI Daily Savings Withdrawals Summary',
                    'voucher_no' => $voucherNo,
                    'voucher_no_group_sl' => $voucherNoGroupSl,
                ]);

                // Credit Cash
                Transaction::create([
                    'sister_concern_id' => $scId,
                    'voucher_id' => $voucher->id,
                    'account_head_id' => $cashHead->id,
                    'voucher_type' => VoucherType::$JOURNAL_VOUCHER,
                    'transaction_type' => TransactionType::$CREDIT,
                    'amount' => $savingsWithdrawals,
                    'date' => $date,
                    'user_id' => auth()->id(),
                    'notes' => 'MFI Daily Savings Withdrawals Summary',
                    'voucher_no' => $voucherNo,
                    'voucher_no_group_sl' => $voucherNoGroupSl,
                ]);

                // Sync current balance
                $cashHead->decrement('current_balance', $savingsWithdrawals);
                $savingsLiabilityHead->decrement('current_balance', $savingsWithdrawals);
            }

            // 3. Savings Interest Posted: Debit Interest Expense, Credit Savings Liability
            if ($savingsInterest > 0) {
                // Debit Interest Expense
                Transaction::create([
                    'sister_concern_id' => $scId,
                    'voucher_id' => $voucher->id,
                    'account_head_id' => $savingsInterestExpenseHead->id,
                    'voucher_type' => VoucherType::$JOURNAL_VOUCHER,
                    'transaction_type' => TransactionType::$DEBIT,
                    'amount' => $savingsInterest,
                    'date' => $date,
                    'user_id' => auth()->id(),
                    'notes' => 'MFI Daily Savings Interest Posted Summary',
                    'voucher_no' => $voucherNo,
                    'voucher_no_group_sl' => $voucherNoGroupSl,
                ]);

                // Credit Savings Liability
                Transaction::create([
                    'sister_concern_id' => $scId,
                    'voucher_id' => $voucher->id,
                    'account_head_id' => $savingsLiabilityHead->id,
                    'voucher_type' => VoucherType::$JOURNAL_VOUCHER,
                    'transaction_type' => TransactionType::$CREDIT,
                    'amount' => $savingsInterest,
                    'date' => $date,
                    'user_id' => auth()->id(),
                    'notes' => 'MFI Daily Savings Interest Posted Summary',
                    'voucher_no' => $voucherNo,
                    'voucher_no_group_sl' => $voucherNoGroupSl,
                ]);

                // Sync current balance
                $savingsInterestExpenseHead->increment('current_balance', $savingsInterest);
                $savingsLiabilityHead->increment('current_balance', $savingsInterest);
            }

            // 4. Loans Disbursed: Debit Loan Portfolio (Full), Credit Cash (Net), Credit Service Charge Income (Fees)
            if ($loansDisbursed > 0) {
                // Debit Loan Portfolio Asset (Full amount)
                Transaction::create([
                    'sister_concern_id' => $scId,
                    'voucher_id' => $voucher->id,
                    'account_head_id' => $loanPortfolioHead->id,
                    'voucher_type' => VoucherType::$JOURNAL_VOUCHER,
                    'transaction_type' => TransactionType::$DEBIT,
                    'amount' => $loansDisbursed,
                    'date' => $date,
                    'user_id' => auth()->id(),
                    'notes' => 'MFI Daily Loan Disbursements Summary',
                    'voucher_no' => $voucherNo,
                    'voucher_no_group_sl' => $voucherNoGroupSl,
                ]);

                // Credit Cash (Net Disbursed)
                if ($netDisbursed > 0) {
                    Transaction::create([
                        'sister_concern_id' => $scId,
                        'voucher_id' => $voucher->id,
                        'account_head_id' => $cashHead->id,
                        'voucher_type' => VoucherType::$JOURNAL_VOUCHER,
                        'transaction_type' => TransactionType::$CREDIT,
                        'amount' => $netDisbursed,
                        'date' => $date,
                        'user_id' => auth()->id(),
                        'notes' => 'MFI Daily Loan Net Disbursed Outflow',
                        'voucher_no' => $voucherNo,
                        'voucher_no_group_sl' => $voucherNoGroupSl,
                    ]);
                    $cashHead->decrement('current_balance', $netDisbursed);
                }

                // Credit Service Charge Income (Fees deducted)
                if ($loanFees > 0) {
                    Transaction::create([
                        'sister_concern_id' => $scId,
                        'voucher_id' => $voucher->id,
                        'account_head_id' => $mfiIncomeHead->id,
                        'voucher_type' => VoucherType::$JOURNAL_VOUCHER,
                        'transaction_type' => TransactionType::$CREDIT,
                        'amount' => $loanFees,
                        'date' => $date,
                        'user_id' => auth()->id(),
                        'notes' => 'MFI Daily Loan Processing & Insurance Fees Summary',
                        'voucher_no' => $voucherNo,
                        'voucher_no_group_sl' => $voucherNoGroupSl,
                    ]);
                    $mfiIncomeHead->increment('current_balance', $loanFees);
                }

                $loanPortfolioHead->increment('current_balance', $loansDisbursed);
            }

            // 5. Loan Collections: Debit Cash (Total), Credit Loan Portfolio (Principal), Credit Service Charge Income (Interest)
            if ($loanCollections > 0) {
                // Debit Cash (Full amount collected)
                Transaction::create([
                    'sister_concern_id' => $scId,
                    'voucher_id' => $voucher->id,
                    'account_head_id' => $cashHead->id,
                    'voucher_type' => VoucherType::$JOURNAL_VOUCHER,
                    'transaction_type' => TransactionType::$DEBIT,
                    'amount' => $loanCollections,
                    'date' => $date,
                    'user_id' => auth()->id(),
                    'notes' => 'MFI Daily Loan Collections Summary',
                    'voucher_no' => $voucherNo,
                    'voucher_no_group_sl' => $voucherNoGroupSl,
                ]);
                $cashHead->increment('current_balance', $loanCollections);

                // Credit Loan Portfolio (Principal portion)
                if ($collectedPrincipal > 0) {
                    Transaction::create([
                        'sister_concern_id' => $scId,
                        'voucher_id' => $voucher->id,
                        'account_head_id' => $loanPortfolioHead->id,
                        'voucher_type' => VoucherType::$JOURNAL_VOUCHER,
                        'transaction_type' => TransactionType::$CREDIT,
                        'amount' => $collectedPrincipal,
                        'date' => $date,
                        'user_id' => auth()->id(),
                        'notes' => 'MFI Daily Loan Collected Principal Summary',
                        'voucher_no' => $voucherNo,
                        'voucher_no_group_sl' => $voucherNoGroupSl,
                    ]);
                    $loanPortfolioHead->decrement('current_balance', $collectedPrincipal);
                }

                // Credit MFI Interest Income (Interest portion)
                if ($collectedInterest > 0) {
                    Transaction::create([
                        'sister_concern_id' => $scId,
                        'voucher_id' => $voucher->id,
                        'account_head_id' => $mfiIncomeHead->id,
                        'voucher_type' => VoucherType::$JOURNAL_VOUCHER,
                        'transaction_type' => TransactionType::$CREDIT,
                        'amount' => $collectedInterest,
                        'date' => $date,
                        'user_id' => auth()->id(),
                        'notes' => 'MFI Daily Loan Collected Interest Summary',
                        'voucher_no' => $voucherNo,
                        'voucher_no_group_sl' => $voucherNoGroupSl,
                    ]);
                    $mfiIncomeHead->increment('current_balance', $collectedInterest);
                }
            }

            // 6. Savings Account Opening Fees: Debit Cash, Credit MFI Income
            if ($openingFeesCollected > 0) {
                // Debit Cash
                Transaction::create([
                    'sister_concern_id' => $scId,
                    'voucher_id' => $voucher->id,
                    'account_head_id' => $cashHead->id,
                    'voucher_type' => VoucherType::$JOURNAL_VOUCHER,
                    'transaction_type' => TransactionType::$DEBIT,
                    'amount' => $openingFeesCollected,
                    'date' => $date,
                    'user_id' => auth()->id(),
                    'notes' => 'MFI Daily Savings Account Opening Fees Summary',
                    'voucher_no' => $voucherNo,
                    'voucher_no_group_sl' => $voucherNoGroupSl,
                ]);

                // Credit MFI Income
                Transaction::create([
                    'sister_concern_id' => $scId,
                    'voucher_id' => $voucher->id,
                    'account_head_id' => $mfiIncomeHead->id,
                    'voucher_type' => VoucherType::$JOURNAL_VOUCHER,
                    'transaction_type' => TransactionType::$CREDIT,
                    'amount' => $openingFeesCollected,
                    'date' => $date,
                    'user_id' => auth()->id(),
                    'notes' => 'MFI Daily Savings Account Opening Fees Summary',
                    'voucher_no' => $voucherNo,
                    'voucher_no_group_sl' => $voucherNoGroupSl,
                ]);

                $cashHead->increment('current_balance', $openingFeesCollected);
                $mfiIncomeHead->increment('current_balance', $openingFeesCollected);
            }

            // 7. Savings Penalties & Reversals (FDR/DPS Early Closures etc.): Debit Savings Liability, Credit MFI Income
            if ($savingsPenalties > 0) {
                // Debit Savings Liability (since penalties reduce member's savings balance)
                Transaction::create([
                    'sister_concern_id' => $scId,
                    'voucher_id' => $voucher->id,
                    'account_head_id' => $savingsLiabilityHead->id,
                    'voucher_type' => VoucherType::$JOURNAL_VOUCHER,
                    'transaction_type' => TransactionType::$DEBIT,
                    'amount' => $savingsPenalties,
                    'date' => $date,
                    'user_id' => auth()->id(),
                    'notes' => 'MFI Daily Savings Penalties & FDR/DPS Pre-closure Charges',
                    'voucher_no' => $voucherNo,
                    'voucher_no_group_sl' => $voucherNoGroupSl,
                ]);

                // Credit MFI Income
                Transaction::create([
                    'sister_concern_id' => $scId,
                    'voucher_id' => $voucher->id,
                    'account_head_id' => $mfiIncomeHead->id,
                    'voucher_type' => VoucherType::$JOURNAL_VOUCHER,
                    'transaction_type' => TransactionType::$CREDIT,
                    'amount' => $savingsPenalties,
                    'date' => $date,
                    'user_id' => auth()->id(),
                    'notes' => 'MFI Daily Savings Penalties & FDR/DPS Pre-closure Charges',
                    'voucher_no' => $voucherNo,
                    'voucher_no_group_sl' => $voucherNoGroupSl,
                ]);

                $savingsLiabilityHead->decrement('current_balance', $savingsPenalties);
                $mfiIncomeHead->increment('current_balance', $savingsPenalties);
            }

            DB::commit();
            return redirect()->back()->with('success', 'Day closing successfully posted to general ledger. Voucher No: ' . $voucherNo);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error posting day closing: ' . $e->getMessage());
        }
    }
}
