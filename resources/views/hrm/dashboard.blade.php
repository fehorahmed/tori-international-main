@extends('layouts.app')
@section('title', 'HRM Dashboard')
@section('content')
@include('layouts.partial.__premium_dashboard_css')

<!-- Welcome Banner -->
<div class="row mb-4">
    <div class="col-12">
        <div class="welcome-banner theme-office d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #059669, #10b981);">
            <div style="z-index: 1;">
                <div class="welcome-title text-white">Welcome back, {{ auth()->user()->name }}! 👋</div>
                <div class="welcome-subtitle text-white-50">Here is your Human Resource Management overview.</div>
            </div>
            <div class="d-none d-md-block" style="z-index: 1; opacity: 0.8;">
                <i class="fas fa-users-cog fa-4x text-white"></i>
            </div>
        </div>
    </div>
</div>

<!-- Key Metrics Cards -->
<div class="row">
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="premium-card border-top-office h-100" style="border-top-color: #3b82f6;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Total Employees</div>
                        <div class="premium-number text-dark">{{ number_format($totalEmployees) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #60a5fa, #3b82f6);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-user-friends" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('employees.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>View Employees</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="premium-card border-top-urgent h-100" style="border-top-color: #8b5cf6;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Departments</div>
                        <div class="premium-number text-dark">{{ number_format($totalDepartments) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #a78bfa, #8b5cf6);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-building" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('departments.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>Manage Departments</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="premium-card border-top-meeting h-100" style="border-top-color: #f59e0b;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Designations</div>
                        <div class="premium-number text-dark">{{ number_format($totalDesignations) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #fbbf24, #f59e0b);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-id-badge" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('designations.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>View Designations</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="premium-card border-top-upcoming h-100" style="border-top-color: #ef4444;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-danger">On Leave Today</div>
                        <div class="premium-number text-danger">{{ number_format($employeesOnLeave) }}</div>
                    </div>
                    <div class="premium-icon-box bg-gradient-urgent shadow-sm">
                        <div class="deco-circle"></div>
                        <i class="fas fa-plane-departure" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('leave.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span class="text-danger">Check Leaves</span>
                <i class="fas fa-arrow-right text-danger"></i>
            </a>
        </div>
    </div>
</div>

<!-- Recent Employees -->
<div class="row">
    <div class="col-12">
        <div class="premium-card">
            <div class="card-header bg-white border-0 py-3">
                <h3 class="card-title font-weight-bold m-0" style="color: #1e293b; font-family: 'Outfit', sans-serif;"><i class="fas fa-user-plus mr-2 text-success"></i> Recently Added Employees</h3>
                <div class="card-tools">
                    <a href="{{ route('employees.index') }}" class="btn btn-sm btn-light border" style="border-radius: 8px;">View All</a>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table premium-table mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Employee ID</th>
                            <th>Name</th>
                            <th>Department</th>
                            <th>Designation</th>
                            <th>Joined Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentEmployees as $employee)
                        <tr>
                            <td class="font-weight-bold">{{ $employee->id_no }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mr-2" style="width: 32px; height: 32px; font-weight: bold;">
                                        {{ substr($employee->name, 0, 1) }}
                                    </div>
                                    {{ $employee->name }}
                                </div>
                            </td>
                            <td>{{ $employee->department->name ?? 'N/A' }}</td>
                            <td><span class="badge badge-info px-2 py-1" style="border-radius: 6px;">{{ $employee->designation->name ?? 'N/A' }}</span></td>
                            <td class="text-muted">{{ $employee->joining_date ? \Carbon\Carbon::parse($employee->joining_date)->format('d M, Y') : 'N/A' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">No employees found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
