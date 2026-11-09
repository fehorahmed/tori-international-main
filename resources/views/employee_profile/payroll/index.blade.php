@extends('layouts.app')

@section('title', 'My Financials')

@section('content')
<div class="row">
    <!-- Salary Structure -->
    <div class="col-md-6 mb-4">
        <div class="card card-primary card-outline h-100">
            <div class="card-header">
                <h3 class="card-title">Current Salary Structure</h3>
                <div class="card-tools">
                    <span class="badge badge-primary">Active</span>
                </div>
            </div>
            <div class="card-body p-0">
                @if($employee->salaryStructure)
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Basic Salary
                        <span class="font-weight-bold">৳ {{ number_format($employee->salaryStructure->basic_salary, 2) }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        House Rent
                        <span class="font-weight-bold">৳ {{ number_format($employee->salaryStructure->house_rent, 2) }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Medical Allowance
                        <span class="font-weight-bold">৳ {{ number_format($employee->salaryStructure->medical_allowance, 2) }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Conveyance / TA DA
                        <span class="font-weight-bold">৳ {{ number_format($employee->salaryStructure->ta_da_fixed, 2) }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Food Allowance
                        <span class="font-weight-bold">৳ {{ number_format($employee->salaryStructure->food_allowance, 2) }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center border-bottom-0">
                        <strong>Gross Salary</strong>
                        <strong class="text-primary">৳ {{ number_format($employee->salaryStructure->gross_salary, 2) }}</strong>
                    </li>
                </ul>
                @else
                <div class="alert alert-warning m-3">No salary structure is assigned to you yet.</div>
                @endif
            </div>
        </div>
    </div>

    <!-- Recent Pay Slips -->
    <div class="col-md-6 mb-4">
        <div class="card card-info card-outline h-100">
            <div class="card-header">
                <h3 class="card-title">Recent Pay Slips</h3>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th>Net Pay</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- This will be dynamic when integrated with SalaryProcess -->
                        <tr><td colspan="3" class="text-center text-muted py-4">No recent pay slips available.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
