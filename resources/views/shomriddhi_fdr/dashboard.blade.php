@extends('layouts.app')
@section('title', 'Shomriddhi FDR Dashboard')

@section('content')
@include('layouts.partial.__premium_dashboard_css')

<!-- Welcome Banner -->
<div class="row mb-4">
    <div class="col-12">
        <div class="welcome-banner theme-office d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #ef4444, #991b1b);">
            <div style="z-index: 1;">
                <div class="welcome-title text-white">Shomriddhi FDR Dashboard</div>
                <div class="welcome-subtitle text-white-50">Manage Fixed Deposit Receipts, schemes, and track customer investments securely.</div>
            </div>
            <div class="d-none d-md-block" style="z-index: 1; opacity: 0.8;">
                <i class="fas fa-file-invoice-dollar fa-4x text-white"></i>
            </div>
        </div>
    </div>
</div>
<!-- Key Metrics Cards -->
<div class="row">
    <!-- Active Schemes -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="premium-card border-top-office h-100" style="border-top-color: #3b82f6;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Active Schemes</div>
                        <div class="premium-number text-dark">{{ $data['total_schemes'] }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #60a5fa, #3b82f6);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-layer-group" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('shomriddhi-fdr.schemes.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>Manage Schemes</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>

    <!-- Active FDR Accounts -->
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
            <a href="{{ route('shomriddhi-fdr.accounts.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>View FDR Accounts</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>

    <!-- Total Invested Amount -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="premium-card border-top-office h-100" style="border-top-color: #f59e0b;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Total Principal</div>
                        <div class="premium-number text-dark">৳ {{ number_format($data['total_principal'], 2) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #fbbf24, #f59e0b);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-money-bill-wave" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('shomriddhi-fdr.transactions.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>View Ledger</span>
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
            <a href="{{ route('shomriddhi-fdr.accounts.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>Review Now</span>
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
                    <a href="{{ route('shomriddhi-fdr.transactions.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">View All</a>
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
                            <td class="font-weight-bold">{{ $txn->account->fdr_no ?? 'N/A' }}</td>
                            <td>
                                @if($txn->type == 'Deposit')
                                    <span class="badge badge-success shadow-sm"><i class="fas fa-arrow-down mr-1"></i> Deposit</span>
                                @elseif($txn->type == 'Encashment')
                                    <span class="badge badge-danger shadow-sm"><i class="fas fa-arrow-up mr-1"></i> Encashment</span>
                                @else
                                    <span class="badge badge-info shadow-sm">{{ $txn->type }}</span>
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
