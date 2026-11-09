<?php

namespace App\Models\ShomriddhiFdr;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FdrTransaction extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function account()
    {
        return $this->belongsTo(FdrAccount::class, 'fdr_account_id');
    }
}
