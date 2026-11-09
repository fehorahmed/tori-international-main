@extends('layouts.app')
@section('title', 'Accounts Dashboard')

@section('content')
@include('layouts.partial.__premium_dashboard_css')

<!-- Welcome Banner -->
<div class="row mb-4">
    <div class="col-12">
        <div class="welcome-banner theme-office d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #0284c7, #0369a1);">
            <div style="z-index: 1;">
                <div class="welcome-title text-white">Accounts Dashboard</div>
                <div class="welcome-subtitle text-white-50">Financial overview, voucher tracking, and reports.</div>
            </div>
            <div class="d-none d-md-block" style="z-index: 1; opacity: 0.8;">
                <i class="fas fa-calculator fa-4x text-white"></i>
            </div>
        </div>
    </div>
</div>

<!-- Key Metrics Cards -->
<div class="row">
    <!-- Today's Vouchers -->
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="premium-card border-top-office h-100" style="border-top-color: #3b82f6;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Today's Vouchers</div>
                        <div class="premium-number text-dark">{{ number_format($todayVouchersCount) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #60a5fa, #3b82f6);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-file-invoice-dollar" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            @can('receipt_voucher')
            <a href="{{ route('voucher.index', ['voucher_type' => \App\Enumeration\VoucherType::$COLLECTION_VOUCHER]) }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>View Vouchers</span>
                <i class="fas fa-arrow-right"></i>
            </a>
            @else
            <div class="premium-footer d-flex justify-content-between align-items-center text-muted">
                <span>Today's Vouchers</span>
            </div>
            @endcan
        </div>
    </div>

    <!-- Active Accounts -->
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="premium-card border-top-office h-100" style="border-top-color: #10b981;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Active Chart of Accounts</div>
                        <div class="premium-number text-dark">{{ number_format($totalActiveAccounts) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #34d399, #10b981);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-list-ol" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            @can('account_head')
            <a href="{{ route('account-head.index', ['payment_mode' => 0]) }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>View Accounts</span>
                <i class="fas fa-arrow-right"></i>
            </a>
            @else
            <div class="premium-footer d-flex justify-content-between align-items-center text-muted">
                <span>Chart of Accounts</span>
            </div>
            @endcan
        </div>
    </div>

    <!-- Financial Reports -->
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="premium-card border-top-office h-100" style="border-top-color: #8b5cf6;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Financial Statements</div>
                        <div class="premium-number text-dark"><i class="fas fa-chart-line"></i></div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #a78bfa, #8b5cf6);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-chart-pie" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            @can('accounts_reports')
            <a href="{{ route('report.balance_sheet') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>View Balance Sheet</span>
                <i class="fas fa-arrow-right"></i>
            </a>
            @else
            <div class="premium-footer d-flex justify-content-between align-items-center text-muted">
                <span>Reports Overview</span>
            </div>
            @endcan
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Vouchers Table -->
    <div class="col-md-12">
        <div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
            <div class="card-header bg-white border-bottom" style="padding: 15px 20px;">
                <h5 class="card-title m-0 text-dark font-weight-bold" style="font-size: 1.1rem;">
                    <i class="fas fa-history text-primary mr-2"></i>Recent Vouchers
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="font-size: 0.9rem;">
                        <thead class="bg-light text-secondary">
                            <tr>
                                <th class="border-0 px-4 py-3">Date</th>
                                <th class="border-0 px-4 py-3">Voucher No</th>
                                <th class="border-0 px-4 py-3">Type</th>
                                <th class="border-0 px-4 py-3 text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentVouchers as $voucher)
                            <tr>
                                <td class="px-4 py-3 align-middle">{{ \Carbon\Carbon::parse($voucher->date)->format('d M, Y') }}</td>
                                <td class="px-4 py-3 align-middle">
                                    <span class="badge badge-light border border-secondary text-dark">{{ $voucher->voucher_no }}</span>
                                </td>
                                <td class="px-4 py-3 align-middle">
                                    @if($voucher->voucher_type == \App\Enumeration\VoucherType::$JOURNAL_VOUCHER)
                                        <span class="badge badge-info">Journal</span>
                                    @elseif($voucher->voucher_type == \App\Enumeration\VoucherType::$PAYMENT_VOUCHER)
                                        <span class="badge badge-danger">Payment</span>
                                    @elseif($voucher->voucher_type == \App\Enumeration\VoucherType::$COLLECTION_VOUCHER)
                                        <span class="badge badge-success">Receipt</span>
                                    @elseif($voucher->voucher_type == \App\Enumeration\VoucherType::$CONTRA_VOUCHER)
                                        <span class="badge badge-warning">Contra</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 align-middle text-right font-weight-bold text-dark">৳{{ number_format($voucher->amount, 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">
                                    <i class="fas fa-inbox fa-2x mb-2"></i>
                                    <p class="mb-0">No vouchers found today.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
