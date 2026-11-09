@extends('layouts.app')
@section('title', 'SMS Dashboard')

@section('content')
<div class="row">
    <!-- KPI Grid -->
    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-success elevation-1"><i class="fas fa-paper-plane"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Sent</span>
                <span class="info-box-number">{{ number_format($stats['total_sent']) }}</span>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-exclamation-triangle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Failed</span>
                <span class="info-box-number">{{ number_format($stats['total_failed']) }}</span>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box mb-3">
            <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-file-code text-white"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Active Templates</span>
                <span class="info-box-number">{{ $stats['active_templates'] }}</span>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box mb-3">
            <span class="info-box-icon bg-info elevation-1"><i class="fas fa-satellite-dish"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Active Gateway</span>
                <span class="info-box-number">{{ $stats['active_gateway']->provider_name ?? 'None' }}</span>
            </div>
        </div>
    </div>

    <!-- Details -->
    <div class="col-md-8">
        <div class="card card-default">
            <div class="card-header border-transparent">
                <h3 class="card-title"><i class="fas fa-history text-secondary mr-2"></i> Recent SMS Logs</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped m-0">
                        <thead>
                        <tr>
                            <th>Mobile No</th>
                            <th>Message</th>
                            <th>Status</th>
                            <th>Time</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($recentLogs as $log)
                            <tr>
                                <td>{{ $log->mobile_no }}</td>
                                <td>{{ \Str::limit($log->message, 40) }}</td>
                                <td>
                                    @if($log->status == 'Sent')
                                        <span class="badge badge-success">Sent</span>
                                    @elseif($log->status == 'Failed')
                                        <span class="badge badge-danger">Failed</span>
                                    @else
                                        <span class="badge badge-warning">Pending</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="sparkbar" data-color="#00a65a" data-height="20">{{ \Carbon\Carbon::parse($log->sent_at)->diffForHumans() }}</div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No logs available yet.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer text-center">
                <a href="{{ route('sms_management.logs.index') }}" class="uppercase btn btn-sm btn-info bg-gradient-info">View All Logs</a>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card card-default">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-rocket text-warning mr-2"></i> Quick Actions</h3>
            </div>
            <div class="card-body">
                <a href="{{ route('sms_management.campaign.index') }}" class="btn btn-block btn-success bg-gradient-success mb-3 text-left">
                    <i class="fas fa-bullhorn mr-2"></i> Start Bulk Campaign
                </a>
                
                <a href="{{ route('sms_management.templates.index') }}" class="btn btn-block btn-info bg-gradient-info mb-3 text-left">
                    <i class="fas fa-plus mr-2"></i> Add SMS Template
                </a>
                
                <a href="{{ route('sms_management.gateways.index') }}" class="btn btn-block btn-secondary bg-gradient-secondary text-left">
                    <i class="fas fa-cog mr-2"></i> Configure Gateway
                </a>
            </div>
            <div class="card-footer">
                <p class="small text-muted mb-0"><i class="fas fa-info-circle mr-1"></i> All SMS sending is processed via background queues.</p>
            </div>
        </div>
    </div>
</div>
@endsection
