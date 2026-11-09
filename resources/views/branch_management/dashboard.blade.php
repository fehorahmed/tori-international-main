@extends('layouts.app')
@section('title', 'Branches Workspace')
@section('content')
@include('layouts.partial.__premium_dashboard_css')

<!-- Welcome Banner -->
<div class="row mb-4">
    <div class="col-12">
        <div class="welcome-banner theme-branches d-flex justify-content-between align-items-center">
            <div style="z-index: 1;">
                <div class="welcome-title">Welcome back, {{ auth()->user()->name }}! 👋</div>
                <div class="welcome-subtitle">Here is your Branch Management overview for today.</div>
            </div>
            <div class="d-none d-md-block" style="z-index: 1; opacity: 0.8;">
                <i class="fas fa-store-alt fa-4x" style="color: #ec4899;"></i>
            </div>
        </div>
    </div>
</div>

<!-- Today's P&L Summary Cards -->
<div class="row mb-3">
    <div class="col-12">
        <h4 class="font-weight-bold" style="color: #1e293b; font-family: 'Outfit', sans-serif;">
            <i class="fas fa-chart-line mr-2" style="color: #10b981;"></i> Today's P&L Summary ({{ date('d M Y') }})
        </h4>
    </div>
</div>

<div class="row">
    <!-- All Branches P&L Card -->
    <div class="col-lg-4 col-md-4 col-sm-6 mb-4">
        <div class="premium-card h-100 {{ $allBranchesProfit >= 0 ? 'border-top-success' : 'border-top-danger' }}">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="premium-title font-weight-bold text-dark">All Branches</div>
                    <div class="premium-icon-box shadow-sm {{ $allBranchesProfit >= 0 ? 'bg-success' : 'bg-danger' }}" style="width: 40px; height: 40px;">
                        <i class="fas {{ $allBranchesProfit >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' }} text-white"></i>
                    </div>
                </div>
                <div class="mb-1 text-muted small">Net {{ $allBranchesProfit >= 0 ? 'Profit' : 'Loss' }}</div>
                <h3 class="font-weight-bold mb-0 {{ $allBranchesProfit >= 0 ? 'text-success' : 'text-danger' }}">
                    {{ number_format(abs($allBranchesProfit), 2) }} <small class="text-muted" style="font-size: 14px;">Tk</small>
                </h3>
            </div>
            <a href="{{ route('branch_report.daily_income_statement', ['search' => '1', 'sister_concern' => '', 'start_date' => date('d-m-Y', strtotime($today)), 'end_date' => date('d-m-Y', strtotime($today))]) }}" target="_blank" class="premium-footer d-flex justify-content-between align-items-center">
                <span>View Full Report</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>

    <!-- Individual Branches P&L Cards -->
    @foreach($branchProfits as $bp)
    <div class="col-lg-4 col-md-4 col-sm-6 mb-4">
        <div class="premium-card h-100 {{ $bp['profit'] >= 0 ? 'border-top-success' : 'border-top-danger' }}">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="premium-title font-weight-bold text-dark text-truncate" title="{{ $bp['branch']->name }}">{{ $bp['branch']->name }}</div>
                    <div class="premium-icon-box shadow-sm {{ $bp['profit'] >= 0 ? 'bg-success' : 'bg-danger' }}" style="width: 40px; height: 40px;">
                        <i class="fas {{ $bp['profit'] >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' }} text-white"></i>
                    </div>
                </div>
                <div class="mb-1 text-muted small">Net {{ $bp['profit'] >= 0 ? 'Profit' : 'Loss' }}</div>
                <h3 class="font-weight-bold mb-0 {{ $bp['profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                    {{ number_format(abs($bp['profit']), 2) }} <small class="text-muted" style="font-size: 14px;">Tk</small>
                </h3>
            </div>
            <a href="{{ route('branch_report.daily_income_statement', ['search' => '1', 'sister_concern' => $bp['branch']->id, 'start_date' => date('d-m-Y', strtotime($today)), 'end_date' => date('d-m-Y', strtotime($today))]) }}" target="_blank" class="premium-footer d-flex justify-content-between align-items-center">
                <span>View Full Report</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
    @endforeach
</div>

<!-- Key Metrics Cards -->
<div class="row">
    <div class="col-lg-6 col-md-6 mb-4">
        <div class="premium-card border-top-branches h-100">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2">Total Branches</div>
                        <div class="premium-number">{{ $totalBranches }}</div>
                    </div>
                    <div class="premium-icon-box bg-gradient-branches shadow-sm">
                        <div class="deco-circle"></div>
                        <i class="fas fa-store" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('sister-concerns.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>View All Branches</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
    
    <div class="col-lg-6 col-md-6 mb-4">
        <div class="premium-card border-top-active h-100">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2" style="color: #8b5cf6;">Active Branches</div>
                        <div class="premium-number">{{ $activeBranches }}</div>
                    </div>
                    <div class="premium-icon-box bg-gradient-active shadow-sm">
                        <div class="deco-circle"></div>
                        <i class="fas fa-store-alt" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('sister-concerns.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>Manage Branches</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</div>

<!-- Recent Activity / Branches -->
<div class="row">
    <div class="col-12">
        <div class="premium-card">
            <div class="card-header bg-white border-0 py-3">
                <h3 class="card-title font-weight-bold m-0" style="color: #1e293b; font-family: 'Outfit', sans-serif;"><i class="fas fa-history mr-2" style="color: #ec4899;"></i> Recently Added Branches</h3>
                <div class="card-tools">
                    <a href="{{ route('sister-concerns.index') }}" class="btn btn-sm btn-light border" style="border-radius: 8px;">View All</a>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table premium-table mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th width="50px">S/L</th>
                            <th>Branch Name</th>
                            <th>Code</th>
                            <th>Contact</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentBranches as $branch)
                        <tr>
                            <td class="text-muted">{{ $loop->iteration }}</td>
                            <td class="font-weight-bold">
                                {{ $branch->name }}
                            </td>
                            <td>
                                <span class="badge badge-light border px-2 py-1" style="border-radius: 6px;">{{ $branch->code ?? 'N/A' }}</span>
                            </td>
                            <td class="text-muted">{{ $branch->contact ?? 'N/A' }}</td>
                            <td>
                                @if($branch->status == 1)
                                    <span class="badge badge-success px-2 py-1" style="border-radius: 6px;">Active</span>
                                @else
                                    <span class="badge badge-danger px-2 py-1" style="border-radius: 6px;">Inactive</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">No branches found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
