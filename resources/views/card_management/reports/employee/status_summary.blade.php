@extends('layouts.app')
@section('title', $pageTitle ?? 'Employee Card Status Summary')

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
                                <h3 class="text-center m-0 mb-2 mt-2 header-common-info" style="font-size: 19px !important;"><b>{{ $pageTitle }}</b></h3>
                            </div>
                        </div>

                        <div class="row justify-content-center">
                            <div class="col-md-8">
                                <table class="table table-bordered table-striped" style="width: 100%;">
                                    <thead>
                                        <tr>
                                            <th>Status</th>
                                            <th class="text-center">Count</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach(['Active', 'Suspended', 'Lost', 'Damaged', 'Expired', 'Revoked'] as $status)
                                        <tr>
                                            <td class="text-left font-weight-bold">{{ $status }}</td>
                                            <td class="text-center"><strong>{{ $summary[$status] ?? 0 }}</strong></td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="bg-light">
                                            <th class="text-right">Total</th>
                                            <th class="text-center"><strong>{{ $total }}</strong></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
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
