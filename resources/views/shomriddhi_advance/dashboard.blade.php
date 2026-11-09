@extends('layouts.app')
@section('title', 'Shomriddhi Advance Dashboard')

@section('content')
@include('layouts.partial.__premium_dashboard_css')

<!-- Welcome Banner -->
<div class="row mb-4">
    <div class="col-12">
        <div class="welcome-banner theme-office d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #10b981, #047857);">
            <div style="z-index: 1;">
                <div class="welcome-title text-white">Shomriddhi Advance Dashboard</div>
                <div class="welcome-subtitle text-white-50">Manage customer savings deposits, withdrawals, and track financial liabilities securely.</div>
            </div>
            <div class="d-none d-md-block" style="z-index: 1; opacity: 0.8;">
                <i class="fas fa-piggy-bank fa-4x text-white"></i>
            </div>
        </div>
    </div>
</div>

<!-- Key Metrics Cards -->
<div class="row">
    <!-- Total Savings Members -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="premium-card border-top-office h-100" style="border-top-color: #0d9488;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Savings Members</div>
                        <div class="premium-number text-dark">{{ number_format($totalMembers) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #2dd4bf, #0d9488);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-users" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="#" class="premium-footer d-flex justify-content-between align-items-center">
                <span>View Customer List</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>

    <!-- Pending Deposits -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="premium-card border-top-office h-100" style="border-top-color: #f59e0b;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Pending Deposits</div>
                        <div class="premium-number text-dark">৳ {{ number_format($pendingDepositAmount, 2) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #fbbf24, #f59e0b);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-arrow-down" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('shomriddhi-advance.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>Approve Deposits</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>

    <!-- Pending Withdrawals -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="premium-card border-top-office h-100" style="border-top-color: #e11d48;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Pending Withdrawals</div>
                        <div class="premium-number text-dark">৳ {{ number_format($pendingWithdrawAmount, 2) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #fb7185, #e11d48);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-arrow-up" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('shomriddhi-advance.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>Approve Withdrawals</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>

    <!-- Total Liability -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="premium-card border-top-office h-100" style="border-top-color: #4f46e5;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Total Liability</div>
                        <div class="premium-number text-dark">৳ {{ number_format($totalLiability, 2) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #6366f1, #4f46e5);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-chart-line" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('shomriddhi-advance.reports.summary') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>View Financial Summary</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</div>

<!-- Recent Transactions Table -->
<div class="row">
    <div class="col-12">
        <div class="card premium-card border-top-office" style="border-top-color: #64748b;">
            <div class="card-header border-0">
                <h3 class="card-title font-weight-bold text-dark"><i class="fas fa-history mr-2 text-secondary"></i> Recent Transactions</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover align-middle">
                    <thead style="background-color: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                        <tr>
                            <th class="text-secondary font-weight-600">Date</th>
                            <th class="text-secondary font-weight-600">Customer</th>
                            <th class="text-secondary font-weight-600">Type</th>
                            <th class="text-secondary font-weight-600">Amount</th>
                            <th class="text-secondary font-weight-600">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentTransactions as $txn)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($txn->date)->format('d-M-Y') }}</td>
                            <td>{{ $txn->customer ? $txn->customer->name : '-' }}</td>
                            <td>
                                @if($txn->type == 'deposit')
                                    <span class="badge badge-success shadow-sm">Deposit</span>
                                @else
                                    <span class="badge badge-danger shadow-sm">Withdraw</span>
                                @endif
                            </td>
                            <td class="font-weight-bold">৳ {{ number_format($txn->amount, 2) }}</td>
                            <td>
                                @if($txn->status == 'Pending')
                                    <span class="badge badge-warning shadow-sm">Pending</span>
                                @elseif($txn->status == 'Approved')
                                    <span class="badge badge-primary shadow-sm">Approved</span>
                                @else
                                    <span class="badge badge-danger shadow-sm">Rejected</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                <i class="fas fa-inbox fa-2x mb-2 text-light"></i><br>
                                No recent transactions found
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
