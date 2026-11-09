@extends('layouts.app')
@section('title','Price Book')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fa fa-search mr-2"></i> Filter Price Book</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="category">Category</label>
                                <select name="category" id="category" class="form-control select2">
                                    <option value="">All Categories</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }} - {{ $category->code }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3" style="margin-top: 31px;">
                            <button type="button" id="searchBtn" class="btn btn-primary"><i class="fa fa-plus"></i> Add to List</button>
                        </div>
                    </div>
                </div>
            </div>

            <form action="{{ route('report.price_book.print') }}" method="POST" target="_blank">
                @csrf
                <div class="card card-default mt-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="fa fa-list mr-2"></i> Selected Products List</h5>
                        <div class="card-tools">
                            <button type="submit" class="btn btn-success btn-sm"><i class="fa fa-print"></i> Print Selected</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="selectedProductsTable">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width: 50px;">
                                            <input type="checkbox" id="selectAll" checked>
                                        </th>
                                        <th class="text-center">Image</th>
                                        <th>Product Code</th>
                                        <th>Product Name</th>
                                        <th>Category</th>
                                        <th class="text-right">DP</th>
                                        <th class="text-right">MRP</th>
                                        <th>Program</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="resultContainer">
                                    <!-- Rows go here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        $('.select2').select2({ theme: 'bootstrap4' });

        $('#searchBtn').click(function() {
            var category = $('#category').val();
            var btn = $(this);
            btn.html('<i class="fa fa-spinner fa-spin"></i> Adding...');
            btn.prop('disabled', true);

            $.ajax({
                url: "{{ route('report.price_book') }}",
                type: "GET",
                data: {
                    search: 1,
                    category: category
                },
                success: function(response) {
                    var products = response.products;
                    var addedCount = 0;
                    
                    products.forEach(function(p) {
                        if ($('#row_' + p.id).length === 0) {
                            var imgHtml = p.image_url 
                                ? '<img src="'+p.image_url+'" style="height: 40px; width: 40px; object-fit: cover; border-radius: 4px;">' 
                                : '<div style="height: 40px; width: 40px; background: #eee; border-radius: 4px; display: inline-block;"></div>';
                            
                            var catName = p.category ? p.category.name : '-';
                            
                            var tr = '<tr id="row_' + p.id + '">' +
                                '<td class="text-center"><input type="checkbox" name="product_ids[]" value="'+p.id+'" class="product-checkbox" checked></td>' +
                                '<td class="text-center">' + imgHtml + '</td>' +
                                '<td>' + (p.code || '') + '</td>' +
                                '<td>' + p.name + '</td>' +
                                '<td>' + catName + '</td>' +
                                '<td class="text-right">' + parseFloat(p.wholesale_price || 0).toFixed(2) + '</td>' +
                                '<td class="text-right">' + parseFloat(p.selling_price || 0).toFixed(2) + '</td>' +
                                '<td><input type="text" name="program['+p.id+']" class="form-control form-control-sm" placeholder="Write program..."></td>' +
                                '<td class="text-center"><button type="button" class="btn btn-danger btn-sm remove-btn"><i class="fa fa-trash"></i></button></td>' +
                            '</tr>';
                            $('#resultContainer').append(tr);
                            addedCount++;
                        }
                    });
                    
                    btn.html('<i class="fa fa-plus"></i> Add to List');
                    btn.prop('disabled', false);

                    if (addedCount > 0) {
                        toastr.success(addedCount + ' products added to list.');
                    } else {
                        toastr.info('No new products found or all already added.');
                    }
                },
                error: function() {
                    alert('Failed to load data.');
                    btn.html('<i class="fa fa-plus"></i> Add to List');
                    btn.prop('disabled', false);
                }
            });
        });

        $(document).on('click', '.remove-btn', function() {
            $(this).closest('tr').remove();
        });

        $('#selectAll').change(function() {
            $('.product-checkbox').prop('checked', $(this).prop('checked'));
        });
    });
</script>
@endsection
