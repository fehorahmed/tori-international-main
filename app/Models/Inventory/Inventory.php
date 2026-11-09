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
