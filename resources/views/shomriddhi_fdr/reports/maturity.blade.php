@extends('layouts.app')

@section('title', 'FDR Maturity Report')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header bg-gradient-info text-white">
            <h5 class="card-title mb-0"><i class="fas fa-calendar-alt mr-2"></i> FDR Maturity Report</h5>
        </div>
        <div class="card-body">
            <form target="_blank" action="{{ route('shomriddhi-fdr.reports.maturity') }}" method="GET" class="mb-4">
                    @include('layouts.partial.__report_branch_filter')
                <input type="hidden" name="search" value="1">
                <div class="row align-items-end">
                    <div class="col-md-3">
                        <label for="start_date">Maturity From Date <small class="text-muted font-weight-normal">(Default Today)</small></label>
                        <input type="text" name="start_date" id="start_date" class="form-control date-picker" value="{{ old('start_date', request('start_date', now()->format('d-m-Y'))) }}">
                    </div>
                    <div class="col-md-3">
                        <label for="end_date">Maturity To Date <small class="text-muted font-weight-normal">(Default Today)</small></label>
                        <input type="text" name="end_date" id="end_date" class="form-control date-picker" value="{{ old('end_date', request('end_date', now()->format('d-m-Y'))) }}">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-info btn-block"><i class="fas fa-search"></i> Generate Report</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
