<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Settings\Brand;
use App\Models\Settings\Category;
use App\Models\Settings\Product;

class ReportController extends Controller
{
    public function brandReport(Request $request) {
        if ($request->has('search')) {
            $query = Brand::query();
            if ($request->status != '') {
                $query->where('status', $request->status);
            }
            $brands = $query->orderBy('name')->get();
            return view('settings.reports.brand_report_data', compact('brands'));
        }
        return view('settings.reports.brand_report');
    }

    public function categoryReport(Request $request) {
        if ($request->has('search')) {
            $query = Category::query();
            if ($request->status != '') {
                $query->where('status', $request->status);
            }
            $categories = $query->orderBy('name')->get();
            return view('settings.reports.category_report_data', compact('categories'));
        }
        return view('settings.reports.category_report');
    }

    public function subCategoryReport(Request $request) {
        $categories = Category::where('status', 1)->get();
        if ($request->has('search')) {
            $query = Category::where('status', 1)->with(['subCategories' => function($q) use ($request) {
                if ($request->status != '') {
                    $q->where('status', $request->status);
                }
                $q->withCount('products')->orderBy('name');
            }]);
            if ($request->category != '') {
                $query->where('id', $request->category);
            }
            $reportCategories = $query->orderBy('name')->get();
            return view('settings.reports.sub_category_report_data', compact('categories', 'reportCategories'));
        }
        return view('settings.reports.sub_category_report', compact('categories'));
    }

    public function productReport(Request $request) {
        $categories = Category::all();
        $brands = Brand::where('status', 1)->get();
        if ($request->has('search')) {
            $query = Product::with('category', 'subcategory', 'brand', 'unit');
            if ($request->category != '') {
                $query->where('category_id', $request->category);
            }
            if ($request->sub_category != '') {
                $query->where('sub_category_id', $request->sub_category);
            }
            if ($request->brand != '') {
                $query->where('brand_id', $request->brand);
            }
            if ($request->status != '') {
                $query->where('status', $request->status);
            }
            $products = $query->orderBy('name')->get();
            return view('settings.reports.product_report_data', compact('products', 'categories', 'brands'));
        }
        return view('settings.reports.product_report', compact('categories', 'brands'));
    }

    public function priceBookReport(Request $request)
    {
        $categories = Category::where('status', 1)->get();
        if ($request->has('search')) {
            $query = Product::with('category')->where('status', 1);
            if ($request->category != '') {
                $query->where('category_id', $request->category);
            }
            $products = $query->orderBy('name')->get();
            
            $products->map(function($p) {
                $p->image_url = ($p->image && file_exists($p->image)) ? asset($p->image) : null;
                return $p;
            });
            
            return response()->json(['products' => $products]);
        }
        return view('settings.reports.price_book', compact('categories'));
    }

    public function priceBookPrint(Request $request)
    {
        $request->validate(['product_ids' => 'required|array']);
        
        $products = Product::with('category')->whereIn('id', $request->product_ids)->orderBy('name')->get();
        $programs = $request->program ?? [];
        
        return view('settings.reports.price_book_print', compact('products', 'programs'));
    }

    public function priceBookHistoryReport(Request $request)
    {
        $products = \App\Models\Settings\Product::where('status', 1)->get();
        if ($request->has('search')) {
            $query = \App\Models\Settings\ProductPriceHistory::with('product', 'creator');
            if ($request->product != '') {
                $query->where('product_id', $request->product);
            }
            if ($request->start_date != '' && $request->end_date != '') {
                $query->whereBetween('effective_date', [
                    \Carbon\Carbon::parse($request->start_date)->format('Y-m-d'),
                    \Carbon\Carbon::parse($request->end_date)->format('Y-m-d')
                ]);
            }
            $histories = $query->orderBy('created_at', 'desc')->get();
            return view('settings.reports.price_book_history_data', compact('histories', 'products'));
        }
        return view('settings.reports.price_book_history', compact('products'));
    }
}
