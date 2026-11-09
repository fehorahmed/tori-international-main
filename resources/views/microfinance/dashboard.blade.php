@extends('layouts.app')
@section('title', 'Microfinance Dashboard')

@section('content')
@include('layouts.partial.__premium_dashboard_css')

<!-- Welcome Banner -->
<div class="row mb-4">
    <div class="col-12">
        <div class="welcome-banner theme-office d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #0f766e, #134e4a);">
            <div style="z-index: 1;">
                <div class="welcome-title text-white">Microfinance Dashboard</div>
                <div class="welcome-subtitle text-white-50">Manage somitis, members, savings, and loans.</div>
            </div>
            <div class="d-none d-md-block" style="z-index: 1; opacity: 0.8;">
                <i class="fas fa-hand-holding-usd fa-4x text-white"></i>
            </div>
        </div>
    </div>
</div>

<!-- Key Metrics Cards -->
<div class="row">
    <!-- Total Active Members -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="premium-card border-top-office h-100" style="border-top-color: #0d9488;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Active Members</div>
                        <div class="premium-number text-dark">{{ number_format($totalMembers) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #2dd4bf, #0d9488);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-users" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            @can('mf_member')
            <a href="{{ route('microfinance.member.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>Manage Members</span>
                <i class="fas fa-arrow-right"></i>
            </a>
            @else
            <div class="premium-footer d-flex justify-content-between align-items-center text-muted">
                <span>Members Overview</span>
            </div>
            @endcan
        </div>
    </div>

    <!-- Total Somitis -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="premium-card border-top-office h-100" style="border-top-color: #3b82f6;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Total Somitis</div>
                        <div class="premium-number text-dark">{{ number_format($totalSomitis) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #60a5fa, #3b82f6);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-sitemap" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            @can('mf_somiti')
            <a href="{{ route('microfinance.somiti.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>View Somitis</span>
                <i class="fas fa-arrow-right"></i>
            </a>
            @else
            <div class="premium-footer d-flex justify-content-between align-items-center text-muted">
                <span>Somitis Overview</span>
            </div>
            @endcan
        </div>
    </div>

    <!-- Active Loans -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="premium-card border-top-office h-100" style="border-top-color: #8b5cf6;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Active Loans</div>
                        <div class="premium-number text-dark">{{ number_format($totalActiveLoans) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #a78bfa, #8b5cf6);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-money-check-alt" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            @can('mf_loan')
            <a href="{{ route('microfinance.loan.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>Loan Details</span>
                <i class="fas fa-arrow-right"></i>
            </a>
            @else
            <div class="premium-footer d-flex justify-content-between align-items-center text-muted">
                <span>Loans Overview</span>
            </div>
            @endcan
        </div>
    </div>

    <!-- Today's Collection -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="premium-card border-top-office h-100" style="border-top-color: #f59e0b;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Today's Collection</div>
                        <div class="premium-number text-dark">৳ {{ number_format($todayCollection, 2) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #fbbf24, #f59e0b);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-coins" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            @can('mf_collection_sheet')
            <a href="{{ route('microfinance.collection.sheet') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>Collection Sheet</span>
                <i class="fas fa-arrow-right"></i>
            </a>
            @else
            <div class="premium-footer d-flex justify-content-between align-items-center text-muted">
                <span>Collection Overview</span>
            </div>
            @endcan
        </div>
    </div>
</div>
@endsection
