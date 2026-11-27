<?php

namespace App\Http\Controllers\ShomriddhiFdr;

use App\Http\Controllers\Controller;
use App\Models\ShomriddhiFdr\FdrAccount;
use App\Models\ShomriddhiFdr\FdrScheme;
use App\Models\ShomriddhiFdr\FdrTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function maturity(Request $request)
    {
        $accounts = [];
        if ($request->has('search')) {
            $startDate = $request->start_date ? Carbon::parse($request->start_date)->format('Y-m-d') : null;
            $endDate = $request->end_date ? Carbon::parse($request->end_date)->format('Y-m-d') : null;

            $query = FdrAccount::with(['customer', 'scheme'])
                ->whereReportBranch('origin_sister_concern_id')
                ->whereIn('status', ['Active', 'Matured']);

            if ($startDate) {
                $query->whereDate('maturity_date', '>=', $startDate);
            }
            if ($endDate) {
                $query->whereDate('maturity_date', '<=', $endDate);
            }

            $accounts = $query->orderBy('maturity_date', 'asc')->get();

            return view('shomriddhi_fdr.reports.maturity_report', compact('accounts'));
        }

        return view('shomriddhi_fdr.reports.maturity');
    }

    public function transaction(Request $request)
    {
        $transactions = [];
        if ($request->has('search')) {
            $startDate = $request->start_date ? Carbon::parse($request->start_date)->format('Y-m-d') : null;
            $endDate = $request->end_date ? Carbon::parse($request->end_date)->format('Y-m-d') : null;

            $query = FdrTransaction::with(['account.customer'])
                ->join('fdr_accounts', 'fdr_transactions.fdr_account_id', '=', 'fdr_accounts.id')
                ->whereReportBranch('fdr_accounts.origin_sister_concern_id')
                ->select('fdr_transactions.*');

            if ($request->type) {
                $query->where('fdr_transactions.type', $request->type);
            }
            if ($startDate) {
                $query->whereDate('fdr_transactions.date', '>=', $startDate);
            }
            if ($endDate) {
                $query->whereDate('fdr_transactions.date', '<=', $endDate);
            }

            $transactions = $query->orderBy('fdr_transactions.date', 'desc')->get();

            return view('shomriddhi_fdr.reports.transaction_report', compact('transactions'));
        }

        return view('shomriddhi_fdr.reports.transaction');
    }

    public function ledger(Request $request)
    {
        $accounts = FdrAccount::with('customer')
            ->whereReportBranch('origin_sister_concern_id')
            ->get();

        $transactions = [];
        $fdrAccount = null;

        if ($request->account_id) {
            $fdrAccount = FdrAccount::with(['customer', 'scheme'])
                ->where('id', $request->account_id)
                ->whereReportBranch('origin_sister_concern_id')
                ->first();

            if ($fdrAccount) {
                $transactions = FdrTransaction::where('fdr_account_id', $fdrAccount->id)
                    ->orderBy('date', 'asc')
                    ->orderBy('id', 'asc')
                    ->get();

                return view('shomriddhi_fdr.reports.ledger_report', compact('fdrAccount', 'transactions'));
            }
        }

        return view('shomriddhi_fdr.reports.ledger', compact('accounts'));
    }

    public function activeAccounts(Request $request)
    {
        $accounts = [];
        if ($request->has('search')) {
            $query = FdrAccount::with(['customer', 'scheme'])
                ->whereReportBranch('origin_sister_concern_id')
                ->where('status', 'Active');

            if ($request->scheme_id) {
                $query->where('scheme_id', $request->scheme_id);
            }

            $accounts = $query->orderBy('id', 'desc')->get();

            return view('shomriddhi_fdr.reports.active_accounts_report', compact('accounts'));
        }

        $schemes = FdrScheme::all();

        return view('shomriddhi_fdr.reports.active_accounts', compact('schemes'));
    }

    public function closedAccounts(Request $request)
    {
        $accounts = [];
        if ($request->has('search')) {
            $query = FdrAccount::with(['customer', 'scheme'])
                ->whereReportBranch('origin_sister_concern_id')
                ->where('status', 'Closed');

            $accounts = $query->orderBy('updated_at', 'desc')->get();

            return view('shomriddhi_fdr.reports.closed_accounts_report', compact('accounts'));
        }

        return view('shomriddhi_fdr.reports.closed_accounts');
    }

    public function schemeSummary(Request $request)
    {
        $schemes = [];
        if ($request->has('search')) {
            $schemes = FdrScheme::withCount(['accounts' => function ($query) {
                $query->whereReportBranch('origin_sister_concern_id')
                    ->where('status', 'Active');
            }])->withSum(['accounts' => function ($query) {
                $query->whereReportBranch('origin_sister_concern_id')
                    ->where('status', 'Active');
            }], 'principal_amount')->get();

            return view('shomriddhi_fdr.reports.scheme_summary_report', compact('schemes'));
        }

        return view('shomriddhi_fdr.reports.scheme_summary');
    }
}
