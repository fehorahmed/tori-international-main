@extends('layouts.app')
@section('title', 'User Management Workspace')
@section('content')
@include('layouts.partial.__premium_dashboard_css')

<!-- Welcome Banner -->
<div class="row mb-4">
    <div class="col-12">
        <div class="welcome-banner theme-users d-flex justify-content-between align-items-center">
            <div style="z-index: 1;">
                <div class="welcome-title">Welcome back, {{ auth()->user()->name }}! 👋</div>
                <div class="welcome-subtitle">Here is your Access Management overview for today.</div>
            </div>
            <div class="d-none d-md-block" style="z-index: 1; opacity: 0.8;">
                <i class="fas fa-user-shield fa-4x text-indigo" style="color: #6366f1;"></i>
            </div>
        </div>
    </div>
</div>

<!-- Key Metrics Cards -->
<div class="row">
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="premium-card border-top-users h-100">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2">Total Users</div>
                        <div class="premium-number">{{ $totalUsers }}</div>
                    </div>
                    <div class="premium-icon-box bg-gradient-users shadow-sm">
                        <div class="deco-circle"></div>
                        <i class="fas fa-users" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('user.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>View All Users</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
    
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="premium-card border-top-active h-100">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-success">Active Users</div>
                        <div class="premium-number text-success">{{ $activeUsers }}</div>
                    </div>
                    <div class="premium-icon-box bg-gradient-active shadow-sm">
                        <div class="deco-circle"></div>
                        <i class="fas fa-user-check" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('user.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span class="text-success">Manage Active Users</span>
                <i class="fas fa-arrow-right text-success"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <div class="premium-card border-top-roles h-100">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2" style="color: #f59e0b;">Total Roles</div>
                        <div class="premium-number">{{ $totalRoles }}</div>
                    </div>
                    <div class="premium-icon-box bg-gradient-roles shadow-sm">
                        <div class="deco-circle"></div>
                        <i class="fas fa-user-tag" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('role.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>View Roles</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</div>

<!-- Recent Activity / Users -->
<div class="row">
    <div class="col-12">
        <div class="premium-card">
            <div class="card-header bg-white border-0 py-3">
                <h3 class="card-title font-weight-bold m-0" style="color: #1e293b; font-family: 'Outfit', sans-serif;"><i class="fas fa-user-clock mr-2 text-indigo" style="color: #6366f1;"></i> Recently Added Users</h3>
                <div class="card-tools">
                    <a href="{{ route('user.index') }}" class="btn btn-sm btn-light border" style="border-radius: 8px;">View All</a>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table premium-table mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th width="50px">S/L</th>
                            <th>Name</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Joined At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentUsers as $user)
                        <tr>
                            <td class="text-muted">{{ $loop->iteration }}</td>
                            <td class="font-weight-bold">
                                {{ $user->name }}
                                <div class="text-muted" style="font-size: 0.8rem; font-weight: 400;">{{ $user->email }}</div>
                            </td>
                            <td>
                                <span class="badge badge-light border px-2 py-1" style="border-radius: 6px;">{{ $user->role ?? 'N/A' }}</span>
                            </td>
                            <td>
                                @if($user->status == 1)
                                    <span class="badge badge-success px-2 py-1" style="border-radius: 6px;">Active</span>
                                @else
                                    <span class="badge badge-danger px-2 py-1" style="border-radius: 6px;">Inactive</span>
                                @endif
                            </td>
                            <td class="text-muted">{{ $user->created_at->format('d M Y') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">No users found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
