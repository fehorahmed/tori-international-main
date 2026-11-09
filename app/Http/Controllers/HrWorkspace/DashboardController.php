<?php

namespace App\Http\Controllers\HrWorkspace;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        return view('hr_workspace.dashboard');
    }
}
