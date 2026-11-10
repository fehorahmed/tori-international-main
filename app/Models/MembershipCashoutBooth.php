<?php

namespace App\Models;

use App\Models\BranchManagement\SisterConcern;
use Illuminate\Database\Eloquent\Model;

class MembershipCashoutBooth extends Model
{
    protected $guarded = [];

    public function branch()
    {
        return $this->belongsTo(SisterConcern::class, 'branch_id');
    }
}
