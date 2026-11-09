<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskItem extends Model
{
    use HasFactory;
    
    protected $table = 'task_items';
    protected $guarded = [];

    public function assignment()
    {
        return $this->belongsTo(TaskAssignment::class, 'task_assignment_id');
    }

    public function type()
    {
        return $this->belongsTo(TaskType::class, 'task_type_id');
    }
}
