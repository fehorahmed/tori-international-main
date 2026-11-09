@extends('layouts.app')
@section('title','Supplier Payments')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-outline card-default">
                <div class="card-header">
                    <h3 class="card-title">Filter Suppliers</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="division_id">Division</label>
                                <select name="division_id" id="division_id" class="form-control select2">
                                    <option value="">All Divisions</option>
                                    @foreach($divisions as $division)
                                        <option value="{{ $division->id }}">{{ $division->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="district_id">District</label>
                                <select name="district_id" id="district_id" class="form-control select2">
                                    <option value="">All Districts</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="upazila_id">Upazila</label>
                                <select name="upazila_id" id="upazila_id" class="form-control select2">
                                    <option value="">All Upazilas</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="union_id">Pourashava/Union</label>
                                <select name="union_id" id="union_id" class="form-control select2">
                                    <option value="">All Unions</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="village_id">Village/Road</label>
                                <select name="village_id" id="village_id" class="form-control select2">
                                    <option value="">All Villages</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">Supplier Payments</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table" class="table table-bordered" style="width: 100%;">
                            <thead>
                            <tr>
                                <th class="text-center">S/L</th>
                                <th class="text-center">Supplier Name</th>
                                <th class="text-center">Opening Balance</th>
                                <th class="text-right"> Purchase</th>
                                <th class="text-right"> Receive/Loan</th>
                                <th class="text-right"> Paid</th>
                                <th class="text-center">Current Due / Advance</th>
                                <th class="text-center">Action</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">Payment: <span id="modal-supplier-name"></span></h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <h4 class="text-center text-primary" id="modal-supplier-name-body"></h4>
                    <hr>
                    <!-- Future form goes here -->
                    <p class="text-center text-muted">Payment form controls will be loaded here...</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Receipt Modal -->
    <div class="modal fade" id="receiptModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Receive from: <span id="modal-supplier-receipt-name"></span></h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <h4 class="text-center text-success" id="modal-supplier-receipt-name-body"></h4>
                    <hr>
                    <!-- Future form goes here -->
                    <p class="text-center text-muted">Receipt form controls will be loaded here...</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    @include('layouts.partial.__location_filter_js')
    <script>
        $(function () {
            var table = $('#table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('purchase.supplier_payments.datatable') }}",
                    data: function(d) {
                        d.division_id = $('#division_id').val();
                        d.district_id = $('#district_id').val();
                        d.upazila_id = $('#upazila_id').val();
                        d.union_id = $('#union_id').val();
                        d.village_id = $('#village_id').val();
                    }
                },
                "pagingType": "full_numbers",
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                    {data: 'name', name: 'name'},
                    {data: 'opening_balance', name: 'opening_balance', orderable: false, searchable: false, className: 'text-center'},
                    {data: 'total', name: 'total', orderable: false, searchable: false, className: 'text-right'},
                    {data: 'receive', name: 'receive', orderable: false, searchable: false, className: 'text-right'},
                    {data: 'paid', name: 'paid', orderable: false, searchable: false, className: 'text-right'},
                    {data: 'current_balance', name: 'current_balance', orderable: false, searchable: false, className: 'text-center'},
                    {data: 'action', name: 'action', orderable: false, className: 'text-center'},
                ]
            });

            $(document).on('location_filter_changed', function() {
                table.ajax.reload();
            });

            $('body').on('click', '.supplier-pay', function () {
                let supplierId = $(this).data('id');
                let supplierName = $(this).data('name');
                
                $('#modal-supplier-name').text(supplierName);
                $('#modal-supplier-name-body').text(supplierName);
                $('#paymentModal .modal-body').html('<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-3x text-info"></i><p class="mt-2">Loading data...</p></div>');
                $('#paymentModal').modal('show');
                
                $.ajax({
                    url: "{{ route('purchase.supplier_payment.create') }}",
                    type: "GET",
                    data: { supplier_id: supplierId },
                    success: function (response) {
                        $('#paymentModal .modal-body').html(response);
                        if (typeof businessDateInit === "function") {
                            businessDateInit();
                        }
                        if (typeof dateInit === "function") {
                            dateInit();
                        }
                        if (typeof select2Init === "function") {
                            select2Init();
                        }
                    },
                    error: function() {
                        $('#paymentModal .modal-body').html('<div class="alert alert-danger">Error loading data. Please try again.</div>');
                    }
                });
            });

            $('body').on('click', '.supplier-receipt', function () {
                let supplierId = $(this).data('id');
                let supplierName = $(this).data('name');
                
                $('#modal-supplier-receipt-name').text(supplierName);
                $('#modal-supplier-receipt-name-body').text(supplierName);
                $('#receiptModal .modal-body').html('<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-3x text-success"></i><p class="mt-2">Loading data...</p></div>');
                $('#receiptModal').modal('show');
                
                $.ajax({
                    url: "{{ route('purchase.supplier_receipt.create') }}",
                    type: "GET",
                    data: { supplier_id: supplierId },
                    success: function (response) {
                        $('#receiptModal .modal-body').html(response);
                        if (typeof businessDateInit === "function") {
                            businessDateInit();
                        }
                        if (typeof dateInit === "function") {
                            dateInit();
                        }
                        if (typeof select2Init === "function") {
                            select2Init();
                        }
                    },
                    error: function() {
                        $('#receiptModal .modal-body').html('<div class="alert alert-danger">Error loading data. Please try again.</div>');
                    }
                });
            });
        });
    </script>
@endsection
