<?php

namespace App\Http\Controllers\CardManagement;

use App\Http\Controllers\Controller;
use App\Models\CardManagement\Card;
use App\Models\CardManagement\CardPrintLog;
use App\Models\Supplier;
use App\Models\Hrm\Employee;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        abort_if(!auth()->user()->hasPermissionTo('card_management'), 403);

        $pageTitle = 'Card Management Dashboard';

        $totalMemberCards = Card::where('cardable_type', Supplier::class)
            ->count();

        $activeMemberCards = Card::where('cardable_type', Supplier::class)
            ->where('status', 'Active')
            ->count();

        $totalEmployeeCards = Card::where('cardable_type', Employee::class)
            ->count();

        $activeEmployeeCards = Card::where('cardable_type', Employee::class)
            ->where('status', 'Active')
            ->count();

        $recentPrintLogs = CardPrintLog::with(['card', 'printer'])
            ->whereDate('created_at', auth()->user()->business_date)
            ->has('card')
            ->orderBy('id', 'DESC')
            ->take(10)
            ->get();

        return view('card_management.dashboard', compact(
            'pageTitle',
            'totalMemberCards',
            'activeMemberCards',
            'totalEmployeeCards',
            'activeEmployeeCards',
            'recentPrintLogs'
        ));
    }
}
