@extends('layouts.app')
@section('title', 'Route Plan Dashboard')

@section('content')
@include('layouts.partial.__premium_dashboard_css')

<!-- Welcome Banner -->
<div class="row mb-4">
    <div class="col-12">
        <div class="welcome-banner theme-office d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #0f766e, #134e4a);">
            <div style="z-index: 1;">
                <div class="welcome-title text-white">Route Plan</div>
                <div class="welcome-subtitle text-white-50">Manage markets, SR route plans, and coverage analytics.</div>
            </div>
            <div class="d-none d-md-block" style="z-index: 1; opacity: 0.8;">
                <i class="fas fa-route fa-4x text-white"></i>
            </div>
        </div>
    </div>
</div>

<!-- Key Metrics Cards -->
<div class="row">
    <!-- Total Active Markets -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="premium-card border-top-office h-100" style="border-top-color: #0d9488;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Active Markets</div>
                        <div class="premium-number text-dark">{{ number_format($totalMarkets) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #2dd4bf, #0d9488);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-market" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('markets.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>Manage Markets</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>

    <!-- Total Route Plans -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="premium-card border-top-office h-100" style="border-top-color: #3b82f6;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Route Plans</div>
                        <div class="premium-number text-dark">{{ number_format($totalRoutePlans) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #60a5fa, #3b82f6);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-map-signs" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('route-plans.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>View Route Plans</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>

    <!-- Total Shops on Markets -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="premium-card border-top-office h-100" style="border-top-color: #8b5cf6;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Shops on Markets</div>
                        <div class="premium-number text-dark">{{ number_format($totalShops) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #a78bfa, #8b5cf6);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-store" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('report.route_leads') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>Route-wise Leads</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>

    <!-- Today's Visits -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="premium-card border-top-office h-100" style="border-top-color: #f59e0b;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Today's SR Visits</div>
                        <div class="premium-number text-dark">{{ number_format($todayVisits) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #fbbf24, #f59e0b);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-map-marker-alt" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('report.route_coverage') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>Route Coverage</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</div>
@endsection
