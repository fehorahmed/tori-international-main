<?php

namespace App\Models\Ecommerce;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'type',
        'sort',
        'image',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }
}
