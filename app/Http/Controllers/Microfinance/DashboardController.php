<?php

namespace App\Http\Controllers\Microfinance;

use App\Http\Controllers\Controller;
use App\Models\Microfinance\MfMember;
use App\Models\Microfinance\MfSomiti;
use App\Models\Microfinance\MfLoan;
use App\Models\Microfinance\MfInstallment;
use App\Models\Microfinance\MfSavingsTransaction;
use App\Models\Hrm\Employee;
use App\Enumeration\RoleType;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $concernId = auth()->user()->activeConcernId();
        
        $totalSomitis = MfSomiti::where('sister_concern_id', $concernId)->count();
        $totalMembers = MfMember::where('sister_concern_id', $concernId)->where('status', 1)->count();
        $totalActiveLoans = MfLoan::where('sister_concern_id', $concernId)->where('status', 1)->count();
        
        $todayLoanCollection = MfInstallment::whereHas('loan', function($q) use ($concernId) {
            $q->where('sister_concern_id', $concernId);
        })->whereDate('paid_date', Carbon::today())->sum('paid_amount');

        $todaySavingsCollection = MfSavingsTransaction::where('sister_concern_id', $concernId)
            ->where('transaction_type', MfSavingsTransaction::TYPE_DEPOSIT)
            ->whereDate('date', Carbon::today())->sum('amount');
            
        $todayCollection = $todayLoanCollection + $todaySavingsCollection;

        $totalFieldOfficers = Employee::where('sister_concern_id', $concernId)
            ->fieldOfficers()
            ->where('status', 1)->count();

        return view('microfinance.dashboard', compact(
            'totalSomitis',
            'totalMembers',
            'totalActiveLoans',
            'todayCollection',
            'totalFieldOfficers'
        ));
    }
}

