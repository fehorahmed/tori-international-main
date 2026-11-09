@extends('layouts.app')

@section('title', 'DPS Scheme Summary Report')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header bg-gradient-teal text-white">
            <h5 class="card-title mb-0"><i class="fas fa-chart-pie mr-2"></i> DPS Scheme Summary Report</h5>
        </div>
        <div class="card-body">
            <form target="_blank" action="{{ route('shomriddhi-dps.reports.scheme-summary') }}" method="GET" class="mb-4">
                <input type="hidden" name="search" value="1">
                <div class="row align-items-end">
                    <div class="col-md-12">
                        <p class="text-muted">Click the button below to view a summarized report of all active schemes, total active accounts, and total liabilities.</p>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-teal btn-block text-white" style="background-color: #20c997; border-color: #20c997;"><i class="fas fa-search"></i> Generate Report</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
