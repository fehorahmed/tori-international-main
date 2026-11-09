@extends('layouts.app')
@section('title', 'Ecommerce Dashboard')

@section('content')
@include('layouts.partial.__premium_dashboard_css')

<!-- Welcome Banner -->
<div class="row mb-4">
    <div class="col-12">
        <div class="welcome-banner theme-office d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #1e3a8a, #3b82f6);">
            <div style="z-index: 1;">
                <div class="welcome-title text-white">Ecommerce Workspace</div>
                <div class="welcome-subtitle text-white-50">Manage your online store, products, orders, and configurations.</div>
            </div>
            <div class="d-none d-md-block" style="z-index: 1; opacity: 0.8;">
                <i class="fas fa-shopping-cart fa-4x text-white"></i>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Total Orders -->
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="premium-card border-top-office h-100" style="border-top-color: #3b82f6;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Total Orders</div>
                        <div class="premium-number text-dark">{{ number_format($totalOrders) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #60a5fa, #3b82f6);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-shopping-bag" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('ecom_order') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>View Orders</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
    
    <!-- Total Revenue -->
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="premium-card border-top-urgent h-100" style="border-top-color: #10b981;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Total Revenue</div>
                        <div class="premium-number text-dark" style="font-size: 1.4rem;">৳ {{ number_format($totalRevenue, 2) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #34d399, #10b981);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-money-bill-wave" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('ecom_order') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>Revenue Details</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>

    <!-- Total Products -->
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="premium-card border-top-urgent h-100" style="border-top-color: #f59e0b;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Online Products</div>
                        <div class="premium-number text-dark" style="font-size: 1.4rem;">{{ number_format($totalProducts) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #fbbf24, #f59e0b);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-box-open" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('ecom_product') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>Manage Catalog</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</div>

<!-- Content Row -->
<div class="row">
    <!-- Recent Orders -->
    <div class="col-md-12 mb-4">
        <div class="card shadow-sm border-0 h-100" style="border-radius: 12px; overflow: hidden;">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-2">
                <h3 class="card-title font-weight-bold" style="color: #1e293b; font-size: 1.1rem;">
                    <i class="fas fa-history text-primary mr-2"></i> Recent Orders
                </h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="font-size: 0.95rem;">
                        <thead style="background-color: #f8fafc; color: #64748b; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px;">
                            <tr>
                                <th class="border-0 pl-4 py-3">S/L</th>
                                <th class="border-0 py-3">Date</th>
                                <th class="border-0 py-3">Order No</th>
                                <th class="border-0 py-3">Customer</th>
                                <th class="border-0 py-3 text-right">Amount</th>
                                <th class="border-0 py-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentOrders as $order)
                                <tr>
                                    <td class="align-middle pl-4 border-light py-3 text-muted font-weight-bold">
                                        {{ $loop->iteration }}
                                    </td>
                                    <td class="align-middle border-light py-3">
                                        <div class="text-dark font-weight-bold">{{ \Carbon\Carbon::parse($order->date)->format('d M') }}</div>
                                        <div class="text-muted" style="font-size: 0.8rem;">{{ \Carbon\Carbon::parse($order->date)->format('Y') }}</div>
                                    </td>
                                    <td class="align-middle border-light py-3">
                                        <a href="{{ route('ecom_order.details', $order->id) }}" class="font-weight-bold text-primary">{{ $order->order_no }}</a>
                                    </td>
                                    <td class="align-middle border-light py-3">
                                        <div class="text-dark font-weight-500">{{ $order->customer ? $order->customer->name : 'Walk-in Customer' }}</div>
                                    </td>
                                    <td class="align-middle border-light py-3 text-right">
                                        <span class="font-weight-bold text-dark">৳ {{ number_format($order->total, 2) }}</span>
                                    </td>
                                    <td class="align-middle border-light py-3 text-center">
                                        @if($order->status == 0)
                                            <span class="badge badge-pill badge-warning px-3 py-2" style="font-size: 0.9rem; font-weight: 600;">Pending</span>
                                        @elseif($order->status == 1)
                                            <span class="badge badge-pill badge-info px-3 py-2" style="font-size: 0.9rem; font-weight: 600;">Processing</span>
                                        @elseif($order->status == 2)
                                            <span class="badge badge-pill badge-info px-3 py-2" style="font-size: 0.9rem; font-weight: 600;">In-transit</span>
                                        @elseif($order->status == 3)
                                            <span class="badge badge-pill badge-success px-3 py-2" style="font-size: 0.9rem; font-weight: 600;">Completed</span>
                                        @else
                                            <span class="badge badge-pill badge-danger px-3 py-2" style="font-size: 0.9rem; font-weight: 600;">Cancelled</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-5 border-light">
                                        <div class="mb-3"><i class="fas fa-shopping-basket fa-3x" style="color: #cbd5e1;"></i></div>
                                        <div style="font-size: 1.1rem; color: #94a3b8;">No recent orders found</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white text-right border-top border-light py-3">
                <a href="{{ route('ecom_order') }}" class="btn btn-sm btn-outline-primary rounded-pill px-4 font-weight-bold">View All Orders</a>
            </div>
        </div>
    </div>
</div>
@endsection
