@extends('layouts.app')
@section('title', 'Edit Salary Structure')
@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-default">
            <div class="card-header">
                <div class="card-title">Edit Salary Structure</div>
            </div>
            <form action="{{ route('salary-structures.update', $salaryStructure->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Structure Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ $salaryStructure->name }}" required>
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Basic Salary (%) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="basic_percent" class="form-control" value="{{ $salaryStructure->basic_percent }}" required>
                        </div>
                        <div class="col-md-3 form-group">
                            <label>House Rent (%) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="house_rent_percent" class="form-control" value="{{ $salaryStructure->house_rent_percent }}" required>
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Medical Allowance (%) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="medical_percent" class="form-control" value="{{ $salaryStructure->medical_percent }}" required>
                        </div>
                        <div class="col-md-3 form-group">
                            <label>TA/DA (Fixed Amount)</label>
                            <input type="number" step="0.01" name="ta_da_fixed" class="form-control" value="{{ $salaryStructure->ta_da_fixed }}">
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Provident Fund Deduction (%)</label>
                            <input type="number" step="0.01" name="pf_percent" class="form-control" value="{{ $salaryStructure->pf_percent }}">
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Tax Deduction (%)</label>
                            <input type="number" step="0.01" name="tax_percent" class="form-control" value="{{ $salaryStructure->tax_percent }}">
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Status</label>
                            <select name="status" class="form-control">
                                <option value="1" {{ $salaryStructure->status == 1 ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ $salaryStructure->status == 0 ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Update Structure</button>
                    <a href="{{ route('salary-structures.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
