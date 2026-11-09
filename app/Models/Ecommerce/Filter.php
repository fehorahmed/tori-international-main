<?php

namespace App\Models\Ecommerce;

use App\Models\Settings\Category;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Filter extends Model
{
    use HasFactory;

    protected $fillable = [
        'name','type'
    ];


    public function options() {
        return $this->hasMany(FilterOption::class);
    }
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_filter');
    }
    
}
