<?php

namespace App\Http\Controllers\UserManagement;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserManagement\Role;

class DashboardController extends Controller
{
    public function index()
    {
        if (!auth()->user()->can('user_management')) {
            abort(403, 'Unauthorized');
        }

        $totalUsers = User::where('sister_concern_id', auth()->user()->activeConcernId())->count();
        $activeUsers = User::where('status', 1)->where('sister_concern_id', auth()->user()->activeConcernId())->count();
        $totalRoles = Role::count();
        
        $recentUsers = User::where('sister_concern_id', auth()->user()->activeConcernId())->orderBy('created_at', 'desc')->take(5)->get();

        return view('user_management.dashboard', compact('totalUsers', 'activeUsers', 'totalRoles', 'recentUsers'));
    }
}
