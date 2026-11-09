@extends('layouts.app')
@section('title', 'Payroll Dashboard')
@section('content')
@include('layouts.partial.__premium_dashboard_css')

<!-- Welcome Banner -->
<div class="row mb-4">
    <div class="col-12">
        <div class="welcome-banner theme-office d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #0d9488, #115e59);">
            <div style="z-index: 1;">
                <div class="welcome-title text-white">Welcome back, {{ auth()->user()->name }}! 👋</div>
                <div class="welcome-subtitle text-white-50">Here is your Payroll Management overview.</div>
            </div>
            <div class="d-none d-md-block" style="z-index: 1; opacity: 0.8;">
                <i class="fas fa-wallet fa-4x text-white"></i>
            </div>
        </div>
    </div>
</div>

<!-- Key Metrics Cards -->
<div class="row">
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="premium-card border-top-office h-100" style="border-top-color: #0ea5e9;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Total Processes</div>
                        <div class="premium-number text-dark">{{ number_format($totalProcesses) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #38bdf8, #0ea5e9);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-spinner" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('salary-processes.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>View Processes</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="premium-card border-top-urgent h-100" style="border-top-color: #10b981;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Paid Employees</div>
                        <div class="premium-number text-dark">{{ number_format($totalPaidItems) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #34d399, #10b981);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-check-circle" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('salary-processes.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>View Paid</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="premium-card border-top-meeting h-100" style="border-top-color: #f59e0b;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Pending Payments</div>
                        <div class="premium-number text-dark">{{ number_format($totalUnpaidItems) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #fbbf24, #f59e0b);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-hourglass-half" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('salary-processes.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>View Pending</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="premium-card border-top-upcoming h-100" style="border-top-color: #6366f1;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2" style="color: #6366f1;">Total Net Pay</div>
                        <div class="premium-number">৳ {{ number_format($totalNetPay, 2) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #818cf8, #6366f1);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-money-bill-wave" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('salary-processes.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span style="color: #6366f1;">All Salaries</span>
                <i class="fas fa-arrow-right" style="color: #6366f1;"></i>
            </a>
        </div>
    </div>
</div>

<!-- Recent Processes -->
<div class="row">
    <div class="col-12">
        <div class="premium-card">
            <div class="card-header bg-white border-0 py-3">
                <h3 class="card-title font-weight-bold m-0" style="color: #1e293b; font-family: 'Outfit', sans-serif;"><i class="fas fa-history mr-2 text-info"></i> Recent Salary Processes</h3>
                <div class="card-tools">
                    <a href="{{ route('salary-processes.index') }}" class="btn btn-sm btn-light border" style="border-radius: 8px;">View All</a>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table premium-table mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th width="50px">S/L</th>
                            <th>Year</th>
                            <th>Month</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentProcesses as $process)
                        <tr>
                            <td class="text-muted">{{ $loop->iteration }}</td>
                            <td>{{ $process->year }}</td>
                            <td>{{ date("F", mktime(0, 0, 0, $process->month, 10)) }}</td>
                            <td class="text-muted">{{ $process->created_at->format('d M, Y h:i A') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">No recent processes found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
