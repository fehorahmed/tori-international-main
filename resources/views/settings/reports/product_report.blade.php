@extends('layouts.app')
@section('title','Product Report')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    <div class="card-title">Filter</div>
                </div>
                <div class="card-body">
                    <form action="{{ route('report.product') }}" target="_blank">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="category">Category</label>
                                    <select name="category" id="category" class="form-control select2">
                                        <option value="">All Categories</option>
                                        @foreach($categories as $category)
                                            <option {{ request('category') == $category->id ? 'selected' : '' }} value="{{ $category->id }}">{{ $category->name }} - {{ $category->code }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="sub_category">Sub-Category</label>
                                    <select name="sub_category" id="sub_category" class="form-control select2">
                                        <option value="">All Sub-Categories</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="brand">Brand</label>
                                    <select name="brand" id="brand" class="form-control select2">
                                        <option value="">All Brands</option>
                                        @foreach($brands as $brand)
                                            <option {{ request('brand') == $brand->id ? 'selected' : '' }} value="{{ $brand->id }}">{{ $brand->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="status">Status</label>
                                    <select name="status" id="status" class="form-control select2">
                                        <option value="">All Statuses</option>
                                        <option {{ request('status') === '1' ? 'selected' : '' }} value="1">Active</option>
                                        <option {{ request('status') === '0' ? 'selected' : '' }} value="0">Inactive</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <input style="margin-top: -1px;" type="submit" name="search"
                                           class="btn btn-info bg-gradient-info form-control" value="Search">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(function () {
            var subCategorySelected = '{{ old('sub_category', request('sub_category')) }}';
            $("#category").change(function () {
                let categoryId = $(this).val();
                $('#sub_category').html('<option value="">All Sub-Categories</option>');
                if (categoryId != '') {
                    $.ajax({
                        method: "GET",
                        url: "{{ route('get_sub_categories') }}",
                        data: {categoryId: categoryId}
                    }).done(function (data) {
                        $.each(data, function (index, item) {
                            if (subCategorySelected == item.id)
                                $('#sub_category').append('<option value="' + item.id + '" selected>' + item.name + '</option>');
                            else
                                $('#sub_category').append('<option value="' + item.id + '">' + item.name + '</option>');
                        });
                    });
                }
            })
            $("#category").trigger('change');
        });
    </script>
@endsection
