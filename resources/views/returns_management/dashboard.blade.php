@extends('layouts.app')
@section('title', 'Returns Management Dashboard')

@section('content')
@include('layouts.partial.__premium_dashboard_css')

<!-- Welcome Banner -->
<div class="row mb-4">
    <div class="col-12">
        <div class="welcome-banner theme-office d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #f43f5e, #9f1239);">
            <div style="z-index: 1;">
                <div class="welcome-title text-white">Returns Workspace</div>
                <div class="welcome-subtitle text-white-50">Manage your sales returns, purchase returns, and view summary.</div>
            </div>
            <div class="d-none d-md-block" style="z-index: 1; opacity: 0.8;">
                <i class="fas fa-undo-alt fa-4x text-white"></i>
            </div>
        </div>
    </div>
</div>

<!-- Key Metrics Cards -->
<div class="row">
    <!-- Approved Sales Returns -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="premium-card border-top-urgent h-100" style="border-top-color: #f59e0b;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Approved Sales Returns</div>
                        <div class="premium-number text-dark" style="font-size: 1.4rem;">৳ {{ number_format($salesReturnTotal, 0) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #fbbf24, #f59e0b);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-undo-alt" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('returns-management.sales-returns.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>View Details</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
    
    <!-- Approved Purchase Returns -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="premium-card border-top-urgent h-100" style="border-top-color: #06b6d4;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Approved Purchase Returns</div>
                        <div class="premium-number text-dark" style="font-size: 1.4rem;">৳ {{ number_format($purchaseReturnTotal, 0) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #22d3ee, #06b6d4);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-exchange-alt" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('returns-management.purchase-returns.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>View Details</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>

    <!-- Pending Sales Returns -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="premium-card border-top-urgent h-100" style="border-top-color: #ef4444;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Pending Sales Returns</div>
                        <div class="premium-number text-dark" style="font-size: 1.4rem;">{{ $salesReturnPendingCount }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #f87171, #ef4444);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-hourglass-half" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('returns-management.sales-returns.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>View Pending</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>

    <!-- Pending Purchase Returns -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="premium-card border-top-urgent h-100" style="border-top-color: #ef4444;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Pending Purchase Returns</div>
                        <div class="premium-number text-dark" style="font-size: 1.4rem;">{{ $purchaseReturnPendingCount }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #f87171, #ef4444);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-hourglass-half" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('returns-management.purchase-returns.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>View Pending</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Customer Returns -->
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm border-0 h-100" style="border-radius: 12px; overflow: hidden;">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-2">
                <h3 class="card-title font-weight-bold" style="color: #1e293b; font-size: 1.1rem;">
                    <i class="fas fa-history text-primary mr-2"></i> Recent Sales Returns
                </h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="font-size: 0.95rem;">
                        <thead style="background-color: #f8fafc; color: #64748b; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px;">
                            <tr>
                                <th class="border-0 pl-4 py-3">Return No</th>
                                <th class="border-0 py-3">Customer</th>
                                <th class="border-0 py-3 text-right">Amount</th>
                                <th class="border-0 py-3 text-right pr-4">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentSalesReturns as $sr)
                                <tr>
                                    <td class="align-middle pl-4 border-light py-3">
                                        <a href="#" class="font-weight-bold text-primary">{{ $sr->return_no }}</a>
                                    </td>
                                    <td class="align-middle border-light py-3">
                                        <div class="text-dark font-weight-500">{{ $sr->customer->name ?? 'N/A' }}</div>
                                    </td>
                                    <td class="align-middle border-light py-3 text-right">
                                        <span class="font-weight-bold text-dark">৳ {{ number_format($sr->total_return_amount, 2) }}</span>
                                    </td>
                                    <td class="align-middle border-light py-3 text-right pr-4">
                                        @if($sr->status == 1)
                                            <span class="badge badge-pill badge-warning px-3 py-2" style="font-size: 0.85rem; font-weight: 600;">Pending</span>
                                        @else
                                            <span class="badge badge-pill badge-success px-3 py-2" style="font-size: 0.85rem; font-weight: 600;">Approved</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-5 border-light">
                                        <div class="mb-3"><i class="fas fa-undo-alt fa-3x" style="color: #cbd5e1;"></i></div>
                                        <div style="font-size: 1.1rem; color: #94a3b8;">No recent sales returns found</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white text-right border-top border-light py-3">
                <a href="{{ route('returns-management.sales-returns.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-4 font-weight-bold">View All</a>
            </div>
        </div>
    </div>
    
    <!-- Recent Supplier Returns -->
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm border-0 h-100" style="border-radius: 12px; overflow: hidden;">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-2">
                <h3 class="card-title font-weight-bold" style="color: #1e293b; font-size: 1.1rem;">
                    <i class="fas fa-history text-primary mr-2"></i> Recent Purchase Returns
                </h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="font-size: 0.95rem;">
                        <thead style="background-color: #f8fafc; color: #64748b; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px;">
                            <tr>
                                <th class="border-0 pl-4 py-3">Return No</th>
                                <th class="border-0 py-3">Supplier</th>
                                <th class="border-0 py-3 text-right">Amount</th>
                                <th class="border-0 py-3 text-right pr-4">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentPurchaseReturns as $pr)
                                <tr>
                                    <td class="align-middle pl-4 border-light py-3">
                                        <a href="#" class="font-weight-bold text-primary">{{ $pr->return_no }}</a>
                                    </td>
                                    <td class="align-middle border-light py-3">
                                        <div class="text-dark font-weight-500">{{ $pr->supplier->name ?? 'N/A' }}</div>
                                    </td>
                                    <td class="align-middle border-light py-3 text-right">
                                        <span class="font-weight-bold text-dark">৳ {{ number_format($pr->total_return_value, 2) }}</span>
                                    </td>
                                    <td class="align-middle border-light py-3 text-right pr-4">
                                        @if($pr->status == 1)
                                            <span class="badge badge-pill badge-warning px-3 py-2" style="font-size: 0.85rem; font-weight: 600;">Pending</span>
                                        @else
                                            <span class="badge badge-pill badge-success px-3 py-2" style="font-size: 0.85rem; font-weight: 600;">Approved</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-5 border-light">
                                        <div class="mb-3"><i class="fas fa-exchange-alt fa-3x" style="color: #cbd5e1;"></i></div>
                                        <div style="font-size: 1.1rem; color: #94a3b8;">No recent purchase returns found</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white text-right border-top border-light py-3">
                <a href="{{ route('returns-management.purchase-returns.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-4 font-weight-bold">View All</a>
            </div>
        </div>
    </div>
</div>
@endsection
