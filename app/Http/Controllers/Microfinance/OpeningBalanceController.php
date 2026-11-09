<?php

namespace App\Http\Controllers\Microfinance;

use App\Http\Controllers\Controller;
use App\Models\Microfinance\MfLoanProduct;
use App\Models\Microfinance\MfMember;
use App\Models\Microfinance\MfSavingsProduct;
use App\Models\Microfinance\MfSomiti;
use App\Services\Microfinance\OpeningBalanceService;
use Illuminate\Http\Request;

class OpeningBalanceController extends Controller
{
    protected OpeningBalanceService $service;

    public function __construct(OpeningBalanceService $service)
    {
        $this->service = $service;
    }

    // ── General Savings ───────────────────────────────────────────

    public function generalSavingsForm()
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_opening_balance'), 403);
        $scId     = auth()->user()->activeConcernId();
        $pageTitle= 'Opening Balance — General Savings';
        $somitis  = MfSomiti::where('sister_concern_id', $scId)->where('status', 1)->orderBy('name')->get();
        $members  = MfMember::with('somiti')->where('sister_concern_id', $scId)->where('status', 1)->orderBy('name')->get();
        $products = MfSavingsProduct::where('product_type', 1)->where('status', 1)->get();
        return view('microfinance.opening_balance.general_savings', compact('pageTitle', 'somitis', 'members', 'products'));
    }

    public function storeGeneralSavings(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_opening_balance'), 403);
        $request->validate([
            'member_id'              => 'required|exists:mf_members,id',
            'mf_savings_product_id'  => 'required|exists:mf_savings_products,id',
            'opening_date'           => 'required|string',
            'current_balance'        => 'required|numeric|min:0.01',
        ]);

        try {
            $account = $this->service->createGeneralSavingsOpening($request->all());
            return redirect()->route('microfinance.savings-account.show', $account->id)
                ->with('success', 'General Savings opening balance entry created. Account No: ' . $account->account_no);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    // ── DPS ───────────────────────────────────────────────────────

    public function dpsForm()
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_opening_balance'), 403);
        $scId     = auth()->user()->activeConcernId();
        $pageTitle= 'Opening Balance — DPS';
        $somitis  = MfSomiti::where('sister_concern_id', $scId)->where('status', 1)->orderBy('name')->get();
        $members  = MfMember::with('somiti')->where('sister_concern_id', $scId)->where('status', 1)->orderBy('name')->get();
        $products = MfSavingsProduct::where('product_type', 2)->where('status', 1)->get();
        return view('microfinance.opening_balance.dps', compact('pageTitle', 'somitis', 'members', 'products'));
    }

    public function storeDps(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_opening_balance'), 403);
        $request->validate([
            'member_id'             => 'required|exists:mf_members,id',
            'mf_savings_product_id' => 'required|exists:mf_savings_products,id',
            'opening_date'          => 'required|string',
            'installments_paid'     => 'required|integer|min:1',
            'current_balance'       => 'required|numeric|min:0.01',
            'next_due_date'         => 'required|string',
        ]);

        try {
            $account = $this->service->createDpsOpening($request->all());
            return redirect()->route('microfinance.savings-account.show', $account->id)
                ->with('success', 'DPS opening balance entry created. Account No: ' . $account->account_no);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    // ── FDR ───────────────────────────────────────────────────────

    public function fdrForm()
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_opening_balance'), 403);
        $scId     = auth()->user()->activeConcernId();
        $pageTitle= 'Opening Balance — FDR';
        $somitis  = MfSomiti::where('sister_concern_id', $scId)->where('status', 1)->orderBy('name')->get();
        $members  = MfMember::with('somiti')->where('sister_concern_id', $scId)->where('status', 1)->orderBy('name')->get();
        $products = MfSavingsProduct::where('product_type', 3)->where('status', 1)->get();
        return view('microfinance.opening_balance.fdr', compact('pageTitle', 'somitis', 'members', 'products'));
    }

    public function storeFdr(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_opening_balance'), 403);
        $request->validate([
            'member_id'               => 'required|exists:mf_members,id',
            'mf_savings_product_id'   => 'required|exists:mf_savings_products,id',
            'opening_date'            => 'required|string',
            'maturity_date'           => 'nullable|string',
            'principal_amount'        => 'required|numeric|min:0.01',
            'opening_accrued_interest'=> 'nullable|numeric|min:0',
        ]);

        try {
            $account = $this->service->createFdrOpening($request->all());
            return redirect()->route('microfinance.savings-account.show', $account->id)
                ->with('success', 'FDR opening balance entry created. Account No: ' . $account->account_no);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    // ── Loan ──────────────────────────────────────────────────────

    public function loanForm()
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_opening_balance'), 403);
        $scId     = auth()->user()->activeConcernId();
        $pageTitle= 'Opening Balance — Loan';
        $somitis  = \App\Models\Microfinance\MfSomiti::where('sister_concern_id', $scId)->where('status', 1)->orderBy('name')->get();
        $members  = MfMember::with('somiti')->where('sister_concern_id', $scId)->where('status', 1)->orderBy('name')->get();
        $products = MfLoanProduct::where('status', 1)->get();
        return view('microfinance.opening_balance.loan', compact('pageTitle', 'somitis', 'members', 'products'));
    }

    public function storeLoan(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_opening_balance'), 403);
        $request->validate([
            'member_id'            => 'required|exists:mf_members,id',
            'mf_loan_product_id'   => 'required|exists:mf_loan_products,id',
            'disbursement_date'    => 'required|string',
            'approved_amount'      => 'required|numeric|min:1',
            'total_installments'   => 'required|integer|min:1',
            'installments_paid'    => 'required|integer|min:0',
            'outstanding_principal'=> 'required|numeric|min:0.01',
            'next_due_date'        => 'required|string',
        ]);

        try {
            $loan = $this->service->createLoanOpening($request->all());
            return redirect()->route('microfinance.loan.show', $loan->id)
                ->with('success', 'Loan opening balance entry created. Loan No: ' . $loan->loan_no);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }
}
