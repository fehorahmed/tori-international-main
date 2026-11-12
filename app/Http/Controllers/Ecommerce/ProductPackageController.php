<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\Settings\Category;
use App\Models\Settings\Product;
use App\Models\Settings\Unit;
use App\Models\Settings\Warehouse;
use App\Services\Packages\ProductPackageService;
use App\Traits\UploadTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductPackageController extends Controller
{
    use UploadTrait;

    public function index()
    {
        abort_unless(auth()->user()->can('ecommerce_product'), 403);
        $packages = Product::where('catalog_type', 'package')->when(auth()->user()->role !== 'Admin', fn ($q) => $q->whereHas('packageWarehouse', fn ($w) => $w->where('sister_concern_id', auth()->user()->activeConcernId())))->with(['packageItems.product', 'packageWarehouse.sisterConcern'])->latest('id')->paginate(20);

        return view('ecommerce.packages.index', compact('packages'));
    }

    public function create()
    {
        abort_unless(auth()->user()->can('ecommerce_product_create'), 403);

        return $this->form(new Product(['status' => 1, 'is_show' => 0]));
    }

    public function edit(Product $package)
    {
        abort_unless(auth()->user()->can('ecommerce_product_edit'), 403);
        abort_unless($package->isPackage(), 404);
        abort_unless(auth()->user()->role === 'Admin' || $package->packageWarehouse?->sister_concern_id == auth()->user()->activeConcernId(), 403);

        return $this->form($package);
    }

    private function form(Product $package)
    {
        $package->load('packageItems');
        $products = Product::where('catalog_type', 'single')->where('status', 1)->with('unit')->orderBy('name')->get();
        $categories = Category::where('status', 1)->get();
        $units = Unit::where('status', 1)->get();
        $warehouses = Warehouse::with('sisterConcern')->where('status', 1)->when(auth()->user()->role !== 'Admin', fn ($q) => $q->where('sister_concern_id', auth()->user()->activeConcernId()))->get();

        return view('ecommerce.packages.form', compact('package', 'products', 'categories', 'units', 'warehouses'));
    }

    public function store(Request $r, ProductPackageService $service)
    {
        abort_unless(auth()->user()->can('ecommerce_product_create'), 403);

        return $this->save($r, new Product, $service);
    }

    public function update(Request $r, Product $package, ProductPackageService $service)
    {
        abort_unless(auth()->user()->can('ecommerce_product_edit'), 403);
        abort_unless($package->isPackage(), 404);
        abort_unless(auth()->user()->role === 'Admin' || $package->packageWarehouse?->sister_concern_id == auth()->user()->activeConcernId(), 403);

        return $this->save($r, $package, $service);
    }

    private function save(Request $r, Product $package, ProductPackageService $service)
    {
        $data = $r->validate(['name' => 'required|string|max:255', 'category_id' => 'required|exists:categories,id', 'unit_id' => 'required|exists:units,id', 'package_warehouse_id' => 'required|exists:warehouses,id', 'selling_price' => 'required|numeric|decimal:0,2|min:0.01|max:10000000', 'description' => 'nullable|string|max:10000', 'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120', 'status' => 'required|boolean', 'is_show' => 'required|boolean', 'items' => 'required|array|min:2|max:50', 'items.*.product_id' => 'required|integer|distinct|exists:products,id', 'items.*.quantity' => 'required|integer|min:1|max:10000']);
        $warehouse = Warehouse::with('sisterConcern')->findOrFail($data['package_warehouse_id']);
        abort_unless($warehouse->status == 1 && $warehouse->sisterConcern?->status == 1 && (auth()->user()->role === 'Admin' || $warehouse->sister_concern_id == auth()->user()->activeConcernId()), 403);
        DB::transaction(function () use ($package, $r, $data, $service) {
            if ($package->exists) {
                Product::whereKey($package->id)->lockForUpdate()->firstOrFail();
            }
            $category = Category::findOrFail($data['category_id']);
            $image = $package->image;
            if ($r->hasFile('image')) {
                $image = $this->uploadImage($r->file('image'), 'product/image');
            }
            $package->fill(collect($data)->except(['items', 'image'])->all());
            $package->catalog_type = 'package';
            $package->product_type = $category->product_type ?? 2;
            $package->image = $image;
            $package->old_selling_price = $data['selling_price'];
            $package->discount = 0;
            $package->discount_percent = 0;
            if (! $package->exists) {
                $package->code = 'PKG-'.Str::upper(Str::random(10));
                $package->slug = Str::slug($data['name']).'-'.strtolower(Str::random(8));
                $package->user_id = auth()->id();
            }
            $package->save();
            $service->replaceItems($package, $data['items']);
        });

        return redirect()->route('product-packages.index')->with('success', 'Package saved.');
    }
}
