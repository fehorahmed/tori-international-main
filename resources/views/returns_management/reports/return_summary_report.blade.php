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
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Start Date</label>
                                <input type="text" class="form-control date-picker" autocomplete="off" name="start_date" id="start_date" value="{{ date('d-m-Y', strtotime(date('Y-m-01'))) }}" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>End Date</label>
                                <input type="text" class="form-control date-picker" autocomplete="off" name="end_date" id="end_date" value="{{ date('d-m-Y') }}" required>
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
