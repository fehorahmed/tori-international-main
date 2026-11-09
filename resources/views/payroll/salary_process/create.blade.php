@extends('layouts.app')
@section('title','Salary Process')
@section('style')
    <style>
        .table td, .table th {
            padding: 6px;
            vertical-align: middle;
        }
        .table td label {
            display: inline-block;
            margin-bottom: 0;
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
                    <h3 class="card-title">Salary Process Information</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                <!-- Filter Form (GET) -->
                <form action="{{ route('salary-processes.create') }}" method="GET">
                    <div class="card-body pb-0">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="employee_id">Employee</label>
                                    <select name="employee_id" id="employee_id" class="form-control select2">
                                        <option value="">All Employees</option>
                                        @foreach($employees as $employee)
                                            <option {{ request('employee_id') == $employee->id ? 'selected' : '' }} value="{{ $employee->id }}">{{ $employee->name }} - {{ $employee->id_no }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group  {{ $errors->has('year') ? 'has-error' :'' }}">
                                    <label for="year">Year <span class="text-danger">*</span></label>
                                    <select name="year" id="year" class="form-control select2" required>
                                        <option value="">Select Year</option>
                                        @for($i=2024;$i <= date('Y');$i++)
                                            <option {{ request('year',date('Y')) == $i ? 'selected' : '' }} value="{{ $i }}">{{ $i }}</option>
                                        @endfor
                                    </select>
                                    @error('year')
                                    <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group {{ $errors->has('month') ? 'has-error' :'' }}">
                                    <label for="month">Month <span class="text-danger">*</span></label>
                                    <select  name="month" id="month" class="form-control select2" required>
                                        <option value="">Select Month</option>
                                        @for ($monthIndex = 1; $monthIndex <= 12; $monthIndex++)
                                            <option {{ request('month',date('m')) == $monthIndex ? 'selected' : '' }} value="{{ $monthIndex }}">{{ numberToMonthName($monthIndex) }}</option>
                                        @endfor
                                    </select>
                                    @error('month')
                                    <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <button type="submit" class="btn btn-warning btn-block bg-gradient-warning"><i class="fa fa-search"></i> Search / Preview</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                @if(request('year') && request('month'))
                    <hr class="m-0">
                    <!-- Save Form (POST) -->
                    <form id="salary-form" enctype="multipart/form-data" action="{{ route('salary-processes.store') }}" class="form-horizontal" method="post">
                        @csrf
                        <input type="hidden" name="year" value="{{ request('year') }}">
                        <input type="hidden" name="month" value="{{ request('month') }}">
                        <input type="hidden" name="employee_id" value="{{ request('employee_id') }}">

                        <div class="card-body">
                            <h5 class="text-info font-weight-bold mb-3">Accounting Details</h5>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group {{ $errors->has('process_date') ? 'has-error' :'' }}">
                                        <label for="process_date">Process Date <span class="text-danger">*</span></label>
                                        <input type="text" value="{{ old('process_date',date('d-m-Y')) }}" id="process_date" name="process_date" class="form-control business-date-picker" required readonly>
                                    </div>
                                </div>
                            </div>
                            
                            <h5 class="text-info font-weight-bold mb-3 mt-3">Salary Grid</h5>
                            @include('payroll.salary_process.preview_table')

                        </div>
                        
                        <div class="card-footer">
                            <button type="submit" id="btn-submit" class="btn btn-info bg-gradient-info btn-sm">Confirm & Save Process</button>
                            <a href="{{ route('salary-processes.index') }}" class="btn btn-danger bg-gradient-danger btn-sm float-right">Cancel</a>
                        </div>
                    </form>
                @endif
            </div>
            <!-- /.card -->
        </div>
        <!--/.col (left) -->
    </div>
@endsection

@section('script')
    <script>
        $(function (){
            // Automatically collapse the sidebar and remove mini to hide it completely
            $('body').addClass('sidebar-collapse').removeClass('sidebar-mini');



            // Prevent form submit & Show Confirmation
            $('#salary-form').submit(function(e) {
                e.preventDefault();

                if ($('.row-employee:checked').length === 0 && !$('#employee_id').val()) {
                    // if we have checkboxes later, or just check if previews exist
                    if($('.net-pay-val').length === 0) {
                        Swal.fire('Warning', 'No employees to process!', 'warning');
                        return;
                    }
                }

                Swal.fire({
                    title: 'Are you sure?',
                    text: "Have you checked the preview carefully? This will process salary and generate accounting vouchers for all listed employees.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, process it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Unbind the event and submit natively
                        $(this).off('submit').submit();

                        // Show a loading spinner because the process takes time
                        Swal.fire({
                            title: 'Processing Payroll...',
                            html: 'Generating Vouchers. Please do not close the window.',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                    }
                });
            });
        })
    </script>
@endsection
