@extends('layouts.app')
@section('title','Shift Create')
@section('content')
    <div class="row">
        <!-- left column -->
        <div class="col-md-12">
            <!-- jquery validation -->
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">Shift Information</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                <form enctype="multipart/form-data" action="{{ route('shifts.store') }}" class="form-horizontal" method="post">
                    @csrf
                    <div class="card-body">
                        <div class="form-group row {{ $errors->has('name') ? 'has-error' :'' }}">
                            <label for="name" class="col-sm-2 col-form-label">Name <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <input type="text" value="{{ old('name') }}" name="name" class="form-control" id="name" placeholder="Enter Name">
                                @error('name')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row {{ $errors->has('start_time') ? 'has-error' :'' }}">
                            <label for="start_time" class="col-sm-2 col-form-label">Start Time <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <input type="time" autocomplete="off" value="{{ old('start_time') }}" name="start_time" class="form-control" id="start_time" placeholder="Enter Start Time">
                                @error('start_time')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row {{ $errors->has('end_time') ? 'has-error' :'' }}">
                            <label for="end_time" class="col-sm-2 col-form-label">End Time <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <input type="time" autocomplete="off" value="{{ old('end_time') }}" name="end_time" class="form-control" id="end_time" placeholder="Enter End Time">
                                @error('end_time')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer">
                        <button type="submit" class="btn btn-info bg-gradient-info btn-sm">Save</button>
                        <a href="{{ route('holidays.index') }}" class="btn btn-danger bg-gradient-danger btn-sm float-right">Cancel</a>
                    </div>
                    <!-- /.card-footer -->
                </form>
            </div>
            <!-- /.card -->
        </div>
        <!--/.col (left) -->
    </div>
@endsection

