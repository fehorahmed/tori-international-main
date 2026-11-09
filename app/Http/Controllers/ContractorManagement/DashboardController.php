<?php

namespace App\Http\Controllers\ContractorManagement;

use App\Http\Controllers\Controller;
use App\Models\ContractorManagement\Contractor;
use App\Models\ContractorManagement\ContractorType;

use App\Models\ContractorManagement\ContractorBill;
use App\Models\ContractorManagement\ContractorPayment;

class DashboardController extends Controller
{
    public function index()
    {
        $totalContractors = Contractor::where('origin_sister_concern_id', auth()->user()->activeConcernId())->count();
        $activeContractors = Contractor::where('origin_sister_concern_id', auth()->user()->activeConcernId())
                                        ->where('status', 1)
                                        ->count();
        $totalTypes = ContractorType::count();
        $recentContractors = Contractor::with('contractorType')
            ->where('origin_sister_concern_id', auth()->user()->activeConcernId())
            ->whereDate('created_at', auth()->user()->business_date)
            ->latest()
            ->take(5)
            ->get();
            
        $totalBilled = ContractorBill::where('sister_concern_id', auth()->user()->activeConcernId())->sum('total_amount');
        $totalPaid = ContractorPayment::where('sister_concern_id', auth()->user()->activeConcernId())->sum('amount'); // Paid + Advance
        $totalDue = $totalBilled - $totalPaid;

        return view('contractor_management.dashboard', compact('totalContractors', 'activeContractors', 'totalTypes', 'recentContractors', 'totalBilled', 'totalPaid', 'totalDue'));
    }
}
