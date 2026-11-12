<?php

namespace App\Models\Inventory;

use App\Models\Project\Project;
use App\Models\Settings\Warehouse;
use App\Models\Settings\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inventory extends Model
{
    use HasFactory,SoftDeletes;
    protected $guarded = [];

    protected static function booted(): void {
        static::saving(function ($inventory) {
            if (\App\Models\Settings\Product::find($inventory->product_id)?->isPackage()) {
                \App\Services\Packages\ProductPackageService::fail('Packages use component stock and cannot have their own inventory.');
            }
        });
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
    public function product()
    {
        return $this->belongsTo(Product::class)
            ->with('unit','brand','supplier');
    }
    


}
