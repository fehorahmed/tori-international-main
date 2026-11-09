@extends('layouts.app')
@section('title','Filter Create')
@section('content')
    <div class="row">
        <!-- left column -->
        <div class="col-md-12">
            <!-- jquery validation -->
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">Filter Information</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                <form action="{{ route('filters.store') }}" class="form-horizontal" method="post">
                    @csrf
                    <div class="card-body">
                        <div class="form-group row {{ $errors->has('name') ? 'has-error' :'' }}">
                            <label for="name" class="col-sm-2 col-form-label">Filter Name <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <input type="text" value="{{ old('name') }}" name="name" class="form-control" id="name" placeholder="Enter Filter Name" required>
                                @error('name')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row {{ $errors->has('type') ? 'has-error' :'' }}">
                            <label for="type" class="col-sm-2 col-form-label">Filter Type <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <select name="type" id="type" class="form-control select2" required>
                                    <option value="checkbox" {{ old('type') == 'checkbox' ? 'selected' : '' }}>Checkbox</option>
                                    <option value="select" {{ old('type') == 'select' ? 'selected' : '' }}>Select</option>
                                    <option value="radio" {{ old('type') == 'radio' ? 'selected' : '' }}>Radio</option>
                                    <option value="text" {{ old('type') == 'text' ? 'selected' : '' }}>Text</option>
                                    <option value="number" {{ old('type') == 'number' ? 'selected' : '' }}>Number</option>
                                </select>
                                @error('type')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row options-group" style="display: {{ in_array(old('type'), ['select','radio','checkbox']) ? 'flex' : 'none' }};">
                            <label class="col-sm-2 col-form-label">Options</label>
                            <div class="col-sm-10">
                                <div id="options-wrapper">
                                    @if(old('options'))
                                        @foreach(old('options') as $option)
                                            <div class="option-input mb-2">
                                                <input type="text" name="options[]" class="form-control d-inline-block w-75" value="{{ $option }}" placeholder="Option Value">
                                                <button type="button" class="btn btn-danger remove-option"><i class="fa fa-times"></i></button>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                                <button type="button" class="btn btn-primary btn-sm mt-2" id="add-option"><i class="fa fa-plus"></i> Add Option</button>
                            </div>
                        </div>

                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer">
                        <button type="submit" class="btn btn-info bg-gradient-info btn-sm">Save</button>
                        <a href="{{ route('filters.index') }}" class="btn btn-danger bg-gradient-danger btn-sm float-right">Cancel</a>
                    </div>
                    <!-- /.card-footer -->
                </form>
            </div>
            <!-- /.card -->
        </div>
        <!--/.col (left) -->
    </div>
@endsection

@section('script')
    <script>
        $(function () {
            $('#type').change(function() {
                var type = $(this).val();
                if (['select', 'radio', 'checkbox'].includes(type)) {
                    $('.options-group').show();
                } else {
                    $('.options-group').hide();
                    $('#options-wrapper').empty();
                }
            });

            $('#add-option').click(function() {
                $('#options-wrapper').append(`
                    <div class="option-input mb-2">
                        <input type="text" name="options[]" class="form-control d-inline-block w-75" placeholder="Option Value">
                        <button type="button" class="btn btn-danger remove-option"><i class="fa fa-times"></i></button>
                    </div>
                `);
            });

            $(document).on('click', '.remove-option', function() {
                $(this).closest('.option-input').remove();
            });
        });
    </script>
@endsection
