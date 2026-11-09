@extends('layouts.app')
@section('title', $pageTitle ?? 'Member Card Print History')

@section('style')
    <style>
        .table-bordered td, .table-bordered th {
            border: 1px solid #000 !important;
            vertical-align: middle;
            text-align: center;
            padding: 0.4rem !important;
        }
        .table-bordered td, .table-bordered th {
            font-size: 16px !important;
        }
        @media print{
            .table-bordered td, .table-bordered th {
                font-size: 18px !important;
            }
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <!-- left column -->
        <div class="col-md-12">
            <!-- Data Filter -->
            <div class="card card-outline card-default">
                <div class="card-header">
                    <h3 class="card-title">Data Filter</h3>
                </div>
                <form action="{{ route('card-management.reports.member.print_history') }}">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="start_date">Start Date</label>
                                    <input required type="text" id="start_date" autocomplete="off"
                                           name="start_date" class="form-control date-picker"
                                           placeholder="Enter Start Date" value="{{ request('start_date') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="end_date">End Date</label>
                                    <input required type="text" id="end_date" autocomplete="off"
                                           name="end_date" class="form-control date-picker"
                                           placeholder="Enter End Date" value="{{ request('end_date') }}">
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
            <!-- /.card -->
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    <a href="#" onclick="getprint('printArea')" class="btn btn-info bg-gradient-info btn-sm "><i class="fa fa-print"></i> Print</a>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="table-responsive" id="printArea">
                        @include('layouts.partial.__report_header')
                        
                        <div class="row mb-3">
                            <div class="col-12">
                                <h3 class="text-center m-0 mb-2 mt-2 header-common-info" style="font-size: 19px !important;">
                                    <b>{{ $pageTitle }}</b>
                                    @if(request('start_date') && request('end_date'))
                                    <br>
                                    <span style="font-size: 14px;">Date: {{ request('start_date') }} to {{ request('end_date') }}</span>
                                    @endif
                                </h3>
                            </div>
                        </div>

                        <table class="table table-bordered table-striped" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Card No</th>
                                    <th>Holder Name</th>
                                    <th>Reason</th>
                                    <th>Printed By</th>
                                    <th>Printed At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($logs as $index => $log)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $log->card ? $log->card->card_number : '-' }}</td>
                                    <td>{{ ($log->card && $log->card->cardable) ? $log->card->cardable->name : '-' }}</td>
                                    <td>{{ $log->print_reason }}</td>
                                    <td>{{ $log->printer ? $log->printer->name : '-' }}</td>
                                    <td>{{ $log->printed_at ? $log->printed_at->format('d-m-Y h:i A') : '-' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">No Print History Found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
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
