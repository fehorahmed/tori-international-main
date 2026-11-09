@extends('layouts.app')
@section('title', 'Customer Payment Receive')

@section('style')
    <style>
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 0 !important;
            margin: 0 !important;
        }

        /* Custom Thin Scrollbar for Modal Body */
        .modal-body::-webkit-scrollbar {
            width: 6px;
        }

        .modal-body::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .modal-body::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 10px;
        }

        .modal-body::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-outline card-default">
                <div class="card-header">
                    <h3 class="card-title">Filter Clients</h3>
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
                        <div class="col-md-3">
                            <label for="client_type_filter">Client Type:</label>
                            <select id="client_type_filter" class="form-control form-control-sm select2">
                                <option value="Customer" selected>General Customer</option>
                                <option value="Corporate_Customer">Corporate Customer</option>
                                <option value="Dealer">Dealer</option>
                            </select>
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
                    <h3 class="card-title">Customer List</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table" class="table table-bordered table-striped" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th width="5%">S/L</th>
                                    <th>Customer</th>
                                    <th>Member No</th>
                                    <th class="text-center">Opening Balance</th>
                                    <th class="text-right">Total Sales</th>
                                    <th class="text-right text-danger">Net Return/Adj.</th>
                                    <th class="text-right">Total Paid</th>
                                    <th class="text-center">Due / Advance</th>
                                    <th class="text-center" width="10%">Action</th>
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
                    <h5 class="modal-title">Payment: <span id="modal-customer-name"></span></h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <h4 class="text-center text-primary" id="modal-customer-name"></h4>
                    <hr>
                    <!-- Future form goes here -->
                    <p class="text-center text-muted">Payment form controls will be loaded here...</p>
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
                    url: '{{ route('customer-payment.datatable') }}',
                    data: function (d) {
                        d.type = $('#client_type_filter').val();
                        d.division_id = $('#division_id').val();
                        d.district_id = $('#district_id').val();
                        d.upazila_id = $('#upazila_id').val();
                        d.union_id = $('#union_id').val();
                        d.village_id = $('#village_id').val();
                        @if(isset($customer) && $customer)
                            d.customer_id = '{{ $customer->id }}';
                        @endif
                        }
                },
                "pagingType": "full_numbers",
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                columns: [
                    { data: 'DT_RowIndex', 'orderable': false, 'searchable': false, className: 'text-center' },
                    { data: 'name', name: 'name' },
                    { data: 'member_no', name: 'member_no' },
                    { data: 'opening_balance', name: 'opening_balance', className: 'text-center' },
                    { data: 'total_sales', name: 'total_sales', className: 'text-right' },
                    { data: 'total_return', name: 'total_return', className: 'text-right text-danger', searchable: false },
                    { data: 'total_paid', name: 'total_paid', className: 'text-right' },
                    { data: 'current_due_advance', name: 'current_due_advance', className: 'text-center', orderable: false, searchable: false },
                    { data: 'action', name: 'action', className: 'text-center', orderable: false, searchable: false },
                ],
                order: [[1, 'asc']],
            });

            $('#client_type_filter').change(function () {
                table.ajax.reload();
            });

            $(document).on('location_filter_changed', function () {
                table.ajax.reload();
            });

            $('body').on('click', '.btn-payment', function () {
                var customerId = $(this).data('id');
                var customerName = $(this).data('name');

                $('#modal-customer-name').text(customerName);
                $('#paymentModal .modal-body').html('<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-3x text-info"></i><p class="mt-2">Loading customer data...</p></div>');
                $('#paymentModal').modal('show');

                $.ajax({
                    url: '{{ route('customer-payment.create') }}',
                    type: 'GET',
                    data: { customer_id: customerId },
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
                    error: function (xhr) {
                        $('#paymentModal .modal-body').html('<div class="alert alert-danger">Error loading data. Please try again.</div>');
                    }
                });
            });

            @if(isset($customer) && $customer)
                // Switch client type if needed
                @if($customer->type == \App\Enumeration\SupplierType::$CORPORATE_CUSTOMER)
                    $('#client_type_filter').val('Corporate_Customer').trigger('change');
                @elseif($customer->type == \App\Enumeration\SupplierType::$DEALER)
                    $('#client_type_filter').val('Dealer').trigger('change');
                @endif

                // Auto trigger payment modal
                setTimeout(function () {
                    var btn = $('<button>').data('id', '{{ $customer->id }}').data('name', {!! json_encode($customer->name) !!}).addClass('btn-payment');
                    $('body').append(btn);
                    btn.trigger('click');
                    btn.remove();
                }, 500);
            @endif
            });
    </script>
@endsection