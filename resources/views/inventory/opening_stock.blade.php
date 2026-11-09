@extends('layouts.app')
@section('title', 'Opening Stock Entry')

@section('style')
<style>
    .table td, .table th { padding: 6px; vertical-align: middle; }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <!-- form start -->
        <form action="{{ route('inventory.opening_stock.store') }}" method="POST" id="opening-stock-form">
            @csrf
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-boxes mr-1"></i> Opening Stock Entry</h3>
                </div>
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Warehouse <span class="text-danger">*</span></label>
                                <select name="warehouse_id" class="form-control select2 @error('warehouse_id') is-invalid @enderror" required>
                                    <option value="">-- Select Warehouse --</option>
                                    @foreach($warehouses as $w)
                                        <option value="{{ $w->id }}" {{ old('warehouse_id') == $w->id ? 'selected' : '' }} selected>{{ $w->name }}</option>
                                    @endforeach
                                </select>
                                @error('warehouse_id') <span class="help-block text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Entry Date <span class="text-danger">*</span></label>
                                <input type="text" autocomplete="off" name="date" class="form-control opening-date-picker @error('date') is-invalid @enderror"
                                    placeholder="Enter date" readonly>
                                @error('date') <span class="help-block text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Notes</label>
                                <input type="text" name="notes" class="form-control" value="{{ old('notes', 'Opening Stock Entry') }}" placeholder="Opening Stock Entry">
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
                    @endif

                    <div class="alert alert-info mb-3">
                        <i class="fas fa-info-circle"></i>
                        <strong>Accounting Entry:</strong>
                        Dr. <strong>Finished Goods Inventory</strong> → Cr. <strong>Opening Balance Equity</strong>.
                        Use this only once at the start of operations to enter existing stock balances.
                    </div>

                    <div class="row">
                        <div class="col-sm-12">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th class="text-center" width="4%">S/L</th>
                                        <th class="text-center" width="22%">Category</th>
                                        <th class="text-center">Product</th>
                                        <th class="text-center" width="10%">Quantity</th>
                                        <th class="text-center" width="15%">Unit Price (Cost)</th>
                                        <th class="text-center" width="13%">Total Value</th>
                                        <th class="text-center" width="5%"></th>
                                    </tr>
                                </thead>
                                <tbody id="product-rows">
                                    {{-- rows added dynamically --}}
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="5" class="text-right">Grand Total</th>
                                        <th class="text-right">
                                            <div class="form-group mb-0">
                                                <input type="text" id="grand-total" class="form-control text-right font-weight-bold bg-light" readonly value="0.00">
                                            </div>
                                        </th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <button type="button" class="btn btn-secondary btn-sm mb-2" id="add-row">
                        <i class="fa fa-plus"></i> Add Row
                    </button>
                </div>
                <!-- /.card-body -->
                <div class="card-footer">
                    <button type="button" id="btn-save-opening-stock" class="btn btn-info bg-gradient-info btn-sm">Save</button>
                    <a href="{{ route('inventory.index') }}" class="btn btn-danger bg-gradient-danger btn-sm float-right">Cancel</a>
                </div>
                <!-- /.card-footer -->
            </div>
        </form>
    </div>
</div>

{{-- Product data for JS --}}
<script id="product-data" type="application/json">
@php
    $productsByCategory = [];
    foreach($products as $p) {
        $catId = $p->category_id ?? 0;
        $productsByCategory[$catId][] = ['id' => $p->id, 'name' => $p->name, 'code' => $p->code];
    }
@endphp
{!! json_encode($productsByCategory) !!}
</script>

{{-- Opening Stock Log --}}
<div class="row mt-3">
    <div class="col-12">
        <div class="card card-default">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-history mr-1"></i> Opening Stock Log</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="log-table" class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th>S/L</th>
                                <th>Date</th>
                                <th>Warehouse</th>
                                <th>Product</th>
                                <th>Code</th>
                                <th class="text-right">Qty</th>
                                <th class="text-right">Unit Price</th>
                                <th class="text-right">Total Value</th>
                                <th>Entered By</th>
                                <th>Notes</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Opening Stock Preview Modal --}}
<div class="modal fade" id="modal-opening-stock-overview" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h5 class="modal-title"><i class="fas fa-boxes mr-1"></i> Opening Stock Preview</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div>
                            <strong class="text-secondary"><i class="fa fa-warehouse text-info mr-1"></i> Warehouse:</strong>
                            <span id="ov-warehouse" class="text-dark ml-1 font-weight-bold">-</span>
                        </div>
                        <div>
                            <strong class="text-secondary"><i class="fa fa-calendar-alt text-info mr-1"></i> Entry Date:</strong>
                            <span id="ov-date" class="text-dark ml-1">-</span>
                        </div>
                    </div>
                    <div class="col-md-6 pl-md-3">
                        <strong><i class="fa fa-sticky-note text-info mr-1"></i> Notes:</strong>
                        <div id="ov-notes" class="text-dark mt-1 p-2 bg-light rounded border" style="min-height: 50px; font-style: italic; font-size: 13px;">
                            -
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-striped table-bordered text-center">
                        <thead>
                            <tr class="bg-light">
                                <th>S/L</th>
                                <th>Category</th>
                                <th>Product Name</th>
                                <th>Qty</th>
                                <th>Unit Price</th>
                                <th>Total Price</th>
                            </tr>
                        </thead>
                        <tbody id="overview-items-body">
                            <!-- Dynamic Rows Go Here -->
                        </tbody>
                    </table>
                </div>

                <!-- Financial Summary -->
                <div class="row mt-4">
                    <div class="col-md-5 offset-md-7">
                        <table class="table table-sm table-bordered">
                            <tr class="table-info">
                                <th class="text-right">Grand Total:</th>
                                <td class="text-right font-weight-bold" id="ov-grand-total" style="font-size: 16px;">0.00</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fa fa-edit"></i> Edit Entry</button>
                <button type="button" class="btn btn-success" id="btn-confirm-submit"><i class="fa fa-check-circle"></i> Confirm & Save</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script>
$(function () {
    // ── Product data from PHP ─────────────────────────────────────────────
    var productsByCategory = JSON.parse(document.getElementById('product-data').textContent);
    var allProducts = [];
    $.each(productsByCategory, function(catId, items) { allProducts = allProducts.concat(items); });

    var categories = @json($categories->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'code' => $c->code]));

    var previousDate = '{{ \Carbon\Carbon::parse(auth()->user()->activeSisterConcern->business_date ?? auth()->user()->sisterConcern->business_date ?? now())->subDay()->format('d-m-Y') }}';
    $(".opening-date-picker").datepicker({
        dateFormat: 'dd-mm-yy',
        changeMonth: true,
        changeYear: true,
    }).val(previousDate);

    // ── Build a new blank row ─────────────────────────────────────────────
    function buildRow() {
        // Category options
        var catOptions = '<option value="" selected disabled>-- Select Category --</option>';
        $.each(categories, function(i, cat) {
            catOptions += '<option value="' + cat.id + '">' + cat.name + ' - ' + (cat.code ? cat.code : '') + '</option>';
        });

        // Product options (default)
        var prodOptions = '<option value="" selected disabled>-- Select Category First --</option>';

        var tr = $('<tr class="product-row"></tr>');
        tr.html(
            '<td class="text-center sl-col"></td>' +
            '<td><select class="form-control select2 cat-select">' + catOptions + '</select></td>' +
            '<td><select name="product_id[]" class="form-control select2 product-select" required>' + prodOptions + '</select></td>' +
            '<td class="text-right"><div class="form-group mb-0"><input type="number" name="quantity[]" class="form-control text-right qty-input" min="0.01" step="0.01" placeholder="0" required></div></td>' +
            '<td class="text-right"><div class="form-group mb-0"><input type="number" name="unit_price[]" class="form-control text-right price-input" min="0" step="0.01" placeholder="0.00" required></div></td>' +
            '<td class="text-right total-value-cell">0.00</td>' +
            '<td class="text-center"><button type="button" class="btn btn-danger bg-gradient-danger btn-sm remove-row"><i class="fa fa-trash-alt"></i></button></td>'
        );
        return tr;
    }

    // ── Add first row on page load ────────────────────────────────────────
    function appendRow() {
        var row = buildRow();
        $('#product-rows').append(row);
        row.find('.select2').select2();
        updateSl();
    }
    appendRow();

    // ── SL numbering ─────────────────────────────────────────────────────
    function updateSl() {
        $('.product-row').each(function(i) { $(this).find('.sl-col').text(i + 1); });
    }

    // ── Category filter → product dropdown ───────────────────────────────
    $(document).on('change', '.cat-select', function() {
        var catId   = $(this).val();
        var prodSel = $(this).closest('.product-row').find('.product-select');
        var opts    = '<option value="" selected disabled>-- Select Product --</option>';
        var list    = catId ? (productsByCategory[catId] || []) : [];
        $.each(list, function(i, p) {
            opts += '<option value="' + p.id + '">' + p.name + ' (' + p.code + ')</option>';
        });
        prodSel.html(opts).val('').trigger('change.select2');
    });

    // ── Prevent Duplicate Product Selection ─────────────────────────────────
    $(document).on('change', '.product-select', function() {
        var selectedProductId = $(this).val();
        if (!selectedProductId) return;
        
        var currentSelect = this;
        var duplicateFound = false;

        $('.product-select').not(currentSelect).each(function() {
            if ($(this).val() == selectedProductId) {
                duplicateFound = true;
                return false;
            }
        });

        if (duplicateFound) {
            Swal.fire({
                icon: 'warning',
                title: 'Duplicate Product',
                text: 'This product is already selected in another row! Please adjust the quantity instead of adding a new row.',
            });
            $(this).val('').trigger('change.select2');
        }
    });

    // ── Row calculation ───────────────────────────────────────────────────
    function calcRow($row) {
        var qty   = parseFloat($row.find('.qty-input').val()) || 0;
        var price = parseFloat($row.find('.price-input').val()) || 0;
        $row.find('.total-value-cell').text(jsNumberFormat((qty * price).toFixed(2)));
        calcGrandTotal();
    }
    function calcGrandTotal() {
        var grand = 0;
        $('.product-row').each(function() {
            grand += parseFloat($(this).find('.total-value-cell').text().replace(/,/g, '')) || 0;
        });
        $('#grand-total').val(jsNumberFormat(grand.toFixed(2)));
    }

    $(document).on('input', '.qty-input, .price-input', function() {
        calcRow($(this).closest('.product-row'));
    });

    // ── Add row ──────────────────────────────────────────────────────────
    $('#add-row').click(function() { appendRow(); });

    // ── Remove row ───────────────────────────────────────────────────────
    $(document).on('click', '.remove-row', function() {
        if ($('.product-row').length > 1) {
            $(this).closest('.product-row').remove();
            calcGrandTotal(); updateSl();
        } else {
            Swal.fire('Notice', 'At least one product row is required.', 'info');
        }
    });

    // ── Datatable ────────────────────────────────────────────────────────
    $('#log-table').DataTable({
        processing: true, serverSide: true,
        ajax: "{{ route('inventory.opening_stock.datatable') }}",
        order: [[1, 'desc']],
        columns: [
            {data: 'DT_RowIndex', orderable: false, searchable: false},
            {data: 'date', name: 'date'},
            {data: 'warehouse_name', name: 'warehouse_name'},
            {data: 'product_name', name: 'product_name'},
            {data: 'product_code', name: 'product_code'},
            {data: 'quantity', name: 'quantity', className: 'text-right'},
            {data: 'unit_price', name: 'unit_price', className: 'text-right',
                render: d => jsNumberFormat(parseFloat(d||0).toFixed(2))},
            {data: 'total_value', name: 'total_value', className: 'text-right font-weight-bold'},
            {data: 'entered_by', name: 'entered_by'},
            {data: 'notes', name: 'notes'},
            {data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center'},
        ],
        dom: 'lBfrtip', buttons: datatableButtons(), responsive: true, autoWidth: false,
    });

    // ── Delete button ─────────────────────────────────────────────────────
    $(document).on('click', '.btn-delete-log', function() {
        let id = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            text: "This will delete the entry and reverse the accounting voucher!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, Delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ url('admin/inventory/opening-stock/destroy') }}/" + id,
                    type: 'POST',
                    data: { _token: "{{ csrf_token() }}" },
                    success: function(res) {
                        if (res.status) {
                            Swal.fire('Deleted!', res.message, 'success');
                            $('#log-table').DataTable().ajax.reload();
                        } else {
                            Swal.fire('Error!', res.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error!', 'Something went wrong.', 'error');
                    }
                });
            }
        });
    });

    // ── Save button ───────────────────────────────────────────────────────
    $('#btn-save-opening-stock').click(function(e) {
        e.preventDefault();
        var form = document.getElementById('opening-stock-form');
        if (!form.checkValidity()) { form.reportValidity(); return; }

        let rows = $(".product-row");
        let hasValidRow = false;
        
        let overviewBody = $('#overview-items-body');
        overviewBody.empty();

        rows.each(function(index) {
            let sl = $(this).find('.sl-col').text().trim();
            let catName = $(this).find('.cat-select option:selected').text().trim();
            let prodId = $(this).find('.product-select').val();
            let prodName = $(this).find('.product-select option:selected').text().trim();
            let qty = parseFloat($(this).find('.qty-input').val()) || 0;
            let unitPrice = parseFloat($(this).find('.price-input').val()) || 0;
            let total = qty * unitPrice;

            if (prodId && qty > 0) {
                hasValidRow = true;
                let rowHtml = `
                    <tr>
                        <td>${sl}</td>
                        <td>${catName.replace('-- All Category --', '-')}</td>
                        <td class="text-left font-weight-bold">${prodName}</td>
                        <td class="font-weight-bold">${qty}</td>
                        <td>${unitPrice.toFixed(2)}</td>
                        <td class="text-right font-weight-bold">${total.toFixed(2)}</td>
                    </tr>
                `;
                overviewBody.append(rowHtml);
            }
        });

        if (!hasValidRow) {
            Swal.fire('Warning', 'Please add at least one valid product with quantity.', 'warning');
            return;
        }

        let warehouseText = $('select[name="warehouse_id"] option:selected').text().trim() || 'N/A';
        let dateText = $('input[name="date"]').val() || 'N/A';
        let notesText = $('input[name="notes"]').val() || 'N/A';

        $('#ov-warehouse').text(warehouseText);
        $('#ov-date').text(dateText);
        $('#ov-notes').text(notesText);
        $('#ov-grand-total').text($('#grand-total').val());

        $('#modal-opening-stock-overview').modal('show');
    });

    $('#btn-confirm-submit').click(function() {
        $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');
        $('#opening-stock-form').submit();
    });
});
</script>
@endsection
