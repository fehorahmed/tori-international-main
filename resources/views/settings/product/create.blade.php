@extends('layouts.app')
@section('title',$pageTitle)
@section('content')
    <div class="row">
        <!-- left column -->
        <div class="col-md-12">
            <!-- jquery validation -->
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">{{ $pageTitle }} Information</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                <form enctype="multipart/form-data" action="{{ route('product.store') }}" class="form-horizontal" method="post">
                    @csrf
                    <div class="card-body">
                        <input type="hidden" value="1" name="product_type"/>
                        <div class="form-group row {{ $errors->has('name') ? 'has-error' :'' }}">
                            <label for="name" class="col-sm-2 col-form-label">Name <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <input type="text" value="{{ old('name') }}" name="name" class="form-control" id="name" placeholder="Enter Name">
                                @error('name')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row {{ $errors->has('brand') ? 'has-error' :'' }}">
                            <label for="brand" class="col-sm-2 col-form-label">Brand</label>
                            <div class="col-sm-10">
                                <select name="brand" id="brand" class="form-control select2">
                                    <option value="">Select Brand</option>
                                    @foreach($brands as $brand)
                                        <option {{ old('brand') == $brand->id ? 'selected' : '' }} value="{{ $brand->id }}">{{ $brand->name }}</option>
                                    @endforeach
                                </select>
                                @error('brand')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row {{ $errors->has('product_category') ? 'has-error' :'' }}">
                            <label for="product_category" class="col-sm-2 col-form-label">Product Category <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <select name="product_category" id="product_category" class="form-control select2">
                                    <option value="">Select Product Category</option>
                                    @foreach($categories as $category)
                                        <option {{ old('product_category') == $category->id ? 'selected' : '' }} value="{{ $category->id }}">{{ $category->name }} - {{ $category->code }}</option>
                                    @endforeach
                                </select>
                                @error('product_category')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row {{ $errors->has('sub_category') ? 'has-error' :'' }}">
                            <label for="sub_category" class="col-sm-2 col-form-label">Sub Category</label>
                            <div class="col-sm-10">
                                <select name="sub_category" id="sub_category" class="form-control select2">
                                    <option value="">Select Sub Category</option>
                                </select>
                                @error('sub_category')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row {{ $errors->has('unit') ? 'has-error' :'' }}">
                            <label for="unit" class="col-sm-2 col-form-label">Unit <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <select name="unit" id="unit" class="form-control select2">
                                    <option value="">Select Unit</option>
                                    @foreach($units as $unit)
                                        <option {{ old('unit') == $unit->id ? 'selected' : '' }} value="{{ $unit->id }}">{{ $unit->name }}</option>
                                    @endforeach
                                </select>
                                @error('unit')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row {{ $errors->has('image') ? 'has-error' :'' }}">
                            <label for="image" class="col-sm-2 col-form-label">Image</label>
                            <div class="col-sm-10">
                                <input type="file" value="{{ old('image') }}" name="image" class="form-control" id="image">
                                @error('image')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row {{ $errors->has('has_warranty') ? 'has-error' :'' }}">
                            <label class="col-sm-2 col-form-label">Has Warranty? <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <div class="icheck-success d-inline pull-right">
                                    <input type="radio" id="warranty_yes" name="has_warranty" value="1" {{ old('has_warranty') == '1' ? 'checked' : '' }}>
                                    <label for="warranty_yes">Yes</label>
                                </div>
                                <div class="icheck-danger d-inline pull-right ml-3">
                                    <input checked type="radio" id="warranty_no" name="has_warranty" value="0" {{ old('has_warranty', '0') == '0' ? 'checked' : '' }}>
                                    <label for="warranty_no">No</label>
                                </div>
                                @error('has_warranty')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="form-group row warranty-duration-group {{ $errors->has('warranty_duration') ? 'has-error' :'' }}" style="display: {{ old('has_warranty') == '1' ? 'flex' : 'none' }};">
                            <label for="warranty_duration" class="col-sm-2 col-form-label">Warranty Duration <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <div class="input-group">
                                    <input type="number" step="1" min="1" value="{{ old('warranty_duration') }}" name="warranty_duration" class="form-control" id="warranty_duration" placeholder="e.g. 6">
                                    <select name="warranty_type" class="form-control" style="max-width: 150px;">
                                        <option value="Month" {{ old('warranty_type') == 'Month' ? 'selected' : '' }}>Months</option>
                                        <option value="Year" {{ old('warranty_type') == 'Year' ? 'selected' : '' }}>Years</option>
                                    </select>
                                </div>
                                @error('warranty_duration')
                                <span class="help-block text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row {{ $errors->has('emi_interest') ? 'has-error' :'' }}">
                            <label for="emi_interest" class="col-sm-2 col-form-label">EMI Interest (%)</label>
                            <div class="col-sm-10">
                                <input type="number" step="any" min="0" value="{{ old('emi_interest', 0) }}" name="emi_interest" class="form-control" id="emi_interest" placeholder="Enter EMI Interest Percentage">
                                @error('emi_interest')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row {{ $errors->has('status') ? 'has-error' :'' }}">
                            <label class="col-sm-2 col-form-label">Status <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <div class="icheck-success d-inline pull-right">
                                    <input checked type="radio" id="active" name="status" value="1" {{ old('status') == '1' ? 'checked' : '' }}>
                                    <label for="active">
                                        Active
                                    </label>
                                </div>
                                <div class="icheck-danger d-inline pull-right">
                                    <input type="radio" id="inactive" name="status" value="0" {{ old('status') == '0' ? 'checked' : '' }}>
                                    <label for="inactive">
                                        Inactive
                                    </label>
                                </div>
                                @error('status')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer">
                        <button type="submit" class="btn btn-info bg-gradient-info btn-sm">Save</button>
                        <a href="{{ route('product.index') }}" class="btn btn-danger bg-gradient-danger btn-sm float-right">Cancel</a>
                    </div>
                    <!-- /.card-footer -->
                </form>
            </div>
            <!-- /.card -->
        </div>
        <!--/.col (left) -->
    </div>
@section('script')
    <script>
        $(function () {

            // Warranty toggle logic
            $('input[name="has_warranty"]').change(function() {
                if ($(this).val() == '1') {
                    $('.warranty-duration-group').show();
                    $('#warranty_duration').prop('required', true);
                } else {
                    $('.warranty-duration-group').hide();
                    $('#warranty_duration').prop('required', false);
                    $('#warranty_duration').val('');
                }
            });
            $('input[name="has_warranty"]:checked').trigger('change');

            $('#product_category').change(function () {
                var categoryId = $(this).val();
                $('#sub_category').html('<option value="">Select Sub Category</option>');

                if (categoryId != '') {
                    $.ajax({
                        method: "GET",
                        url: "{{ route('get_sub_categories') }}",
                        data: { categoryId: categoryId }
                    }).done(function( response ) {
                        $.each(response, function(index, item) {
                            var selected = item.id == "{{ old('sub_category') }}" ? 'selected' : '';
                            $('#sub_category').append('<option '+selected+' value="'+item.id+'">'+item.name+'</option>');
                        });
                    });
                }
            });

            $('#product_category').trigger('change');
        });
    </script>
@endsection
@endsection

