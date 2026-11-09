<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;

use App\Models\Crm\Lead;
use App\Models\BranchManagement\SisterConcern;
use App\Models\Crm\Market;
use App\Models\District;
use App\Models\Division;
use App\Models\Upazila;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function leadsReport(Request $request)
    {
        $leads = [];
        if ($request->start_date != '' && $request->end_date != '') {
            $query = Lead::with('crmClient.division', 'crmClient.district', 'crmClient.upazila', 'crmClient.union');

            $query->where('sister_concern_id', auth()->user()->activeConcernId());



            if ($request->user_id != '') {
                $query->where('user_id', $request->user_id);
            }

            if ($request->customer_type_id != '') {
                $query->whereHas('crmClient', function ($q) use ($request) {
                    $q->where('customer_type_id', $request->customer_type_id);
                });
            }

            if ($request->market_id != '') {
                $query->whereHas('crmClient', function ($q) use ($request) {
                    $q->where('market_id', $request->market_id);
                });
            }

            $query->whereBetween('date', [
                Carbon::parse($request->start_date)->format('Y-m-d'),
                Carbon::parse($request->end_date)->format('Y-m-d')
            ]);

            $leads = $query->get();
        }


        
        $userIds = Lead::where('sister_concern_id', auth()->user()->activeConcernId())->pluck('user_id')->unique();
        $users = \App\Models\User::whereIn('id', $userIds)->orderBy('name')->get();
        $customerTypes = \App\Models\Crm\CustomerType::where('status', 1)->get();
        $markets = \App\Models\Crm\Market::where('status', 1)->get();

        if ($request->has('search')) {
            return view('crm.reports.lead_report', compact('leads', 'users', 'customerTypes', 'markets'));
        }

        return view('crm.reports.lead', compact('users', 'customerTypes', 'markets'));
    }

    public function outletTypeReport(Request $request)
    {
        $outletTypes = \App\Models\Crm\CustomerType::orderBy('name')->get();
        return view('crm.reports.outlet_type', compact('outletTypes'));
    }

    public function marketReport(Request $request)
    {
        $query = Market::with('upazila')->orderBy('name');
        
        if ($request->upazila_id != '') {
            $query->where('upazila_id', $request->upazila_id);
        } elseif ($request->district_id != '') {
            $query->whereHas('upazila', function($q) use ($request) {
                $q->where('district_id', $request->district_id);
            });
        } elseif ($request->division_id != '') {
            $query->whereHas('upazila.district', function($q) use ($request) {
                $q->where('division_id', $request->division_id);
            });
        }
        
        $markets = $query->get();
        
        $divisions = Division::orderBy('name')->get();
        $districts = [];
        if ($request->division_id != '') {
            $districts = District::where('division_id', $request->division_id)->orderBy('name')->get();
        }
        $upazilas = [];
        if ($request->district_id != '') {
            $upazilas = Upazila::where('district_id', $request->district_id)->orderBy('name')->get();
        }

        if ($request->has('search')) {
            return view('crm.reports.market_report', compact('markets', 'divisions', 'districts', 'upazilas'));
        }

        return view('crm.reports.market', compact('divisions', 'districts', 'upazilas'));
    }
}
