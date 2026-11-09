<?php

namespace App\Models\TraineeManagement;

use App\Models\Division;
use App\Models\District;
use App\Models\Upazila;
use App\Models\Union;
use App\Models\Pourashava;
use App\Models\Supplier;
use App\Models\Village;
use Illuminate\Database\Eloquent\Model;

class TraineeVisitClient extends Model
{
    protected $guarded = [];

    public function trainee()
    {
        return $this->belongsTo(Supplier::class, 'trainee_id');
    }

    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function upazila()
    {
        return $this->belongsTo(Upazila::class);
    }

    public function pourashava()
    {
        return $this->belongsTo(Pourashava::class);
    }

    public function union()
    {
        return $this->belongsTo(Union::class);
    }
    public function village()
    {
        return $this->belongsTo(Village::class);
    }
}
