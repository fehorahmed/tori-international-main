@extends('layouts.app')
@section('title','Salary Sheet')
@section('style')
    <style>
        .table-bordered td, .table-bordered th {
            border: 1px solid #000000 !important;
            padding: 2px;
        }

        .table thead th {
            vertical-align: middle;
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

            .signature-area {
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
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    <div class="card-title">Data Filter</div>
                </div>
                <div class="card-header">
                    <form action="{{ route('report.salary-sheet') }}" target="_blank">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="employee">Employee <span class="text-danger">*</span></label>
                                    <select name="employee" id="employee" class="form-control select2">
                                        <option value="">All Employee</option>
                                        @foreach($employees as $employee)
                                            <option {{ request('employee') == $employee->id ? 'selected' : '' }} value="{{ $employee->id }}">{{ $employee->name }} ID No. {{ $employee->id_no }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="year">Year <span class="text-danger">*</span></label>
                                    <select required name="year" id="year" class="form-control select2">
                                        <option value="">Select Year</option>
                                        @for($i=2024;$i <= date('Y');$i++)
                                            <option
                                                {{ request('year', date('Y')) == $i ? 'selected' : '' }} value="{{ $i }}">{{ $i }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="month">Month <span class="text-danger">*</span></label>
                                    <select required name="month" id="month" class="form-control select2">
                                        <option value="">Select Month</option>
                                        @for ($monthIndex = 1; $monthIndex <= 12; $monthIndex++)
                                            <option
                                                {{ request('month', date('n')) == $monthIndex ? 'selected' : '' }} value="{{ $monthIndex }}">{{ numberToMonthName($monthIndex) }}</option>
                                        @endfor
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
                    </form>
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
            $('.extra_column,.print-remove').remove();
            document.title = '{{ $salaryMonth }}';
            $('body').html($('#' + print).html());
            window.print();
            window.location.replace(APP_URL)
        }
    </script>
@endsection
