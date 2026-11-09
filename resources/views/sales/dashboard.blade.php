@extends('layouts.app')
@section('title', 'Sales Dashboard')

@section('content')
@include('layouts.partial.__premium_dashboard_css')

<!-- Welcome Banner -->
<div class="row mb-4">
    <div class="col-12">
        <div class="welcome-banner theme-office d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #1e3a8a, #3b82f6);">
            <div style="z-index: 1;">
                <div class="welcome-title text-white">Sales Workspace</div>
                <div class="welcome-subtitle text-white-50">Manage your sales, invoices, and revenue overview.</div>
            </div>
            <div class="d-none d-md-block" style="z-index: 1; opacity: 0.8;">
                <i class="fas fa-chart-line fa-4x text-white"></i>
            </div>
        </div>
    </div>
</div>

<!-- Key Metrics Cards -->
<div class="row">
    <!-- Total Invoices -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="premium-card border-top-office h-100" style="border-top-color: #3b82f6;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Total Invoices</div>
                        <div class="premium-number text-dark">{{ number_format($totalSalesCount) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #60a5fa, #3b82f6);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-file-invoice" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('sales.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>View Invoices</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
    
    <!-- Total Sales Amount -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="premium-card border-top-urgent h-100" style="border-top-color: #10b981;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Total Sales Amount</div>
                        <div class="premium-number text-dark" style="font-size: 1.4rem;">৳ {{ number_format($totalSalesAmount, 2) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #34d399, #10b981);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-money-bill-wave" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('report.sale') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>Sales Report</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
    
    <!-- Total Paid -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="premium-card border-top-urgent h-100" style="border-top-color: #f59e0b;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Total Paid</div>
                        <div class="premium-number text-dark" style="font-size: 1.4rem;">৳ {{ number_format($totalPaidAmount, 2) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #fbbf24, #f59e0b);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-hand-holding-usd" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('customer-payment.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>Payment History</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
    
    <!-- Total Due -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="premium-card border-top-urgent h-100" style="border-top-color: #ef4444;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Total Due</div>
                        <div class="premium-number text-dark" style="font-size: 1.4rem;">৳ {{ number_format($totalDueAmount, 2) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #f87171, #ef4444);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-exclamation-circle" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('report.customer_due') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>Customer Due Report</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</div>

<!-- Content Row -->
<div class="row">
    <!-- Recent Sales -->
    <div class="col-md-12 mb-4">
        <div class="card shadow-sm border-0 h-100" style="border-radius: 12px; overflow: hidden;">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-2">
                <h3 class="card-title font-weight-bold" style="color: #1e293b; font-size: 1.1rem;">
                    <i class="fas fa-history text-primary mr-2"></i> Recent Sales
                </h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="font-size: 0.95rem;">
                        <thead style="background-color: #f8fafc; color: #64748b; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px;">
                            <tr>
                                <th class="border-0 pl-4 py-3">S/L</th>
                                <th class="border-0 py-3">Date</th>
                                <th class="border-0 py-3">Invoice No</th>
                                <th class="border-0 py-3">Customer</th>
                                <th class="border-0 py-3 text-right">Total</th>
                                <th class="border-0 py-3 text-right">Paid</th>
                                <th class="border-0 py-3 text-right pr-4">Due</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentSales as $sale)
                                <tr>
                                    <td class="align-middle pl-4 border-light py-3 text-muted font-weight-bold">
                                        {{ $loop->iteration }}
                                    </td>
                                    <td class="align-middle border-light py-3">
                                        <div class="text-dark font-weight-bold">{{ \Carbon\Carbon::parse($sale->date)->format('d M') }}</div>
                                        <div class="text-muted" style="font-size: 0.8rem;">{{ \Carbon\Carbon::parse($sale->date)->format('Y') }}</div>
                                    </td>
                                    <td class="align-middle border-light py-3">
                                        <a href="{{ route('sales.details', $sale->id) }}" class="font-weight-bold text-primary">{{ $sale->order_no }}</a>
                                    </td>
                                    <td class="align-middle border-light py-3">
                                        <div class="text-dark font-weight-500">{{ $sale->customer ? $sale->customer->name : 'Walk-in Customer' }}</div>
                                    </td>
                                    <td class="align-middle border-light py-3 text-right">
                                        <span class="font-weight-bold text-dark">৳ {{ number_format($sale->total, 2) }}</span>
                                    </td>
                                    <td class="align-middle border-light py-3 text-right">
                                        <span class="badge badge-pill badge-success px-3 py-2" style="font-size: 0.9rem; font-weight: 600;">৳ {{ number_format($sale->paid, 2) }}</span>
                                    </td>
                                    <td class="align-middle border-light py-3 text-right pr-4">
                                        <span class="badge badge-pill badge-danger px-3 py-2" style="font-size: 0.9rem; font-weight: 600;">৳ {{ number_format($sale->due, 2) }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-5 border-light">
                                        <div class="mb-3"><i class="fas fa-file-invoice fa-3x" style="color: #cbd5e1;"></i></div>
                                        <div style="font-size: 1.1rem; color: #94a3b8;">No recent sales found</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white text-right border-top border-light py-3">
                <a href="{{ route('sales.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-4 font-weight-bold">View All Sales</a>
            </div>
        </div>
    </div>
</div>
@endsection
