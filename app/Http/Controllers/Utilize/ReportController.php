<?php

namespace App\Http\Controllers\Utilize;

use App\Http\Controllers\Controller;
use App\Models\Utilize\ProductUtilize;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('product_utilize_report'), 403, 'Unauthorized');

        $query = ProductUtilize::with('warehouse', 'productUtilizeItems.product')
            ->where('sister_concern_id', auth()->user()->activeConcernId());

        if ($request->has('start_date') && $request->start_date != '') {
            $query->whereDate('date', '>=', Carbon::parse($request->start_date)->format('Y-m-d'));
        }

        if ($request->has('end_date') && $request->end_date != '') {
            $query->whereDate('date', '<=', Carbon::parse($request->end_date)->format('Y-m-d'));
        }

        $reports = $query->orderBy('date', 'desc')->get();

        return view('product_utilize.reports.product_utilize_report', compact('reports'));
    }
}
