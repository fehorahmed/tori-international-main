@extends('layouts.app')
@section('title','Attendance Sheet')
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
                    <form enctype="multipart/form-data" action="{{ route('report.daily-attendance-sheet') }}"
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
                                    <label for="date">Date <span class="text-danger">*</span></label>
                                    <input required type="text" placeholder="Enter Date" autocomplete="off"
                                           value="{{ request('date', date('d-m-Y')) }}" id="date"
                                           name="date" class="form-control date-picker">
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
            @if(request('date') != '')
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
                                            <h3 class="text-center" style="font-size: 25px"><u><b>Daily Attendance Sheet</b></u></h3>
                                            <h5 class="text-center mb-0" style="font-size: 18px;font-weight: bold">Date: {{ \Carbon\Carbon::parse(request('date'))->format('d-m-Y') }}</h5>
                                            @if($selectEmployee)
                                                <h5 class="text-center" style="font-size: 18px">Employee: {{ $selectEmployee->name }} (ID: {{ $selectEmployee->id_no }})</h5>
                                            @endif
                                        </div>
                                    </div>
                                    <table class="table table-bordered mt-2">
                                        <thead>
                                        <tr>
                                            <th class="text-center">S/L</th>
                                            <th class="text-center">ID No</th>
                                            <th>Employee Name</th>
                                            <th>Designation</th>
                                            <th class="text-center">In Time</th>
                                            <th class="text-center">Out Time</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center">Overtime</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @php
                                            $overtimeInSeconds = 0;
                                        @endphp
                                        @if(count($report) > 0)
                                            @foreach($report as $key =>  $data)
                                                @php
                                                    $overtimeInSeconds += $data['overtime_in_seconds'];
                                                @endphp
                                                <tr>
                                                    <td class="text-center">{{ ++$key }}</td>
                                                    <td class="text-center">{{ $data['id_no'] }}</td>
                                                    <td>{{ $data['employee_name'] }}</td>
                                                    <td>{{ $data['designation'] }}</td>
                                                    <td class="text-center">{{ $data['punch_in_time'] }}</td>
                                                    <td class="text-center">{{ $data['punch_out_time'] }}</td>
                                                    <td class="text-center">{!! $data['status'] !!}</td>
                                                    <td class="text-center">{{ $data['overtime'] }}</td>
                                                </tr>
                                            @endforeach
                                        @endif
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="7" class="text-right">Total Overtime:</th>
                                                <th class="text-center">{{ overtimeCalcHours($overtimeInSeconds) }}</th>
                                            </tr>
                                        </tfoot>
                                    </table>
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
