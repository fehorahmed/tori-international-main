@extends('layouts.app')
@section('title', 'Area Manager Performance Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Shared Tabs -->
    @include('performance_dashboard.partials.__tabs')

    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                <div class="card-body text-center py-5">
                    <img src="{{ asset('themes/backend/dist/img/working-illustration.svg') }}" onerror="this.src=''" alt="Coming Soon" style="max-width: 250px; margin-bottom: 20px; opacity: 0.7;">
                    <h3 class="font-weight-bold" style="color: #1e293b; font-family: 'Outfit', sans-serif;">Area Manager Performance</h3>
                    <p class="text-muted">This module is currently under development. Analytics for Area Managers will be available soon.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
