<?php

namespace App\Models\ShomriddhiDps;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DpsNominee extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function dpsAccount(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(DpsAccount::class, 'dps_account_id');
    }

    public function nominee(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Nominee::class, 'nominee_id');
    }
}
