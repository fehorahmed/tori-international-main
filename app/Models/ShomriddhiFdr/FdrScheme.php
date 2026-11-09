<?php

namespace App\Models\ShomriddhiFdr;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FdrScheme extends Model
{
    use SoftDeletes;
    
    protected $guarded = [];

    public function accounts()
    {
        return $this->hasMany(FdrAccount::class, 'scheme_id');
    }
}
