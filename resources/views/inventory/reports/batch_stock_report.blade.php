@extends('layouts.app')
@section('title','Batch-wise Stock Report')
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
                    <form action="{{ route('report.batch_stock') }}" target="_blank">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="warehouse">Warehouse</label>
                                    <select name="warehouse" id="warehouse" class="form-control select2">
                                        @foreach($warehouses as $warehouse)
                                            <option {{ request('warehouse', $warehouses->first()->id ?? '') == $warehouse->id ? 'selected' : '' }} value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="category">Category</label>
                                    <select name="category" id="category" class="form-control select2">
                                        <option value="">All Category</option>
                                        @foreach($categories as $category)
                                            <option {{ request('category') == $category->id ? 'selected' : '' }} value="{{ $category->id }}">{{ $category->name }} - {{ $category->code }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="product">Product</label>
                                    <select name="product" id="product" class="form-control select2">
                                        <option value="">All Product</option>
                                        @foreach($products as $product)
                                            <option {{ request('product') == $product->id ? 'selected' : '' }} value="{{ $product->id }}">{{ $product->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="batch_no">Batch No</label>
                                    <input type="text" name="batch_no" id="batch_no" class="form-control" placeholder="e.g. 1" value="{{ request('batch_no') }}">
                                </div>
                            </div>
                            <div class="col-md-1">
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
            var selectedProduct = '{{ request('product') }}';

            $('#category').change(function () {
                let categoryId = $(this).val();
                $('#product').html('<option value="">All Product</option>');
                
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
            });

            if ($('#category').val() !== '') {
                $('#category').trigger('change');
            }
        });
    </script>
@endsection
