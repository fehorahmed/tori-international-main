@extends('layouts.app')
@section('title', 'Shomriddhi DPS Dashboard')

@section('content')
@include('layouts.partial.__premium_dashboard_css')

<!-- Welcome Banner -->
<div class="row mb-4">
    <div class="col-12">
        <div class="welcome-banner theme-office d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #8b5cf6, #5b21b6);">
            <div style="z-index: 1;">
                <div class="welcome-title text-white">Shomriddhi DPS Dashboard</div>
                <div class="welcome-subtitle text-white-50">Manage Deposit Pension Schemes, accounts, installments, and track customer investments securely.</div>
            </div>
            <div class="d-none d-md-block" style="z-index: 1; opacity: 0.8;">
                <i class="fas fa-piggy-bank fa-4x text-white"></i>
            </div>
        </div>
    </div>
</div>

<!-- Key Metrics Cards -->
<div class="row">
    <!-- Total Deposited Amount -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="premium-card border-top-office h-100" style="border-top-color: #3b82f6;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Total Deposited</div>
                        <div class="premium-number text-dark">৳ {{ number_format($data['total_deposited'], 2) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #60a5fa, #3b82f6);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-coins" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('shomriddhi-dps.transactions.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>View Ledger</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>

    <!-- Active DPS Accounts -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="premium-card border-top-office h-100" style="border-top-color: #10b981;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Active Accounts</div>
                        <div class="premium-number text-dark">{{ $data['active_accounts'] }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #34d399, #10b981);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-users" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('shomriddhi-dps.accounts.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>View DPS Accounts</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>

    <!-- Defaulted Accounts -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="premium-card border-top-office h-100" style="border-top-color: #f59e0b;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Defaulted Accounts</div>
                        <div class="premium-number text-dark">{{ $data['defaulted_accounts'] }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #fbbf24, #f59e0b);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-exclamation-triangle" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('shomriddhi-dps.reports.defaulted-accounts') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>Review Defaulted</span>
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
                        <div class="premium-number text-dark">{{ $data['pending_accounts'] }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #f87171, #ef4444);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-clock" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('shomriddhi-dps.accounts.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>Review Now</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</div>

<div class="row">
    <!-- Matured Accounts -->
    <div class="col-lg-6 col-md-6 mb-4">
        <div class="premium-card border-top-office h-100" style="border-top-color: #8b5cf6;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Matured Accounts</div>
                        <div class="premium-number text-dark">{{ $data['matured_accounts'] }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #a78bfa, #8b5cf6);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-calendar-check" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('shomriddhi-dps.reports.matured-accounts') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>View Matured Accounts</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
    
    <!-- Total Payable at Maturity -->
    <div class="col-lg-6 col-md-6 mb-4">
        <div class="premium-card border-top-office h-100" style="border-top-color: #0ea5e9;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Payable at Maturity</div>
                        <div class="premium-number text-dark">৳ {{ number_format($data['total_payable_maturity'], 2) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #38bdf8, #0ea5e9);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-chart-line" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('shomriddhi-dps.reports.scheme-summary') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>View Scheme Summary</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</div>

<!-- Recent Transactions & Quick Actions -->
<div class="row">
    <div class="col-lg-12 mb-4">
        <div class="card premium-card border-top-office" style="border-top-color: #64748b;">
            <div class="card-header border-0">
                <h3 class="card-title font-weight-bold text-dark"><i class="fas fa-history mr-2 text-secondary"></i> Recent Transactions</h3>
                <div class="card-tools">
                    <a href="{{ route('shomriddhi-dps.transactions.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">View All</a>
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
                            <th class="text-secondary font-weight-600">Account No</th>
                            <th class="text-secondary font-weight-600">Type</th>
                            <th class="text-secondary font-weight-600 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data['recent_transactions'] as $txn)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($txn->date)->format('d M, Y') }}</td>
                            <td class="font-weight-bold">{{ $txn->dpsAccount->dps_no ?? 'N/A' }}</td>
                            <td>
                                @if($txn->type == 'Installment')
                                    <span class="badge badge-success shadow-sm"><i class="fas fa-arrow-down mr-1"></i> Installment</span>
                                @elseif($txn->type == 'Encashment')
                                    <span class="badge badge-primary shadow-sm"><i class="fas fa-arrow-up mr-1"></i> Encashment</span>
                                @elseif($txn->type == 'Penalty')
                                    <span class="badge badge-warning shadow-sm"><i class="fas fa-exclamation-triangle mr-1"></i> Penalty</span>
                                @else
                                    <span class="badge badge-info shadow-sm">{{ str_replace('_', ' ', $txn->type) }}</span>
                                @endif
                            </td>
                            <td class="text-right font-weight-bold">৳ {{ number_format($txn->amount, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">
                                <i class="fas fa-inbox fa-2x mb-2 text-light"></i><br>
                                No recent transactions found.
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
