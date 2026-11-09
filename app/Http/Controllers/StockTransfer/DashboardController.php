<?php

namespace App\Http\Controllers\StockTransfer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StockTransfer\StockTransfer;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $scId = $user->activeConcernId();

        $query = StockTransfer::query();
        
        // Restrict to the active concern's transfers (both sent and received)
        if ($scId) {
            $query->where(function($q) use ($scId) {
                $q->where('from_sister_concern_id', $scId)
                  ->orWhere('to_sister_concern_id', $scId);
            });
        }

        $totalTransfers = (clone $query)->where('is_return', 0)->count();
        
        // Pending Receipts (Incoming to this branch, status = 1 (pending dispatch) or 2 (in transit))
        $pendingTransfers = (clone $query)
            ->where('to_sister_concern_id', $scId)
            ->where('is_return', 0)
            ->whereIn('status', [1, 2])
            ->count();

        // Completed Transfers (Incoming to this branch, status = 3 or 4)
        $completedTransfers = (clone $query)
            ->where('to_sister_concern_id', $scId)
            ->where('is_return', 0)
            ->whereIn('status', [3, 4])
            ->count();
            
        // Pending Returns (Returns sent to this branch, waiting to be received, status = 1 or 2)
        $pendingReturns = (clone $query)
            ->where('to_sister_concern_id', $scId)
            ->whereIn('is_return', [1, 2])
            ->whereIn('status', [1, 2])
            ->count();
        
        $recentTransfers = (clone $query)
            ->where('is_return', 0)
            ->with(['fromSisterConcern', 'toSisterConcern'])
            ->orderBy('id', 'desc')
            ->take(5)
            ->get();
            
        $recentReturns = (clone $query)
            ->whereIn('is_return', [1, 2])
            ->with(['fromSisterConcern', 'toSisterConcern'])
            ->orderBy('id', 'desc')
            ->take(5)
            ->get();

        return view('stock_transfer.dashboard', compact(
            'totalTransfers', 
            'pendingTransfers', 
            'completedTransfers',
            'pendingReturns',
            'recentTransfers',
            'recentReturns'
        ));
    }
}
