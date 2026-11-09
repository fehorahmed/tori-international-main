@extends('layouts.app')

@section('title', 'Settings Dashboard')

@section('content')
@include('layouts.partial.__premium_dashboard_css')

<!-- Welcome Banner -->
<div class="row mb-4">
    <div class="col-12">
        <div class="welcome-banner theme-office d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #64748b, #475569);">
            <div style="z-index: 1;">
                <div class="welcome-title text-white">Welcome back, {{ auth()->user()->name }}! 👋</div>
                <div class="welcome-subtitle text-white-50">Here is your Settings and Master Data overview.</div>
            </div>
            <div class="d-none d-md-block" style="z-index: 1; opacity: 0.8;">
                <i class="fas fa-cogs fa-4x text-white"></i>
            </div>
        </div>
    </div>
</div>

<!-- Key Metrics Cards -->
<div class="row">
    <!-- Products -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="premium-card border-top-office h-100" style="border-top-color: #3b82f6;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Total Products</div>
                        <div class="premium-number text-dark">{{ number_format($totalProducts) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #60a5fa, #3b82f6);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-box" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('product.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>View Products</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
    
    <!-- Categories -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="premium-card border-top-urgent h-100" style="border-top-color: #f59e0b;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Categories</div>
                        <div class="premium-number text-dark">{{ number_format($totalCategories) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #fbbf24, #f59e0b);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-folder" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('product-categories.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>View Categories</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
    
    <!-- Brands -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="premium-card border-top-urgent h-100" style="border-top-color: #8b5cf6;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Brands</div>
                        <div class="premium-number text-dark">{{ number_format($totalBrands) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #a78bfa, #8b5cf6);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-copyright" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('brand.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>View Brands</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
    
    <!-- Warehouses -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="premium-card border-top-urgent h-100" style="border-top-color: #10b981;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Warehouses</div>
                        <div class="premium-number text-dark">{{ number_format($totalWarehouses) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #34d399, #10b981);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-warehouse" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('warehouses.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>View Warehouses</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</div>

@endsection
