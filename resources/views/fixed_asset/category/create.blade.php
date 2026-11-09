@extends('layouts.app')
@section('title', 'Create Category')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card card-default">
            <div class="card-header">
                <h3 class="card-title">New Category Info</h3>
            </div>
            <!-- /.card-header -->
            <!-- form start -->
            <form action="{{ route('fixed-assets.categories.store') }}" id="category-form" class="form-horizontal" method="POST">
                @csrf
                <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Category Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="e.g. Furniture & Fixture" required>
                                @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                <small class="text-muted">An Account Head with this exact name will be automatically created under Fixed Assets.</small>
                            </div>

                            <div class="col-md-6 form-group">
                                <label>Depreciation Method <span class="text-danger">*</span></label>
                                <select name="depreciation_method" id="depreciation_method" class="form-control select2" required>
                                    <option value="SLM" {{ old('depreciation_method') == 'SLM' ? 'selected' : '' }}>Straight Line Method (SLM)</option>
                                    <option value="WDV" {{ old('depreciation_method') == 'WDV' ? 'selected' : '' }}>Written Down Value (WDV)</option>
                                    <option value="none" {{ old('depreciation_method') == 'none' ? 'selected' : '' }}>No Depreciation</option>
                                </select>
                            </div>

                            <div class="col-md-6 form-group wdv-group" style="display:none;">
                                <label>Depreciation Rate (%) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="depreciation_rate" class="form-control @error('depreciation_rate') is-invalid @enderror" value="{{ old('depreciation_rate') }}" placeholder="e.g. 10.00">
                                @error('depreciation_rate') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                <small class="form-text text-muted mt-2"><strong>WDV (Written Down Value):</strong> Calculated as a fixed percentage on the <strong>Current Book Value</strong> (Cost - Accumulated Depreciation). The depreciation amount decreases over time.</small>
                            </div>

                            <div class="col-md-6 form-group slm-group">
                                <label>Useful Life (Years) <span class="text-danger">*</span></label>
                                <input type="number" name="useful_life_years" class="form-control @error('useful_life_years') is-invalid @enderror" value="{{ old('useful_life_years') }}" placeholder="e.g. 10">
                                @error('useful_life_years') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                <small class="form-text text-muted mt-2"><strong>SLM (Straight Line Method):</strong> Calculated evenly over the <strong>Useful Life</strong>. Formula: (Cost - Salvage Value) / Useful Life = Fixed Yearly Amount.</small>
                            </div>

                            <div class="col-md-12 form-group">
                                <label>Notes</label>
                                <textarea name="note" class="form-control" rows="2">{{ old('note') }}</textarea>
                            </div>
                        </div>
                </div>
                <!-- /.card-body -->
                <div class="card-footer">
                    <button type="submit" class="btn btn-info bg-gradient-info">Save</button>
                    <a href="{{ route('fixed-assets.categories.index') }}" class="btn btn-danger bg-gradient-danger float-right">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function () {
        function toggleDepreciationFields() {
            let method = $('#depreciation_method').val();
            if (method === 'SLM') {
                $('.slm-group').show();
                $('.wdv-group').hide();
            } else if (method === 'WDV') {
                $('.slm-group').hide();
                $('.wdv-group').show();
            } else {
                $('.slm-group').hide();
                $('.wdv-group').hide();
            }
        }

        $('#depreciation_method').change(toggleDepreciationFields);
        toggleDepreciationFields(); // on load

        // SweetAlert Confirmation on Submit
        $('#category-form').on('submit', function (e) {
            e.preventDefault();
            Swal.fire({
                title: 'Are you sure?',
                text: "An Account Ledger will be automatically generated with this Category name. This action cannot be easily undone.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, create it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            });
        });
    });
</script>
@endsection
