@extends('layouts.app')
@section('title', 'Create Employee Advance')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card card-default">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fa fa-plus mr-2"></i> New Employee Advance Request
                    </h5>
                    <div class="card-tools">
                        <a href="{{ route('employee-advances.index') }}" class="btn btn-sm btn-info">
                            <i class="fa fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
                
                <form action="{{ route('employee-advances.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label>Employee <span class="text-danger">*</span></label>
                            <select name="employee_id" class="form-control select2" required style="width: 100%;">
                                <option value="">Select Employee</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}" {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                                        {{ $emp->name }} ({{ $emp->id_no }})
                                    </option>
                                @endforeach
                            </select>
                            @error('employee_id') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label>Date <span class="text-danger">*</span></label>
                            <input type="text" name="date" class="form-control business-date-picker" value="{{ old('date', date('d-m-Y')) }}" required autocomplete="off" readonly>
                            @error('date') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label>Advance Amount <span class="text-danger">*</span></label>
                            <input type="number" name="amount" class="form-control" value="{{ old('amount') }}" required min="1" step="0.01" placeholder="e.g. 10000">
                            @error('amount') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label>Note</label>
                            <textarea name="note" class="form-control" rows="3" placeholder="Reason for advance">{{ old('note') }}</textarea>
                        </div>
                    </div>
                    
                    <div class="card-footer text-right">
                        <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Submit Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
