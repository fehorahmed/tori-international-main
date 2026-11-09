<?php

namespace App\Models\TraineeManagement;

use App\Models\Crm\CrmClient;
use App\Models\Hrm\Employee;
use App\Models\Sales\TaskAssignment;
use App\Models\Sales\TaskItem;
use Illuminate\Database\Eloquent\Model;

class TraineeVisit extends Model
{
    protected $guarded = [];

    public function trainee()
    {
        return $this->belongsTo(Employee::class, 'trainee_id');
    }

    public function customer()
    {
        return $this->belongsTo(CrmClient::class, 'customer_id');
    }

    public function visitClient()
    {
        return $this->belongsTo(TraineeVisitClient::class, 'trainee_visit_client_id');
    }

    public function task()
    {
        return $this->belongsTo(TaskAssignment::class, 'task_id');
    }

    public function taskItem()
    {
        return $this->belongsTo(TaskItem::class, 'task_item_id');
    }

    public function visitType()
    {
        return $this->belongsTo(TraineeVisitType::class, 'trainee_visit_type_id');
    }
}
