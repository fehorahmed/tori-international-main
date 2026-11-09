<?php

namespace App\Models\Settings;

use App\Models\BranchManagement\SisterConcern;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = [];

    public function sisterConcern()
    {
        return $this->belongsTo(SisterConcern::class, 'sister_concern_id');
    }
}
