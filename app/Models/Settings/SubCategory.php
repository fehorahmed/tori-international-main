<?php

namespace App\Models\Settings;

use App\Models\Settings\Product;
use App\Models\Settings\Category;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubCategory extends Model
{
    use HasFactory,SoftDeletes;
    protected $guarded = [];

    public function category(){
        return $this->belongsTo(Category::class);
    }
    public function products(){
        return $this->hasMany(Product::class,'sub_category_id','id');
    }
}
