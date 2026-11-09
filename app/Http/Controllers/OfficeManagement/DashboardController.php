<?php

namespace App\Http\Controllers\OfficeManagement;

use App\Http\Controllers\Controller;
use App\Models\OfficeManagement\Notice;
use App\Models\OfficeManagement\Meeting;

class DashboardController extends Controller
{
    public function index()
    {
        if (!auth()->user()->can('office_management')) {
            abort(403, 'Unauthorized');
        }

        $totalNotices = Notice::count();
        $urgentNotices = Notice::where('is_urgent', 1)->count();
        
        $totalMeetings = Meeting::count();
        $upcomingMeetings = Meeting::where('meeting_date', '>=', now()->toDateString())->count();

        $recentNotices = Notice::whereDate('created_at', auth()->user()->business_date)->orderBy('created_at', 'desc')->take(5)->get();

        return view('office_management.dashboard', compact(
            'totalNotices', 
            'urgentNotices', 
            'totalMeetings', 
            'upcomingMeetings',
            'recentNotices'
        ));
    }
}
