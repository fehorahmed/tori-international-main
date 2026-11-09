@extends('layouts.app')
@section('title',$pageTitle)
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header text-right">
                    @if(auth()->user()->can($permissionCreate))
                    <a href="{{ route('product.create',['product_type'=>request('product_type')]) }}" class="btn btn-info bg-gradient-info btn-sm">Create
                        {{ $pageTitle }} <i class="fa fa-plus"></i></a>
                    @endif
                </div>
                <div class="card-header bg-light">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label for="filter_category">Category</label>
                            <select id="filter_category" class="form-control select2">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }} - {{ $category->code }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="filter_product">Product</label>
                            <select id="filter_product" class="form-control select2">
                                <option value="">All Products</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <button id="btn-filter" class="btn btn-primary btn-sm"><i class="fa fa-search"></i> Filter</button>
                            <button id="btn-reset" class="btn btn-danger btn-sm"><i class="fa fa-undo"></i> Reset</button>
                        </div>
                    </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table" class="table table-bordered">
                            <thead>
                            <tr>
                                <th>S/L</th>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Brand</th>
                                <th>Unit</th>
                                <th>EMI Int (%)</th>
                                <th>Image</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
                <!-- /.card-body -->
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        $(function () {

            $('#table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('product.datatable') }}",
                    data: function(d) {
                        d.category_id = $('#filter_category').val();
                        d.product_id = $('#filter_product').val();
                    }
                },
                "pagingType": "full_numbers",
                "lengthMenu": [[10, 25, 50, -1],[10, 25, 50, "All"]
                ],
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                    {data: 'code', name: 'code'},
                    {data: 'name', name: 'name'},
                    {data: 'product_category_name', name: 'category.name'},
                    {data: 'brand_name', name: 'brand.name'},
                    {data: 'unit_name', name: 'unit.name'},
                    {data: 'emi_interest', name: 'emi_interest'},
                    {data: 'image', name: 'image'},
                    {
                        data: 'status',
                        name: 'status',
                        render: function (data, type, row) {
                            if (data === 1) {
                                return '<span class="badge badge-success">Active</span>';
                            } else if (data === 0) {
                                return '<span class="badge badge-danger">Inactive</span>';
                            }
                            return data;
                        }
                    },
                    {data: 'action', name: 'action', orderable: false},
                ],
                "dom": 'lBfrtip',
                "buttons": datatableButtons(),
                "responsive": true, "autoWidth": false,"colReorder": true,
            });

            $('#btn-filter').click(function(){
                $('#table').DataTable().draw(true);
            });
            
            $('#btn-reset').click(function(){
                $('#filter_category').val('').trigger('change.select2'); // Prevent AJAX loop
                // Reset product dropdown options to full list
                $('#filter_product').html('<option value="">All Products</option>@foreach($products as $product)<option value="{{ $product->id }}">{{ $product->name }}</option>@endforeach');
                $('#filter_product').val('').trigger('change.select2');
                $('#table').DataTable().draw(true);
            });

            $('#filter_category').on('change', function() {
                // Auto filter table
                $('#table').DataTable().draw(true);
                
                let categoryId = $(this).val();
                $.ajax({
                    url: "{{ route('product.by_category') }}",
                    type: "GET",
                    data: { category_id: categoryId },
                    success: function(data) {
                        let html = '<option value="">All Products</option>';
                        $.each(data, function(index, product) {
                            html += '<option value="' + product.id + '">' + product.name + '</option>';
                        });
                        $('#filter_product').html(html);
                        $('#filter_product').trigger('change.select2'); // Update select2 UI without triggering another change event if bound later
                    }
                });
            });

            $('#filter_product').on('change', function() {
                // Auto filter table
                $('#table').DataTable().draw(true);
            });

            $('body').on('click', '.btn-delete', function () {
                let id = $(this).data('id');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, Delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        preloaderToggle(true);
                        $.ajax({
                            method: "DELETE",
                            url: "{{ route('product.destroy', ['product' => 'REPLACE_WITH_ID_HERE']) }}".replace('REPLACE_WITH_ID_HERE', id),
                            data: { id: id }
                        }).done(function( response ) {
                            preloaderToggle(false);
                            if (response.success) {
                                Swal.fire(
                                    'Deleted!',
                                    response.message,
                                    'success'
                                ).then((result) => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Oops...',
                                    text: response.message,
                                });
                            }
                        });

                    }
                })

            });
        });
    </script>
@endsection
