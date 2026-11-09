<?php

namespace App\Http\Controllers\BranchManagement;

use App\Http\Controllers\Controller;
use App\Models\BranchManagement\SisterConcern;

use App\Services\BranchManagement\FinancialService;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        if (!auth()->user()->can('branch_management')) {
            abort(403, 'Unauthorized');
        }

        $totalBranches = SisterConcern::count();
        $activeBranches = SisterConcern::where('status', 1)->count();
        $recentBranches = SisterConcern::orderBy('created_at', 'desc')->take(5)->get();

        $activeSisterConcern = SisterConcern::find(auth()->user()->activeConcernId());
        $today = $activeSisterConcern && $activeSisterConcern->business_date 
            ? Carbon::parse($activeSisterConcern->business_date)->format('Y-m-d') 
            : Carbon::now()->format('Y-m-d');
        
        // Calculate for All Branches
        $allBranchesData = FinancialService::calculateDailyIncomeStatement($today, $today, null);
        $allBranchesProfit = $allBranchesData['netProfit'];

        // Calculate for individual branches
        $branchProfits = [];
        $branches = SisterConcern::where('status', 1)->get();
        foreach ($branches as $branch) {
            $branchData = FinancialService::calculateDailyIncomeStatement($today, $today, $branch->id);
            $branchProfits[] = [
                'branch' => $branch,
                'profit' => $branchData['netProfit']
            ];
        }

        return view('branch_management.dashboard', compact('totalBranches', 'activeBranches', 'recentBranches', 'allBranchesProfit', 'branchProfits', 'today'));
    }
}
