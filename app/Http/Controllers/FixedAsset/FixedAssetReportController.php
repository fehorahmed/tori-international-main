<?php

namespace App\Http\Controllers\FixedAsset;

use App\Http\Controllers\Controller;
use App\Models\FixedAsset\FixedAsset;
use App\Models\FixedAsset\FixedAssetCategory;
use Illuminate\Http\Request;

class FixedAssetReportController extends Controller
{
    public function registerIndex()
    {
        abort_if(!auth()->user()->hasPermissionTo('fixed_asset_view'), 403);
        $categories = FixedAssetCategory::all();
        $reportTitle = 'Asset Register Report (FAR)';
        $reportRoute = route('fixed-assets.reports.register.generate');
        return view('fixed_asset.reports.filter', compact('categories', 'reportTitle', 'reportRoute'));
    }

    public function registerGenerate(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('fixed_asset_view'), 403);
        $query = FixedAsset::with('category')->where('sister_concern_id', auth()->user()->activeConcernId());
        
        if ($request->filled('category_id')) $query->where('category_id', $request->category_id);
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('date', [
                \Carbon\Carbon::parse($request->start_date)->startOfDay(), 
                \Carbon\Carbon::parse($request->end_date)->endOfDay()
            ]);
        }
        $assets = $query->get();
        return view('fixed_asset.reports.register_result', compact('assets', 'request'));
    }

    public function depreciationIndex()
    {
        abort_if(!auth()->user()->hasPermissionTo('fixed_asset_view'), 403);
        $categories = FixedAssetCategory::all();
        $reportTitle = 'Depreciation Schedule';
        $reportRoute = route('fixed-assets.reports.depreciation.generate');
        return view('fixed_asset.reports.filter', compact('categories', 'reportTitle', 'reportRoute'));
    }

    public function depreciationGenerate(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('fixed_asset_view'), 403);
        $query = FixedAsset::with('category')->where('sister_concern_id', auth()->user()->activeConcernId());
        
        if ($request->filled('category_id')) $query->where('category_id', $request->category_id);
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('date', [
                \Carbon\Carbon::parse($request->start_date)->startOfDay(), 
                \Carbon\Carbon::parse($request->end_date)->endOfDay()
            ]);
        }
        $assets = $query->get();
        return view('fixed_asset.reports.depreciation_result', compact('assets', 'request'));
    }

    public function additionDisposalIndex()
    {
        abort_if(!auth()->user()->hasPermissionTo('fixed_asset_view'), 403);
        $categories = FixedAssetCategory::all();
        $reportTitle = 'Asset Addition & Disposal Report';
        $reportRoute = route('fixed-assets.reports.addition_disposal.generate');
        return view('fixed_asset.reports.filter', compact('categories', 'reportTitle', 'reportRoute'));
    }

    public function additionDisposalGenerate(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('fixed_asset_view'), 403);
        $query = FixedAsset::with('category', 'disposal')->where('sister_concern_id', auth()->user()->activeConcernId());
        
        if ($request->filled('category_id')) $query->where('category_id', $request->category_id);
        // Specifically look for additions or disposals within this date range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $start = \Carbon\Carbon::parse($request->start_date)->startOfDay();
            $end = \Carbon\Carbon::parse($request->end_date)->endOfDay();
            
            $query->where(function ($q) use ($start, $end) {
                // Addition
                $q->whereBetween('date', [$start, $end])
                // Disposal
                  ->orWhereHas('disposal', function ($q2) use ($start, $end) {
                      $q2->whereBetween('date', [$start, $end]);
                  });
            });
        }
        $assets = $query->get();
        return view('fixed_asset.reports.addition_disposal_result', compact('assets', 'request'));
    }

    public function categorySummaryIndex()
    {
        abort_if(!auth()->user()->hasPermissionTo('fixed_asset_view'), 403);
        $categories = FixedAssetCategory::all();
        $reportTitle = 'Category-wise Summary Report';
        $reportRoute = route('fixed-assets.reports.category_summary.generate');
        return view('fixed_asset.reports.filter', compact('categories', 'reportTitle', 'reportRoute'));
    }

    public function categorySummaryGenerate(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('fixed_asset_view'), 403);
        $query = FixedAsset::with('category')->where('sister_concern_id', auth()->user()->activeConcernId());
        
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('date', [
                \Carbon\Carbon::parse($request->start_date)->startOfDay(), 
                \Carbon\Carbon::parse($request->end_date)->endOfDay()
            ]);
        }
        
        $assets = $query->get();
        $summary = $assets->groupBy('category_id')->map(function ($items) {
            return [
                'category_name' => $items->first()->category->name ?? 'Uncategorized',
                'total_count' => $items->count(),
                'total_cost' => $items->sum('total'),
                'accumulated_depreciation' => $items->sum('accumulated_depreciation'),
                'book_value' => $items->sum('book_value'),
            ];
        });
        
        return view('fixed_asset.reports.category_summary_result', compact('summary', 'request'));
    }
}
