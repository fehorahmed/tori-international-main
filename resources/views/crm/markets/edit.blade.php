@extends('layouts.app')
@section('title','Edit Market')
@section('content')
    <div class="row">
        <div class="col-md-6 mx-auto">
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">Edit Market</h3>
                    <a href="{{ route('markets.index') }}" class="btn btn-dark btn-sm float-right"><i class="fa fa-arrow-left"></i> Back</a>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <form action="{{ route('markets.update', $market->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="form-group">
                            <label for="district_id">District <span class="text-danger">*</span></label>
                            <select name="district_id" id="district_id" class="form-control select2">
                                <option value="">Select District</option>
                                @foreach($districts as $district)
                                    <option {{ (isset($market->upazila) && $market->upazila->district_id == $district->id) ? 'selected' : '' }} value="{{ $district->id }}">{{ $district->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group {{ $errors->has('upazila_id') ? 'has-error' :'' }}">
                            <label for="upazila_id">Upazila / Thana <span class="text-danger">*</span></label>
                            <select name="upazila_id" id="upazila_id" class="form-control select2">
                                <option value="">Select Upazila</option>
                                @foreach($upazilas as $upazila)
                                    <option {{ old('upazila_id', $market->upazila_id) == $upazila->id ? 'selected' : '' }} value="{{ $upazila->id }}">{{ $upazila->name }}</option>
                                @endforeach
                            </select>
                            @error('upazila_id')
                            <span class="help-block text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group {{ $errors->has('name') ? 'has-error' :'' }}">
                            <label for="name">Market Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" value="{{ old('name', $market->name) }}" class="form-control" placeholder="E.g. Mirpur 10 Market">
                            @error('name')
                            <span class="help-block text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        

                        <div class="form-group {{ $errors->has('status') ? 'has-error' :'' }}">
                            <label for="status">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-control select2">
                                <option {{ $market->status == 1 ? 'selected' : '' }} value="1">Active</option>
                                <option {{ $market->status == 0 ? 'selected' : '' }} value="0">Inactive</option>
                            </select>
                            @error('status')
                            <span class="help-block text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group text-right">
                            <button type="submit" class="btn btn-info bg-gradient-info">Update Market <i class="fa fa-save"></i></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
<script>
    $(document).ready(function() {
        $('#district_id').on('change', function() {
            var districtId = $(this).val();
            var $upazilaSelect = $('#upazila_id');
            var selectedUpazilaId = "{{ old('upazila_id', $market->upazila_id) }}";
            
            $upazilaSelect.empty().append('<option value="">Select Upazila</option>');
            
            if (districtId) {
                $.ajax({
                    url: "{{ route('get_upazilas') }}",
                    type: "GET",
                    data: { districtId: districtId },
                    success: function(response) {
                        $.each(response, function(index, upazila) {
                            var selected = (selectedUpazilaId == upazila.id) ? 'selected' : '';
                            $upazilaSelect.append('<option value="'+upazila.id+'" '+selected+'>'+upazila.name+'</option>');
                        });
                        $upazilaSelect.trigger('change');
                    }
                });
            }
        });

        // Trigger change on load if district is already selected
        if ($('#district_id').val()) {
            $('#district_id').trigger('change');
        }
    });
</script>
@endsection
