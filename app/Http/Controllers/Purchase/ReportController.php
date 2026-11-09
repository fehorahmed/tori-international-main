<?php

namespace App\Http\Controllers\Purchase;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Purchase\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Settings\Category;
use App\Models\BranchManagement\SisterConcern;
use App\Enumeration\SupplierType;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Account\AccountHead;
use App\Models\Division;

class ReportController extends Controller
{
    public function supplierReport(Request $request) {
        $sister_concerns = SisterConcern::where('status', 1)->get();
        
        $query = Supplier::where('type', SupplierType::$SUPPLIER)
            ->where('origin_sister_concern_id', auth()->user()->activeConcernId());

        if ($request->status != '') {
            $query->where('status', $request->status);
        }

        if ($request->division_id) {
            $query->where('division_id', $request->division_id);
        }
        if ($request->district_id) {
            $query->where('district_id', $request->district_id);
        }
        if ($request->upazila_id) {
            $query->where('upazila_id', $request->upazila_id);
        }
        if ($request->union_id) {
            if (strpos($request->union_id, 'union_') !== false) {
                $query->where('union_id', str_replace('union_', '', $request->union_id));
            } elseif (strpos($request->union_id, 'pourashava_') !== false) {
                $query->where('pourashava_id', str_replace('pourashava_', '', $request->union_id));
            }
        }
        if ($request->village_id) {
            $query->where('village_id', $request->village_id);
        }

        if ($request->has('search')) {
            $suppliers = $query->orderBy('name')->get();
            return view('purchase.reports.supplier_report_data', compact('suppliers', 'sister_concerns'));
        }

        $divisions = Division::orderBy('name')->get();
        return view('purchase.reports.supplier_report', compact('sister_concerns', 'divisions'));
    }

    public function purchaseReport(Request $request) {
        $orders = [];
        $suppliers = Supplier::where('origin_sister_concern_id', auth()->user()->activeConcernId())
            ->where('type', SupplierType::$SUPPLIER)
            ->get();
        
        $query = PurchaseOrder::with('supplier');
        if ($request->has('search')) {
            $query->where('sister_concern_id', auth()->user()->activeConcernId());

            if ($request->supplier != ''){
                $query = $query->where('supplier_id', $request->supplier);
            }
            $query = $query->whereBetween('date',[
                Carbon::parse($request->start_date)->format('Y-m-d'),
                Carbon::parse($request->end_date)->format('Y-m-d')
            ]);
            
            if ($request->division_id || $request->district_id || $request->upazila_id || $request->union_id || $request->village_id) {
                $query->whereHas('supplier', function($q) use ($request) {
                    if ($request->division_id) $q->where('division_id', $request->division_id);
                    if ($request->district_id) $q->where('district_id', $request->district_id);
                    if ($request->upazila_id) $q->where('upazila_id', $request->upazila_id);
                    if ($request->union_id) {
                        if (strpos($request->union_id, 'union_') !== false) {
                            $q->where('union_id', str_replace('union_', '', $request->union_id));
                        } elseif (strpos($request->union_id, 'pourashava_') !== false) {
                            $q->where('pourashava_id', str_replace('pourashava_', '', $request->union_id));
                        }
                    }
                    if ($request->village_id) $q->where('village_id', $request->village_id);
                });
            }

            $orders = $query->get();
            
            return view('purchase.reports.purchase_report_data', compact('orders'));
        }
        $sister_concerns = SisterConcern::where('status', 1)->get();
        $divisions = Division::orderBy('name')->get();
        return view('purchase.reports.purchase', compact('suppliers', 'sister_concerns', 'divisions'));
    }

    public function purchaseDetailsReport(Request $request) {
        $orders = [];
        $suppliers = Supplier::where('origin_sister_concern_id', auth()->user()->activeConcernId())
            ->where('type', SupplierType::$SUPPLIER)
            ->get();

        $query = PurchaseOrder::with('supplier', 'purchaseItems.product');
        if ($request->has('search')) {
            $query->where('sister_concern_id', auth()->user()->activeConcernId());

            if ($request->supplier != ''){
                $query = $query->where('supplier_id', $request->supplier);
            }
            if ($request->category_group != ''){
                $query = $query->whereHas('purchaseItems.product', function($q) use($request) {
                    $q->where('category_id', $request->category_group);
                });
            }
            $query = $query->whereBetween('date',[
                Carbon::parse($request->start_date)->format('Y-m-d'),
                Carbon::parse($request->end_date)->format('Y-m-d')
            ]);
            
            if ($request->division_id || $request->district_id || $request->upazila_id || $request->union_id || $request->village_id) {
                $query->whereHas('supplier', function($q) use ($request) {
                    if ($request->division_id) $q->where('division_id', $request->division_id);
                    if ($request->district_id) $q->where('district_id', $request->district_id);
                    if ($request->upazila_id) $q->where('upazila_id', $request->upazila_id);
                    if ($request->union_id) {
                        if (strpos($request->union_id, 'union_') !== false) {
                            $q->where('union_id', str_replace('union_', '', $request->union_id));
                        } elseif (strpos($request->union_id, 'pourashava_') !== false) {
                            $q->where('pourashava_id', str_replace('pourashava_', '', $request->union_id));
                        }
                    }
                    if ($request->village_id) $q->where('village_id', $request->village_id);
                });
            }

            $orders = $query->get();
            return view('purchase.reports.purchase_details_report_data', compact('orders'));
        }
        $sister_concerns = SisterConcern::where('status', 1)->get();
        $categoryGroups = Category::all();
        $divisions = Division::orderBy('name')->get();
        
        return view('purchase.reports.purchase_details', compact('suppliers', 'sister_concerns', 'categoryGroups', 'divisions'));
    }

    public function supplierLedger(Request $request)
    {
        $startDate = $request->start_date ? date('Y-m-d', strtotime($request->start_date)) : null;
        $endDate = $request->end_date ? date('Y-m-d', strtotime($request->end_date)) : null;

        $query = Supplier::where('type', SupplierType::$SUPPLIER)
            ->where('status', 1)
            ->where('origin_sister_concern_id', auth()->user()->activeConcernId());

        if ($request->division_id) {
            $query->where('division_id', $request->division_id);
        }
        if ($request->district_id) {
            $query->where('district_id', $request->district_id);
        }
        if ($request->upazila_id) {
            $query->where('upazila_id', $request->upazila_id);
        }
        if ($request->union_id) {
            if (strpos($request->union_id, 'union_') !== false) {
                $query->where('union_id', str_replace('union_', '', $request->union_id));
            } elseif (strpos($request->union_id, 'pourashava_') !== false) {
                $query->where('pourashava_id', str_replace('pourashava_', '', $request->union_id));
            }
        }
        if ($request->village_id) {
            $query->where('village_id', $request->village_id);
        }

        $suppliers = $query->orderBy('name')->get();

        $accountHeads = [];
        if ($startDate && $endDate) {
            $previousDay = date('Y-m-d', strtotime('-1 day', strtotime($startDate)));

            $query = AccountHead::where('status', 1)
                ->whereHas('supplier', function($q) use($request) {
                    $q->where('type', SupplierType::$SUPPLIER);
                    if ($request->division_id) {
                        $q->where('division_id', $request->division_id);
                    }
                    if ($request->district_id) {
                        $q->where('district_id', $request->district_id);
                    }
                    if ($request->upazila_id) {
                        $q->where('upazila_id', $request->upazila_id);
                    }
                    if ($request->union_id) {
                        if (strpos($request->union_id, 'union_') !== false) {
                            $q->where('union_id', str_replace('union_', '', $request->union_id));
                        } elseif (strpos($request->union_id, 'pourashava_') !== false) {
                            $q->where('pourashava_id', str_replace('pourashava_', '', $request->union_id));
                        }
                    }
                    if ($request->village_id) {
                        $q->where('village_id', $request->village_id);
                    }
                });

            if ($request->supplier_id != '') {
                $query->where('supplier_id', $request->supplier_id);
            }

            $query->with([
                'openingTransactions' => function ($query) use ($previousDay) {
                    $query->where('sister_concern_id', auth()->user()->activeConcernId());
                    $query->whereDate('date', '<=', $previousDay)
                        ->select(DB::raw('account_head_id,
                            SUM(CASE WHEN transaction_type IN (1) THEN amount ELSE 0 END) AS previous_debit,
                            SUM(CASE WHEN transaction_type IN (2) THEN amount ELSE 0 END) AS previous_credit'))
                        ->groupBy('account_head_id');
                }, 'transactions' => function ($query) use ($startDate, $endDate) {
                    $query->where('sister_concern_id', auth()->user()->activeConcernId());
                    $query->whereBetween('date', [$startDate, $endDate])
                        ->orderBy('date')
                        ->orderBy('voucher_no_group_sl');
                },
                'supplier'
            ]);

            $accountHeads = $query->orderBy('name')->get();
        }

        $currentMonth = date('m');
        if ($currentMonth < 7) {
            $currentYear = date('Y') - 1;
            $currentDate = date('01-07-' . $currentYear);
        } else {
            $currentDate = date('01-07-Y');
        }

        $divisions = Division::orderBy('name')->get();

        $selectedSupplierInfo = null;
        if ($request->supplier_id != '') {
            $selectedSupplierInfo = Supplier::find($request->supplier_id);
        }

        if ($request->has('search') || $request->has('supplier_id')) {
            return view('purchase.reports.supplier_ledger_report', compact('accountHeads', 'currentDate', 'suppliers', 'divisions', 'selectedSupplierInfo'));
        }

        return view('purchase.reports.supplier_ledger', compact('accountHeads', 'currentDate', 'suppliers', 'divisions'));
    }
}
