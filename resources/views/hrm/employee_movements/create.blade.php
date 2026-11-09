@extends('layouts.app')
@section('title','Employee Movement Create')
@section('content')
    <div class="row">
        <!-- left column -->
        <div class="col-md-12">
            <!-- jquery validation -->
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">Employee Movement Information</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                <form enctype="multipart/form-data" action="{{ route('employee-movements.store') }}" class="form-horizontal" method="post">
                    @csrf
                    <div class="card-body">
                        <div class="form-group row {{ $errors->has('employee') ? 'has-error' :'' }}">
                            <label for="employee" class="col-sm-2 col-form-label">Employee <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <select name="employee" class="form-control select2" id="employee">
                                    <option value="">Select Employee</option>
                                    @foreach($employees as $employee)
                                    <option {{ old('employee') == $employee->id ? 'selected' : '' }} value="{{ $employee->id }}">{{ $employee->name }}</option>
                                    @endforeach
                                </select>
                                @error('employee')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row {{ $errors->has('from_location') ? 'has-error' :'' }}">
                            <label for="from_location" class="col-sm-2 col-form-label">From Location <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <input type="text" value="{{ old('from_location') }}" name="from_location" class="form-control" id="from_location" placeholder="Enter From Location">
                                @error('from_location')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row {{ $errors->has('to_location') ? 'has-error' :'' }}">
                            <label for="to_location" class="col-sm-2 col-form-label">To Location <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <input type="text" value="{{ old('to_location') }}" name="to_location" class="form-control" id="to_location" placeholder="Enter To Location">
                                @error('to_location')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row {{ $errors->has('start_date') ? 'has-error' :'' }}">
                            <label for="start_date" class="col-sm-2 col-form-label">Start date <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <input type="text" autocomplete="off" value="{{ old('start_date') }}" name="start_date" class="form-control date-picker" id="start_date" placeholder="Enter Start Date">
                                @error('start_date')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row {{ $errors->has('end_date') ? 'has-error' :'' }}">
                            <label for="end_date" class="col-sm-2 col-form-label">End date <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <input type="text" autocomplete="off" value="{{ old('end_date') }}" name="end_date" class="form-control date-picker" id="end_date" placeholder="Enter End Date">
                                @error('end_date')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row {{ $errors->has('reason') ? 'has-error' :'' }}">
                            <label for="reason" class="col-sm-2 col-form-label">Reason</label>
                            <div class="col-sm-10">
                                <input type="text"  value="{{ old('reason') }}" name="reason" class="form-control" id="reason" placeholder="Enter Reason">
                                @error('reason')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row {{ $errors->has('status') ? 'has-error' :'' }}">
                            <label for="status" class="col-sm-2 col-form-label">Status <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <select name="status" class="form-control select2" id="status">
                                    <option value="">Select Status</option>
                                    <option {{ old('status') ==  'pending' ? 'selected' : '' }} value="pending">Pending</option>
                                    <option {{ old('status') == 'approved' ? 'selected' : '' }} value="approved">Approved</option>
                                    <option {{ old('status') == 'rejected' ? 'selected' : '' }} value="rejected">Rejected</option>
                                </select>
                                @error('status')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer">
                        <button type="submit" class="btn btn-info bg-gradient-info btn-sm">Save</button>
                        <a href="{{ route('employee-movements.index') }}" class="btn btn-danger bg-gradient-danger btn-sm float-right">Cancel</a>
                    </div>
                    <!-- /.card-footer -->
                </form>
            </div>
            <!-- /.card -->
        </div>
        <!--/.col (left) -->
    </div>
@endsection

