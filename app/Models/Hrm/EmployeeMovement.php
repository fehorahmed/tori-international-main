<?php

namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeMovement extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = [];
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
