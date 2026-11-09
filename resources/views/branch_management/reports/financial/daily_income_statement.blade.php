@extends('layouts.app')
@section('title','Daily Income Statement')
@section('style')
    <style>

        .table-bordered td, .table-bordered th {
            border: 1px solid #000 !important;
            vertical-align: middle !important;
            text-align: center;
            padding: 0.2rem !important;
            font-size: 19px !important;
        }

        .table-bordered td {
            border: none !important;
        }

        .table-bordered {
            border: 1px solid #ffffff;
        }

        table thead {
            border-collapse: inherit !important;
        }

       td a:hover{
            color: #000 !important;
            text-decoration: underline;
        }
        @media print {

            td a{
                color: #000 !important;
                text-decoration: none !important;
            }
        }

        td.double-single-border {
            border-top: 1px solid #000 !important;
            border-bottom: 4px double #000 !important;
        }

        td.border-left-right {
            border-left: 1px solid #000 !important;
            border-right: 1px solid #000 !important;
        }

        td.border-top {
            border-top: 1px solid #000 !important;
        }

        td.border-bottom {
            border-bottom: 1px solid #000 !important;
        }

        .custom-ml-18 {
            margin-left: 18px !important;
        }

    </style>
@endsection
@section('content')
    <div class="row">
        <!-- left column -->
        <div class="col-md-12">
            <!-- jquery validation -->
            <div class="card card-outline card-default">
                <div class="card-header">
                    <h3 class="card-title">Daily Income Statement Filter</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                <form target="_blank" action="{{ route('branch_report.daily_income_statement') }}">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 col-md-3">
                                <div class="form-group">
                                    <label>Branch<span class="text-danger">*</span></label>
                                    <select class="form-control select2" id="sister_concern" name="sister_concern">
                                        <option value="">All Branches</option>
                                        @foreach ($sister_concerns as $sister_concern)
                                            <option value="{{ $sister_concern->id }}" {{ request('sister_concern') == $sister_concern->id ? 'selected' : ''  }}>{{ $sister_concern->display_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="start_date">Start Date <span
                                            class="text-danger">*</span></label>
                                    <input required type="text" id="start_date" autocomplete="off"
                                           name="start_date" class="form-control date-picker"
                                           value="{{ request()->get('start_date') ?? date('d-m-Y',strtotime(auth()->user()->sisterConcern->business_date ?? now())) }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="end_date">End Date <span
                                            class="text-danger">*</span></label>
                                    <input required type="text" id="end_date" autocomplete="off"
                                           name="end_date" class="form-control date-picker"
                                           value="{{ request()->get('end_date') ?? date('d-m-Y',strtotime(auth()->user()->sisterConcern->business_date ?? now())) }}">
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <input type="submit" name="search"
                                           class="btn btn-info bg-gradient-info form-control" value="Generate Report">
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <!-- /.card -->
        </div>
        <!--/.col (left) -->
    </div>
@endsection
