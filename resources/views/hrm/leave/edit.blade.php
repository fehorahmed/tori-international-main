@extends('layouts.app')
@section('title','Leave Edit')
@section('content')
    <div class="row">
        <!-- left column -->
        <div class="col-md-12">
            <!-- jquery validation -->
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">Leave Information</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                <form enctype="multipart/form-data" action="{{ route('leave.update',['leave'=>$leave->id]) }}" class="form-horizontal" method="post">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="form-group row {{ $errors->has('employee') ? 'has-error' :'' }}">
                            <label for="employee" class="col-sm-2 col-form-label">Employee <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <select name="employee" class="form-control select2" id="employee">
                                    <option value="">Select Employee</option>
                                    @foreach($employees as $employee)
                                        <option {{ old('employee',$leave->employee_id) == $employee->id ? 'selected' : '' }} value="{{ $employee->id }}">{{ $employee->name }}</option>
                                    @endforeach
                                </select>
                                @error('employee')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row {{ $errors->has('leave_type') ? 'has-error' :'' }}">
                            <label for="leave_type" class="col-sm-2 col-form-label">Leave Type <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <select name="leave_type" class="form-control select2" id="leave_type">
                                    <option value="">Select Leave Type</option>
                                    @foreach($leaveTypes as $leaveType)
                                        <option {{ old('leave_type',$leave->leave_type_id) == $leaveType->id ? 'selected' : '' }} value="{{ $leaveType->id }}">{{ $leaveType->name }}</option>
                                    @endforeach
                                </select>
                                @error('leave_type')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row {{ $errors->has('start_date') ? 'has-error' :'' }}">
                            <label for="start_date" class="col-sm-2 col-form-label">Start date <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <input type="text" autocomplete="off" value="{{ old('start_date',\Carbon\Carbon::parse($leave->start_date)->format('d-m-Y')) }}" name="start_date" class="form-control date-picker" id="start_date" placeholder="Enter Start Date">
                                @error('start_date')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row {{ $errors->has('end_date') ? 'has-error' :'' }}">
                            <label for="end_date" class="col-sm-2 col-form-label">End date <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <input type="text" autocomplete="off" value="{{ old('end_date',\Carbon\Carbon::parse($leave->end_date)->format('d-m-Y')) }}" name="end_date" class="form-control date-picker" id="end_date" placeholder="Enter End Date">
                                @error('end_date')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row {{ $errors->has('reason') ? 'has-error' :'' }}">
                            <label for="reason" class="col-sm-2 col-form-label">Reason</label>
                            <div class="col-sm-10">
                                <input type="text"  value="{{ old('reason',$leave->reason) }}" name="reason" class="form-control" id="reason" placeholder="Enter Reason">
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
                                    <option {{ old('status',$leave->status) ==  'pending' ? 'selected' : '' }} value="pending">Pending</option>
                                    <option {{ old('status',$leave->status) == 'approved' ? 'selected' : '' }} value="approved">Approved</option>
                                    <option {{ old('status',$leave->status) == 'rejected' ? 'selected' : '' }} value="rejected">Rejected</option>
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
                        <a href="{{ route('leave.index') }}" class="btn btn-danger bg-gradient-danger btn-sm float-right">Cancel</a>
                    </div>
                    <!-- /.card-footer -->
                </form>
            </div>
            <!-- /.card -->
        </div>
        <!--/.col (left) -->
    </div>
@endsection
