@extends('layouts.app')
@section('title', 'Employee Report')
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
        <div class="col-12 extra-remove">
            <div class="card card-default">
                <div class="card-header">
                    <div class="card-title">Data Filter</div>
                </div>
                <div class="card-body">
                    <form action="{{ route('branch.employee_report') }}" target="_blank">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="sister_concern_id">Branch</label>
                                    <select name="sister_concern_id" id="sister_concern_id" class="form-control select2">
                                        <option value="">All Branches</option>
                                        @foreach($sisterConcerns as $branch)
                                            <option {{ request('sister_concern_id') == $branch->id ? 'selected' : '' }} value="{{ $branch->id }}">{{ $branch->display_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="department_id">Department</label>
                                    <select name="department_id" id="department_id" class="form-control select2">
                                        <option value="">All Departments</option>
                                        @foreach($departments as $department)
                                            <option {{ request('department_id') == $department->id ? 'selected' : '' }} value="{{ $department->id }}">{{ $department->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="designation_id">Designation</label>
                                    <select name="designation_id" id="designation_id" class="form-control select2">
                                        <option value="">All Designations</option>
                                        @foreach($designations as $designation)
                                            <option {{ request('designation_id') == $designation->id ? 'selected' : '' }} value="{{ $designation->id }}">{{ $designation->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <input style="margin-top: -1px;" type="submit" name="search" class="btn btn-info bg-gradient-info form-control" value="Search">
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
        // No extra scripts needed here
    </script>
@endsection
