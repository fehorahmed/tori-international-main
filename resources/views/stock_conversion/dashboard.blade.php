@extends('layouts.app')
@section('title', 'Stock Conversion Dashboard')

@section('content')
@include('layouts.partial.__premium_dashboard_css')

<!-- Welcome Banner -->
<div class="row mb-4">
    <div class="col-12">
        <div class="welcome-banner theme-office d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #0284c7, #0ea5e9);">
            <div style="z-index: 1;">
                <div class="welcome-title text-white">Stock Conversion Workspace</div>
                <div class="welcome-subtitle text-white-50">Manage raw material conversions, view production history, and approve pending stock entries.</div>
            </div>
            <div class="d-none d-md-block" style="z-index: 1; opacity: 0.8;">
                <i class="fas fa-random fa-4x text-white"></i>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Total Conversions -->
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="premium-card border-top-office h-100" style="border-top-color: #0284c7;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Total Conversions</div>
                        <div class="premium-number text-dark">{{ number_format($totalConversions) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #38bdf8, #0284c7);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-layer-group" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('stock-conversions.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>View All Conversions</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
    
    <!-- Pending Approvals -->
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="premium-card border-top-urgent h-100" style="border-top-color: #f59e0b;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Pending Approvals</div>
                        <div class="premium-number text-dark">{{ number_format($pendingConversions) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #fbbf24, #f59e0b);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-clock" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('stock-conversions.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>View Pending</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>

    <!-- Completed Conversions -->
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="premium-card border-top-info h-100" style="border-top-color: #10b981;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Completed</div>
                        <div class="premium-number text-dark">{{ number_format($completedConversions) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #34d399, #10b981);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-check-double" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('stock-conversions.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>View History</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Conversions -->
    <div class="col-12 mb-4">
        <div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
            <div class="card-header bg-white border-bottom-0 pt-3 pb-2 px-4">
                <h3 class="card-title font-weight-bold mt-1" style="color: #1f2937; font-size: 1.1rem;">
                    <i class="fas fa-history text-primary mr-2"></i> Recent Stock Conversions
                </h3>
                <div class="card-tools mt-1">
                    <a href="{{ route('stock-conversions.index') }}" class="btn btn-sm btn-light font-weight-bold px-3 shadow-sm rounded-pill text-primary" style="font-size: 0.8rem;">View All</a>
                </div>
            </div>
            <div class="card-body p-4">
                @if($recentConversions->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover border-bottom mb-0">
                            <thead>
                                <tr style="background-color: #f8fafc;">
                                    <th class="border-top-0 border-bottom-0 pl-4 py-3 text-muted text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">Date</th>
                                    <th class="border-top-0 border-bottom-0 py-3 text-muted text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">Conversion No</th>
                                    <th class="border-top-0 border-bottom-0 py-3 text-muted text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">Warehouse</th>
                                    <th class="border-top-0 border-bottom-0 py-3 text-center text-muted text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($recentConversions as $conversion)
                                <tr>
                                    <td class="align-middle pl-4 border-light py-3">
                                        <div class="text-dark font-weight-bold">{{ \Carbon\Carbon::parse($conversion->date)->format('d M') }}</div>
                                        <div class="text-muted" style="font-size: 0.8rem;">{{ \Carbon\Carbon::parse($conversion->date)->format('Y') }}</div>
                                    </td>
                                    <td class="align-middle border-light py-3">
                                        <a href="{{ route('stock-conversions.show', $conversion->id) }}" class="font-weight-bold text-primary">{{ $conversion->conversion_no }}</a>
                                    </td>
                                    <td class="align-middle border-light py-3">
                                        <div class="text-dark font-weight-500">{{ $conversion->warehouse ? $conversion->warehouse->name : 'N/A' }}</div>
                                    </td>
                                    <td class="align-middle border-light py-3 text-center">
                                        @if($conversion->status == 1)
                                            <span class="badge badge-pill badge-warning px-3 py-2" style="font-size: 0.9rem; font-weight: 600;">Pending</span>
                                        @elseif($conversion->status == 2)
                                            <span class="badge badge-pill badge-success px-3 py-2" style="font-size: 0.9rem; font-weight: 600;">Approved</span>
                                        @else
                                            <span class="badge badge-pill badge-secondary px-3 py-2" style="font-size: 0.9rem; font-weight: 600;">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <div class="mb-3">
                            <i class="fas fa-random fa-3x text-light"></i>
                        </div>
                        <h6 class="text-muted mb-1">No Recent Conversions</h6>
                        <p class="text-muted small">No stock conversions have been recorded recently.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
