@extends('layouts.app')

@section('title', 'DPS Closed Accounts Report')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header bg-gradient-secondary text-white">
            <h5 class="card-title mb-0"><i class="fas fa-times-circle mr-2"></i> DPS Closed Accounts Report</h5>
        </div>
        <div class="card-body">
            <form target="_blank" action="{{ route('shomriddhi-dps.reports.closed-accounts') }}" method="GET" class="mb-4">
                <input type="hidden" name="search" value="1">
                <div class="row align-items-end">
                    <div class="col-md-3">
                        <label for="start_date">From Date (Closed On)</label>
                        <input type="date" name="start_date" id="start_date" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label for="end_date">To Date (Closed On)</label>
                        <input type="date" name="end_date" id="end_date" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-secondary btn-block"><i class="fas fa-search"></i> Generate Report</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
