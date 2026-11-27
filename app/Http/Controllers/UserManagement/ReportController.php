<?php

namespace App\Http\Controllers\UserManagement;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function userReport()
    {
        $users = User::whereReportBranch('sister_concern_id')->get();
        return view('user_management.reports.user', compact('users'));
    }
}
