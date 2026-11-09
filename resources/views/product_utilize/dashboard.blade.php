@extends('layouts.app')

@section('title', 'Product Utilize Dashboard')

@section('content')
@include('layouts.partial.__premium_dashboard_css')

<!-- Welcome Banner -->
<div class="row mb-4">
    <div class="col-12">
        <div class="welcome-banner theme-office d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #475569, #334155);">
            <div style="z-index: 1;">
                <div class="welcome-title text-white">Product Utilize Dashboard</div>
                <div class="welcome-subtitle text-white-50">Overview of product utilizations and history.</div>
            </div>
            <div class="d-none d-md-block" style="z-index: 1; opacity: 0.8;">
                <i class="fas fa-tasks fa-4x text-white"></i>
            </div>
        </div>
    </div>
</div>

<!-- Key Metrics Cards -->
<div class="row">
    <!-- Total Utilizes -->
    <div class="col-lg-6 col-md-6 mb-4">
        <div class="premium-card border-top-office h-100" style="border-top-color: #3b82f6;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Total Product Utilizes</div>
                        <div class="premium-number text-dark">{{ number_format($totalUtilize) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #60a5fa, #3b82f6);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-boxes" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            @can('product_utilize_list')
            <a href="{{ route('product-utilizes.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>View All Utilizes</span>
                <i class="fas fa-arrow-right"></i>
            </a>
            @else
            <div class="premium-footer d-flex justify-content-between align-items-center text-muted">
                <span>Total records overview</span>
            </div>
            @endcan
        </div>
    </div>

    <!-- Today's Utilizes -->
    <div class="col-lg-6 col-md-6 mb-4">
        <div class="premium-card border-top-office h-100" style="border-top-color: #10b981;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Today's Utilizes</div>
                        <div class="premium-number text-dark">{{ number_format($todayUtilize) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #34d399, #10b981);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-calendar-day" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <div class="premium-footer d-flex justify-content-between align-items-center text-muted">
                <span>Activities recorded today</span>
            </div>
        </div>
    </div>
</div>

<div class="row mt-2">
    <div class="col-md-12">
        <div class="card card-outline card-primary" style="border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border-top: 3px solid #334155;">
            <div class="card-header bg-white" style="border-bottom: 1px solid #f1f5f9;">
                <h3 class="card-title font-weight-bold" style="color: #334155;"><i class="fas fa-history mr-2"></i>Recent Product Utilizes</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped table-hover mb-0">
                    <thead style="background-color: #f8fafc; color: #475569;">
                        <tr>
                            <th class="pl-4">Date</th>
                            <th>Order No</th>
                            <th>Warehouse</th>
                            <th>Created By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentUtilizes as $utilize)
                            <tr>
                                <td class="pl-4">{{ \Carbon\Carbon::parse($utilize->date)->format('d M Y') }}</td>
                                <td>
                                    <a href="{{ route('product-utilizes.details', ['product_utilize' => $utilize->id]) }}" class="font-weight-bold text-primary">
                                        {{ $utilize->order_no }}
                                    </a>
                                </td>
                                <td>{{ $utilize->warehouse->name ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge badge-light border">{{ $utilize->user->name ?? 'N/A' }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">No recent utilizes found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
