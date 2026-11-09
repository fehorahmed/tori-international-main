<?php

namespace App\Models\ShomriddhiAdvance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Nominee;

class ShomriddhiNominee extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function nominee()
    {
        return $this->belongsTo(Nominee::class, 'nominee_id');
    }
}
