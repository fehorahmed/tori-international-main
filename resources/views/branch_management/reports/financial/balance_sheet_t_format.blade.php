@extends('layouts.app')
@section('title','Balance Sheet')
@section('style')
    <style>
        .table-bordered td, .table-bordered th {
            border: 1px solid #000 !important;
            vertical-align: middle !important;
            padding: 0.2rem !important;
            font-size: 16px !important;
        }
        .table-borderless td, .table-borderless th {
            border: none !important;
            padding: 0.2rem !important;
            font-size: 16px !important;
        }
        td a:hover{
            color: #000 !important;
            text-decoration: underline;
        }
        @media print {
            td a{
                text-decoration: none !important;
                color: #000 !important;
            }
            .row.outer-border {
                display: flex !important;
                flex-wrap: nowrap !important;
                align-items: stretch !important;
            }
            .col-6 {
                width: 50% !important;
                max-width: 50% !important;
                flex: 0 0 50% !important;
                float: none !important;
            }
            .border-middle {
                border-right: 2px solid #000 !important;
            }
            .outer-border {
                border: 2px solid #000 !important;
            }
        }
        .double-single-border {
            border-top: 1px solid #000 !important;
            border-bottom: 4px double #000 !important;
        }
        .custom-ml-18{
            margin-left: 18px !important;
        }
    </style>
@endsection
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-outline card-default">
                <div class="card-header">
                    <h3 class="card-title">Data Filter</h3>
                </div>
                <form target="_blank" action="{{ route('branch_report.balance_sheet') }}">
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
                                    <label for="start_date">Start Date <span class="text-danger">*</span></label>
                                    <input required type="text" id="start_date" autocomplete="off"
                                           name="start_date" class="form-control date-picker"
                                           placeholder="Enter Start Date"
                                           value="{{ request()->get('start_date') ?? date('d-m-Y',strtotime(auth()->user()->sisterConcern->business_date)) }}"> 
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="end_date">End Date <span class="text-danger">*</span></label>
                                    <input required type="text" id="end_date" autocomplete="off"
                                           name="end_date" class="form-control date-picker"
                                           placeholder="Enter Start Date"
                                           value="{{ request()->get('end_date') ?? date('d-m-Y',strtotime(auth()->user()->sisterConcern->business_date)) }}">
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

@section('script')
    <script>
        var APP_URL = '{!! url()->full()  !!}';

        function getprint(print) {
            $('.print-heading').css('display', 'block');
            $('.extra_column').remove();
            $('body').html($('#' + print).html());
            window.print();
            window.location.replace(APP_URL)
        }
    </script>
@endsection


