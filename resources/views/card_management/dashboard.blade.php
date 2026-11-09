@extends('layouts.app')
@section('title', $pageTitle)

@section('content')
@include('layouts.partial.__premium_dashboard_css')

<!-- Welcome Banner -->
<div class="row mb-4">
    <div class="col-12">
        <div class="welcome-banner theme-office d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #4f46e5, #312e81);">
            <div style="z-index: 1;">
                <div class="welcome-title text-white">{{ $pageTitle }}</div>
                <div class="welcome-subtitle text-white-50">Manage, issue, and track ID cards for members and employees efficiently.</div>
            </div>
            <div class="d-none d-md-block" style="z-index: 1; opacity: 0.8;">
                <i class="fas fa-id-card fa-4x text-white"></i>
            </div>
        </div>
    </div>
</div>

<!-- Key Metrics Cards -->
<div class="row">
    <!-- Member Cards -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="premium-card border-top-office h-100" style="border-top-color: #4f46e5;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Total Member Cards</div>
                        <div class="premium-number text-dark">{{ number_format($totalMemberCards) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #6366f1, #4f46e5);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-users" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            @can('member_card')
            <a href="{{ route('card-management.member.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>View Member Cards</span>
                <i class="fas fa-arrow-right"></i>
            </a>
            @else
            <div class="premium-footer d-flex justify-content-between align-items-center text-muted">
                <span>Active: {{ number_format($activeMemberCards) }}</span>
            </div>
            @endcan
        </div>
    </div>

    <!-- Employee Cards -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="premium-card border-top-office h-100" style="border-top-color: #0d9488;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Total Employee Cards</div>
                        <div class="premium-number text-dark">{{ number_format($totalEmployeeCards) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #2dd4bf, #0d9488);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-user-tie" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            @can('employee_card')
            <a href="{{ route('card-management.employee.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>View Employee Cards</span>
                <i class="fas fa-arrow-right"></i>
            </a>
            @else
            <div class="premium-footer d-flex justify-content-between align-items-center text-muted">
                <span>Active: {{ number_format($activeEmployeeCards) }}</span>
            </div>
            @endcan
        </div>
    </div>

    <!-- Recent Prints -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="premium-card border-top-office h-100" style="border-top-color: #f59e0b;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Recent Prints</div>
                        <div class="premium-number text-dark">{{ number_format($recentPrintLogs->count()) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #fbbf24, #f59e0b);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-print" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            @canany(['member_card_logs', 'employee_card_logs'])
            <a href="{{ route('card-management.member.logs') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>View Logs</span>
                <i class="fas fa-arrow-right"></i>
            </a>
            @else
            <div class="premium-footer d-flex justify-content-between align-items-center text-muted">
                <span>Print Activity</span>
            </div>
            @endcanany
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="premium-card border-top-office h-100" style="border-top-color: #e11d48;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Quick Actions</div>
                        <div class="text-dark mt-2">Issue New Cards</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #fb7185, #e11d48);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-bolt" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <div class="premium-footer d-flex justify-content-between align-items-center">
                @can('member_card_issue')
                <a href="{{ route('card-management.member.create') }}" class="text-dark" title="Issue Member Card"><i class="fas fa-plus-circle text-primary"></i> Member</a>
                @endcan
                @can('employee_card_issue')
                <a href="{{ route('card-management.employee.create') }}" class="text-dark" title="Issue Employee Card"><i class="fas fa-plus-circle text-success"></i> Employee</a>
                @endcan
            </div>
        </div>
    </div>
</div>

<!-- Recent Print Logs Table -->
<div class="row">
    <div class="col-12">
        <div class="card premium-card border-top-office" style="border-top-color: #64748b;">
            <div class="card-header border-0">
                <h3 class="card-title font-weight-bold text-dark"><i class="fas fa-history mr-2 text-secondary"></i> Recent Print Logs</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover align-middle">
                    <thead style="background-color: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                        <tr>
                            <th class="text-secondary font-weight-600">Date & Time</th>
                            <th class="text-secondary font-weight-600">Card Number</th>
                            <th class="text-secondary font-weight-600">Printed By</th>
                            <th class="text-secondary font-weight-600">Reason</th>
                            <th class="text-secondary font-weight-600">Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentPrintLogs as $log)
                        <tr>
                            <td>{{ $log->printed_at ? $log->printed_at->format('d-M-Y h:i A') : '-' }}</td>
                            <td><span class="badge badge-info shadow-sm">{{ $log->card ? $log->card->card_number : '-' }}</span></td>
                            <td>{{ $log->printer ? $log->printer->name : '-' }}</td>
                            <td>
                                @if($log->print_reason == 'First Issue')
                                    <span class="badge badge-success shadow-sm">{{ $log->print_reason }}</span>
                                @elseif(in_array($log->print_reason, ['Lost Replacement', 'Damaged Replacement']))
                                    <span class="badge badge-danger shadow-sm">{{ $log->print_reason }}</span>
                                @else
                                    <span class="badge badge-warning shadow-sm">{{ $log->print_reason }}</span>
                                @endif
                            </td>
                            <td class="text-muted">{{ Str::limit($log->notes, 30) ?: '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                <i class="fas fa-inbox fa-2x mb-2 text-light"></i><br>
                                No recent print logs found
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
