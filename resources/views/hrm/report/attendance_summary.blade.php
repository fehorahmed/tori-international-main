@extends('layouts.app')
@section('title','Attendance Summary')
@section('style')
    <style>
        .table-bordered td, .table-bordered th {
            border: 1px solid #000000 !important;
            padding: 2px;
        }
        .table thead th {
            vertical-align: bottom;
            border-bottom: 2px solid #000000 !important;;
        }
        .customer-area-border {
            border: 1.5px solid #000 !important;;
            padding: 3px;
            min-height: 128px;
            margin-top: 10px;
            margin-bottom: 50px;
        }
        .customer-area-border p {
            font-size: 20px !important;
        }
        .table-bordered td, .table-bordered th {
            font-size: 18px !important;
        }
        @media print {
            .table-bordered td, .table-bordered th {
                font-size: 18px !important;
            }
            .signature-area{
                position: fixed;
                bottom: 0;
                left: 0;
                width: 100%;
            }
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <!-- left column -->
        <div class="col-md-12">
            <!-- jquery validation -->
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">Data Filter</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->

                <div class="card-body">
                    <form enctype="multipart/form-data" action="{{ route('report.attendance-summary') }}"
                          class="form-horizontal">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="employee">Employee <span class="text-danger">*</span></label>
                                    <select name="employee" id="employee" class="form-control select2">
                                        <option value="">All Employee</option>
                                        @foreach($employees as $employee)
                                            <option
                                                {{ request('employee') == $employee->id ? 'selected' : '' }} value="{{ $employee->id }}">{{ $employee->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="start_date">Start Date <span class="text-danger">*</span></label>
                                    <input type="text" placeholder="Enter Start Date" autocomplete="off"
                                           value="{{ request('start_date',date('d-m-Y')) }}" id="start_date"
                                           name="start_date" class="form-control date-picker">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="end_date">End Date <span class="text-danger">*</span></label>
                                    <input type="text" placeholder="Enter End Date" autocomplete="off"
                                           value="{{ request('end_date',date('d-m-Y')) }}" id="end_date" name="end_date"
                                           class="form-control date-picker">
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
                    </form>

                </div>
            </div>
            <!-- /.card -->
            @if(request('start_date') != '')
            <div class="card">
                <div class="card-header">
                    <a href="#" onclick="getprint('printArea')"
                       class="btn btn-info bg-gradient-info btn-sm"><i
                            class="fa fa-print"></i></a>
                </div>
                <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="table-responsive" id="printArea">
                                    @include('layouts.partial.__report_header')
                                    <div class="row">
                                        <div class="col-12">
                                            <h3 class="text-center" style="font-size: 25px"><u><b>Attendance Summary</b></u></h3>
                                            <h5 class="text-center" style="font-size: 18px">Date: {{ \Carbon\Carbon::parse(request('start_date'))->format('d-m-Y') }} to {{ \Carbon\Carbon::parse(request('end_date'))->format('d-m-Y') }}</h5>
                                        </div>
                                    </div>
                                    @foreach($employeeSummaries as $key=> $employeeSummary)
                                        <h3>{{ $employeeSummary['employee']->name }} (ID No. {{ $employeeSummary['employee']->id_no }})</h3>
                                        <table class="table table-bordered">
                                            <thead>
                                            <tr>
                                                <th class="text-center" width="5%">S/L</th>
                                                <th class="text-center">Status</th>
                                                <th class="text-center">Count</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr>
                                                <td class="text-center">1</td>
                                                <td>Present</td>
                                                <td class="text-center">{{ $employeeSummary['summary']['present'] }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-center">2</td>
                                                <td>No Checkout</td>
                                                <td class="text-center">{{ $employeeSummary['summary']['no_checkout'] }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-center">3</td>
                                                <td>Absent</td>
                                                <td class="text-center">{{ $employeeSummary['summary']['absent'] }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-center">4</td>
                                                <td>Late</td>
                                                <td class="text-center">{{ $employeeSummary['summary']['late'] }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-center">5</td>
                                                <td>Early Checkout</td>
                                                <td class="text-center">{{ $employeeSummary['summary']['early_checkout'] }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-center">6</td>
                                                <td>Late & Early Checkout</td>
                                                <td class="text-center">{{ $employeeSummary['summary']['late_and_early_checkout'] }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-center">7</td>
                                                <td>Movement</td>
                                                <td class="text-center">{{ $employeeSummary['summary']['movement'] }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-center">8</td>
                                                <td>Public Holiday</td>
                                                <td class="text-center">{{ $employeeSummary['summary']['public_holiday'] }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-center">9</td>
                                                <td>Weekend Holiday</td>
                                                <td class="text-center">{{ $employeeSummary['summary']['weekend_holiday'] }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-center">10</td>
                                                <td>Leave</td>
                                                <td class="text-center">{{ $employeeSummary['summary']['leave'] }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-center">11</td>
                                                <td>Overtime</td>
                                                <td class="text-center">
                                                    @if($employeeSummary['summary']['overtime'] > 0)
                                                    {{ overtimeCalcHours($employeeSummary['summary']['overtime']) }}
                                                    @endif
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    @endforeach
                                </div>

                            </div>
                        </div>
                </div>
            </div>
            @endif
        </div>
        <!--/.col (left) -->
    </div>
@endsection
@section('script')
    <script>

        var APP_URL = '{!! url()->full()  !!}';

        function getprint(print) {
            $('.print-heading').css('display', 'block');
            $('.extra_column,.print-remove').remove();
            $('body').html($('#' + print).html());
            window.print();
            window.location.replace(APP_URL)
        }
    </script>
@endsection
