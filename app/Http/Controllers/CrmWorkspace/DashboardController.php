<?php

namespace App\Http\Controllers\CrmWorkspace;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        return view('crm_workspace.dashboard');
    }
}
