<?php

namespace App\Repositories\Payroll;

use App\Models\Payroll\EmployeeAdvance;
use App\Repositories\Payroll\Contracts\EmployeeAdvanceRepositoryInterface;

class EloquentEmployeeAdvanceRepository implements EmployeeAdvanceRepositoryInterface
{
    public function datatable(int $sisterConcernId)
    {
        return EmployeeAdvance::with(['employee'])
            ->where('sister_concern_id', $sisterConcernId)
            ->latest();
    }

    public function create(array $data)
    {
        return EmployeeAdvance::create($data);
    }

    public function update(int $id, array $data)
    {
        $advance = EmployeeAdvance::findOrFail($id);
        $advance->update($data);
        return $advance;
    }

    public function findById(int $id)
    {
        return EmployeeAdvance::findOrFail($id);
    }
}
