<?php

namespace App\Http\Controllers\AccountsWorkspace;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        return view('accounts_workspace.dashboard');
    }
}
