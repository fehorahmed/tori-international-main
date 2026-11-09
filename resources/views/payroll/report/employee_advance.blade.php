@extends('layouts.app')
@section('title', 'Employee Advance Report')

@section('style')
    <style>
        .table-bordered td, .table-bordered th {
            border: 1px solid #000000 !important;
            padding: 5px;
        }

        .table thead th {
            vertical-align: middle;
            border-bottom: 2px solid #000000 !important;
        }

        .table-bordered td, .table-bordered th {
            font-size: 15px !important;
        }

        @media print {
            .table-bordered td, .table-bordered th {
                font-size: 15px !important;
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
                <form action="{{ route('report.employee-advance') }}" method="GET" target="_blank">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Employee</label>
                                <select name="employee_id" class="form-control select2">
                                    <option value="">All Employee</option>
                                    @foreach($employees as $emp)
                                        <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                                            {{ $emp->name }} ID No. {{ $emp->id_no }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <input type="submit" name="search" class="btn btn-info bg-gradient-info form-control" value="Search">
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
    var APP_URL = '{!! url()->full() !!}';

    function getprint(print) {
        $('.print-heading').css('display', 'block');
        $('.extra_column,.print-remove').remove();
        document.title = 'Employee Advance Report';
        $('body').html($('#' + print).html());
        window.print();
        window.location.replace(APP_URL)
    }
</script>
@endsection
