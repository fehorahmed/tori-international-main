@extends('layouts.app')
@section('title', 'Edit ' . $config['title'])

@section('content')

    @include('settings.locations._tabs')

    <div class="row">
        <div class="col-md-12">
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">Edit {{ $config['title'] }} Information</h3>
                </div>

                <form action="{{ route('locations.update', [$type, $item->id]) }}" class="form-horizontal" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">

                        @foreach($config['parents'] as $parent)
                            <div class="form-group row {{ $errors->has($parent['key']) ? 'has-error' : '' }}">
                                <label for="{{ $parent['key'] }}" class="col-sm-2 col-form-label">{{ $parent['title'] }} <span
                                        class="text-danger">*</span></label>
                                <div class="col-sm-10">
                                    <select name="{{ $parent['key'] }}" id="{{ $parent['key'] }}" class="form-control select2"
                                        required>
                                        <option value="">Select {{ $parent['title'] }}</option>
                                        @foreach($parentsData[$parent['key']] as $pData)
                                            <option value="{{ $pData->id }}" {{ old($parent['key'], $item->{$parent['key']}) == $pData->id ? 'selected' : '' }}>{{ $pData->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error($parent['key'])
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        @endforeach

                        <div class="form-group row {{ $errors->has('name') ? 'has-error' : '' }}">
                            <label for="name" class="col-sm-2 col-form-label">Name (English) <span
                                    class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <input type="text" name="name" id="name" class="form-control" required
                                    value="{{ old('name', $item->name) }}" placeholder="Enter {{ $config['title'] }} Name">
                                @error('name')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row {{ $errors->has('bn_name') ? 'has-error' : '' }}">
                            <label for="bn_name" class="col-sm-2 col-form-label">Name (Bangla)</label>
                            <div class="col-sm-10">
                                <input type="text" name="bn_name" id="bn_name" class="form-control"
                                    value="{{ old('bn_name', $item->bn_name) }}"
                                    placeholder="Enter Name in Bangla (Optional)">
                                @error('bn_name')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        @if($type === 'villages')
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Status</label>
                                <div class="col-sm-10">
                                    <div class="icheck-success d-inline">
                                        <input type="radio" id="active" name="status" value="1" {{ $item->status == 1 ? 'checked' : '' }}>
                                        <label for="active">Active</label>
                                    </div>
                                    <div class="icheck-danger d-inline ml-3">
                                        <input type="radio" id="inactive" name="status" value="0" {{ $item->status == 0 ? 'checked' : '' }}>
                                        <label for="inactive">Inactive</label>
                                    </div>
                                </div>
                            </div>
                        @endif

                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-info bg-gradient-info">Update</button>
                        <a href="{{ route('locations.index', $type) }}"
                            class="btn btn-danger bg-gradient-danger float-right">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script>
    $(function() {
        var oldUnionId = '{{ old('union_id', $item->union_id ?? '') }}';
        
        function loadUnions(upazilaId, selectedUnionId) {
            var unionSelect = $('#union_id');
            if(unionSelect.length > 0 && upazilaId) {
                unionSelect.html('<option value="">Select Union</option>');
                $.ajax({
                    url: '{{ route('locations.unions_by_upazila') }}',
                    type: 'GET',
                    data: { upazila_id: upazilaId },
                    success: function(response) {
                        $.each(response, function(key, value) {
                            var selected = (value.id == selectedUnionId) ? 'selected' : '';
                            unionSelect.append('<option value="'+ value.id +'" '+ selected +'>'+ value.name +'</option>');
                        });
                        unionSelect.trigger('change');
                    }
                });
            } else if (unionSelect.length > 0) {
                unionSelect.html('<option value="">Select Union</option>');
                unionSelect.trigger('change');
            }
        }

        // On page load
        if ($('#upazila_id').val()) {
            loadUnions($('#upazila_id').val(), oldUnionId);
        }

        // On change
        $('#upazila_id').change(function() {
            loadUnions($(this).val(), '');
        });
    });
</script>
@endsection