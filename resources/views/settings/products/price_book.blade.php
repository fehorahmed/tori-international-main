@extends('layouts.app')
@section('title', 'Price Book')

@section('style')
    <style>
        .table th, .table td {
            vertical-align: middle;
        }
        .margin-display {
            font-size: 0.9rem;
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-default mb-3">
                <div class="card-body py-2">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <label for="category_filter" class="m-0">Filter by Category:</label>
                            <select id="category_filter" class="form-control form-control-sm select2">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }} - {{ $category->code }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">Manage Product Prices</h3>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <form id="price-book-form">
                        @csrf
                        <div class="table-responsive">
                            <table id="table" class="table table-bordered table-striped">
                                <thead>
                                <tr>
                                    <th>S/L</th>
                                    <th>Product Name</th>
                                    <th>Category</th>
                                    <th>Code</th>
                                    <th class="text-center">Current Stock</th>
                                    <th class="text-center">Last Cost Price</th>
                                    <th class="text-center">Current DP</th>
                                    <th class="text-center" style="width: 12%">New DP</th>
                                    <th class="text-center">Current MRP</th>
                                    <th class="text-center" style="width: 12%">New MRP</th>
                                </tr>
                                </thead>
                            </table>
                        </div>
                        <div class="mt-3 text-right">
                            <button type="button" id="btn-save-prices" class="btn btn-info bg-gradient-info btn-sm">
                                Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Preview Modal -->
    <div class="modal fade" id="previewModal" tabindex="-1" role="dialog" aria-labelledby="previewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="previewModalLabel"><i class="fa fa-list"></i> Review Price Changes</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="text-muted">Please review the new prices before confirming.</p>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm" id="preview-table">
                            <thead class="bg-light">
                                <tr>
                                    <th>Product Name</th>
                                    <th class="text-center">Last Cost Price</th>
                                    <th class="text-center">Current DP</th>
                                    <th class="text-center">New DP</th>
                                    <th class="text-center">Current MRP</th>
                                    <th class="text-center">New MRP</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Preview rows will be injected here -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="btn-confirm-save">
                        <i class="fa fa-check"></i> Confirm & Save
                    </button>
                </div>
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
                    url: '{{ route('price_book.datatable') }}',
                    data: function(d) {
                        d.category_id = $('#category_filter').val();
                    }
                },
                columns: [
                    {data: 'DT_RowIndex', 'orderable': false, 'searchable': false},
                    {data: 'name', name: 'name'},
                    {data: 'category.name', name: 'category.name', defaultContent: ''},
                    {data: 'code', name: 'code'},
                    {data: 'current_stock', name: 'current_stock', className: 'text-center', orderable: false, searchable: false},
                    {data: 'last_cost_price', name: 'last_cost_price', className: 'text-right font-weight-bold text-danger', orderable: false, searchable: false},
                    {data: 'current_dp', name: 'current_dp', className: 'text-right font-weight-bold', orderable: false, searchable: false},
                    {data: 'new_dp', name: 'new_dp', className: 'text-center', orderable: false, searchable: false},
                    {data: 'current_mrp', name: 'current_mrp', className: 'text-right font-weight-bold', orderable: false, searchable: false},
                    {data: 'new_mrp', name: 'new_mrp', className: 'text-center', orderable: false, searchable: false},
                ],
                order: [[1, 'asc']],
                pageLength: 50
            });

            $('#category_filter').change(function() {
                table.ajax.reload();
            });

            var productsDataToSave = [];

            $('#btn-save-prices').click(function () {
                productsDataToSave = [];
                var previewHtml = '';
                var hasChanges = false;

                $('#table tbody tr').each(function() {
                    var tr = $(this);
                    var dpInput = tr.find('.wholesale-price-input');
                    var mrpInput = tr.find('.selling-price-input');
                    
                    if(dpInput.length && mrpInput.length) {
                        var productId = dpInput.attr('name').match(/\[(.*?)\]/)[1];
                        var productName = tr.find('td:eq(1)').text();
                        var lastCostPrice = tr.find('td:eq(5)').text();
                        
                        var currentDpRaw = tr.find('td:eq(6)').text().replace(/,/g, '');
                        var currentMrpRaw = tr.find('td:eq(8)').text().replace(/,/g, '');
                        
                        var currentDp = parseFloat(currentDpRaw) || 0;
                        var currentMrp = parseFloat(currentMrpRaw) || 0;
                        var newDp = parseFloat(dpInput.val()) || 0;
                        var newMrp = parseFloat(mrpInput.val()) || 0;

                        // We can check if values exist
                        if(!isNaN(newDp) && !isNaN(newMrp)) {
                            // Only log if something changed
                            if (currentDp !== newDp || currentMrp !== newMrp) {
                                productsDataToSave.push({
                                    id: productId,
                                    wholesale_price: newDp,
                                    selling_price: newMrp
                                });

                                previewHtml += '<tr>';
                                previewHtml += '<td>' + productName + '</td>';
                                previewHtml += '<td class="text-center text-danger font-weight-bold">' + lastCostPrice + '</td>';
                                previewHtml += '<td class="text-center text-muted"><del>' + currentDp.toFixed(2) + '</del></td>';
                                previewHtml += '<td class="text-center font-weight-bold text-primary">' + newDp.toFixed(2) + '</td>';
                                previewHtml += '<td class="text-center text-muted"><del>' + currentMrp.toFixed(2) + '</del></td>';
                                previewHtml += '<td class="text-center font-weight-bold text-success">' + newMrp.toFixed(2) + '</td>';
                                previewHtml += '</tr>';
                                
                                hasChanges = true;
                            }
                        }
                    }
                });

                if(!hasChanges) {
                    Swal.fire('Warning', 'No prices found to update on this page.', 'warning');
                    return;
                }

                $('#preview-table tbody').html(previewHtml);
                $('#previewModal').modal('show');
            });

            $('#btn-confirm-save').click(function () {
                var btn = $(this);
                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');

                $.ajax({
                    method: "POST",
                    url: "{{ route('price_book.update') }}",
                    data: {
                        _token: '{{ csrf_token() }}',
                        products: productsDataToSave
                    },
                    success: function (response) {
                        if (response.success) {
                            $('#previewModal').modal('hide');
                            Swal.fire({
                                title: 'Success!',
                                text: response.message,
                                icon: 'success',
                                showCancelButton: true,
                                confirmButtonColor: '#3085d6',
                                cancelButtonColor: '#d33',
                                confirmButtonText: '<i class="fa fa-history"></i> View Price History',
                                cancelButtonText: 'Stay Here'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = "{{ route('report.price_book_history') }}";
                                } else {
                                    table.ajax.reload(null, false);
                                }
                            });
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function (xhr) {
                        var msg = 'An error occurred';
                        if(xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        Swal.fire('Error', msg, 'error');
                    },
                    complete: function() {
                        btn.prop('disabled', false).html('<i class="fa fa-check"></i> Confirm & Save');
                    }
                });
            });
        });
    </script>
@endsection
