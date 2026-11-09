@extends('layouts.app')
@section('title', $pageTitle)
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="warehouse">Warehouse</label>
                                <select class="form-control select2" name="warehouse" id="warehouse">
                                    <!-- <option value="">All Damage Warehouse</option> -->
                                    @foreach($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="category">Category</label>
                                <select class="form-control select2" name="category" id="category">
                                    <option value="">All Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }} - {{ $category->code }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="product">Product</label>
                                <select class="form-control select2" name="product" id="product">
                                    <option value="">All Product</option>
                                    @php
                                        $allProducts = \App\Models\Settings\Product::where('status', 1)->get();
                                    @endphp
                                    @foreach($allProducts as $product)
                                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        {{-- Product data for JS --}}
                        <script id="product-data" type="application/json">
                        @php
                            $productsByCategory = [];
                            foreach($allProducts as $p) {
                                $catId = $p->category_id ?? 0;
                                $productsByCategory[$catId][] = ['id' => $p->id, 'name' => $p->name, 'code' => $p->code];
                            }
                        @endphp
                        {!! json_encode($productsByCategory) !!}
                        </script>
                    </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table" class="table table-bordered table-striped">
                            <thead>
                            <tr>
                                <th class="text-center">S/L</th>
                                <th class="text-center">Product</th>
                                <th class="text-center">Image</th>
                                <th class="text-center">Batch</th>
                                <th class="text-center">Barcode</th>
                                <th class="text-center">Quantity</th>
                                <th class="text-center">Unit Price</th>
                                <th class="text-center">DP Price</th>
                                <th class="text-center">MRP Price</th>
                                <th class="text-center">Action</th>
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
            var table = $('#table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('returns-management.defect-stock.datatable') }}",
                    data: function (d) {
                        d.warehouse = $("#warehouse").val()
                        d.product = $("#product").val()
                        d.category = $("#category").val()
                    }
                },
                "pagingType": "full_numbers",
                "lengthMenu": [[10, 25, 50, -1],[10, 25, 50, "All"]],
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className:'text-center'},
                    {data: 'product_name', name: 'product.name'},
                    {data: 'product_image', name: 'product_image', orderable: false, searchable: false, className:'text-center'},
                    {data: 'batch_no', name: 'batch_no',
                        render: function(data) {
                            return 'Batch-'+data;
                        }, className:'text-right'
                    },
                    {data: 'product_code', name: 'product.code', className:'text-center'},
                    {data: 'quantity', name: 'quantity',
                        render: function(data, type, row) {
                            return `<strong>${data}</strong> <span class="badge badge-danger ml-1" style="font-size: 11px;">${row.unit_name ?? ''}</span>`;
                        },
                        className:'text-right'
                    },
                    {data: 'unit_price', name: 'unit_price',
                        render: function(data) {
                            return jsNumberFormat(parseFloat(data).toFixed(2));
                        }, className:'text-right'
                    },
                    {data: 'product.wholesale_price', name: 'product.wholesale_price',
                        render: function(data) {
                            return jsNumberFormat(parseFloat(data ?? 0).toFixed(2));
                        }, className:'text-right'
                    },
                    {data: 'product.selling_price', name: 'product.selling_price',
                        render: function(data) {
                            return jsNumberFormat(parseFloat(data ?? 0).toFixed(2));
                        }, className:'text-right'
                    },
                    {data: 'action', name: 'action', className:'text-center'},
                ],
                "dom": 'lBfrtip',
                "buttons": datatableButtons(),
                "responsive": true, "autoWidth": false, "colReorder": true,
            });

            // Product data from PHP for category filter
            var productsByCategory = JSON.parse(document.getElementById('product-data').textContent);
            var allProducts = [];
            $.each(productsByCategory, function(catId, items) { allProducts = allProducts.concat(items); });

            $('#category').change(function() {
                var catId = $(this).val();
                var prodSel = $('#product');
                var opts = '<option value="">All Product</option>';
                var list = catId ? (productsByCategory[catId] || []) : allProducts;
                $.each(list, function(i, p) {
                    opts += '<option value="' + p.id + '">' + p.name + '</option>';
                });
                prodSel.html(opts).val('').trigger('change.select2');
            });

            $('#category, #product, #warehouse').change(function () {
                table.ajax.reload();
            });

        });
    </script>
@endsection
