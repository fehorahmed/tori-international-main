@extends('layouts.app')
@section('title', 'Inventory Dashboard')

@section('content')
@include('layouts.partial.__premium_dashboard_css')

<!-- Welcome Banner -->
<div class="row mb-4">
    <div class="col-12">
        <div class="welcome-banner theme-office d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #1e3a8a, #3b82f6);">
            <div style="z-index: 1;">
                <div class="welcome-title text-white">Inventory Workspace</div>
                <div class="welcome-subtitle text-white-50">Manage your stocks, valuation and recent inventory movements.</div>
            </div>
            <div class="d-none d-md-block" style="z-index: 1; opacity: 0.8;">
                <i class="fas fa-boxes fa-4x text-white"></i>
            </div>
        </div>
    </div>
</div>

<!-- Key Metrics Cards -->
<div class="row">
    <!-- Total Stock Qty -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="premium-card border-top-office h-100" style="border-top-color: #3b82f6;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Total Stock Qty</div>
                        <div class="premium-number text-dark">{{ number_format($totalStockQty) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #60a5fa, #3b82f6);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-cubes" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('inventory.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>View Inventory</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
    
    <!-- Total Valuation -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="premium-card border-top-urgent h-100" style="border-top-color: #10b981;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Total Valuation</div>
                        <div class="premium-number text-dark" style="font-size: 1.4rem;">৳ {{ number_format($totalValuation ?? 0, 2) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #34d399, #10b981);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-money-bill-wave" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('report.stock_valuation') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>Valuation Report</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
    
    <!-- Low Stock -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="premium-card border-top-urgent h-100" style="border-top-color: #f59e0b;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Low Stock Alerts</div>
                        <div class="premium-number text-dark">{{ number_format($lowStockCount) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #fbbf24, #f59e0b);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-exclamation-triangle" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('report.stock') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>Stock Report</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
    
    <!-- Out of Stock -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="premium-card border-top-urgent h-100" style="border-top-color: #ef4444;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Out of Stock</div>
                        <div class="premium-number text-dark">{{ number_format($outOfStockCount) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #f87171, #ef4444);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-times-circle" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('report.stock') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>View Alerts</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</div>

<!-- Content Row -->
<div class="row">
    <!-- Recent Stock Ins -->
    <div class="col-md-7 mb-4">
        <div class="card shadow-sm border-0 h-100" style="border-radius: 12px; overflow: hidden;">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-2">
                <h3 class="card-title font-weight-bold" style="color: #1e293b; font-size: 1.1rem;">
                    <i class="fas fa-history text-primary mr-2"></i> Recent Inventory Entries
                </h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="font-size: 0.95rem;">
                        <thead style="background-color: #f8fafc; color: #64748b; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px;">
                            <tr>
                                <th class="border-0 pl-4 py-3">Date</th>
                                <th class="border-0 py-3">Product</th>
                                <th class="border-0 py-3">Batch No</th>
                                <th class="border-0 py-3 text-right pr-4">Qty</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentInventories as $inv)
                                <tr>
                                    <td class="align-middle pl-4 border-light py-3">
                                        <div class="text-dark font-weight-bold">{{ $inv->created_at->format('d M') }}</div>
                                        <div class="text-muted" style="font-size: 0.8rem;">{{ $inv->created_at->format('Y') }}</div>
                                    </td>
                                    <td class="align-middle border-light py-3">
                                        <div class="text-dark font-weight-500">{{ $inv->product->name ?? 'N/A' }}</div>
                                        <div class="text-muted" style="font-size: 0.8rem;">{{ $inv->warehouse->name ?? 'N/A' }}</div>
                                    </td>
                                    <td class="align-middle border-light py-3">
                                        <span class="badge" style="background-color: #f1f5f9; color: #475569; font-weight: 500;">{{ $inv->batch_no ?? 'N/A' }}</span>
                                    </td>
                                    <td class="align-middle border-light py-3 text-right pr-4">
                                        <span class="badge badge-pill badge-success px-3 py-2" style="font-size: 0.9rem; font-weight: 600;">+{{ $inv->quantity }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-5 border-light">
                                        <div class="mb-3"><i class="fas fa-box-open fa-3x" style="color: #cbd5e1;"></i></div>
                                        <div style="font-size: 1.1rem; color: #94a3b8;">No recent entries found</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Low Stock Products List -->
    <div class="col-md-5 mb-4">
        <div class="card shadow-sm border-0 h-100" style="border-radius: 12px;">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-2">
                <h3 class="card-title font-weight-bold" style="color: #1e293b; font-size: 1.1rem;">
                    <i class="fas fa-exclamation-circle text-warning mr-2"></i> Items Needing Attention
                </h3>
            </div>
            <div class="card-body px-4 pb-4 pt-2">
                <div class="d-flex flex-column gap-3" style="gap: 15px;">
                    @forelse($lowStockProducts->take(5) as $lowProduct)
                    <div class="d-flex justify-content-between align-items-center p-3 rounded" style="background-color: #fffbeb; border: 1px solid #fef3c7;">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle d-flex align-items-center justify-content-center mr-3 shadow-sm" style="width: 42px; height: 42px; background-color: #f59e0b; color: white;">
                                <i class="fas fa-exclamation"></i>
                            </div>
                            <div>
                                <h6 class="mb-1 font-weight-bold" style="color: #92400e;">{{ $lowProduct->name }}</h6>
                                <div style="font-size: 0.8rem; color: #b45309;">ID: {{ $lowProduct->code ?? 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="font-weight-bold" style="color: #d97706; font-size: 1.2rem;">{{ $lowProduct->inventories_sum_quantity }}</div>
                            <div style="font-size: 0.75rem; color: #d97706; font-weight: 600; text-transform: uppercase;">Left</div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-5">
                        <div class="mb-3"><i class="fas fa-check-circle fa-3x text-success" style="opacity: 0.5;"></i></div>
                        <div style="font-size: 1.1rem; color: #94a3b8;">Stock levels are healthy</div>
                    </div>
                    @endforelse
                </div>
                
                @if($lowStockCount > 5)
                <div class="text-center mt-4">
                    <a href="{{ route('inventory.index') }}" class="btn btn-sm btn-outline-warning rounded-pill px-4 font-weight-bold">
                        View All {{ $lowStockCount }} Items
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
