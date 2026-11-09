<?php

namespace App\Models\Ecommerce;

use App\Models\Settings\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Compare extends Model
{
    use HasFactory;

    protected $fillable = ['customer_id', 'product_id'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

