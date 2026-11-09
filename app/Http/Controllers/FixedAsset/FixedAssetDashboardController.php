<?php

namespace App\Http\Controllers\FixedAsset;

use App\Http\Controllers\Controller;
use App\Models\FixedAsset\FixedAsset;
use Illuminate\Http\Request;

class FixedAssetDashboardController extends Controller
{
    public function index()
    {
        abort_if(!auth()->user()->hasPermissionTo('fixed_asset_view'), 403);

        $scId = auth()->user()->activeConcernId();

        $totalAssets = FixedAsset::where('sister_concern_id', $scId)->count();
        $activeAssets = FixedAsset::where('sister_concern_id', $scId)->where('status', 'active')->count();
        $soldAssets = FixedAsset::where('sister_concern_id', $scId)->where('status', 'sold')->count();
        $scrappedAssets = FixedAsset::where('sister_concern_id', $scId)->where('status', 'scrapped')->count();

        $totalCost = FixedAsset::where('sister_concern_id', $scId)->where('status', 'active')->sum('total');
        $totalBookValue = FixedAsset::where('sister_concern_id', $scId)->where('status', 'active')->sum('book_value');
        $totalAccumulatedDep = FixedAsset::where('sister_concern_id', $scId)->where('status', 'active')->sum('accumulated_depreciation');

        $recentAssets = FixedAsset::with('category')
            ->where('sister_concern_id', $scId)
            ->latest('date')
            ->take(5)
            ->get();

        return view('fixed_asset.dashboard.index', compact(
            'totalAssets', 'activeAssets', 'soldAssets', 'scrappedAssets',
            'totalCost', 'totalBookValue', 'totalAccumulatedDep', 'recentAssets'
        ));
    }
}
