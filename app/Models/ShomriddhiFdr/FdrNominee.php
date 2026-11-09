<?php

namespace App\Models\ShomriddhiFdr;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Nominee;

class FdrNominee extends Model
{
    use SoftDeletes;
    
    protected $guarded = [];

    public function account()
    {
        return $this->belongsTo(FdrAccount::class, 'fdr_account_id');
    }

    public function nominee()
    {
        return $this->belongsTo(Nominee::class, 'nominee_id');
    }
}
