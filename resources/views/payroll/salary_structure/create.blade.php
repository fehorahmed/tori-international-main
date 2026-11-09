@extends('layouts.app')
@section('title', 'Add Salary Structure')
@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-default">
            <div class="card-header">
                <div class="card-title">Add New Salary Structure</div>
            </div>
            <form action="{{ route('salary-structures.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Structure Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Management Structure" required>
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Basic Salary (%) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="basic_percent" class="form-control" value="60" required>
                        </div>
                        <div class="col-md-3 form-group">
                            <label>House Rent (%) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="house_rent_percent" class="form-control" value="30" required>
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Medical Allowance (%) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="medical_percent" class="form-control" value="10" required>
                        </div>
                        <div class="col-md-3 form-group">
                            <label>TA/DA (Fixed Amount)</label>
                            <input type="number" step="0.01" name="ta_da_fixed" class="form-control" value="0">
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Provident Fund Deduction (%)</label>
                            <input type="number" step="0.01" name="pf_percent" class="form-control" value="0">
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Tax Deduction (%)</label>
                            <input type="number" step="0.01" name="tax_percent" class="form-control" value="0">
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Save Structure</button>
                    <a href="{{ route('salary-structures.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
