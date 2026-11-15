<?php

namespace App\Services\Ecommerce;

use App\Models\Settings\Product;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class CompareService
{
    protected $compareKey = 'compare';
    protected $maxItems   = 4; // Max products in compare list

    /** Get current compare list from session */
    protected function getCompare()
    {
        return Session::get($this->compareKey, []);
    }

    /** Get all compared products */
    public function all()
    {
        $rows = $this->getCompare();
        $eligible = \App\Services\Membership\DiscountService::eligible(\Illuminate\Support\Facades\Auth::guard('customer')->user());
        $products = Product::whereIn('id', array_column($rows, 'product_id'))->get()->keyBy('id');
        return array_map(function ($row) use ($products, $eligible) {
            if ($product = $products->get($row['product_id'])) $row['price'] = \App\Services\Membership\CashoutService::money(\App\Services\Membership\DiscountService::price($product, $eligible)['net']);
            return $row;
        }, $rows);
    }

    /** Add product to compare */
    public function add($productId)
    {
        $compare = $this->getCompare();

        if (!isset($compare[$productId])) {
            // Enforce max items
            if (count($compare) >= $this->maxItems) {
                array_shift($compare); // remove oldest item
            }

            $product = Product::with('brand', 'category')->findOrFail($productId);

            $compare[$productId] = [
                'product_id'  => $product->id,
                'name'        => $product->name,
                'slug'       => $product->slug,
                'image'       => $product->image,
                'price'       => $product->selling_price,
                'description' => $product->description,
                'brand'       => $product->brand->name ?? null,
                'category'    => $product->category->name ?? null,
            ];

            Session::put($this->compareKey, $compare);
        }
    }

    /** Remove product from compare */
    public function remove($productId)
    {
        $compare = $this->getCompare();
        if (isset($compare[$productId])) {
            unset($compare[$productId]);
            Session::put($this->compareKey, $compare);
        }
    }

    /** Clear compare list */
    public function clear()
    {
        Session::forget($this->compareKey);
    }

    /** Count compare items */
    public function count()
    {
        return count($this->getCompare());
    }

    /** Check if product exists in compare */
    public function has($productId)
    {
        $compare = $this->getCompare();
        return isset($compare[$productId]);
    }
}
