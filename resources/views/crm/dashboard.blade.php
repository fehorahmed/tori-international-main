@extends('layouts.app')
@section('title', 'CRM Workspace')
@section('content')
@include('layouts.partial.__premium_dashboard_css')

<!-- Welcome Banner -->
<div class="row mb-4">
    <div class="col-12">
        <div class="welcome-banner theme-office d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #be185d, #ec4899);">
            <div style="z-index: 1;">
                <div class="welcome-title text-white">Welcome back, {{ auth()->user()->name }}! 👋</div>
                <div class="welcome-subtitle text-white-50">Here is your CRM Workspace overview for today.</div>
            </div>
            <div class="d-none d-md-block" style="z-index: 1; opacity: 0.8;">
                <i class="fas fa-funnel-dollar fa-4x text-white"></i>
            </div>
        </div>
    </div>
</div>

<!-- Key Metrics Cards -->
<div class="row">
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="premium-card border-top-office h-100" style="border-top-color: #ec4899;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Total Leads</div>
                        <div class="premium-number text-dark">{{ $totalLeads }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #ec4899, #be185d);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-users" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('leads.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>View Leads</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="premium-card border-top-urgent h-100">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-danger">Today's Leads</div>
                        <div class="premium-number text-danger">{{ $todaysLeads }}</div>
                    </div>
                    <div class="premium-icon-box bg-gradient-urgent shadow-sm">
                        <div class="deco-circle"></div>
                        <i class="fas fa-user-clock" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('leads.index', ['start_date' => date('d-m-Y'), 'end_date' => date('d-m-Y')]) }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span class="text-danger">Review Today</span>
                <i class="fas fa-arrow-right text-danger"></i>
            </a>
        </div>
    </div>
    

</div>

<!-- Recent Leads -->
<div class="row">
    <div class="col-12">
        <div class="premium-card">
            <div class="card-header bg-white border-0 py-3">
                <h3 class="card-title font-weight-bold m-0" style="color: #1e293b; font-family: 'Outfit', sans-serif;"><i class="fas fa-history mr-2 text-info"></i> Recent Leads</h3>
                <div class="card-tools">
                    <a href="{{ route('leads.index') }}" class="btn btn-sm btn-light border" style="border-radius: 8px;">View All</a>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table premium-table mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th width="50px">S/L</th>
                            <th>Date</th>
                            <th>Client Name</th>
                            <th>Contact No</th>

                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentLeads as $lead)
                        <tr>
                            <td class="text-muted">{{ $loop->iteration }}</td>
                            <td class="text-muted">{{ \Carbon\Carbon::parse($lead->date)->format('d M Y') }}</td>
                            <td class="font-weight-bold">{{ $lead->crmClient->name ?? 'N/A' }}</td>
                            <td>{{ $lead->crmClient->contact_no ?? 'N/A' }}</td>

                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">No leads found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
