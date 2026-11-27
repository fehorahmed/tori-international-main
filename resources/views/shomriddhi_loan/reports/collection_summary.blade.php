@extends('layouts.app')
@section('title', 'Collection Summary')
@section('content')
<div class="card card-default">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-chart-bar mr-2"></i> Collection Summary
        </h5>
    </div>
    <div class="card-body">
        <form target="_blank" action="" method="GET" class="mb-4">
                    @include('layouts.partial.__report_branch_filter')
            <input type="hidden" name="search" value="1">
            <div class="row align-items-end">
                <div class="col-md-3">
                    <div class="form-group mb-0">
                        <label>Start Date <small class="text-muted font-weight-normal">(Default Today)</small></label>
                        <input type="text" name="start_date" class="form-control date-picker" value="{{ old('start_date', request('start_date', now()->format('d-m-Y'))) }}" required autocomplete="off">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group mb-0">
                        <label>End Date <small class="text-muted font-weight-normal">(Default Today)</small></label>
                        <input type="text" name="end_date" class="form-control date-picker" value="{{ old('end_date', request('end_date', now()->format('d-m-Y'))) }}" required autocomplete="off">
                    </div>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-info btn-block"><i class="fas fa-search"></i> Generate Report</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
