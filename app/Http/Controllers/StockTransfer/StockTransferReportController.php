<?php

namespace App\Http\Controllers\StockTransfer;

use App\Http\Controllers\Controller;
use App\Models\BranchManagement\SisterConcern;
use App\Models\StockTransfer\StockTransfer;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StockTransferReportController extends Controller
{
    public function transferReport(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('stock_transfer')) {
            abort(403, 'Unauthorized');
        }

        $sister_concerns = SisterConcern::orderBy('name')->get();
        $transfers = [];

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query = StockTransfer::with('fromSisterConcern', 'toSisterConcern', 'fromWarehouse', 'toWarehouse', 'stockTransferItems.product');

            // Apply Date Filter
            $start = Carbon::parse($request->start_date)->format('Y-m-d');
            $end = Carbon::parse($request->end_date)->format('Y-m-d');
            $query->whereBetween('date', [$start, $end]);

            $query->where('is_return', 0); // Only Standard Transfers

            // Apply Direction Filter
            $activeScId = auth()->user()->activeConcernId();
            if ($request->filled('direction') && $request->direction == 'received') {
                $query->where('to_sister_concern_id', $activeScId);
                // Filter sender branch
                if ($request->filled('sister_concern')) {
                    $query->where('from_sister_concern_id', $request->sister_concern);
                }
            } else { // default to 'sent'
                $query->where('from_sister_concern_id', $activeScId);
                // Filter receiver branch
                if ($request->filled('sister_concern')) {
                    $query->where('to_sister_concern_id', $request->sister_concern);
                }
            }

            // Apply Status Filter
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Restrict Branch Managers to see only their concern's transfers
            $user = auth()->user();
            if ($user->role === 'Branch_Manager' && $user->sister_concern_id) {
                $query->where(function($q) use ($user) {
                    $q->where('from_sister_concern_id', $user->sister_concern_id)
                      ->orWhere('to_sister_concern_id', $user->sister_concern_id);
                });
            }

            $transfers = $query->orderBy('date', 'desc')->orderBy('id', 'desc')->get();
            return view('stock_transfer.report_print', compact('sister_concerns', 'transfers'));
        }

        return view('stock_transfer.report', compact('sister_concerns', 'transfers'));
    }

    public function returnReport(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('stock_transfer_report')) {
            abort(403, 'Unauthorized');
        }

        $sister_concerns = SisterConcern::orderBy('name')->get();
        $transfers = [];

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query = StockTransfer::with('fromSisterConcern', 'toSisterConcern', 'fromWarehouse', 'toWarehouse', 'stockTransferItems.product');

            // Apply Date Filter
            $start = Carbon::parse($request->start_date)->format('Y-m-d');
            $end = Carbon::parse($request->end_date)->format('Y-m-d');
            $query->whereBetween('date', [$start, $end]);

            $query->whereIn('is_return', [1, 2]); // Only Return Transfers

            // Apply Direction Filter
            $activeScId = auth()->user()->activeConcernId();
            if ($request->filled('direction') && $request->direction == 'received') {
                $query->where('to_sister_concern_id', $activeScId);
                if ($request->filled('sister_concern')) {
                    $query->where('from_sister_concern_id', $request->sister_concern);
                }
            } else { // default to 'sent'
                $query->where('from_sister_concern_id', $activeScId);
                if ($request->filled('sister_concern')) {
                    $query->where('to_sister_concern_id', $request->sister_concern);
                }
            }
            
            // Apply Status Filter
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            
            // Apply Return Reason Filter
            if ($request->filled('return_reason')) {
                $query->where('return_reason', $request->return_reason);
            }

            // Restrict Branch Managers
            $user = auth()->user();
            if ($user->role === 'Branch_Manager' && $user->sister_concern_id) {
                $query->where(function($q) use ($user) {
                    $q->where('from_sister_concern_id', $user->sister_concern_id)
                      ->orWhere('to_sister_concern_id', $user->sister_concern_id);
                });
            }

            $transfers = $query->orderBy('date', 'desc')->orderBy('id', 'desc')->get();
            return view('stock_transfer.return_report_print', compact('sister_concerns', 'transfers'));
        }

        return view('stock_transfer.return_report', compact('sister_concerns', 'transfers'));
    }
}
