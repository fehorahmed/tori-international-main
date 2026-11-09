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
            <input type="hidden" name="search" value="1">
            <div class="row align-items-end">
                <div class="col-md-3">
                    <div class="form-group mb-0">
                        <label>Start Date</label>
                        <input type="text" name="start_date" class="form-control date-picker" value="{{ $startDate->format('Y-m-d') }}" required autocomplete="off">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group mb-0">
                        <label>End Date</label>
                        <input type="text" name="end_date" class="form-control date-picker" value="{{ $endDate->format('Y-m-d') }}" required autocomplete="off">
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
