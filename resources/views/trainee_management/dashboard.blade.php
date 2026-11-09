@extends('layouts.app')
@section('title', 'Trainee Management Dashboard')

@section('content')
@include('layouts.partial.__premium_dashboard_css')

<!-- Welcome Banner -->
<div class="row mb-4">
    <div class="col-12">
        <div class="welcome-banner theme-office d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #10b981, #047857);">
            <div style="z-index: 1;">
                <div class="welcome-title text-white">Trainee Workspace</div>
                <div class="welcome-subtitle text-white-50">Manage Trainees, tracking, tasks and visit reports.</div>
            </div>
            <div class="d-none d-md-block" style="z-index: 1; opacity: 0.8;">
                <i class="fas fa-graduation-cap fa-4x text-white"></i>
            </div>
        </div>
    </div>
</div>

<!-- Key Metrics Cards -->
<div class="row">
    <!-- Total Trainees -->
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="premium-card border-top-office h-100" style="border-top-color: #3b82f6;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Total Trainees</div>
                        <div class="premium-number text-dark">{{ number_format($totalTrainees) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #60a5fa, #3b82f6);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-user-graduate" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('trainee.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>View Trainees</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>

    <!-- Total Visits -->
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="premium-card border-top-office h-100" style="border-top-color: #10b981;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Total Visits</div>
                        <div class="premium-number text-dark">{{ number_format($totalVisits) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #34d399, #10b981);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-map-marked-alt" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('trainee.visits.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>View All Visits</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>

    <!-- Today's Visits -->
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="premium-card border-top-office h-100" style="border-top-color: #f59e0b;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Today's Visits</div>
                        <div class="premium-number text-dark">{{ number_format($todayVisits) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #fbbf24, #f59e0b);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-calendar-day" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('trainee.visits.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>View Today's Log</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</div>
@endsection
