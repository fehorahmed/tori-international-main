<?php

namespace App\Services\Packages;

use App\Models\Inventory\Inventory;
use App\Models\Settings\Product;
use App\Models\Settings\Warehouse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductPackageService
{
    public static function fail(string $message): never
    {
        throw ValidationException::withMessages(['package' => $message]);
    }

    public function replaceItems(Product $package, array $items): void
    {
        if (! $package->isPackage()) {
            self::fail('Only packages can contain products.');
        }
        if (count($items) < 2 || count($items) > 50) {
            self::fail('Select at least two different products for a package.');
        }
        $seen = [];
        foreach ($items as $item) {
            $id = (int) $item['product_id'];
            $quantity = (int) $item['quantity'];
            $product = Product::find($id);
            if (isset($seen[$id]) || ! $product || $product->status != 1 || $product->isPackage() || $id === $package->id || $quantity < 1 || $quantity > 10000 || (string) $quantity !== (string) $item['quantity']) {
                self::fail('Components must be distinct active single products with positive whole quantities.');
            }
            $seen[$id] = true;
        }
        DB::transaction(function () use ($package, $items) {
            Product::whereKey($package->id)->lockForUpdate()->firstOrFail();
            $package->packageItems()->delete();
            foreach ($items as $item) {
                $package->packageItems()->create(['product_id' => $item['product_id'], 'quantity' => $item['quantity']]);
            }
        });
    }

    public function availability(Product $package): int
    {
        if (! $package->isPackage() || $package->status != 1 || ! $package->package_warehouse_id) {
            return 0;
        }
        $warehouse = Warehouse::with('sisterConcern')->find($package->package_warehouse_id);
        if (! $warehouse || $warehouse->status != 1 || $warehouse->sisterConcern?->status != 1) {
            return 0;
        }
        $items = $package->packageItems()->with('product')->get();
        if ($items->count() < 2) {
            return 0;
        }
        $available = PHP_INT_MAX;
        foreach ($items as $item) {
            if (! $item->product || $item->product->status != 1 || $item->product->isPackage()) {
                return 0;
            }
            $stock = Inventory::where('warehouse_id', $warehouse->id)->where('sister_concern_id', $warehouse->sister_concern_id)->where('product_id', $item->product_id)->sum('quantity');
            $available = min($available, (int) floor((float) $stock / $item->quantity));
        }

        return max(0, $available);
    }

    public function details(Product $package): array
    {
        return ['available_quantity' => $this->availability($package), 'components' => $package->packageItems()->with(['product.unit'])->get()->map(fn ($item) => ['product_id' => $item->product_id, 'name' => $item->product?->name, 'quantity' => $item->quantity, 'unit' => $item->product?->unit?->name])->all()];
    }
}
