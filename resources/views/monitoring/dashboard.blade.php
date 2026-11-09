@extends('layouts.app')
@section('title', 'Monitoring Dashboard')

@section('content')
@include('layouts.partial.__premium_dashboard_css')

<!-- Welcome Banner -->
<div class="row mb-4">
    <div class="col-12">
        <div class="welcome-banner theme-office d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #0284c7, #0369a1);">
            <div style="z-index: 1;">
                <div class="welcome-title text-white">Monitoring Dashboard</div>
                <div class="welcome-subtitle text-white-50">Real-time tracking and location history overview.</div>
            </div>
            <div class="d-none d-md-block" style="z-index: 1; opacity: 0.8;">
                <i class="fas fa-map-marked-alt fa-4x text-white"></i>
            </div>
        </div>
    </div>
</div>

<!-- Key Metrics Cards -->
<div class="row">
    <!-- Total Tracked Users -->
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="premium-card border-top-office h-100" style="border-top-color: #3b82f6;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Tracked Personnel</div>
                        <div class="premium-number text-dark">{{ number_format($totalTrackedUsers) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #60a5fa, #3b82f6);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-users" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <div class="premium-footer d-flex justify-content-between align-items-center text-muted">
                <span>Total enabled for tracking</span>
            </div>
        </div>
    </div>

    <!-- Active/Online Users -->
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="premium-card border-top-office h-100" style="border-top-color: #10b981;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Currently Online</div>
                        <div class="premium-number text-dark">{{ number_format($onlineUsers) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #34d399, #10b981);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-signal" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            @can('live_location')
            <a href="{{ route('tracking.live_location') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>View Live Map</span>
                <i class="fas fa-arrow-right"></i>
            </a>
            @else
            <div class="premium-footer d-flex justify-content-between align-items-center text-muted">
                <span>Online status overview</span>
            </div>
            @endcan
        </div>
    </div>

    <!-- Today's Tracking Sessions -->
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="premium-card border-top-office h-100" style="border-top-color: #8b5cf6;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Today's Sessions</div>
                        <div class="premium-number text-dark">{{ number_format($todayLocations) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #a78bfa, #8b5cf6);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-route" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            @can('location_history')
            <a href="{{ route('tracking.location_history') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>View History</span>
                <i class="fas fa-arrow-right"></i>
            </a>
            @else
            <div class="premium-footer d-flex justify-content-between align-items-center text-muted">
                <span>History overview</span>
            </div>
            @endcan
        </div>
    </div>
</div>
@endsection
