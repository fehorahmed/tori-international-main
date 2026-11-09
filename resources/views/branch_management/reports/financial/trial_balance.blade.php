@extends('layouts.app')
@section('title','Trial Balance Report')
@section('style')
    <style>
        .table-bordered td, .table-bordered th {
            border: 1px solid #000 !important;
            vertical-align: middle;
            text-align: center;
            padding: 0.2rem !important;
        }
        .table-bordered td, .table-bordered th {
            font-size: 18px !important;
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
    </style>
@endsection
@section('content')
    @if(request('account_group_id') == '')
    <div class="row">
        <!-- left column -->
        <div class="col-md-12">
            <!-- jquery validation -->
            <div class="card card-outline card-default">
                <div class="card-header">
                    <h3 class="card-title">Data Filter</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                <form target="_blank" action="{{ route('branch_report.trial_balance') }}">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 col-md-3">
                                    <div class="form-group">
                                        <label>Branch<span class="text-danger">*</span></label>
                                        <select class="form-control select2" id="sister_concern" name="sister_concern">
                                            <option value="">All Sister Concern</option>
                                            @foreach ($sister_concerns as $sister_concern)
                                                <option value="{{ $sister_concern->id }}" {{ request('sister_concern') == $sister_concern->id ? 'selected' : ''  }}>{{ $sister_concern->display_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="start_date">Start Date <span
                                            class="text-danger">*</span></label>
                                    <input required type="text" id="start_date" autocomplete="off"
                                           name="start_date" class="form-control date-picker"
                                           value="{{ request()->get('start_date') ?? date('d-m-Y',strtotime(auth()->user()->sisterConcern->business_date)) }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="end_date">End Date <span
                                            class="text-danger">*</span></label>
                                    <input required type="text" id="end_date" autocomplete="off"
                                           name="end_date" class="form-control date-picker"
                                           value="{{ request()->get('end_date') ?? date('d-m-Y',strtotime(auth()->user()->sisterConcern->business_date)) }}">
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="account_head">Account Head <span
                                            class="text-danger">*</span></label>
                                    <select name="account_head" id="account_head" class="form-control select2">
                                        <option value="">All Account Head</option>
                                        @foreach($accountHeadsList as $accountHeadList)
                                            <option
                                                {{ request('account_head') == $accountHeadList->id ? 'selected' : '' }} value="{{ $accountHeadList->id }}">{{ $accountHeadList->name }}
                                                - {{ $accountHeadList->code }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <input type="submit" name="search"
                                           class="btn btn-info bg-gradient-info form-control" value="Search">
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
    @endif
@endsection

@section('script')
    <script>
        $(function (){
            
        })

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


