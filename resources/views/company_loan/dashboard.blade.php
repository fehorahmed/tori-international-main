@extends('layouts.app')
@section('title', 'Company Loan Dashboard')

@section('content')
@include('layouts.partial.__premium_dashboard_css')

<!-- Welcome Banner -->
<div class="row mb-4">
    <div class="col-12">
        <div class="welcome-banner theme-office d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #0f766e, #134e4a);">
            <div style="z-index: 1;">
                <div class="welcome-title text-white">Company Loan Dashboard</div>
                <div class="welcome-subtitle text-white-50">Overview of inter-company loans, receivables, and payables.</div>
            </div>
            <div class="d-none d-md-block" style="z-index: 1; opacity: 0.8;">
                <i class="fas fa-handshake fa-4x text-white"></i>
            </div>
        </div>
    </div>
</div>

<!-- Key Metrics Cards -->
<div class="row">
    <!-- Outstanding Receivable -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="premium-card border-top-office h-100" style="border-top-color: #0d9488;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Outstanding Receivable</div>
                        <div class="premium-number text-dark">৳ {{ number_format($outstandingReceivable, 2) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #2dd4bf, #0d9488);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-arrow-down" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            @can('company_loan_report_receivable')
            <a href="{{ route('company_loan.reports.receivable_summary') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>View Receivables</span>
                <i class="fas fa-arrow-right"></i>
            </a>
            @else
            <div class="premium-footer d-flex justify-content-between align-items-center text-muted">
                <span>Receivables Overview</span>
            </div>
            @endcan
        </div>
    </div>

    <!-- Outstanding Payable -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="premium-card border-top-office h-100" style="border-top-color: #f43f5e;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Outstanding Payable</div>
                        <div class="premium-number text-dark">৳ {{ number_format($outstandingPayable, 2) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #fb7185, #f43f5e);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-arrow-up" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            @can('company_loan_report_payable')
            <a href="{{ route('company_loan.reports.payable_summary') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>View Payables</span>
                <i class="fas fa-arrow-right"></i>
            </a>
            @else
            <div class="premium-footer d-flex justify-content-between align-items-center text-muted">
                <span>Payables Overview</span>
            </div>
            @endcan
        </div>
    </div>

    <!-- Pending Requests -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="premium-card border-top-office h-100" style="border-top-color: #f59e0b;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Pending Requests</div>
                        <div class="premium-number text-dark">{{ number_format($pendingRequests) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #fbbf24, #f59e0b);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-clock" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            @can('manage_company_loan')
            <a href="{{ route('company_loan.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>Manage Loans</span>
                <i class="fas fa-arrow-right"></i>
            </a>
            @else
            <div class="premium-footer d-flex justify-content-between align-items-center text-muted">
                <span>Loans Overview</span>
            </div>
            @endcan
        </div>
    </div>

    <!-- Total Loans Given/Taken -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="premium-card border-top-office h-100" style="border-top-color: #3b82f6;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Total Loans</div>
                        <div class="premium-number text-dark" style="font-size: 1.2rem;">
                            Given: {{ $loansGiven }} <br> Taken: {{ $loansTaken }}
                        </div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #60a5fa, #3b82f6);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-chart-pie" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            @can('manage_company_loan')
            <a href="{{ route('company_loan.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>Loan List</span>
                <i class="fas fa-arrow-right"></i>
            </a>
            @else
            <div class="premium-footer d-flex justify-content-between align-items-center text-muted">
                <span>Total Loans Overview</span>
            </div>
            @endcan
        </div>
    </div>
</div>
@endsection
