@extends('layouts.app')
@section('title', 'Shomriddhi Loan Dashboard')

@section('content')
@include('layouts.partial.__premium_dashboard_css')

<!-- Welcome Banner -->
<div class="row mb-4">
    <div class="col-12">
        <div class="welcome-banner theme-office d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #0f766e, #14b8a6);">
            <div style="z-index: 1;">
                <div class="welcome-title text-white">Shomriddhi Loan Dashboard</div>
                <div class="welcome-subtitle text-white-50">Manage Cash & Product (EMI) Loans, automated installments, and collections seamlessly.</div>
            </div>
            <div class="d-none d-md-block" style="z-index: 1; opacity: 0.8;">
                <i class="fas fa-hand-holding-usd fa-4x text-white"></i>
            </div>
        </div>
    </div>
</div>

<!-- Key Metrics Cards -->
<div class="row">
    <!-- Total Loan Disbursed -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="premium-card border-top-office h-100" style="border-top-color: #3b82f6;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Total Loan Disbursed</div>
                        <div class="premium-number text-dark">৳ {{ number_format($data['total_loan_amount'], 2) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #60a5fa, #3b82f6);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-money-bill-wave" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('shomriddhi-loan.accounts.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>View Loan Accounts</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>

    <!-- Active Loans -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="premium-card border-top-office h-100" style="border-top-color: #10b981;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Active Loans</div>
                        <div class="premium-number text-dark">{{ $data['active_loans'] }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #34d399, #10b981);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-users" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('shomriddhi-loan.accounts.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>View Accounts</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>

    <!-- Total Collected -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="premium-card border-top-office h-100" style="border-top-color: #8b5cf6;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Total Collected</div>
                        <div class="premium-number text-dark">৳ {{ number_format($data['total_collection'], 2) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #a78bfa, #8b5cf6);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-chart-line" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('shomriddhi-loan.collections.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>View Collection History</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>

    <!-- Pending Approvals -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="premium-card border-top-office h-100" style="border-top-color: #ef4444;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Pending Approvals</div>
                        <div class="premium-number text-dark">{{ $data['pending_loans'] }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #f87171, #ef4444);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-clock" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('shomriddhi-loan.accounts.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>Review Now</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</div>

<!-- Recent Collections & Quick Actions -->
<div class="row">
    <div class="col-lg-12 mb-4">
        <div class="card premium-card border-top-office" style="border-top-color: #0f766e;">
            <div class="card-header border-0">
                <h3 class="card-title font-weight-bold text-dark"><i class="fas fa-history mr-2 text-secondary"></i> Recent Loan Collections</h3>
                <div class="card-tools">
                    <a href="{{ route('shomriddhi-loan.collections.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">View All</a>
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
                            <th class="text-secondary font-weight-600">Loan Account No</th>
                            <th class="text-secondary font-weight-600">Type</th>
                            <th class="text-secondary font-weight-600 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data['recent_transactions'] as $txn)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($txn->collection_date)->format('d M, Y') }}</td>
                            <td class="font-weight-bold">{{ $txn->loanAccount->loan_no ?? 'N/A' }}</td>
                            <td>
                                <span class="badge badge-success shadow-sm"><i class="fas fa-arrow-down mr-1"></i> Installment Collection</span>
                            </td>
                            <td class="text-right font-weight-bold">৳ {{ number_format($txn->amount, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">
                                <i class="fas fa-inbox fa-2x mb-2 text-light"></i><br>
                                No recent collections found.
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
