<?php

namespace App\Repositories\Payroll\Contracts;

interface EmployeeAdvanceRepositoryInterface
{
    public function datatable(int $sisterConcernId);
    public function create(array $data);
    public function update(int $id, array $data);
    public function findById(int $id);
}
