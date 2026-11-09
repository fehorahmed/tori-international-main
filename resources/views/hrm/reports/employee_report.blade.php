@extends('layouts.app')
@section('title','Employee Report')
@section('style')
    <style>
        .table-bordered thead td, .table-bordered thead th {
            vertical-align: middle;
            border: 1px solid #000!important;
            padding: 4px;
        }
        .table-bordered td, .table-bordered th {
            border: 1px solid #000000!important;
            vertical-align: middle;
            padding: 4px;
        }
        
        @page{
            margin: .3in .5in .5in .5in;
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
                    <form action="{{ route('report.employee') }}">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="employee_status_id">Employee Status</label>
                                    <select name="employee_status_id" id="employee_status_id" class="form-control select2">
                                        <option value="">All Statuses</option>
                                        @foreach($employeeStatuses as $empStatus)
                                            <option {{ request('employee_status_id') == $empStatus->id ? 'selected' : '' }} value="{{ $empStatus->id }}">{{ $empStatus->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <input style="margin-top: -1px;" type="submit" name="search"
                                           class="btn btn-primary bg-gradient-primary form-control" value="Search">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    <a role="button" onclick="getprint('printArea')" class="btn btn-primary bg-gradient-primary btn-sm"><i class="fa fa-print"></i> Print</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive" id="printArea">
                        @include('layouts.partial.__report_header')
                        <div class="row header-common-info">
                            <div class="col-12">
                                <h3 class="text-center" style="font-size: 25px; margin-top: 10px;"><u><b>Employee Report</b></u></h3>
                                @if(request('employee_status_id') != '')
                                    <h5 class="text-center" style="font-size: 16px">Employment Status: {{ $employeeStatuses->where('id', request('employee_status_id'))->first()->name ?? '' }}</h5>
                                @endif
                            </div>
                        </div>

                        <table class="table table-bordered mt-3">
                            <thead>
                            <tr>
                                <th class="text-center">S/L</th>
                                <th>Name</th>
                                <th>Mobile No</th>
                                <th>Department</th>
                                <th>Designation</th>
                                <th>Branch</th>
                                <th class="text-center">Employment Status</th>
                            </tr>
                            </thead>
                            <tbody>
                                @forelse($employees as $employee)
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td>{{ $employee->name }}</td>
                                        <td>{{ $employee->mobile_no }}</td>
                                        <td>{{ $employee->department->name ?? '' }}</td>
                                        <td>{{ $employee->designation->name ?? '' }}</td>
                                        <td>{{ $employee->sisterConcern->name ?? '' }}</td>
                                        <td class="text-center">
                                            <span class="badge badge-info">
                                                {{ $employee->employeeStatus->name ?? 'N/A' }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No employees found.</td>
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
            document.title = "Employee_Report";
            $(".extra-remove").remove();
            $(".footer-print-area").show();
            $('body').html($('#' + print).html());
            window.print();
            window.location.replace(APP_URL)
        }
    </script>
@endsection
