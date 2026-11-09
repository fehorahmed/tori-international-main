@extends('layouts.app')
@section('title','Account Group Create')
@section('content')
    <div class="row">
        <!-- left column -->
        <div class="col-md-12">
            <!-- jquery validation -->
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">Account Group Information</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                <form enctype="multipart/form-data" action="{{ route('account-group.store') }}" class="form-horizontal" method="post">
                    @csrf
                    <div class="card-body">
                        <div class="form-group row {{ $errors->has('account_group') ? 'has-error' :'' }}">
                            <label for="account_group" class="col-sm-2 col-form-label">Account Group</label>
                            <div class="col-sm-10">
                                <select name="account_group" class="form-control select2" id="account_group">
                                    <option value="">Select Account Group</option>
                                    @foreach($accountGroups as $accountGroup)
                                        <option {{ old('account_group') == $accountGroup->id ? 'selected' : '' }} value="{{ $accountGroup->id }}">{{ $accountGroup->name }}</option>
                                    @endforeach
                                </select>
                                @error('account_group')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row {{ $errors->has('name') ? 'has-error' :'' }}">
                            <label for="name" class="col-sm-2 col-form-label">Name <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <input type="text" value="{{ old('name') }}" name="name" class="form-control" id="name" placeholder="Enter Name">
                                @error('name')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row {{ $errors->has('note_no') ? 'has-error' :'' }}">
                            <label for="note_no" class="col-sm-2 col-form-label">Note No.</label>
                            <div class="col-sm-10">
                                <input type="number" step="any" value="{{ old('note_no') }}" name="note_no" class="form-control" id="note_no" placeholder="Enter Note No.">
                                @error('note_no')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer">
                        <button type="submit" class="btn btn-info bg-gradient-info btn-sm">Save</button>
                        <a href="{{ route('account-group.index') }}" class="btn btn-danger bg-gradient-danger btn-sm float-right">Cancel</a>
                    </div>
                    <!-- /.card-footer -->
                </form>
            </div>
            <!-- /.card -->
        </div>
        <!--/.col (left) -->
    </div>
@endsection

