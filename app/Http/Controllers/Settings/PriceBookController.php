<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Settings\Product;
use App\Models\Settings\Category;
use App\Models\Settings\ProductPriceHistory;
use App\Models\Inventory\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class PriceBookController extends Controller
{
    public function index()
    {
        $categories = Category::where('status', 1)->get();
        return view('settings.products.price_book', compact('categories'));
    }

    public function dataTable(Request $request)
    {
        $query = Product::with('unit','category')->where('status', 1);

        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('current_stock', function (Product $product) {
                // Get global stock count for this product across all warehouses
                // Alternatively, sum up inventory table for this product
                $stock = Inventory::where('product_id', $product->id)->sum('quantity');
                $badgeClass = $stock > 0 ? 'badge-success' : 'badge-danger';
                return '<span class="badge ' . $badgeClass . '">' . number_format($stock) . ' ' . ($product->unit->name ?? '') . '</span>';
            })
            ->addColumn('category.name', function (Product $product) {
                return $product->category->name ?? '';
            })
            ->addColumn('last_cost_price', function (Product $product) {
                return number_format($product->purchase_price, 2);
            })
            ->addColumn('current_dp', function (Product $product) {
                return number_format($product->wholesale_price, 2);
            })
            ->addColumn('new_dp', function (Product $product) {
                return '<input type="number" step="0.01" class="form-control form-control-sm wholesale-price-input" name="wholesale_price['.$product->id.']" value="' . $product->wholesale_price . '" style="min-width: 90px;">';
            })
            ->addColumn('current_mrp', function (Product $product) {
                return number_format($product->selling_price, 2);
            })
            ->addColumn('new_mrp', function (Product $product) {
                return '<input type="number" step="0.01" class="form-control form-control-sm selling-price-input" name="selling_price['.$product->id.']" value="' . $product->selling_price . '" style="min-width: 90px;">';
            })
            ->rawColumns(['current_stock', 'new_dp', 'new_mrp'])
            ->toJson();
    }

    public function update(Request $request)
    {
        $request->validate([
            'products' => 'required|array',
            'products.*.id' => 'required|exists:products,id',
            'products.*.wholesale_price' => 'required|numeric|min:0',
            'products.*.selling_price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->products as $productData) {
                $product = Product::find($productData['id']);
                
                $oldDp = $product->wholesale_price ?? 0;
                $oldMrp = $product->selling_price ?? 0;
                $newDp = $productData['wholesale_price'];
                $newMrp = $productData['selling_price'];

                // Only update if there's a change
                if ($oldDp != $newDp || $oldMrp != $newMrp) {
                    
                    // 1. Log History
                    ProductPriceHistory::create([
                        'product_id' => $product->id,
                        'old_wholesale_price' => $oldDp,
                        'new_wholesale_price' => $newDp,
                        'old_selling_price' => $oldMrp,
                        'new_selling_price' => $newMrp,
                        'effective_date' => date('Y-m-d'),
                        'created_by' => auth()->id()
                    ]);

                    // 2. Update Product Master
                    $product->update([
                        'wholesale_price' => $newDp,
                        'selling_price' => $newMrp
                    ]);
                }
            }
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Prices updated successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to update prices: ' . $e->getMessage()]);
        }
    }
}
