@extends('layouts.app')
@section('title', 'Edit Income Head')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-outline card-default">
                <div class="card-header">
                    <h3 class="card-title">Income Head Information</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                <form action="{{ route('contractor_management.income-heads.update', $account_head->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="form-group row {{ $errors->has('name') ? 'has-error' :'' }}">
                            <label for="name" class="col-sm-2 col-form-label">Name <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <input type="text" value="{{ old('name', $account_head->name) }}" name="name" class="form-control" id="name" placeholder="Enter Income Head Name" required>
                                @error('name')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row d-none {{ $errors->has('opening_balance') ? 'has-error' :'' }}">
                            <label for="opening_balance" class="col-sm-2 col-form-label">Opening Balance <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <input type="number" step="any" min="0" value="{{ old('opening_balance', $account_head->getOpeningBalance(auth()->user()->activeConcernId())) }}" name="opening_balance" class="form-control" id="opening_balance" placeholder="Enter Opening Balance" required>
                                @error('opening_balance')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer">
                        <button type="submit" class="btn btn-info bg-gradient-info">Update</button>
                        <a href="{{ route('contractor_management.income-heads.index') }}" class="btn btn-default float-right">Cancel</a>
                    </div>
                    <!-- /.card-footer -->
                </form>
            </div>
            <!-- /.card -->
        </div>
    </div>
@endsection
