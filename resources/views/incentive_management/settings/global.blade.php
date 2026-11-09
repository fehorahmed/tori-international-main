@extends('layouts.app')
@section('title', $pageTitle)

@section('content')
<div class="row justify-content-center">
    <div class="col-md-12">
        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title">{{ $pageTitle }} (Apply to all products)</h3>
                <div class="card-tools">
                    <a href="{{ route('incentive-settings.index') }}" class="btn btn-sm btn-default"><i class="fa fa-list"></i> Settings List</a>
                    <a href="{{ route('incentive-settings.create') }}" class="btn btn-sm btn-info"><i class="fa fa-plus"></i> Category Wise Settings</a>
                </div>
            </div>
            
            <form action="{{ route('incentive-settings.global-store') }}" method="post">
                @csrf
                <div class="card-body">
                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label text-right">Branch <span class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            <select name="sister_concern_id" class="form-control select2" required>
                                <option value="">-- Select Branch --</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ old('sister_concern_id') == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->display_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('sister_concern_id') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <hr>

                    <div class="form-group row align-items-center">
                        <label class="col-sm-3 col-form-label text-right">SR Percentage <span class="text-danger">*</span></label>
                        <div class="col-sm-5">
                            <div class="input-group">
                                <input type="number" step="0.01" min="0" max="100" name="sr_percent" class="form-control" value="{{ old('sr_percent', 0) }}" required>
                                <div class="input-group-append"><span class="input-group-text">%</span></div>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="sr_active" name="sr_active" value="1" {{ old('sr_active', true) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="sr_active">Active</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row align-items-center">
                        <label class="col-sm-3 col-form-label text-right">BM Percentage <span class="text-danger">*</span></label>
                        <div class="col-sm-5">
                            <div class="input-group">
                                <input type="number" step="0.01" min="0" max="100" name="bm_percent" class="form-control" value="{{ old('bm_percent', 0) }}" required>
                                <div class="input-group-append"><span class="input-group-text">%</span></div>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="bm_active" name="bm_active" value="1" {{ old('bm_active', true) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="bm_active">Active</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row align-items-center">
                        <label class="col-sm-3 col-form-label text-right">AM Percentage <span class="text-danger">*</span></label>
                        <div class="col-sm-5">
                            <div class="input-group">
                                <input type="number" step="0.01" min="0" max="100" name="am_percent" class="form-control" value="{{ old('am_percent', 0) }}" required>
                                <div class="input-group-append"><span class="input-group-text">%</span></div>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="am_active" name="am_active" value="1" {{ old('am_active', true) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="am_active">Active</label>
                            </div>
                        </div>
                    </div>

                </div>
                
                <div class="card-footer text-center">
                    <button type="button" class="btn btn-warning btnSubmit"><i class="fa fa-save"></i> Apply to All Products</button>
                    <a href="{{ route('incentive-settings.index') }}" class="btn btn-default"><i class="fa fa-times"></i> Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(function() {
        $('.btnSubmit').click(function(e) {
            e.preventDefault();
            let form = $(this).closest('form');
            if (form[0].checkValidity()) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "This will update the percentage for ALL products in the selected branch. Do you want to continue?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, Apply to All!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                })
            } else {
                form[0].reportValidity();
            }
        });
    });
</script>
@endsection
