<?php

namespace App\Http\Controllers\FixedAsset;

use App\Http\Controllers\Controller;
use App\Models\FixedAsset\FixedAssetDepreciationLog;
use App\Services\FixedAsset\DepreciationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class DepreciationController extends Controller
{
    protected $depreciationService;

    public function __construct(DepreciationService $depreciationService)
    {
        $this->depreciationService = $depreciationService;
    }

    public function index()
    {
        abort_if(!auth()->user()->hasPermissionTo('fixed_asset_depreciation_run'), 403);
        
        $currentPeriod = Carbon::now()->format('Y-m');
        return view('fixed_asset.depreciation.index', compact('currentPeriod'));
    }

    public function dataTable()
    {
        abort_if(!auth()->user()->hasPermissionTo('fixed_asset_depreciation_run'), 403);

        $query = FixedAssetDepreciationLog::with('fixedAsset', 'voucher', 'user')
            ->where('sister_concern_id', auth()->user()->activeConcernId());

        if (request()->filled('period')) {
            $query->where('period', request('period'));
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('asset_name', fn($log) => $log->fixedAsset->name ?? '-')
            ->addColumn('asset_code', fn($log) => $log->fixedAsset->asset_code ?? '-')
            ->addColumn('accumulated_depreciation', fn($log) => number_format($log->fixedAsset->accumulated_depreciation ?? 0, 2))
            ->addColumn('book_value_before', fn($log) => number_format($log->book_value_before, 2))
            ->addColumn('depreciation_amount', fn($log) => number_format($log->depreciation_amount, 2))
            ->addColumn('book_value_after', fn($log) => number_format($log->book_value_after, 2))
            ->addColumn('voucher_no', function($log) {
                if ($log->voucher) {
                    return '<a href="' . route('voucher.details', ['voucher' => $log->voucher_id, 'voucher_type' => $log->voucher->voucher_type]) . '" target="_blank">' . $log->voucher->voucher_no . '</a>';
                }
                return '-';
            })
            ->rawColumns(['voucher_no'])
            ->toJson();
    }

    public function preview(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('fixed_asset_depreciation_run'), 403);

        $request->validate([
            'period' => 'required|date_format:Y-m'
        ]);

        $period = $request->period;
        $sisterConcernId = auth()->user()->activeConcernId();

        // Prevent future runs
        $currentPeriod = Carbon::now()->format('Y-m');
        if ($period > $currentPeriod) {
            return response()->json(['status' => false, 'message' => 'Cannot run depreciation for future periods.']);
        }

        $result = $this->depreciationService->previewDepreciation($period, $sisterConcernId);
        return response()->json($result);
    }

    public function run(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('fixed_asset_depreciation_run'), 403);

        $request->validate([
            'period' => 'required|date_format:Y-m'
        ]);

        $period = $request->period;
        $sisterConcernId = auth()->user()->activeConcernId();
        $userId = auth()->user()->id;

        // Prevent future runs
        $currentPeriod = Carbon::now()->format('Y-m');
        if ($period > $currentPeriod) {
            return back()->with('error', 'Cannot run depreciation for future periods.');
        }

        $result = $this->depreciationService->runDepreciation($period, $sisterConcernId, $userId);

        if ($result['status']) {
            return back()->with('success', $result['message'] . ' | Assets Processed: ' . $result['processed'] . ' | Total Amount: ৳' . number_format($result['amount'], 2));
        } else {
            return back()->with('error', $result['message']);
        }
    }
}
