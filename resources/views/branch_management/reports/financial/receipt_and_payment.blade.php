@extends('layouts.app')
@section('title','Receipt & Payment Report')
@section('style')
    <style>
        .table-bordered thead td, .table-bordered thead th {
            vertical-align: middle;
            border: 1px solid #000!important;
            padding: 2px;
        }
        .table-bordered td, .table-bordered th {
            border: 1px solid #000000!important;
            vertical-align: middle;
        }

    </style>
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    <div class="card-title">Filter</div>
                </div>
                <div class="card-body">
                    <form target="_blank" action="{{ route('branch_report.receipt_and_payment') }}">
                    @include('layouts.partial.__report_branch_filter')
                        <div class="row">
                            <div class="col-12 col-md-3">
                                <div class="form-group">
                                    {{-- Branch selector is provided by __report_branch_filter. --}}
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="form-group">
                                    <label for="start_date" class="col-form-label">Start Date <span
                                            class="text-danger">*</span> <small class="text-muted font-weight-normal">(Default Today)</small></label>
                                    <input required autocomplete="off" type="text" value="{{ old('start_date', request('start_date', now()->format('d-m-Y'))) }}"
                                           name="start_date" class="form-control date-picker" id="start_date">
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="form-group">
                                    <label for="end_date" class="col-form-label">End Date <span
                                            class="text-danger">*</span> <small class="text-muted font-weight-normal">(Default Today)</small></label>
                                    <input required autocomplete="off" type="text" value="{{ old('end_date', request('end_date', now()->format('d-m-Y'))) }}"
                                           name="end_date" class="form-control date-picker" id="end_date">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <input style="margin-top: -1px;" type="submit" name="search"
                                           class="btn btn-info bg-gradient-info form-control" value="Search">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
@endsection
