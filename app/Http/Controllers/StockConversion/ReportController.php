<?php

namespace App\Http\Controllers\StockConversion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StockConversion\StockConversion;
use App\Models\Settings\Warehouse;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('stock_conversion_report'), 403, 'Unauthorized');

        $warehouses = Warehouse::orderBy('name')->get();
        $conversions = [];

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query = StockConversion::with('warehouse', 'items.fromProduct', 'items.toProduct');

            // Apply Date Filter
            $start = Carbon::parse($request->start_date)->format('Y-m-d');
            $end = Carbon::parse($request->end_date)->format('Y-m-d');
            $query->whereBetween('date', [$start, $end]);

            // Apply Warehouse Filter
            if ($request->filled('warehouse_id')) {
                $query->where('warehouse_id', $request->warehouse_id);
            }

            // Apply Status Filter
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Restrict Branch Managers
            $user = auth()->user();
            if ($user->role === 'Branch_Manager' && $user->sister_concern_id) {
                $query->where('sister_concern_id', $user->sister_concern_id);
            }

            $conversions = $query->orderBy('date', 'asc')->get();
        }

        return view('stock_conversion.reports.stock_conversion', compact('conversions', 'warehouses'));
    }
}
