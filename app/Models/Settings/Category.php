<?php

namespace App\Models\Settings;

use App\Models\Ecommerce\Filter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = [];

    public function subCategories(){
        return $this->hasMany(SubCategory::class, 'category_id');
    }
    public function filters()
    {
        return $this->belongsToMany(Filter::class, 'category_filter', 'category_id', 'filter_id');
    }
}
