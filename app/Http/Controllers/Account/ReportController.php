<?php

namespace App\Http\Controllers\Account;

use App\Enumeration\RoleType;
use App\Http\Controllers\Controller;
use App\Enumeration\TransactionType;
use App\Enumeration\VoucherType;
use App\Enumeration\PaymentPurpose;
use App\Models\Account\AccountGroup;
use App\Models\Account\AccountHead;
use App\Enumeration\ReservedAccountGroup;
use App\Enumeration\ReservedAccountHead;
use App\Models\BranchManagement\SisterConcern;
use App\Models\Account\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use SakibRahaman\DecimalToWords\DecimalToWords;
use App\Models\Sales\SaleOrder;
use App\Models\Purchase\PurchaseOrder;
use App\Models\StockTransfer\StockTransfer;
use App\Models\StockTransfer\StockTransferItem;

class ReportController extends Controller
{
    public function cashBankBook(Request $request)
    {

        $currentDate = Carbon::now();
        $firstDateOfMonth = $currentDate->startOfMonth()->format('d-m-Y');
        $lastDateOfMonth = $currentDate->endOfMonth()->format('d-m-Y');
        $accounts = AccountHead::where(function ($q) {
            $q->whereNull('sister_concern_id')->orWhere('sister_concern_id', auth()->user()->activeConcernId()); })->where('payment_mode', '>', 0)->get();

        $selectAccount = null;
        $openingBalance = 0;
        $transactions = [];
        $summary = [];
        $start = date('Y-m-d', strtotime($request->start_date));
        $end = date('Y-m-d', strtotime($request->end_date));

        if ($request->payment_mode && $request->start_date && $request->end_date) {
            $query = Transaction::where('account_head_id', $request->payment_mode)
                ->whereIn('transaction_type', [TransactionType::$DEBIT, TransactionType::$CREDIT])
                ->whereBetween('date', [$start, $end]);

            $query->where('sister_concern_id', auth()->user()->activeConcernId());

            $transactions = $query->with('voucher')
                ->orderBy('voucher_no_group_sl')
                ->orderBy('date')
                ->get();

            $selectAccount = AccountHead::where('id', $request->payment_mode)->first();
            $previousDay = date('Y-m-d', strtotime('-1 day', strtotime($request->start_date)));

            $totalReceivedQuery = Transaction::where('account_head_id', $request->payment_mode)
                ->where('transaction_type', TransactionType::$DEBIT)
                ->whereDate('date', '<=', $previousDay);

            $totalReceivedQuery->where('sister_concern_id', auth()->user()->activeConcernId());
            $totalReceived = $totalReceivedQuery->sum('amount');

            $totalPaymentQuery = Transaction::where('account_head_id', $request->payment_mode)
                ->where('transaction_type', TransactionType::$CREDIT)
                ->whereDate('date', '<=', $previousDay);

            $totalPaymentQuery->where('sister_concern_id', auth()->user()->activeConcernId());
            $totalPayment = $totalPaymentQuery->sum('amount');

            $openingBalance = $totalReceived - $totalPayment;

            // Generate summary by payment_purpose
            $purposeNames = PaymentPurpose::names();

            $summary = [];
            foreach ($transactions as $tx) {
                $purpose = $tx->payment_purpose;

                if (!$purpose) {
                    if ($tx->transaction_type == TransactionType::$DEBIT) {
                        $purpose = 'other_receipts';
                    } else {
                        $purpose = 'other_payments';
                    }
                }

                $purposeName = $purposeNames[$purpose] ?? 'Other / Unknown (' . $purpose . ')';

                if (!isset($summary[$purpose])) {
                    $summary[$purpose] = [
                        'id' => $purpose,
                        'name' => $purposeName,
                        'count' => 0,
                        'payment' => 0,
                        'receipt' => 0,
                    ];
                }

                $summary[$purpose]['count']++;

                if ($tx->transaction_type == TransactionType::$DEBIT) {
                    $summary[$purpose]['receipt'] += $tx->amount;
                } else if ($tx->transaction_type == TransactionType::$CREDIT) {
                    $summary[$purpose]['payment'] += $tx->amount;
                }
            }
        }
        $sister_concerns = SisterConcern::where('status', 1)->get();

        if ($request->has('search')) {
            return view(
                'accounts.reports.cash_bank_book_report',
                compact(
                    'firstDateOfMonth',
                    'lastDateOfMonth',
                    'accounts',
                    'selectAccount',
                    'transactions',
                    'openingBalance',
                    'sister_concerns',
                    'summary'
                )
            );
        }

        return view(
            'accounts.reports.cash_bank_book',
            compact(
                'firstDateOfMonth',
                'lastDateOfMonth',
                'accounts',
                'selectAccount',
                'transactions',
                'openingBalance',
                'sister_concerns',
                'summary'
            )
        );
    }

    public function cashBankTransactionDetails(Request $request)
    {

        $currentDate = Carbon::now();
        $firstDateOfMonth = $currentDate->startOfMonth()->format('d-m-Y');
        $lastDateOfMonth = $currentDate->endOfMonth()->format('d-m-Y');
        $accounts = AccountHead::where(function ($q) {
            $q->whereNull('sister_concern_id')->orWhere('sister_concern_id', auth()->user()->activeConcernId()); })->where('payment_mode', '>', 0)->get();

        $selectAccount = null;
        $openingBalance = 0;
        $transactions = [];
        $start = date('Y-m-d', strtotime($request->start_date));
        $end = date('Y-m-d', strtotime($request->end_date));

        if ($request->payment_mode && $request->start_date && $request->end_date) {
            $query = Transaction::where('account_head_id', $request->payment_mode)
                ->whereIn('transaction_type', [TransactionType::$DEBIT, TransactionType::$CREDIT])
                ->whereBetween('date', [$start, $end]);

            $query->where('sister_concern_id', auth()->user()->activeConcernId());

            $transactions = $query->with('voucher')
                ->orderBy('voucher_no_group_sl')
                ->orderBy('date')
                ->get();

            $selectAccount = AccountHead::where('id', $request->payment_mode)->first();
            $previousDay = date('Y-m-d', strtotime('-1 day', strtotime($request->start_date)));

            $totalReceivedQuery = Transaction::where('account_head_id', $request->payment_mode)
                ->where('transaction_type', TransactionType::$DEBIT)
                ->whereDate('date', '<=', $previousDay);

            $totalReceivedQuery->where('sister_concern_id', auth()->user()->activeConcernId());
            $totalReceived = $totalReceivedQuery->sum('amount');

            $totalPaymentQuery = Transaction::where('account_head_id', $request->payment_mode)
                ->where('transaction_type', TransactionType::$CREDIT)
                ->whereDate('date', '<=', $previousDay);

            $totalPaymentQuery->where('sister_concern_id', auth()->user()->activeConcernId());
            $totalPayment = $totalPaymentQuery->sum('amount');

            $openingBalance = $totalReceived - $totalPayment;

        }
        $sister_concerns = SisterConcern::where('status', 1)->get();

        if ($request->has('search')) {
            return view(
                'accounts.reports.cash_bank_transaction_details_report',
                compact(
                    'firstDateOfMonth',
                    'lastDateOfMonth',
                    'accounts',
                    'selectAccount',
                    'transactions',
                    'openingBalance',
                    'sister_concerns'
                )
            );
        }

        return view(
            'accounts.reports.cash_bank_transaction_details',
            compact(
                'firstDateOfMonth',
                'lastDateOfMonth',
                'accounts',
                'selectAccount',
                'transactions',
                'openingBalance',
                'sister_concerns'
            )
        );
    }

    public function receiptAndPayment(Request $request)
    {
        $logs = [];
        if ($request->start_date != '' && $request->end_date != '') {
            $query = Transaction::whereIn(
                'voucher_type',
                [
                    VoucherType::$PAYMENT_VOUCHER,
                    VoucherType::$COLLECTION_VOUCHER
                ]
            )
                ->whereBetween('date', [
                    Carbon::parse($request->start_date)->format('Y-m-d'),
                    Carbon::parse($request->end_date)->format('Y-m-d')
                ])
                ->whereColumn('payment_account_head_id', '!=', 'account_head_id')
                ->where('other_deduct', 0)
                ->where('except_edit', 0);

            $query->where('sister_concern_id', auth()->user()->activeConcernId());

            $logs = $query->orderBy('date')
                ->get();
        }

        if ($request->has('search')) {
            return view('accounts.reports.receipt_and_payment_report', compact('logs'));
        }

        return view('accounts.reports.receipt_and_payment', compact('logs'));
    }

    public function ledger(Request $request)
    {
        $startDate = date('Y-m-d', strtotime($request->start_date));
        $endDate = date('Y-m-d', strtotime($request->end_date));

        $accountHeadsList = AccountHead::where('status', 1)->orderBy('code')->get();
        $accountHeads = [];
        if ($request->start_date != '') {
            $previousDay = date('Y-m-d', strtotime('-1 day', strtotime($request->start_date)));

            $query = AccountHead::where('status', 1);
            if ($request->account_head != '') {
                $query->where('id', $request->account_head);
            }
            if ($request->account_head_id != '') {
                $query->where('id', $request->account_head_id);
            }
            $query->with([
                'openingTransactions' => function ($query) use ($previousDay, $request) {
                    $query->where('sister_concern_id', auth()->user()->activeConcernId());

                    $query->whereDate('date', '<=', $previousDay)
                        ->select(DB::raw('account_head_id,
                            SUM(CASE WHEN transaction_type IN (1) THEN amount ELSE 0 END) AS previous_debit,
                            SUM(CASE WHEN transaction_type IN (2) THEN amount ELSE 0 END) AS previous_credit'))
                        ->groupBy('account_head_id');
                },
                'transactions' => function ($query) use ($startDate, $endDate, $request) {
                    $query->where('sister_concern_id', auth()->user()->activeConcernId());

                    $query->whereBetween('date', [$startDate, $endDate])
                        ->orderBy('date')
                        ->orderBy('voucher_no_group_sl');
                },
            ]);

            $accountHeads = $query->orderBy('code')
                ->get();
        }

        $currentMonth = date('m');
        if ($currentMonth < 7) {
            $currentYear = date('Y') - 1;
            $currentDate = date('01-07-' . $currentYear);
        } else {
            $currentDate = date('01-07-Y');
        }

        if ($request->has('search')) {
            return view('accounts.reports.ledger_report', compact(
                'accountHeads',
                'currentDate',
                'accountHeadsList'
            ));
        }

        return view('accounts.reports.ledger', compact(
            'accountHeads',
            'currentDate',
            'accountHeadsList'
        ));
    }

    public function trialBalance(Request $request)
    {
        $accountHeadsList = AccountHead::where('status', 1)->orderBy('code')->get();

        $accountHeads = [];

        $filterFinancialYear = null;
        if ($request->start_date != '' && $request->end_date != '') {
            $query = AccountHead::where('status', 1);

            if ($request->account_head != '') {
                $query->where('id', $request->account_head);
            }
            if ($request->account_group_id != '') {
                $group = AccountGroup::find($request->account_group_id);
                if ($group) {
                    $groupIds = $group->getAllChildIds();
                    $groupIds[] = $group->id;
                    $query->whereIn('account_group_id', $groupIds);
                }
            }

            $previousDay = date('Y-m-d', strtotime('-1 day', strtotime($request->start_date)));
            $start = Carbon::parse($request->start_date)->format('Y-m-d');
            $end = Carbon::parse($request->end_date)->format('Y-m-d');
            $accountHeads = $query
                ->with([
                    'openingTransactions' => function ($query) use ($previousDay, $request) {

                        $query->where('sister_concern_id', auth()->user()->activeConcernId());

                        $query->whereDate('date', '<=', $previousDay)
                            ->select(DB::raw('account_head_id,
                        SUM(CASE WHEN transaction_type IN (1) THEN amount ELSE 0 END) AS previous_debit,
                        SUM(CASE WHEN transaction_type IN (2) THEN amount ELSE 0 END) AS previous_credit'))
                            ->groupBy('account_head_id');
                    }
                ])
                ->with([
                    'transactions' => function ($query) use ($start, $end, $request) {
                        $query->where('sister_concern_id', auth()->user()->activeConcernId());

                        $query->whereBetween('date', [$start, $end])
                            ->select(DB::raw('account_head_id,
                         SUM(CASE WHEN transaction_type IN (1) THEN amount ELSE 0 END) AS debit,
                         SUM(CASE WHEN transaction_type IN (2) THEN amount ELSE 0 END) AS credit'))
                            ->groupBy('account_head_id');
                    }
                ])
                ->orderBy('code')
                ->get();
            // dd($accountHeads);    
        }

        $in_word = new DecimalToWords();

        $currentMonth = date('m');
        if ($currentMonth < 7) {
            $currentYear = date('Y') - 1;
            $currentDate = date('01-07-' . $currentYear);
        } else {
            $currentDate = date('01-07-Y');
        }


        if ($request->has('search')) {
            return view('accounts.reports.trial_balance_report', compact(
                'currentDate',
                'accountHeads',
                'in_word',
                'accountHeadsList',
                'filterFinancialYear'
            ));
        }

        return view('accounts.reports.trial_balance', compact(
            'currentDate',
            'accountHeads',
            'in_word',
            'accountHeadsList',
            'filterFinancialYear'
        ));
    }

    public function incomeStatement(Request $request)
    {
        $currentMonth = date('m');
        if ($currentMonth < 7) {
            $currentYear = date('Y') - 1;
            $currentDate = date('01-07-' . $currentYear);
        } else {
            $currentDate = date('01-07-Y');
        }

        // --- New 13 Sub-group structure for Income Statement ---
        $operatingRevenue = AccountGroup::where('reserved_id', ReservedAccountGroup::OPERATING_REVENUE)->first();
        $otherIncome = AccountGroup::where('reserved_id', ReservedAccountGroup::OTHER_INCOME)->first();
        $costOfGoodsSold = AccountGroup::where('reserved_id', ReservedAccountGroup::COST_OF_GOODS_SOLD)->first();
        $operatingExpenses = AccountGroup::where('reserved_id', ReservedAccountGroup::OPERATING_EXPENSES)->first();
        $financialExpenses = AccountGroup::where('reserved_id', ReservedAccountGroup::FINANCIAL_EXPENSES)->first();
        $taxExpenses = AccountGroup::where('reserved_id', ReservedAccountGroup::TAX_EXPENSES)->first();

        if ($request->has('search')) {
            return view('accounts.reports.income_statement_t_format_report', compact(
                'currentDate',
                'operatingRevenue',
                'otherIncome',
                'costOfGoodsSold',
                'operatingExpenses',
                'financialExpenses',
                'taxExpenses'
            ));
        }

        return view('accounts.reports.income_statement', compact(
            'currentDate',
            'operatingRevenue',
            'otherIncome',
            'costOfGoodsSold',
            'operatingExpenses',
            'financialExpenses',
            'taxExpenses'
        ));
    }


    public function balanceSheet(Request $request)
    {

        $currentMonth = date('m');
        if ($currentMonth < 7) {
            $currentYear = date('Y') - 1;
            $currentDate = date('01-07-' . $currentYear);
        } else {
            $currentDate = date('01-07-Y');
        }

        $assets = AccountGroup::where('reserved_id', ReservedAccountGroup::ASSETS)
            ->with('accountGroups')
            ->get();
        $liabilities = AccountGroup::where('reserved_id', ReservedAccountGroup::LIABILITIES)
            ->with('accountGroups')
            ->get();
        $equities = AccountGroup::where('reserved_id', ReservedAccountGroup::EQUITY)
            ->with('accountGroups')
            ->get();

        $operatingRevenue = AccountGroup::where('reserved_id', ReservedAccountGroup::OPERATING_REVENUE)->first();
        $otherIncome = AccountGroup::where('reserved_id', ReservedAccountGroup::OTHER_INCOME)->first();
        $costOfGoodsSold = AccountGroup::where('reserved_id', ReservedAccountGroup::COST_OF_GOODS_SOLD)->first();
        $operatingExpenses = AccountGroup::where('reserved_id', ReservedAccountGroup::OPERATING_EXPENSES)->first();
        $financialExpenses = AccountGroup::where('reserved_id', ReservedAccountGroup::FINANCIAL_EXPENSES)->first();
        $taxExpenses = AccountGroup::where('reserved_id', ReservedAccountGroup::TAX_EXPENSES)->first();

        if ($request->has('search')) {
            return view('accounts.reports.balance_sheet_t_format_report', compact(
                'assets',
                'liabilities',
                'equities',
                'currentDate',
                'operatingRevenue',
                'otherIncome',
                'costOfGoodsSold',
                'operatingExpenses',
                'financialExpenses',
                'taxExpenses'
            ));
        }

        return view('accounts.reports.balance_sheet_t_format', compact(
            'assets',
            'liabilities',
            'equities',
            'currentDate',
            'operatingRevenue',
            'otherIncome',
            'costOfGoodsSold',
            'operatingExpenses',
            'financialExpenses',
            'taxExpenses'
        ));
    }

    public function interCompanyLedger(Request $request)
    {
        $branches = SisterConcern::where('status', 1)
            ->where('id', '!=', auth()->user()->activeConcernId())
            ->get();

        $transactions = collect();
        $openingBalance = 0;
        $selectedBranch = null;
        $receivableHead = null;
        $payableHead = null;

        if ($request->branch_id && $request->start_date && $request->end_date) {
            $selectedBranch = SisterConcern::find($request->branch_id);
            $start = date('Y-m-d', strtotime($request->start_date));
            $end = date('Y-m-d', strtotime($request->end_date));

            // The branch code or id is used to find the account heads.
            $receivableHead = AccountHead::where('reserved_id', ReservedAccountHead::INTER_COMPANY_RECEIVABLE)
                ->where('name', 'like', 'Inter-company Receivable - ' . ($selectedBranch->code ?? sprintf('%03d', $selectedBranch->id)) . '%')
                ->whereNull('sister_concern_id')
                ->first();

            $payableHead = AccountHead::where('reserved_id', ReservedAccountHead::INTER_COMPANY_PAYABLE)
                ->where('name', 'like', 'Inter-company Payable - ' . ($selectedBranch->code ?? sprintf('%03d', $selectedBranch->id)) . '%')
                ->whereNull('sister_concern_id')
                ->first();

            $headIds = [];
            if ($receivableHead)
                $headIds[] = $receivableHead->id;
            if ($payableHead)
                $headIds[] = $payableHead->id;

            if (count($headIds) > 0) {
                // Get Opening Balance up to start_date
                $previousDay = date('Y-m-d', strtotime('-1 day', strtotime($start)));

                $prevReceivableDebit = Transaction::where('account_head_id', $receivableHead->id ?? 0)
                    ->where('sister_concern_id', auth()->user()->activeConcernId())
                    ->where('transaction_type', TransactionType::$DEBIT)
                    ->whereDate('date', '<=', $previousDay)->sum('amount');
                $prevReceivableCredit = Transaction::where('account_head_id', $receivableHead->id ?? 0)
                    ->where('sister_concern_id', auth()->user()->activeConcernId())
                    ->where('transaction_type', TransactionType::$CREDIT)
                    ->whereDate('date', '<=', $previousDay)->sum('amount');

                $prevPayableCredit = Transaction::where('account_head_id', $payableHead->id ?? 0)
                    ->where('sister_concern_id', auth()->user()->activeConcernId())
                    ->where('transaction_type', TransactionType::$CREDIT)
                    ->whereDate('date', '<=', $previousDay)->sum('amount');
                $prevPayableDebit = Transaction::where('account_head_id', $payableHead->id ?? 0)
                    ->where('sister_concern_id', auth()->user()->activeConcernId())
                    ->where('transaction_type', TransactionType::$DEBIT)
                    ->whereDate('date', '<=', $previousDay)->sum('amount');

                // Net Receivable Opening Balance = (Rec Debit - Rec Credit) - (Pay Credit - Pay Debit)
                $openingBalance = ($prevReceivableDebit - $prevReceivableCredit) - ($prevPayableCredit - $prevPayableDebit);

                $transactions = Transaction::whereIn('account_head_id', $headIds)
                    ->where('sister_concern_id', auth()->user()->activeConcernId())
                    ->whereBetween('date', [$start, $end])
                    ->with('voucher')
                    ->orderBy('date')
                    ->orderBy('voucher_no_group_sl')
                    ->get();
            }
        }

        if ($request->has('search')) {
            return view('accounts.reports.inter_company_ledger_report', compact('branches', 'transactions', 'openingBalance', 'selectedBranch', 'receivableHead', 'payableHead'));
        }

        return view('accounts.reports.inter_company_ledger', compact('branches', 'transactions', 'openingBalance', 'selectedBranch', 'receivableHead', 'payableHead'));
    }

    public function dailyTransactions(Request $request)
    {
        $startDate = $request->start_date ? Carbon::parse($request->start_date)->format('Y-m-d') : date('Y-m-d', strtotime(auth()->user()->business_date));
        $endDate = $request->end_date ? Carbon::parse($request->end_date)->format('Y-m-d') : date('Y-m-d', strtotime(auth()->user()->business_date));
        $branchId = $request->branch_id ?? auth()->user()->activeConcernId();

        $query = Transaction::whereBetween('date', [$startDate, $endDate])
            ->where('is_opening_balance', 0);

        if ($branchId) {
            $query->where('sister_concern_id', $branchId);
        }

        // To map purpose integer to human readable name
        $purposeNames = PaymentPurpose::names();

        // Fetch transactions joined with account head to find cash/bank movements
        $cashBankTransactions = clone $query;
        $cashBankTransactions = $cashBankTransactions->with(['accountHead', 'voucher.saleOrder.saleOrderItems.product'])->whereHas('accountHead', function ($q) {
            $q->where('payment_mode', '>', 0); // Only Cash or Bank accounts
        })->get();

        $summary = [
            'sales_invoice' => [
                'id' => 'sales_invoice',
                'name' => 'Sales (All Invoices)',
                'count' => 0,
                'sales' => 0,
                'purchase' => 0,
                'transfer' => 0,
                'payment' => 0,
                'receipt' => 0,
                'orders' => []
            ],
            'purchase_bill' => [
                'id' => 'purchase_bill',
                'name' => 'Purchase (All Bills)',
                'count' => 0,
                'sales' => 0,
                'purchase' => 0,
                'transfer' => 0,
                'payment' => 0,
                'receipt' => 0,
                'orders' => []
            ],
            'stock_transfer_sent' => [
                'id' => 'stock_transfer_sent',
                'name' => 'Stock Transfer (Sent)',
                'count' => 0,
                'sales' => 0,
                'purchase' => 0,
                'transfer' => 0,
                'payment' => 0,
                'receipt' => 0,
                'orders' => []
            ],
            'stock_transfer_received' => [
                'id' => 'stock_transfer_received',
                'name' => 'Stock Transfer (Received)',
                'count' => 0,
                'sales' => 0,
                'purchase' => 0,
                'transfer' => 0,
                'payment' => 0,
                'receipt' => 0,
                'orders' => []
            ],
        ];

        // 1. Invoiced Sales Total
        // 1. Invoiced Sales Total
        $salesQ = SaleOrder::whereBetween('date', [$startDate, $endDate]);
        if ($branchId)
            $salesQ->where('sister_concern_id', $branchId);
        $summary['sales_invoice']['sales'] = $salesQ->sum('total');

        // 2. Billed Purchase Total
        $purchaseQ = PurchaseOrder::whereBetween('date', [$startDate, $endDate]);
        if ($branchId)
            $purchaseQ->where('sister_concern_id', $branchId);
        $summary['purchase_bill']['purchase'] = $purchaseQ->sum('total');

        // 3. Stock Transfer Sent
        $transferOutQ = StockTransfer::whereBetween('date', [$startDate, $endDate])->where('is_return', 0);
        if ($branchId)
            $transferOutQ->where('from_sister_concern_id', $branchId);
        $summary['stock_transfer_sent']['transfer'] = StockTransferItem::whereHas('stockTransfer', function ($q) use ($startDate, $endDate, $branchId) {
            $q->whereBetween('date', [$startDate, $endDate])->where('is_return', 0);
            if ($branchId)
                $q->where('from_sister_concern_id', $branchId);
        })->sum(DB::raw('quantity * COALESCE(transfer_price, unit_price)'));

        // 4. Stock Transfer Received
        $transferInQ = StockTransfer::whereBetween('date', [$startDate, $endDate])->where('is_return', 0);
        if ($branchId)
            $transferInQ->where('to_sister_concern_id', $branchId);
        $summary['stock_transfer_received']['transfer'] = StockTransferItem::whereHas('stockTransfer', function ($q) use ($startDate, $endDate, $branchId) {
            $q->whereBetween('date', [$startDate, $endDate])->where('is_return', 0);
            if ($branchId)
                $q->where('to_sister_concern_id', $branchId);
        })->sum(DB::raw('quantity * COALESCE(transfer_price, unit_price)'));

        foreach ($purposeNames as $id => $name) {
            if (!isset($summary[$id])) {
                $summary[$id] = [
                    'id' => $id,
                    'name' => $name,
                    'count' => 0,
                    'sales' => 0,
                    'purchase' => 0,
                    'transfer' => 0,
                    'payment' => 0,
                    'receipt' => 0,
                    'orders' => []
                ];
            }
        }

        foreach ($cashBankTransactions as $tx) {
            $purpose = $tx->payment_purpose;

            if (!$purpose) {
                if ($tx->transaction_type == TransactionType::$DEBIT) {
                    $purpose = 'other_receipts';
                } else {
                    $purpose = 'other_payments';
                }
            }

            // Map Instant Cash Sales to the main 'sales_invoice' row
            if ($purpose == PaymentPurpose::$INSTANT_COLLECTION) {
                $purpose = 'sales_invoice';
            }
            // Map Instant Cash Purchase to the main 'purchase_bill' row
            else if ($purpose == PaymentPurpose::$INSTANT_PAYMENT) {
                $purpose = 'purchase_bill';
            }
            // Map Stock Transfer to stock transfer rows? Wait, Stock Transfer payment is Inter-Company Settlement

            $purposeName = $purposeNames[$purpose] ?? 'Other / Unknown (' . $purpose . ')';

            if (!isset($summary[$purpose])) {
                $summary[$purpose] = [
                    'id' => $purpose,
                    'name' => $purposeName,
                    'count' => 0,
                    'sales' => 0,
                    'purchase' => 0,
                    'transfer' => 0,
                    'payment' => 0,
                    'receipt' => 0,
                    'orders' => []
                ];
            }

            $summary[$purpose]['count']++;

            if ($tx->voucher && $tx->voucher->saleOrder) {
                $orderStr = "<b>Order #" . $tx->voucher->saleOrder->order_no . ":</b> ";
                $products = [];
                if ($tx->voucher->saleOrder->saleOrderItems) {
                    foreach ($tx->voucher->saleOrder->saleOrderItems as $item) {
                        $products[] = ($item->product->name ?? 'Unknown Product') . ' (' . $item->quantity . ' pc)';
                    }
                }
                $orderStr .= implode(', ', $products);

                // Use the string as key to avoid duplicates if multiple payments exist for same order
                if (!in_array($orderStr, $summary[$purpose]['orders'])) {
                    $summary[$purpose]['orders'][] = $orderStr;
                }
            }

            // If Cash/Bank is DEBITED, it's an Inflow (Receipt)
            if ($tx->transaction_type == TransactionType::$DEBIT) {
                $summary[$purpose]['receipt'] += $tx->amount;
                // No need to add to sales again because sales value is already calculated from SaleOrder
            }
            // If Cash/Bank is CREDITED, it's an Outflow (Payment)
            else if ($tx->transaction_type == TransactionType::$CREDIT) {
                $summary[$purpose]['payment'] += $tx->amount;
                // No need to add to purchase again because purchase value is already calculated from PurchaseOrder
            }
        }

        // Remove empty rows
        $summary = array_filter($summary, function ($row) {
            return ($row['sales'] ?? 0) > 0 || ($row['purchase'] ?? 0) > 0 || ($row['transfer'] ?? 0) > 0 || ($row['payment'] ?? 0) > 0 || ($row['receipt'] ?? 0) > 0;
        });

        $sister_concerns = SisterConcern::where('status', 1)->get();

        if ($request->has('search')) {
            return view('accounts.reports.daily_transactions_report', compact(
                'summary',
                'sister_concerns',
                'startDate',
                'endDate'
            ));
        }

        return view('accounts.reports.daily_transactions', compact(
            'summary',
            'sister_concerns',
            'startDate',
            'endDate'
        ));
    }

    public function dailyTransactionsDetails(Request $request)
    {
        $startDate = $request->start_date ? Carbon::parse($request->start_date)->format('Y-m-d') : date('Y-m-d', strtotime(auth()->user()->business_date));
        $endDate = $request->end_date ? Carbon::parse($request->end_date)->format('Y-m-d') : date('Y-m-d', strtotime(auth()->user()->business_date));
        $branchId = $request->branch_id ?? auth()->user()->activeConcernId();
        $paymentPurpose = $request->payment_purpose;

        $query = Transaction::whereBetween('date', [$startDate, $endDate])
            ->where('is_opening_balance', 0);

        if ($paymentPurpose == 'sales_invoice') {
            $query->where('payment_purpose', PaymentPurpose::$INSTANT_COLLECTION);
        } else if ($paymentPurpose == 'purchase_bill') {
            $query->where('payment_purpose', PaymentPurpose::$INSTANT_PAYMENT);
        } else if ($paymentPurpose == 'other_receipts' || $paymentPurpose == 'other_payments') {
            $query->whereNull('payment_purpose');
            $type = ($paymentPurpose == 'other_receipts') ? TransactionType::$DEBIT : TransactionType::$CREDIT;
            $query->where('transaction_type', $type);
        } else {
            $query->where('payment_purpose', $paymentPurpose);
        }

        if ($branchId) {
            $query->where('sister_concern_id', $branchId);
        }

        $cashBankTransactions = clone $query;
        $cashBankTransactions = $cashBankTransactions->with(['accountHead', 'voucher.saleOrder.saleOrderItems.product'])->whereHas('accountHead', function ($q) {
            $q->where('payment_mode', '>', 0); // Only Cash or Bank accounts
        })->orderBy('id', 'asc')->get();

        $purposeNames = PaymentPurpose::names();
        $purposeNames['sales_invoice'] = 'Sales (All Invoices)';
        $purposeNames['purchase_bill'] = 'Purchase (All Bills)';
        $purposeNames['stock_transfer_sent'] = 'Stock Transfer (Sent)';
        $purposeNames['stock_transfer_received'] = 'Stock Transfer (Received)';

        $purposeName = $purposeNames[$paymentPurpose] ?? 'Unknown Purpose';

        return view('accounts.reports.daily_transactions_details', compact(
            'cashBankTransactions',
            'startDate',
            'endDate',
            'purposeName'
        ));
    }
}
