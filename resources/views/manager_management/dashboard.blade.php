@extends('layouts.app')
@section('title', 'Manager Management Dashboard')
@section('content')
@include('layouts.partial.__premium_dashboard_css')

<!-- Welcome Banner -->
<div class="row mb-4">
    <div class="col-12">
        <div class="welcome-banner theme-office d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #14b8a6, #0d9488);">
            <div style="z-index: 1;">
                <div class="welcome-title text-white">Welcome back, {{ auth()->user()->name }}! 👋</div>
                <div class="welcome-subtitle text-white-50">Here is your Manager Management overview.</div>
            </div>
            <div class="d-none d-md-block" style="z-index: 1; opacity: 0.8;">
                <i class="fas fa-user-tie fa-4x text-white"></i>
            </div>
        </div>
    </div>
</div>

<!-- Key Metrics Cards -->
<div class="row">
    <!-- Area Managers -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="premium-card border-top-office h-100" style="border-top-color: #0ea5e9;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Area Managers</div>
                        <div class="premium-number text-dark">{{ number_format($totalAreaManagers) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #38bdf8, #0ea5e9);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-user-tie" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('area-manager.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>View Details</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
    
    <!-- Branch Managers -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="premium-card border-top-urgent h-100" style="border-top-color: #10b981;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Branch Managers</div>
                        <div class="premium-number text-dark">{{ number_format($totalBranchManagers) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #34d399, #10b981);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-user-friends" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('branch-manager.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>View Details</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
    
    <!-- AM Assignments -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="premium-card border-top-urgent h-100" style="border-top-color: #f59e0b;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">AM Assignments</div>
                        <div class="premium-number text-dark">{{ number_format($totalAreaManagerAssignments) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #fbbf24, #f59e0b);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-link" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('area-manager-assignments.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>View Details</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
    
    <!-- BM Assignments -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="premium-card border-top-urgent h-100" style="border-top-color: #ec4899;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">BM Assignments</div>
                        <div class="premium-number text-dark">{{ number_format($totalBranchManagerAssignments) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #f472b6, #ec4899);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-link" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('branch-manager-assignments.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>View Details</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</div>

@endsection
