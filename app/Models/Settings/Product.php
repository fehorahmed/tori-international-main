<?php

namespace App\Models\Settings;

use App\Models\Inventory\Inventory;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory,SoftDeletes;
    protected $guarded = [];


    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function productCategory()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
    public function subcategory()
    {
        return $this->belongsTo(SubCategory::class, 'sub_category_id');
    }
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
    public function inventory()
    {
        return $this->hasOne(Inventory::class);
    }
    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }

    public function getStockInWarehouse($warehouseId)
    {
        return $this->inventories()->where('warehouse_id', $warehouseId)->sum('quantity');
    }

    public function getStockInSisterConcern($sisterConcernId)
    {
        return $this->inventories()->where('sister_concern_id', $sisterConcernId)->sum('quantity');
    }

    /**
     * Generate a dynamic product code based on Category Name and Product Name.
     * The sequence number is GLOBAL across all products.
     */
    public static function generateProductCode($categoryName, $productName, $categoryCode = '00')
    {
        $catPrefix  = self::generatePrefix($categoryName);         // Category rule
        $prodPrefix = self::generateProductNamePrefix($productName); // Product rule

        $baseCode = $catPrefix . '-' . $prodPrefix;

        // Global sequence: find the last product ever created (by highest 3-digit serial)
        $lastProduct = self::whereNotNull('code')
            ->orderByRaw('CAST(RIGHT(code, 3) AS UNSIGNED) DESC')
            ->first();

        if ($lastProduct && preg_match('/(\d{3})$/', $lastProduct->code, $matches)) {
            $nextCodeNumber = intval($matches[1]) + 1;
        } else {
            $nextCodeNumber = 1;
        }
        
        $paddedCatCode = str_pad($categoryCode, 2, '0', STR_PAD_LEFT);

        return $baseCode . '-' . $paddedCatCode . str_pad($nextCodeNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Category prefix rule:
     * - 1 word  → first 2 characters of that word  (Battery → BA)
     * - 2+ words → first character of first 2 words (Electric Auto → EA)
     */
    private static function generatePrefix($name)
    {
        $name  = preg_replace('/[^A-Za-z\s]/', '', $name);
        $words = array_values(array_filter(explode(' ', trim($name))));
        $prefix = '';

        if (count($words) >= 2) {
            $prefix = strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        } elseif (count($words) == 1) {
            $prefix = strtoupper(substr($words[0], 0, 2));
        }

        return str_pad(substr($prefix, 0, 2), 2, 'X');
    }

    /**
     * Product name prefix rule:
     * Always first 2 characters of the FIRST word only.
     * (CNG Parts → CN,  Engine Oil → EN,  Battery → BA)
     */
    private static function generateProductNamePrefix($name)
    {
        $name  = preg_replace('/[^A-Za-z\s]/', '', $name);
        $words = array_values(array_filter(explode(' ', trim($name))));
        $prefix = '';

        if (!empty($words)) {
            $prefix = strtoupper(substr($words[0], 0, 2));
        }

        return str_pad(substr($prefix, 0, 2), 2, 'X');
    }
}
