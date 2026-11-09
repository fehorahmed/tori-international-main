@extends('layouts.app')
@section('title', 'Fixed Asset Dashboard')
@section('content')
@include('layouts.partial.__premium_dashboard_css')

<!-- Welcome Banner -->
<div class="row mb-4">
    <div class="col-12">
        <div class="welcome-banner d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #0ea5e9, #0369a1);">
            <div style="z-index: 1;">
                <div class="welcome-title text-white">Welcome to Fixed Asset Mgmt! 👋</div>
                <div class="welcome-subtitle text-white-50">Manage your organization's physical assets, tracking, and depreciation.</div>
            </div>
            <div class="d-none d-md-block" style="z-index: 1; opacity: 0.8;">
                <i class="fas fa-couch fa-4x text-white"></i>
            </div>
        </div>
    </div>
</div>

<!-- Key Metrics Cards -->
<div class="row">
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="premium-card border-top-office h-100" style="border-top-color: #0ea5e9;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Active Assets</div>
                        <div class="premium-number text-dark">{{ number_format($activeAssets) }} <small class="text-muted" style="font-size:12px;">/ {{ $totalAssets }}</small></div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #38bdf8, #0ea5e9);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-boxes" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('fixed-assets.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>View Assets Register</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="premium-card border-top-meeting h-100" style="border-top-color: #3b82f6;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Total Asset Value</div>
                        <div class="premium-number text-dark">৳ {{ number_format($totalCost, 2) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #60a5fa, #3b82f6);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-money-bill-wave" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('fixed-assets.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>Active Assets Only</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="premium-card border-top-urgent h-100" style="border-top-color: #f59e0b;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Accumulated Dep.</div>
                        <div class="premium-number text-dark">৳ {{ number_format($totalAccumulatedDep, 2) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #fbbf24, #f59e0b);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-arrow-down" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('fixed-assets.depreciation.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>View Depreciation Logs</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="premium-card border-top-upcoming h-100" style="border-top-color: #10b981;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-success">Net Book Value</div>
                        <div class="premium-number text-success">৳ {{ number_format($totalBookValue, 2) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #34d399, #10b981);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-chart-line" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('fixed-assets.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span class="text-success">Current Valuations</span>
                <i class="fas fa-arrow-right text-success"></i>
            </a>
        </div>
    </div>
</div>

<div class="row">
    <!-- Quick Actions -->
    <div class="col-md-4 mb-4">
        <div class="premium-card h-100">
            <div class="card-header bg-white border-0 py-3">
                <h3 class="card-title font-weight-bold m-0" style="color: #1e293b; font-family: 'Outfit', sans-serif;"><i class="fas fa-bolt text-warning mr-2"></i> Quick Actions</h3>
            </div>
            <div class="card-body">
                @if(auth()->user()->can('fixed_asset_create'))
                <a href="{{ route('fixed-assets.create') }}" class="btn btn-block btn-primary bg-gradient-primary mb-3 text-left p-3 rounded shadow-sm">
                    <i class="fas fa-plus-circle mr-2"></i> Register New Asset
                    <span class="float-right"><i class="fas fa-arrow-right opacity-50"></i></span>
                </a>
                @endif

                @if(auth()->user()->can('fixed_asset_depreciation_run'))
                <a href="{{ route('fixed-assets.depreciation.index') }}" class="btn btn-block btn-warning bg-gradient-warning mb-3 text-left p-3 rounded shadow-sm text-dark font-weight-bold">
                    <i class="fas fa-calculator mr-2"></i> Run Monthly Depreciation
                    <span class="float-right"><i class="fas fa-arrow-right opacity-50"></i></span>
                </a>
                @endif

                @if(auth()->user()->can('fixed_asset_category_view'))
                <a href="{{ route('fixed-assets.categories.index') }}" class="btn btn-block btn-info bg-gradient-info text-left p-3 rounded shadow-sm">
                    <i class="fas fa-tags mr-2"></i> Manage Categories
                    <span class="float-right"><i class="fas fa-arrow-right opacity-50"></i></span>
                </a>
                @endif

                <div class="row mt-4">
                    <div class="col-6">
                        <div class="p-3 bg-light rounded text-center shadow-sm border border-white">
                            <h4 class="font-weight-bold text-success mb-1">{{ $soldAssets }}</h4>
                            <span class="text-muted text-xs text-uppercase font-weight-bold">Sold Assets</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 bg-light rounded text-center shadow-sm border border-white">
                            <h4 class="font-weight-bold text-danger mb-1">{{ $scrappedAssets }}</h4>
                            <span class="text-muted text-xs text-uppercase font-weight-bold">Scrapped</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Purchases -->
    <div class="col-md-8 mb-4">
        <div class="premium-card h-100">
            <div class="card-header bg-white border-0 py-3">
                <h3 class="card-title font-weight-bold m-0" style="color: #1e293b; font-family: 'Outfit', sans-serif;"><i class="fas fa-history mr-2 text-info"></i> Recently Acquired Assets</h3>
                <div class="card-tools">
                    <a href="{{ route('fixed-assets.index') }}" class="btn btn-sm btn-light border" style="border-radius: 8px;">View All</a>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table premium-table mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Asset Info</th>
                            <th>Category</th>
                            <th>Purchase Date</th>
                            <th>Cost</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentAssets as $asset)
                        <tr>
                            <td>
                                <a href="{{ route('fixed-assets.show', $asset->id) }}" class="font-weight-bold text-dark">{{ $asset->name }}</a><br>
                                <small class="text-muted">{{ $asset->asset_code }}</small>
                            </td>
                            <td><span class="badge badge-light px-2 py-1 border">{{ $asset->category->name ?? '-' }}</span></td>
                            <td class="text-muted">{{ $asset->date->format('d M, Y') }}</td>
                            <td class="font-weight-bold">৳ {{ number_format($asset->total, 2) }}</td>
                            <td class="text-center">{!! $asset->status_badge !!}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="fas fa-box-open fa-2x mb-2 text-light"></i><br>
                                No assets registered yet.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
