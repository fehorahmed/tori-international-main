@extends('layouts.app')

@section('title', 'Employee Workspace')

@section('content')
@include('layouts.partial.__premium_dashboard_css')

<!-- Welcome Banner -->
<div class="row mb-4">
    <div class="col-12">
        <div class="welcome-banner theme-office d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #0f766e, #14b8a6);">
            <div style="z-index: 1;">
                <div class="welcome-title text-white">Welcome back, {{ $employee->name }}! 🎉</div>
                <div class="welcome-subtitle text-white-50">
                    {{ $employee->designation->name ?? 'N/A' }} 
                    @if($employee->department)
                        • {{ $employee->department->name }}
                    @endif
                </div>
            </div>
            <div class="d-none d-md-block" style="z-index: 1; opacity: 0.9;">
                <img src="{{ $employee->image ? asset($employee->image) : asset('assets/img/avatars/1.png') }}" alt="Avatar" class="rounded-circle shadow-sm" style="width: 80px; height: 80px; object-fit: cover; border: 3px solid rgba(255,255,255,0.2);">
            </div>
        </div>
    </div>
</div>

<!-- Key Metrics Cards -->
<div class="row">
    <!-- Current Salary -->
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="premium-card border-top-office h-100" style="border-top-color: #3b82f6;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Current Salary</div>
                        <div class="premium-number text-dark" style="font-size: 1.4rem;">৳ {{ number_format($employee->salaryStructure->gross_salary ?? 0, 2) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #60a5fa, #3b82f6);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-wallet" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('employee_profile.payroll.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>View Details</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>

    <!-- Leave Taken -->
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="premium-card border-top-urgent h-100" style="border-top-color: #f59e0b;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Leave Taken</div>
                        <div class="premium-number text-dark">0 Days</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #fbbf24, #f59e0b);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-calendar-check" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('employee_profile.leave.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>View Attendance</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>

    <!-- Incentive Balance -->
    <div class="col-lg-4 col-md-12 mb-4">
        <div class="premium-card border-top-urgent h-100" style="border-top-color: #10b981;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Incentive Balance</div>
                        <div class="premium-number text-dark" style="font-size: 1.4rem;">৳ 0.00</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #34d399, #10b981);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-star" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="#" class="premium-footer d-flex justify-content-between align-items-center">
                <span>View Incentives</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</div>
@endsection
