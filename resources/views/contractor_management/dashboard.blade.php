@extends('layouts.app')
@section('title', 'Contractor Management Dashboard')

@section('content')
@include('layouts.partial.__premium_dashboard_css')

<!-- Welcome Banner -->
<div class="row mb-4">
    <div class="col-12">
        <div class="welcome-banner theme-office d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #ea580c, #fb923c);">
            <div style="z-index: 1;">
                <div class="welcome-title text-white">Contractor Workspace</div>
                <div class="welcome-subtitle text-white-50">Manage your contractors, payments, and ledgers efficiently.</div>
            </div>
            <div class="d-none d-md-block" style="z-index: 1; opacity: 0.8;">
                <i class="fas fa-hard-hat fa-4x text-white"></i>
            </div>
        </div>
    </div>
</div>

<!-- Key Metrics Cards -->
<div class="row">
    <!-- Total Contractors -->
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="premium-card border-top-office h-100" style="border-top-color: #3b82f6;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Total Contractors</div>
                        <div class="premium-number text-dark">{{ number_format($totalContractors) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #60a5fa, #3b82f6);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-users-cog" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('contractor_management.contractors.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>View Contractors</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
    
    <!-- Active Contractors -->
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="premium-card border-top-urgent h-100" style="border-top-color: #10b981;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Active Contractors</div>
                        <div class="premium-number text-dark">{{ number_format($activeContractors) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #34d399, #10b981);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-user-check" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('contractor_management.contractors.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>View Details</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
    
    <!-- Total Types -->
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="premium-card border-top-urgent h-100" style="border-top-color: #f59e0b;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Contractor Types</div>
                        <div class="premium-number text-dark" style="font-size: 1.4rem;">{{ number_format($totalTypes) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #fbbf24, #f59e0b);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-cogs" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('contractor_management.types.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>Manage Types</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</div>

<!-- Financial Metrics Cards -->
<div class="row">
    <!-- Total Billed -->
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="premium-card border-top-office h-100" style="border-top-color: #8b5cf6;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Total Billed</div>
                        <div class="premium-number text-dark" style="font-size: 1.4rem;">৳ {{ number_format($totalBilled, 2) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #a78bfa, #8b5cf6);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-file-invoice-dollar" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('contractor_management.bills.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>View Bills</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
    
    <!-- Total Paid -->
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="premium-card border-top-urgent h-100" style="border-top-color: #10b981;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Total Paid</div>
                        <div class="premium-number text-dark" style="font-size: 1.4rem;">৳ {{ number_format($totalPaid, 2) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #34d399, #10b981);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-money-check-alt" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('contractor_management.payments.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>View Payments</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
    
    <!-- Total Due -->
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="premium-card border-top-urgent h-100" style="border-top-color: #ef4444;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Total Due</div>
                        <div class="premium-number text-dark" style="font-size: 1.4rem;">৳ {{ number_format($totalDue, 2) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #f87171, #ef4444);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-exclamation-circle" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <div class="premium-footer d-flex justify-content-between align-items-center">
                <span>Outstanding Balance</span>
            </div>
        </div>
    </div>
</div>

<!-- Content Row -->
<div class="row">
    <!-- Recent Contractors -->
    <div class="col-md-12 mb-4">
        <div class="card shadow-sm border-0 h-100" style="border-radius: 12px; overflow: hidden;">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-2">
                <h3 class="card-title font-weight-bold" style="color: #1e293b; font-size: 1.1rem;">
                    <i class="fas fa-history text-primary mr-2"></i> Recently Added Contractors
                </h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="font-size: 0.95rem;">
                        <thead style="background-color: #f8fafc; color: #64748b; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px;">
                            <tr>
                                <th class="border-0 pl-4 py-3">Added Date</th>
                                <th class="border-0 py-3">Name</th>
                                <th class="border-0 py-3">Type</th>
                                <th class="border-0 py-3">Mobile No.</th>
                                <th class="border-0 py-3 text-right pr-4">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentContractors as $contractor)
                                <tr>
                                    <td class="align-middle pl-4 border-light py-3">
                                        <div class="text-dark font-weight-bold">{{ \Carbon\Carbon::parse($contractor->created_at)->format('d M') }}</div>
                                        <div class="text-muted" style="font-size: 0.8rem;">{{ \Carbon\Carbon::parse($contractor->created_at)->format('Y') }}</div>
                                    </td>
                                    <td class="align-middle border-light py-3">
                                        <div class="text-primary font-weight-500">{{ $contractor->name }}</div>
                                    </td>
                                    <td class="align-middle border-light py-3">
                                        <span class="text-dark font-weight-500">{{ $contractor->contractorType->name ?? 'N/A' }}</span>
                                    </td>
                                    <td class="align-middle border-light py-3">
                                        <span class="font-weight-bold">{{ $contractor->mobile_no }}</span>
                                    </td>
                                    <td class="align-middle border-light py-3 text-right pr-4">
                                        @if($contractor->status == 1)
                                            <span class="badge badge-pill badge-success px-3 py-2">Active</span>
                                        @else
                                            <span class="badge badge-pill badge-danger px-3 py-2">Inactive</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-5 border-light">
                                        <div class="mb-3"><i class="fas fa-hard-hat fa-3x" style="color: #cbd5e1;"></i></div>
                                        <div style="font-size: 1.1rem; color: #94a3b8;">No recent contractors found</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
