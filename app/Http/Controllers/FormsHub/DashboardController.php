<?php

namespace App\Http\Controllers\FormsHub;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ShomriddhiAdvance\ShomriddhiAccount;

class DashboardController extends Controller
{
    public function index()
    {
        abort_if(!auth()->user()->hasPermissionTo('forms_hub'), 403, 'Unauthorized');
        return view('forms_hub.dashboard');
    }

    public function customerForm()
    {
        abort_if(!auth()->user()->hasPermissionTo('forms_hub'), 403, 'Unauthorized');
        $pageTitle = 'Customer Data Collection Form';
        return view('forms_hub.forms.customer', compact('pageTitle'));
    }

    public function supplierForm()
    {
        abort_if(!auth()->user()->hasPermissionTo('forms_hub'), 403, 'Unauthorized');
        $pageTitle = 'Supplier Data Collection Form';
        return view('forms_hub.forms.supplier', compact('pageTitle'));
    }

    public function dealerForm()
    {
        abort_if(!auth()->user()->hasPermissionTo('forms_hub'), 403, 'Unauthorized');
        $pageTitle = 'Dealer Data Collection Form';
        return view('forms_hub.forms.dealer', compact('pageTitle'));
    }

    public function dpsForm()
    {
        abort_if(!auth()->user()->hasPermissionTo('forms_hub'), 403, 'Unauthorized');
        $pageTitle = 'DPS Account Opening Form';
        return view('forms_hub.forms.dps', compact('pageTitle'));
    }

    public function fdrForm()
    {
        abort_if(!auth()->user()->hasPermissionTo('forms_hub'), 403, 'Unauthorized');
        $pageTitle = 'FDR Account Opening Form';
        return view('forms_hub.forms.fdr', compact('pageTitle'));
    }

    public function shomriddhiIndex()
    {
        abort_if(!auth()->user()->hasPermissionTo('forms_hub'), 403, 'Unauthorized');
        $accounts = ShomriddhiAccount::with('customer')
            ->where('origin_sister_concern_id', auth()->user()->activeConcernId())
            ->orderBy('id', 'desc')
            ->get();
        return view('forms_hub.shomriddhi.index', compact('accounts'));
    }

    public function shomriddhiGeneratePrint(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('forms_hub'), 403, 'Unauthorized');
        $request->validate(['account_id' => 'required']);
        $account = ShomriddhiAccount::with('customer')->findOrFail($request->account_id);
        return view('forms_hub.shomriddhi.print', compact('account'));
    }

    public function shomriddhiPrint($id)
    {
        abort_if(!auth()->user()->hasPermissionTo('forms_hub'), 403, 'Unauthorized');
        $account = ShomriddhiAccount::with('customer')->findOrFail($id);
        return view('forms_hub.shomriddhi.print', compact('account'));
    }
}
