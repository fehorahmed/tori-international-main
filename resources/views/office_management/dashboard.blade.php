@extends('layouts.app')
@section('title', 'Office Management Workspace')
@section('content')
@include('layouts.partial.__premium_dashboard_css')

<!-- Welcome Banner -->
<div class="row mb-4">
    <div class="col-12">
        <div class="welcome-banner theme-office d-flex justify-content-between align-items-center">
            <div style="z-index: 1;">
                <div class="welcome-title">Welcome back, {{ auth()->user()->name }}! 👋</div>
                <div class="welcome-subtitle">Here is your Office Workspace overview for today.</div>
            </div>
            <div class="d-none d-md-block" style="z-index: 1; opacity: 0.8;">
                <i class="fas fa-building fa-4x text-info"></i>
            </div>
        </div>
    </div>
</div>

<!-- Key Metrics Cards -->
<div class="row">
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="premium-card border-top-office h-100">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2">Total Notices</div>
                        <div class="premium-number">{{ $totalNotices }}</div>
                    </div>
                    <div class="premium-icon-box bg-gradient-office shadow-sm">
                        <div class="deco-circle"></div>
                        <i class="fas fa-bullhorn" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('notices.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>View All</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="premium-card border-top-urgent h-100">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-danger">Urgent</div>
                        <div class="premium-number text-danger">{{ $urgentNotices }}</div>
                    </div>
                    <div class="premium-icon-box bg-gradient-urgent shadow-sm">
                        <div class="deco-circle"></div>
                        <i class="fas fa-exclamation-triangle" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('notices.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span class="text-danger">Review Urgent</span>
                <i class="fas fa-arrow-right text-danger"></i>
            </a>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="premium-card border-top-meeting h-100">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2" style="color: #6366f1;">Total Meetings</div>
                        <div class="premium-number">{{ $totalMeetings }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #6366f1, #4f46e5);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-handshake" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('meetings.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>View Summaries</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="premium-card border-top-upcoming h-100">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2" style="color: #f59e0b;">Upcoming</div>
                        <div class="premium-number">{{ $upcomingMeetings }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-calendar-alt" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('meetings.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>Review Schedule</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</div>

<!-- Recent Activity / Notices -->
<div class="row">
    <div class="col-12">
        <div class="premium-card">
            <div class="card-header bg-white border-0 py-3">
                <h3 class="card-title font-weight-bold m-0" style="color: #1e293b; font-family: 'Outfit', sans-serif;"><i class="fas fa-history mr-2 text-info"></i> Recent Notices</h3>
                <div class="card-tools">
                    <a href="{{ route('notices.index') }}" class="btn btn-sm btn-light border" style="border-radius: 8px;">View All</a>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table premium-table mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th width="50px">S/L</th>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Created At</th>
                            @can('notice_view')
                            <th>Action</th>
                            @endcan
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentNotices as $notice)
                        <tr>
                            <td class="text-muted">{{ $loop->iteration }}</td>
                            <td class="font-weight-bold">{{ $notice->title }}</td>
                            <td>
                                @if($notice->is_urgent)
                                    <span class="badge badge-danger px-2 py-1" style="border-radius: 6px;">Urgent</span>
                                @else
                                    <span class="badge badge-info px-2 py-1" style="border-radius: 6px;">Normal</span>
                                @endif
                            </td>
                            <td class="text-muted">{{ $notice->created_at->format('d M Y, h:i A') }}</td>
                            @can('notice_view')
                            <td>
                                <a href="{{ route('notices.show', $notice->id) }}" class="btn btn-sm btn-outline-info" style="border-radius: 6px;"><i class="fas fa-eye"></i></a>
                            </td>
                            @endcan
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ auth()->user()->can('notice_view') ? '5' : '4' }}" class="text-center py-4 text-muted">No notices found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
