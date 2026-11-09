<?php

namespace App\Http\Controllers\ShomriddhiAdvance;

use App\Enumeration\SupplierType;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ShomriddhiAdvance\ShomriddhiTransaction;
use App\Models\ShomriddhiAdvance\ShomriddhiDailyInterest;
use App\Models\Account\AccountHead;
use App\Models\Supplier;
use App\Models\Account\Transaction;
use App\Enumeration\TransactionType;
use App\Models\ShomriddhiAdvance\ShomriddhiAccount;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Division;
use App\Models\ShomriddhiAdvance\ShomriddhiGroup;
use App\Models\ShomriddhiLoan\LoanAccount;
use App\Models\ShomriddhiLoan\LoanTransaction;

class ReportController extends Controller
{
    public function ledger(Request $request)
    {
        $accounts = ShomriddhiAccount::with('customer')
            ->where('origin_sister_concern_id', auth()->user()->activeConcernId())
            ->get();

        $transactions = []; 
        $customer = null;
        $savingsHead = null;
        $openingBalance = 0;
        $shomriddhiAccount = null;

        if ($request->customer_id) {
            $customer = Supplier::where('id', $request->customer_id)
                ->where('origin_sister_concern_id', auth()->user()->activeConcernId())
                ->first();
            if ($customer) {
                $shomriddhiAccount = ShomriddhiAccount::where('customer_id', $customer->id)->first();
                $savingsHead = AccountHead::where('supplier_id', $customer->id)
                    ->where('head_type', 'savings')
                    ->first();
                
                if ($savingsHead) {
                    $baseOpening = 0;
                    
                    if ($request->start_date) {
                        $parsedStartDate = Carbon::parse($request->start_date)->format('Y-m-d');
                        
                        $prevDeposit = ShomriddhiTransaction::where('customer_id', $customer->id)
                            ->where('status', 'Approved')
                            ->whereIn('type', ['deposit', 'interest_capitalization'])
                            ->whereDate('date', '<', $parsedStartDate)
                            ->sum('amount');
                            
                        $prevWithdraw = ShomriddhiTransaction::where('customer_id', $customer->id)
                            ->where('status', 'Approved')
                            ->where('type', 'withdraw')
                            ->whereDate('date', '<', $parsedStartDate)
                            ->sum('amount');
                            
                        $openingBalance = $baseOpening + ($prevDeposit - $prevWithdraw);
                    } else {
                        $openingBalance = $baseOpening;
                    }

                    $transactions = ShomriddhiTransaction::where('customer_id', $customer->id)
                        ->where('status', 'Approved')
                        ->when($request->start_date, function($q) use ($request) {
                            $parsedStart = Carbon::parse($request->start_date)->format('Y-m-d');
                            return $q->whereDate('date', '>=', $parsedStart);
                        })
                        ->when($request->end_date, function($q) use ($request) {
                            $parsedEnd = Carbon::parse($request->end_date)->format('Y-m-d');
                            return $q->whereDate('date', '<=', $parsedEnd);
                        })
                        ->orderBy('date', 'asc')
                        ->orderBy('id', 'asc')
                        ->get();
                }
            }
        }

        if ($request->customer_id && $customer) {
            return view('shomriddhi_advance.reports.ledger_report', compact('accounts', 'transactions', 'customer', 'savingsHead', 'openingBalance', 'shomriddhiAccount'));
        }

        $divisions = Division::orderBy('name')->get();

        return view('shomriddhi_advance.reports.ledger', compact('accounts', 'divisions'));
    }

    public function getFilteredAccounts(Request $request)
    {
        $query = ShomriddhiAccount::with('customer')
            ->where('origin_sister_concern_id', auth()->user()->activeConcernId());

        if ($request->division_id || $request->district_id || $request->upazila_id || $request->union_id || $request->village_id) {
            $query->whereHas('customer', function($q) use ($request) {
                if ($request->division_id) {
                    $q->where('division_id', $request->division_id);
                }
                if ($request->district_id) {
                    $q->where('district_id', $request->district_id);
                }
                if ($request->upazila_id) {
                    $q->where('upazila_id', $request->upazila_id);
                }
                if ($request->union_id) {
                    if (strpos($request->union_id, 'union_') !== false) {
                        $q->where('union_id', str_replace('union_', '', $request->union_id));
                    } elseif (strpos($request->union_id, 'pourashava_') !== false) {
                        $q->where('pourashava_id', str_replace('pourashava_', '', $request->union_id));
                    }
                }
                if ($request->village_id) {
                    $q->where('village_id', $request->village_id);
                }
            });
        }

        $accounts = $query->get();
        
        $customers = [];
        foreach ($accounts as $account) {
            if ($account->customer) {
                $customers[] = [
                    'id' => $account->customer->id,
                    'name' => $account->customer->name,
                    'member_no' => $account->customer->member_no,
                ];
            }
        }

        return response()->json($customers);
    }

    public function summary(Request $request)
    {
        if (!$request->has('search')) {
            return view('shomriddhi_advance.reports.summary');
        }

        $startDate = $request->start_date ? Carbon::parse($request->start_date)->format('Y-m-d') : null;
        $endDate   = $request->end_date   ? Carbon::parse($request->end_date)->format('Y-m-d')   : null;

        $query = ShomriddhiTransaction::with('customer:id,name,member_no')
            ->join('shomriddhi_accounts', 'shomriddhi_transactions.customer_id', '=', 'shomriddhi_accounts.customer_id')
            ->where('shomriddhi_accounts.origin_sister_concern_id', auth()->user()->activeConcernId())
            ->where('shomriddhi_transactions.status', 'Approved')
            ->select('shomriddhi_transactions.id', 'shomriddhi_transactions.customer_id', 'shomriddhi_transactions.type', 'shomriddhi_transactions.amount', 'shomriddhi_transactions.date', 'shomriddhi_transactions.note')
            ->orderBy('shomriddhi_transactions.date', 'desc');

        if ($startDate) $query->whereDate('date', '>=', $startDate);
        if ($endDate)   $query->whereDate('date', '<=', $endDate);

        // Aggregate totals via DB — does NOT load all rows into memory
        $totalsQuery = ShomriddhiTransaction::join('shomriddhi_accounts', 'shomriddhi_transactions.customer_id', '=', 'shomriddhi_accounts.customer_id')
            ->where('shomriddhi_accounts.origin_sister_concern_id', auth()->user()->activeConcernId())
            ->where('shomriddhi_transactions.status', 'Approved');
        if ($startDate) $totalsQuery->whereDate('date', '>=', $startDate);
        if ($endDate)   $totalsQuery->whereDate('date', '<=', $endDate);

        $totalDeposit  = (clone $totalsQuery)->where('type', 'deposit')->sum('amount');
        $totalWithdraw = (clone $totalsQuery)->where('type', 'withdraw')->sum('amount');
        $totalInterest = (clone $totalsQuery)->where('type', 'interest_capitalization')->sum('amount');

        // Limit to 5000 rows max to protect server; prompt user to narrow date range
        $count        = $query->count();
        $rowLimit     = 5000;
        $transactions = $query->limit($rowLimit)->get();
        $truncated    = $count > $rowLimit;

        return view('shomriddhi_advance.reports.summary_report', compact(
            'transactions', 'totalDeposit', 'totalWithdraw', 'totalInterest', 'count', 'truncated', 'rowLimit'
        ));
    }

    public function summaryDataTable(Request $request)
    {
        $startDate = $request->start_date ? Carbon::parse($request->start_date)->format('Y-m-d') : null;
        $endDate   = $request->end_date   ? Carbon::parse($request->end_date)->format('Y-m-d')   : null;

        // Totals-only mode: return aggregate sums without loading all rows
        if ($request->totals_only) {
            $base = ShomriddhiTransaction::join('shomriddhi_accounts', 'shomriddhi_transactions.customer_id', '=', 'shomriddhi_accounts.customer_id')
                ->where('shomriddhi_accounts.origin_sister_concern_id', auth()->user()->activeConcernId())
                ->where('shomriddhi_transactions.status', 'Approved');
            if ($startDate) $base->whereDate('date', '>=', $startDate);
            if ($endDate)   $base->whereDate('date', '<=', $endDate);

            return response()->json([
                'total_deposit'  => (clone $base)->where('type', 'deposit')->sum('amount'),
                'total_withdraw' => (clone $base)->where('type', 'withdraw')->sum('amount'),
                'total_interest' => (clone $base)->where('type', 'interest_capitalization')->sum('amount'),
            ]);
        }

        $query = ShomriddhiTransaction::with('customer')
            ->join('shomriddhi_accounts', 'shomriddhi_transactions.customer_id', '=', 'shomriddhi_accounts.customer_id')
            ->where('shomriddhi_accounts.origin_sister_concern_id', auth()->user()->activeConcernId())
            ->where('shomriddhi_transactions.status', 'Approved')
            ->select('shomriddhi_transactions.id', 'shomriddhi_transactions.customer_id', 'shomriddhi_transactions.type', 'shomriddhi_transactions.amount', 'shomriddhi_transactions.date', 'shomriddhi_transactions.note');

        if ($startDate) $query->whereDate('date', '>=', $startDate);
        if ($endDate)   $query->whereDate('date', '<=', $endDate);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('customer_name', fn($row) => $row->customer
                ? $row->customer->name . '<br><small class="text-muted">' . ($row->customer->member_no ?? '') . '</small>'
                : 'N/A')
            ->addColumn('type_badge', function($row) {
                if ($row->type == 'deposit') return '<span class="badge badge-success">Deposit</span>';
                if ($row->type == 'interest_capitalization') return '<span class="badge badge-info">Interest</span>';
                return '<span class="badge badge-danger">Withdraw</span>';
            })
            ->addColumn('amount_formatted', fn($row) => '৳ ' . number_format($row->amount, 2))
            ->rawColumns(['customer_name', 'type_badge'])
            ->make(true);
    }

    public function dailyInterestLogs(Request $request)
    {
        if (!$request->has('search')) {
            return view('shomriddhi_advance.reports.daily_interest');
        }

        $startDate = $request->start_date ? Carbon::parse($request->start_date)->format('Y-m-d') : null;
        $endDate   = $request->end_date   ? Carbon::parse($request->end_date)->format('Y-m-d')   : null;

        $query = ShomriddhiDailyInterest::with('customer')
            ->where('origin_sister_concern_id', auth()->user()->activeConcernId());

        if ($startDate) $query->whereDate('date', '>=', $startDate);
        if ($endDate)   $query->whereDate('date', '<=', $endDate);

        if ($request->customer_id) {
            $query->where('shomriddhi_daily_interests.customer_id', $request->customer_id);
        }

        if ($request->status) {
            $query->where('shomriddhi_daily_interests.status', $request->status);
        }
        
        $count = $query->count();
        $rowLimit = 5000;
        $logs = $query->orderBy('date', 'desc')->limit($rowLimit)->get();
        $truncated = $count > $rowLimit;

        return view('shomriddhi_advance.reports.daily_interest_report', compact('logs', 'count', 'rowLimit', 'truncated'));
    }

    public function dailyInterestLogsDataTable(Request $request)
    {
        $startDate = $request->start_date ? Carbon::parse($request->start_date)->format('Y-m-d') : null;
        $endDate   = $request->end_date   ? Carbon::parse($request->end_date)->format('Y-m-d')   : null;

        $query = ShomriddhiDailyInterest::with('customer')
            ->where('origin_sister_concern_id', auth()->user()->activeConcernId());

        if ($startDate) $query->whereDate('date', '>=', $startDate);
        if ($endDate)   $query->whereDate('date', '<=', $endDate);

        if ($request->customer_id) {
            $query->where('shomriddhi_daily_interests.customer_id', $request->customer_id);
        }

        if ($request->status) {
            $query->where('shomriddhi_daily_interests.status', $request->status);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('customer_name', fn($row) => $row->customer
                ? $row->customer->name . '<br><small class="text-muted">' . ($row->customer->member_no ?? '') . '</small>'
                : 'N/A')
            ->addColumn('principal_formatted', fn($row) => number_format($row->principal_balance, 2))
            ->addColumn('rate_formatted', fn($row) => $row->interest_rate . '%')
            ->addColumn('earned_formatted', fn($row) => '৳ ' . number_format($row->earned_interest, 4))
            ->addColumn('status_badge', function($row) {
                if ($row->status == 'Pending') return '<span class="badge badge-warning">Pending</span>';
                return '<span class="badge badge-success">Capitalized</span>';
            })
            ->rawColumns(['customer_name', 'status_badge'])
            ->make(true);
    }

    public function collectionSheet(Request $request)
    {
        $groups = ShomriddhiGroup::where('status', 1)->get();

        if ($request->group_id && $request->month) {
            $group = ShomriddhiGroup::findOrFail($request->group_id);
            $month = $request->month;
            
            // Get the first day of the selected month to calculate opening balances
            $startOfMonthObj = Carbon::parse($month . '-01')->startOfMonth();
            $startOfMonth = $startOfMonthObj->format('Y-m-d');
            
            // Calculate collection dates for this month based on group's collection_day
            $collectionDates = [];
            $currentDate = $startOfMonthObj->copy();
            
            // Find the first occurrence of the collection day in this month
            while ($currentDate->dayOfWeek != $group->collection_day) {
                $currentDate->addDay();
            }
            
            // Add all occurrences in this month (usually 4 or 5)
            while ($currentDate->format('Y-m') == $month) {
                $collectionDates[] = $currentDate->format('d/m/y');
                $currentDate->addWeek();
            }
            
            $membersData = [];
            $members = Supplier::where('origin_sister_concern_id', auth()->user()->activeConcernId())
                        ->whereIn('type',[SupplierType::$CUSTOMER,SupplierType::$DEALER,SupplierType::$CORPORATE_CUSTOMER])
                        ->where('shomriddhi_group_id', $group->id)
                        ->orderBy('member_no')->get();
            
            foreach ($members as $member) {
                // Savings Opening Balance
                $savingsOpeningBalance = 0;
                $shomriddhiAccount = ShomriddhiAccount::where('customer_id', $member->id)->first();
                if ($shomriddhiAccount) {
                    $prevDeposit = ShomriddhiTransaction::where('customer_id', $member->id)
                        ->where('status', 'Approved')
                        ->whereIn('type', ['deposit', 'interest_capitalization'])
                        ->whereDate('date', '<', $startOfMonth)
                        ->sum('amount');
                        
                    $prevWithdraw = ShomriddhiTransaction::where('customer_id', $member->id)
                        ->where('status', 'Approved')
                        ->where('type', 'withdraw')
                        ->whereDate('date', '<', $startOfMonth)
                        ->sum('amount');
                        
                    $savingsOpeningBalance = $prevDeposit - $prevWithdraw;
                }

                // Loan Opening Outstanding (Multiple Loans Support)
                $activeLoans = LoanAccount::where('customer_id', $member->id)
                    ->where('status', 'Active')
                    ->get();
                    
                $loansData = [];
                
                foreach ($activeLoans as $loan) {
                    $loanOpeningOutstanding = 0;
                    if ($loan->disbursement_date < $startOfMonth) {
                        // Total Paid before this month
                        $paidBefore = LoanTransaction::where('loan_account_id', $loan->id)
                            ->where('transaction_type', 'Recovery')
                            ->whereDate('date', '<', $startOfMonth)
                            ->sum('principal_amount');
                            
                        $loanOpeningOutstanding = $loan->principal_amount - $paidBefore;
                    } else {
                        $loanOpeningOutstanding = $loan->principal_amount; 
                    }
                    
                    $loansData[] = [
                        'loan' => $loan,
                        'loan_opening_outstanding' => $loanOpeningOutstanding
                    ];
                }

                $membersData[] = [
                    'member' => $member,
                    'savings_opening_balance' => $savingsOpeningBalance,
                    'loans' => $loansData
                ];
            }

            return view('shomriddhi_advance.reports.collection_sheet_report', compact('group', 'month', 'membersData', 'collectionDates'));
        }

        return view('shomriddhi_advance.reports.collection_sheet', compact('groups'));
    }
}
