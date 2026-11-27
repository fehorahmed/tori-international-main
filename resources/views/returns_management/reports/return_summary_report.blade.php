@extends('layouts.app')
@section('title', 'Return Summary Report')
@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-default">
            <div class="card-header">
                <h3 class="card-title">Return Summary Report</h3>
            </div>
            <div class="card-body">
                <form id="report-form" action="{{ route('returns-management.report.return_summary') }}" target="_blank">
                    @include('layouts.partial.__report_branch_filter')
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Start Date <small class="text-muted font-weight-normal">(Default Today)</small></label>
                                <input type="text" class="form-control date-picker" autocomplete="off" name="start_date" id="start_date" value="{{ old('start_date', request('start_date', now()->format('d-m-Y'))) }}" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>End Date <small class="text-muted font-weight-normal">(Default Today)</small></label>
                                <input type="text" class="form-control date-picker" autocomplete="off" name="end_date" id="end_date" value="{{ old('end_date', request('end_date', now()->format('d-m-Y'))) }}" required>
                            </div>
                        </div>
                        <div class="col-md-4" style="margin-top: 30px;">
                            <button type="submit" name="search" value="1" class="btn btn-info btn-block">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
