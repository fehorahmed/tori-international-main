@extends('layouts.app')
@section('title', $pageTitle)
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-outline card-default">
                <div class="card-header">
                    <h3 class="card-title">Data Filter</h3>
                </div>
                <!-- form start -->
                <form target="_blank" action="{{ route('microfinance.report.loan-disbursement') }}" method="GET">
                    @include('layouts.partial.__report_branch_filter')
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="start_date">Start Date <span class="text-danger">*</span> <small class="text-muted font-weight-normal">(Default Today)</small></label>
                                    <input required type="text" id="start_date" autocomplete="off"
                                           name="start_date" class="form-control business-date-picker"
                                           placeholder="Enter Start Date" value="{{ old('start_date', request('start_date', now()->format('d-m-Y'))) }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="end_date">End Date <span class="text-danger">*</span> <small class="text-muted font-weight-normal">(Default Today)</small></label>
                                    <input required type="text" id="end_date" autocomplete="off"
                                           name="end_date" class="form-control business-date-picker"
                                           placeholder="Enter End Date" value="{{ old('end_date', request('end_date', now()->format('d-m-Y'))) }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <input type="submit" name="search" class="btn btn-info bg-gradient-info form-control" value="Search">
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

