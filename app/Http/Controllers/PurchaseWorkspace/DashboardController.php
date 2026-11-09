<?php

namespace App\Http\Controllers\PurchaseWorkspace;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class DashboardController extends Controller
{
    /**
     * Display the modular dashboard for Purchase Workspace.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('purchase_workspace.dashboard');
    }
}
