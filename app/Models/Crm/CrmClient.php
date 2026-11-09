<?php

namespace App\Models\Crm;

use App\Models\District;
use App\Models\Division;
use App\Models\Pourashava;
use App\Models\Crm\Market;
use App\Models\Upazila;
use App\Models\Union;
use App\Models\Village;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrmClient extends Model
{
    use HasFactory;

    protected $guarded = [];

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
    public function union()
    {
        return $this->belongsTo(Union::class);
    }
    public function village()
    {
        return $this->belongsTo(Village::class);
    }
    public function pourashava()
    {
        return $this->belongsTo(Pourashava::class);
    }
    public function market()
    {
        return $this->belongsTo(Market::class);
    }

    public function customerType()
    {
        return $this->belongsTo(CustomerType::class, 'customer_type_id');
    }
}
