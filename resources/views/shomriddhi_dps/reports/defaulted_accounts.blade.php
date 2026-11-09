@extends('layouts.app')

@section('title', 'DPS Defaulted Accounts Report')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header bg-gradient-danger text-white">
            <h5 class="card-title mb-0"><i class="fas fa-exclamation-triangle mr-2"></i> DPS Defaulted Accounts Report</h5>
        </div>
        <div class="card-body">
            <form target="_blank" action="{{ route('shomriddhi-dps.reports.defaulted-accounts') }}" method="GET" class="mb-4">
                <input type="hidden" name="search" value="1">
                <div class="row align-items-end">
                    <div class="col-md-12">
                        <p class="text-muted">Click the button below to generate a report of all defaulted accounts (Missed installments &ge; 3).</p>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-danger btn-block"><i class="fas fa-search"></i> Generate Report</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
