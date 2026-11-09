@extends('layouts.app')

@section('title', 'DPS Matured Accounts Report')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header bg-gradient-primary text-white">
            <h5 class="card-title mb-0"><i class="fas fa-calendar-check mr-2"></i> DPS Matured Accounts Report</h5>
        </div>
        <div class="card-body">
            <form target="_blank" action="{{ route('shomriddhi-dps.reports.matured-accounts') }}" method="GET" class="mb-4">
                <input type="hidden" name="search" value="1">
                <div class="row align-items-end">
                    <div class="col-md-12">
                        <p class="text-muted">Click the button below to generate a report of all accounts that have matured but not yet encashed.</p>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-search"></i> Generate Report</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
