<?php

namespace App\Http\Controllers\ShomriddhiDps;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ShomriddhiDps\DpsAccount;
use App\Models\ShomriddhiDps\DpsTransaction;
use App\Models\ShomriddhiDps\DpsScheme;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function activeAccounts(Request $request)
    {
        $schemes  = DpsScheme::all();
        $accounts = [];

        if ($request->has('search')) {
            $query = DpsAccount::with(['customer', 'scheme'])
                ->where('origin_sister_concern_id', auth()->user()->activeConcernId())
                ->where('status', 'Active');

            if ($request->scheme_id) $query->where('scheme_id', $request->scheme_id);

            $accounts = $query->orderBy('opening_date', 'asc')->get();
            return view('shomriddhi_dps.reports.active_accounts_report', compact('accounts'));
        }

        return view('shomriddhi_dps.reports.active_accounts', compact('schemes'));
    }

    public function maturedAccounts(Request $request)
    {
        $accounts = [];

        if ($request->has('search')) {
            $accounts = DpsAccount::with(['customer', 'scheme'])
                ->where('origin_sister_concern_id', auth()->user()->activeConcernId())
                ->where('status', 'Matured')
                ->orderBy('maturity_date', 'desc')->get();
            return view('shomriddhi_dps.reports.matured_accounts_report', compact('accounts'));
        }

        return view('shomriddhi_dps.reports.matured_accounts');
    }

    public function closedAccounts(Request $request)
    {
        $accounts = [];

        if ($request->has('search')) {
            $query = DpsAccount::with(['customer', 'scheme'])
                ->where('origin_sister_concern_id', auth()->user()->activeConcernId())
                ->whereIn('status', ['Closed']);

            if ($request->start_date) {
                try {
                    $startDate = Carbon::createFromFormat('d-m-Y', $request->start_date)->format('Y-m-d');
                } catch (\Exception $e) {
                    $startDate = Carbon::parse($request->start_date)->format('Y-m-d');
                }
                $query->whereDate('updated_at', '>=', $startDate);
            }
            if ($request->end_date) {
                try {
                    $endDate = Carbon::createFromFormat('d-m-Y', $request->end_date)->format('Y-m-d');
                } catch (\Exception $e) {
                    $endDate = Carbon::parse($request->end_date)->format('Y-m-d');
                }
                $query->whereDate('updated_at', '<=', $endDate);
            }

            $accounts = $query->orderBy('updated_at', 'desc')->get();
            return view('shomriddhi_dps.reports.closed_accounts_report', compact('accounts'));
        }

        return view('shomriddhi_dps.reports.closed_accounts');
    }

    public function defaultedAccounts(Request $request)
    {
        $accounts = [];

        if ($request->has('search')) {
            $accounts = DpsAccount::with(['customer', 'scheme'])
                ->where('origin_sister_concern_id', auth()->user()->activeConcernId())
                ->where('status', 'Defaulted')
                ->orderBy('id', 'desc')->get();
            return view('shomriddhi_dps.reports.defaulted_accounts_report', compact('accounts'));
        }

        return view('shomriddhi_dps.reports.defaulted_accounts');
    }

    public function transaction(Request $request)
    {
        $transactions = [];

        if ($request->has('search')) {
            $query = DpsTransaction::with('dpsAccount.customer')
                ->join('dps_accounts', 'dps_transactions.dps_account_id', '=', 'dps_accounts.id')
                ->where('dps_accounts.origin_sister_concern_id', auth()->user()->activeConcernId())
                ->select('dps_transactions.*');

            if ($request->type)       $query->where('dps_transactions.type', $request->type);
            if ($request->start_date) {
                try {
                    $startDate = Carbon::createFromFormat('d-m-Y', $request->start_date)->format('Y-m-d');
                } catch (\Exception $e) {
                    $startDate = Carbon::parse($request->start_date)->format('Y-m-d');
                }
                $query->whereDate('dps_transactions.date', '>=', $startDate);
            }
            if ($request->end_date) {
                try {
                    $endDate = Carbon::createFromFormat('d-m-Y', $request->end_date)->format('Y-m-d');
                } catch (\Exception $e) {
                    $endDate = Carbon::parse($request->end_date)->format('Y-m-d');
                }
                $query->whereDate('dps_transactions.date', '<=', $endDate);
            }

            $transactions = $query->orderBy('dps_transactions.date', 'desc')->get();
            return view('shomriddhi_dps.reports.transaction_report', compact('transactions'));
        }

        return view('shomriddhi_dps.reports.transaction');
    }

    public function ledger(Request $request)
    {
        $accounts   = DpsAccount::with('customer')
            ->where('origin_sister_concern_id', auth()->user()->activeConcernId())
            ->get();

        $dpsAccount   = null;
        $transactions = [];

        if ($request->account_id) {
            $dpsAccount = DpsAccount::with(['customer', 'scheme'])
                ->where('id', $request->account_id)
                ->where('origin_sister_concern_id', auth()->user()->activeConcernId())
                ->first();

            if ($dpsAccount) {
                $transactions = DpsTransaction::where('dps_account_id', $dpsAccount->id)
                    ->orderBy('date', 'asc')->orderBy('id', 'asc')->get();

                return view('shomriddhi_dps.reports.ledger_report', compact('dpsAccount', 'transactions'));
            }
        }

        return view('shomriddhi_dps.reports.ledger', compact('accounts'));
    }

    public function schemeSummary(Request $request)
    {
        $schemes = [];

        if ($request->has('search')) {
            $scId = auth()->user()->activeConcernId();

            $schemes = DpsScheme::withCount(['accounts as active_count' => fn($q) => $q->where('origin_sister_concern_id', $scId)->where('status', 'Active')])
                ->withSum(['accounts as total_deposited' => fn($q) => $q->where('origin_sister_concern_id', $scId)->where('status', 'Active')], 'total_deposited')
                ->withSum(['accounts as total_payable' => fn($q) => $q->where('origin_sister_concern_id', $scId)->where('status', 'Active')], 'payable_at_maturity')
                ->get();

            return view('shomriddhi_dps.reports.scheme_summary_report', compact('schemes'));
        }

        return view('shomriddhi_dps.reports.scheme_summary');
    }
}
