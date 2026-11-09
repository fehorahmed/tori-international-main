@extends('layouts.app')
@section('title', 'Inter-Company Ledger')
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
        @media print{
            .table-bordered td, .table-bordered th {
                font-size: 20px !important;
            }
           td a{
                color: #000 !important;
                text-decoration: none !important;
            }
        }
    </style>
@endsection
@section('content')
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
                <form target="_blank" action="{{ route('report.inter_company_ledger') }}" method="GET">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="start_date">Start Date <span class="text-danger">*</span></label>
                                    <input required type="text" id="start_date" autocomplete="off"
                                           name="start_date" class="form-control date-picker"
                                           placeholder="Enter Start Date" value="{{ request()->get('start_date') ?? date('d-m-Y', strtotime(auth()->user()->business_date)) }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="end_date">End Date <span class="text-danger">*</span></label>
                                    <input required type="text" id="end_date" autocomplete="off"
                                           name="end_date" class="form-control date-picker"
                                           placeholder="Enter End Date" value="{{ request()->get('end_date') ?? date('d-m-Y', strtotime(auth()->user()->business_date)) }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="branch_id">Select Branch <span class="text-danger">*</span></label>
                                    <select name="branch_id" id="branch_id" class="form-control select2" required>
                                        <option value="">Select Branch</option>
                                        @foreach($branches as $branch)
                                        <option {{ request('branch_id') == $branch->id ? 'selected' : '' }} value="{{ $branch->id }}">{{ $branch->name }}</option>
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
                    </div>
                </form>
            </div>
            <!-- /.card -->
        </div>
        <!--/.col (left) -->
    </div>
@endsection
