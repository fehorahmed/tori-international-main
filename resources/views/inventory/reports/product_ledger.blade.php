@extends('layouts.app')
@section('title','Product Ledger')
@section('style')
    <style>
        .table-bordered thead td, .table-bordered thead th {
            vertical-align: middle;
            border: 1px solid #000!important;
            padding: 2px;
        }
        .table-bordered td, .table-bordered th {
            border: 1px solid #000000!important;
            vertical-align: middle;
        }
        @page{
            margin: .3in .5in .5in .5in;
        }
    </style>
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    <div class="card-title">Filter</div>
                </div>
                <div class="card-body">
                    <form action="{{ route('report.product_ledger') }}" target="_blank">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group {{ $errors->has('category') ? 'has-error' :'' }}">
                                    <label for="category">Category <span class="text-danger">*</span></label>
                                    <select name="category" id="category" class="form-control select2" required>
                                        <option value="">Select Category</option>
                                        @foreach($categories as $category)
                                            <option {{ request('category') == $category->id ? 'selected' : '' }} value="{{ $category->id }}">{{ $category->name }} - {{ $category->code }}</option>
                                        @endforeach
                                    </select>
                                    @error('category')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group {{ $errors->has('product') ? 'has-error' :'' }}">
                                    <label for="product">Product <span class="text-danger">*</span></label>
                                    <select name="product" id="product" class="form-control select2" required>
                                        <option value="">Select Product</option>
                                        @foreach($products as $product)
                                            <option {{ request('product') == $product->id ? 'selected' : '' }} value="{{ $product->id }}">{{ $product->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('product')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group {{ $errors->has('start_date') ? 'has-error' :'' }}">
                                    <label for="start_date">Start Date <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control date-picker" name="start_date" id="start_date" value="{{ request('start_date', date('01-m-Y')) }}" autocomplete="off" required>
                                    @error('start_date')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group {{ $errors->has('end_date') ? 'has-error' :'' }}">
                                    <label for="end_date">End Date <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control date-picker" name="end_date" id="end_date" value="{{ request('end_date', date('t-m-Y')) }}" autocomplete="off" required>
                                    @error('end_date')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <input style="margin-top: -1px;" type="submit" name="search" class="btn btn-info bg-gradient-info form-control" value="Search">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection
@section('script')
    <script>
        $(function () {
            var selectedProduct = '{{ request('product') }}';

            $('#category').change(function () {
                let categoryId = $(this).val();
                $('#product').html('<option value="">Select Product</option>');
                
                if (categoryId) {
                    $.ajax({
                        method: "GET",
                        url: "{{ route('product.by_category') }}",
                        data: { category_id: categoryId }
                    }).done(function (data) {
                        $.each(data, function (index, item) {
                            $('#product').append('<option value="' + item.id + '">' + item.name + '</option>');
                        });
                        if (selectedProduct) {
                            $('#product').val(selectedProduct).trigger('change');
                            selectedProduct = '';
                        }
                    });
                }
            });

            if ($('#category').val() !== '') {
                $('#category').trigger('change');
            }
        });
    </script>
@endsection
