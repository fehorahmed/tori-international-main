@extends('layouts.app')
@section('title', 'Edit Asset')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card card-default">
            <div class="card-header">
                <h3 class="card-title">Update Asset Information</h3>
            </div>
            <!-- form start -->
            <form action="{{ route('fixed-assets.update', $fixedAsset->id) }}" class="form-horizontal" method="POST">
                @csrf
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> <strong>Note:</strong> You can only edit descriptive information here. Financial records (Cost, Depreciation) cannot be edited to maintain accounting integrity.
                    </div>

                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label>Asset Code</label>
                            <input type="text" class="form-control" value="{{ $fixedAsset->asset_code }}" readonly>
                        </div>

                        <div class="col-md-4 form-group">
                            <label>Asset Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $fixedAsset->name) }}" required>
                            @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-md-4 form-group">
                            <label>Location</label>
                            <input type="text" name="location" class="form-control @error('location') is-invalid @enderror" value="{{ old('location', $fixedAsset->location) }}">
                        </div>

                        <div class="col-md-4 form-group">
                            <label>Serial No</label>
                            <input type="text" name="serial_no" class="form-control @error('serial_no') is-invalid @enderror" value="{{ old('serial_no', $fixedAsset->serial_no) }}">
                        </div>

                        <div class="col-md-4 form-group">
                            <label>Vendor / Supplier</label>
                            <input type="text" name="vendor" class="form-control @error('vendor') is-invalid @enderror" value="{{ old('vendor', $fixedAsset->vendor) }}">
                        </div>

                        <div class="col-md-4 form-group">
                            <label>Warranty Expiry</label>
                            <input type="text" name="warranty_expiry" class="form-control @error('warranty_expiry') is-invalid @enderror business-date-picker" value="{{ old('warranty_expiry', $fixedAsset->warranty_expiry ? \Carbon\Carbon::parse($fixedAsset->warranty_expiry)->format('d-m-Y') : '') }}">
                        </div>

                        <div class="col-md-12 form-group">
                            <label>Notes</label>
                            <textarea name="notes" class="form-control" rows="2">{{ old('notes', $fixedAsset->notes) }}</textarea>
                        </div>
                    </div>
                </div>
                <!-- /.card-body -->
                <div class="card-footer">
                    <button type="submit" class="btn btn-info bg-gradient-info">Save</button>
                    <a href="{{ route('fixed-assets.index') }}" class="btn btn-danger bg-gradient-danger float-right">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
