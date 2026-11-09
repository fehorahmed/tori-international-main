<?php

namespace App\Http\Controllers\SalesWorkspace;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class DashboardController extends Controller
{
    /**
     * Display the modular dashboard for Sales Workspace.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('sales_workspace.dashboard');
    }
}
