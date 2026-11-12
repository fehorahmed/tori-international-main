<?php

namespace App\Models\Settings;

use Illuminate\Database\Eloquent\Model;

class ProductPackageItem extends Model
{
    protected $guarded = [];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
