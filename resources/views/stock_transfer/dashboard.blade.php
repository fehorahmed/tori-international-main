@extends('layouts.app')
@section('title', 'Stock Transfer Dashboard')

@section('content')
@include('layouts.partial.__premium_dashboard_css')

<!-- Welcome Banner -->
<div class="row mb-4">
    <div class="col-12">
        <div class="welcome-banner theme-office d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #4f46e5, #7c3aed);">
            <div style="z-index: 1;">
                <div class="welcome-title text-white">Stock Transfer Workspace</div>
                <div class="welcome-subtitle text-white-50">Manage branch transfers, receive shipments, and track inventory movement.</div>
            </div>
            <div class="d-none d-md-block" style="z-index: 1; opacity: 0.8;">
                <i class="fas fa-truck-loading fa-4x text-white"></i>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Total Transfers -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="premium-card border-top-office h-100" style="border-top-color: #4f46e5;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Total Transfers</div>
                        <div class="premium-number text-dark">{{ number_format($totalTransfers) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #818cf8, #4f46e5);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-exchange-alt" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('stock-transfers.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>View All Transfers</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
    
    <!-- Pending Transfers -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="premium-card border-top-urgent h-100" style="border-top-color: #f59e0b;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Pending Receipts</div>
                        <div class="premium-number text-dark">{{ number_format($pendingTransfers) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #fbbf24, #f59e0b);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-clock" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('stock-transfers.receive_list') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>View Pending</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>

    <!-- Completed Transfers -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="premium-card border-top-info h-100" style="border-top-color: #10b981;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Completed</div>
                        <div class="premium-number text-dark">{{ number_format($completedTransfers) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #34d399, #10b981);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-check-circle" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('stock-transfers.incoming_history') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>View History</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
    
    <!-- Pending Returns -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="premium-card border-top-danger h-100" style="border-top-color: #ef4444;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Pending Returns</div>
                        <div class="premium-number text-dark">{{ number_format($pendingReturns ?? 0) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #f87171, #ef4444);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-undo-alt" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('stock-transfers.pending_returns') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>View Pending Returns</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Transfers -->
    <div class="col-lg-6 col-12 mb-4">
        <div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
            <div class="card-header bg-white border-bottom-0 pt-3 pb-2 px-4">
                <h3 class="card-title font-weight-bold mt-1" style="color: #1f2937; font-size: 1.1rem;">
                    <i class="fas fa-history text-primary mr-2"></i> Recent Stock Transfers
                </h3>
                <div class="card-tools mt-1">
                    <a href="{{ route('stock-transfers.index') }}" class="btn btn-sm btn-light font-weight-bold px-3 shadow-sm rounded-pill text-primary" style="font-size: 0.8rem;">View All</a>
                </div>
            </div>
            <div class="card-body p-0">
                @if($recentTransfers->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover border-bottom mb-0 table-sm">
                            <thead>
                                <tr style="background-color: #f8fafc;">
                                    <th class="border-top-0 border-bottom-0 pl-4 py-2 text-muted text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">Date</th>
                                    <th class="border-top-0 border-bottom-0 py-2 text-muted text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">Transfer No</th>
                                    <th class="border-top-0 border-bottom-0 py-2 text-muted text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">From</th>
                                    <th class="border-top-0 border-bottom-0 py-2 text-muted text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">To</th>
                                    <th class="border-top-0 border-bottom-0 py-2 text-center text-muted text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($recentTransfers as $transfer)
                                <tr>
                                    <td class="align-middle pl-4 border-light py-2">
                                        <div class="text-dark font-weight-bold">{{ \Carbon\Carbon::parse($transfer->date)->format('d M') }}</div>
                                        <div class="text-muted" style="font-size: 0.8rem;">{{ \Carbon\Carbon::parse($transfer->date)->format('Y') }}</div>
                                    </td>
                                    <td class="align-middle border-light py-2">
                                        <a href="{{ route('stock-transfers.details', $transfer->id) }}" class="font-weight-bold text-primary">{{ $transfer->order_no }}</a>
                                    </td>
                                    <td class="align-middle border-light py-2">
                                        <div class="text-dark font-weight-500">{{ $transfer->fromSisterConcern ? $transfer->fromSisterConcern->name : '' }}</div>
                                    </td>
                                    <td class="align-middle border-light py-2">
                                        <div class="text-dark font-weight-500">{{ $transfer->toSisterConcern ? $transfer->toSisterConcern->name : '' }}</div>
                                    </td>
                                    <td class="align-middle border-light py-2 text-center">
                                        @if($transfer->status == 1)
                                            <span class="badge badge-warning px-2 py-1" style="font-size: 0.8rem; font-weight: 500;">Pending Dispatch</span>
                                        @elseif($transfer->status == 2)
                                            <span class="badge badge-primary px-2 py-1" style="font-size: 0.8rem; font-weight: 500;">In Transit</span>
                                        @elseif($transfer->status == 3 || $transfer->status == 4)
                                            <span class="badge badge-success px-2 py-1" style="font-size: 0.8rem; font-weight: 500;">Received</span>
                                        @elseif($transfer->status == 5)
                                            <span class="badge badge-danger px-2 py-1" style="font-size: 0.8rem; font-weight: 500;">Rejected</span>
                                        @else
                                            <span class="badge badge-secondary px-2 py-1" style="font-size: 0.8rem; font-weight: 500;">Completed</span>
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
                            <i class="fas fa-box-open fa-3x text-light"></i>
                        </div>
                        <h6 class="text-muted mb-1">No Recent Transfers</h6>
                        <p class="text-muted small">No transfers have been initiated recently.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Recent Returns -->
    <div class="col-lg-6 col-12 mb-4">
        <div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
            <div class="card-header bg-white border-bottom-0 pt-3 pb-2 px-4">
                <h3 class="card-title font-weight-bold mt-1" style="color: #1f2937; font-size: 1.1rem;">
                    <i class="fas fa-undo-alt text-danger mr-2"></i> Recent Stock Returns
                </h3>
                <div class="card-tools mt-1">
                    <a href="{{ route('stock-transfers.return_history') }}" class="btn btn-sm btn-light font-weight-bold px-3 shadow-sm rounded-pill text-danger" style="font-size: 0.8rem;">View All</a>
                </div>
            </div>
            <div class="card-body p-0">
                @if($recentReturns->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover border-bottom mb-0 table-sm">
                            <thead>
                                <tr style="background-color: #f8fafc;">
                                    <th class="border-top-0 border-bottom-0 pl-4 py-2 text-muted text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">Date</th>
                                    <th class="border-top-0 border-bottom-0 py-2 text-muted text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">Return No</th>
                                    <th class="border-top-0 border-bottom-0 py-2 text-muted text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">From</th>
                                    <th class="border-top-0 border-bottom-0 py-2 text-muted text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">To</th>
                                    <th class="border-top-0 border-bottom-0 py-2 text-center text-muted text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($recentReturns as $transfer)
                                <tr>
                                    <td class="align-middle pl-4 border-light py-2">
                                        <div class="text-dark font-weight-bold">{{ \Carbon\Carbon::parse($transfer->date)->format('d M') }}</div>
                                        <div class="text-muted" style="font-size: 0.8rem;">{{ \Carbon\Carbon::parse($transfer->date)->format('Y') }}</div>
                                    </td>
                                    <td class="align-middle border-light py-2">
                                        <a href="{{ route('stock-transfers.details', $transfer->id) }}" class="font-weight-bold text-danger">{{ $transfer->order_no }}</a>
                                    </td>
                                    <td class="align-middle border-light py-2">
                                        <div class="text-dark font-weight-500">{{ $transfer->fromSisterConcern ? $transfer->fromSisterConcern->name : '' }}</div>
                                    </td>
                                    <td class="align-middle border-light py-2">
                                        <div class="text-dark font-weight-500">{{ $transfer->toSisterConcern ? $transfer->toSisterConcern->name : '' }}</div>
                                    </td>
                                    <td class="align-middle border-light py-2 text-center">
                                        @if($transfer->status == 1)
                                            <span class="badge badge-warning px-2 py-1" style="font-size: 0.8rem; font-weight: 500;">Pending Dispatch</span>
                                        @elseif($transfer->status == 2)
                                            <span class="badge badge-primary px-2 py-1" style="font-size: 0.8rem; font-weight: 500;">In Transit</span>
                                        @elseif($transfer->status == 3 || $transfer->status == 4)
                                            <span class="badge badge-success px-2 py-1" style="font-size: 0.8rem; font-weight: 500;">Received</span>
                                        @elseif($transfer->status == 5)
                                            <span class="badge badge-danger px-2 py-1" style="font-size: 0.8rem; font-weight: 500;">Rejected</span>
                                        @else
                                            <span class="badge badge-secondary px-2 py-1" style="font-size: 0.8rem; font-weight: 500;">Completed</span>
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
                            <i class="fas fa-undo-alt fa-3x text-light"></i>
                        </div>
                        <h6 class="text-muted mb-1">No Recent Returns</h6>
                        <p class="text-muted small">No returns have been initiated recently.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
