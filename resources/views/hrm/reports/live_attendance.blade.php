@extends('layouts.app')
@section('title','Live Attendance Report')
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
            @page {
                size: auto;
                margin: .5in !important;
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
                    <form enctype="multipart/form-data" action="{{ route('report.live_attendance') }}"
                          class="form-horizontal">
                        <div class="row">

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="employee">Employee <span class="text-danger">*</span></label>
                                    <select required name="employee" id="employee" class="form-control select2">
                                        <option value="0">All Employee</option>
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
            @if(request('search'))
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
                                            <h3 class="text-center" style="font-size: 25px"><u><b>Live Attendance Report</b></u></h3>
                                            <h5 class="text-center" style="font-size: 18px">Date: {{ \Carbon\Carbon::parse(request('start_date'))->format('d-m-Y') }} to {{ \Carbon\Carbon::parse(request('end_date'))->format('d-m-Y') }}</h5>
                                        </div>
                                    </div>
                                    <table class="table table-bordered">
                                        <tbody>
                                            <tr>
                                                <th class="text-center">S/L</th>
                                                <th class="text-center">Date</th>
                                                <th class="text-center" style="white-space: nowrap;">Attendance Time</th>
                                                <th class="text-center">Location</th>
                                                <th class="text-center">Note</th>
                                                <th class="text-center">Image</th>
                                            </tr>
                                            @php
                                                $anyAttendance = false;
                                            @endphp
                                            
                                            @foreach ($employee_attendances as $employee)
                                                @if($employee->attendances->count() > 0)
                                                    @php $anyAttendance = true; @endphp
                                                    <tr>
                                                        <th class="text-center" colspan="4">Employee Name</th>
                                                        <th class="text-left pl-3" colspan="2">{{ $employee->name }}</th>
                                                    </tr>
                                                    @foreach ($employee->attendances as $key => $attendance)
                                                        <tr>
                                                            <td class="text-center">{{ $key+1 }}</td>
                                                            <td class="text-center">{{ date('d-m-Y',strtotime($attendance->punch_time)) }}</td>
                                                            <td class="text-center">{{ date('h:i:s A',strtotime($attendance->punch_time)) }}</td>
                                                            <td class="text-center">{{ $attendance->location_address }}</td>
                                                            <td>{{ $attendance->note }}</td>
                                                            <td class="text-center">
                                                                <a href="{{ asset($attendance->attendance_image) }}" target="_blank"><img src="{{ asset($attendance->attendance_image) }}" width="60"/></a>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @endif
                                            @endforeach

                                            @if(!$anyAttendance)
                                                <tr>
                                                    <td colspan="6" class="text-center">No Attendance Data Found</td>
                                                </tr>
                                            @endif
                                        </tbody>
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
