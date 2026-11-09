@extends('layouts.app')
@section('title')
    {{ $accountHeadTitle }} Create
@endsection
@section('content')
    <div class="row">
        <!-- left column -->
        <div class="col-md-12">
            <!-- jquery validation -->
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">{{ $accountHeadTitle }} Information</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                <form enctype="multipart/form-data" action="{{ route('account-head.store',['payment_mode'=>$paymentMode]) }}" class="form-horizontal" method="post">
                    @csrf
                    <div class="card-body">
                        <div class="form-group row {{ $errors->has('account_group') ? 'has-error' :'' }}">
                            <label for="account_group" class="col-sm-2 col-form-label">Account Group <span class="text-danger">*</span></label>
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
                        @if($paymentMode == 1)
                        <div class="form-group row {{ $errors->has('sister_concern_id') ? 'has-error' :'' }}">
                            <label for="sister_concern_id" class="col-sm-2 col-form-label">Branch Visibility</label>
                            <div class="col-sm-10">
                                <select name="sister_concern_id" id="sister_concern_id" class="form-control select2">
                                    <option value="">Global (All Branches)</option>
                                    @foreach($sister_concerns as $concern)
                                    <option value="{{ $concern->id }}" {{ old('sister_concern_id') == $concern->id ? 'selected' : '' }}>{{ $concern->name }}</option>
                                    @endforeach
                                </select>
                                <span class="text-muted"><small>Leave global for general accounts like Salary, Rent. Select a branch for specific Bank/Cash accounts.</small></span>
                                @error('sister_concern_id')
                                <span class="help-block text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        @endif
                        <div class="form-group row {{ $errors->has('name') ? 'has-error' :'' }}">
                            <label for="name" class="col-sm-2 col-form-label">Name <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <input type="text" value="{{ old('name') }}" name="name" class="form-control" id="name" placeholder="Enter Name">
                                @error('name')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        @if($paymentMode == 1)
                        <div class="form-group row {{ $errors->has('payment_mode_type') ? 'has-error' :'' }}">
                            <label for="payment_mode_type" class="col-sm-2 col-form-label">Payment Mode Type <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <select name="payment_mode_type" id="payment_mode_type" class="form-control select2">
                                    <option value="">Select Payment Mode Type</option>
                                    <option {{ old('payment_mode_type') == 1 ? 'selected' : '' }} value="1">Bank</option>
                                    <option {{ old('payment_mode_type') == 2 ? 'selected' : '' }} value="2">Cash</option>
                                    <option {{ old('payment_mode_type') == 3 ? 'selected' : '' }} value="3">Mobile Banking</option>
                                </select>
                                @error('payment_mode_type')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                            <div class="form-group row {{ $errors->has('bank_commission_percent') ? 'has-error' :'' }}">
                                <label for="bank_commission_percent" class="col-sm-2 col-form-label">Bank Commission(%) <span class="text-danger">*</span></label>
                                <div class="col-sm-10">
                                    <input type="number" step="any" name="bank_commission_percent" value="{{ old('bank_commission_percent',0) }}" class="form-control" id="bank_commission_percent" placeholder="Enter Bank Commission(%)">
                                    @error('bank_commission_percent')
                                    <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        @endif
                        <div class="form-group row {{ $errors->has('opening_balance') ? 'has-error' :'' }}">
                            <label for="opening_balance" class="col-sm-2 col-form-label">Opening Balance</label>
                            <div class="col-sm-10">
                                <input type="number" step="any" name="opening_balance" value="{{ old('opening_balance',0) }}" class="form-control" id="opening_balance" placeholder="Enter Opening Balance">
                                @error('opening_balance')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer">
                        <button type="submit" class="btn btn-info bg-gradient-info btn-sm">Save</button>
                        <a href="{{ route('account-head.index',['payment_mode'=>$paymentMode]) }}" class="btn btn-danger bg-gradient-danger btn-sm float-right">Cancel</a>
                    </div>
                    <!-- /.card-footer -->
                </form>
            </div>
            <!-- /.card -->
        </div>
        <!--/.col (left) -->
    </div>
@endsection

