@extends('layouts.app')
@section('title', 'Customer Receive History')

@section('style')
    <style>
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 0 !important;
            margin: 0 !important;
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h3 class="card-title"><i class="fas fa-history mr-2"></i> Payment History: <span
                                    class="text-primary">{{ $customer->name }}</span></h3>
                        </div>
                        <div class="col-md-6 text-right">
                            <a href="{{ route('customer-payment.index') }}" class="btn btn-sm btn-info bg-gradient-info"><i
                                    class="fas fa-arrow-left"></i> Back to Receive</a>
                        </div>
                    </div>
                </div>
                <div class="card-header bg-light">
                    <div class="row">
                        <div class="col-md-4">
                            <label for="start_date" class="m-0">Start Date:</label>
                            <input type="text" id="start_date" class="form-control form-control-sm date-picker"
                                value="{{ date('01-m-Y') }}">
                        </div>
                        <div class="col-md-4">
                            <label for="end_date" class="m-0">End Date:</label>
                            <input type="text" id="end_date" class="form-control form-control-sm date-picker"
                                value="{{ date('t-m-Y') }}">
                        </div>
                        <div class="col-md-2">
                            <label></label>
                            <button class="btn btn-sm btn-primary bg-gradient-primary w-100" id="btnSearch"><i
                                    class="fas fa-search"></i> Search</button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table" class="table table-bordered table-striped table-hover w-100">
                            <thead>
                                <tr class="bg-info">
                                    <th width="5%" class="text-center">S/L</th>
                                    <th class="text-center">Date</th>
                                    <th>Customer Name</th>
                                    <th class="text-center">Voucher No</th>
                                    <th class="text-center">Order No</th>
                                    <th class="text-center">Notes</th>
                                    <th class="text-right">Amount</th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th colspan="6" class="text-right">Total Amount:</th>
                                    <th class="text-right" id="total_amount_display">0.00</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
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
                    url: '{{ route('customer-payment.history.datatable') }}',
                    data: function (d) {
                        d.customer = '{{ $customer->id }}';
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', 'orderable': false, 'searchable': false, className: 'text-center' },
                    { data: 'date', name: 'date', className: 'text-center' },
                    { data: 'customer_name', name: 'accountHead.supplier.name', 'orderable': false, 'searchable': false },
                    { data: 'voucher_no', name: 'voucher.voucher_no', className: 'text-center', 'orderable': false },
                    { data: 'order_no', name: 'sale_order_id', className: 'text-center', 'orderable': false, 'searchable': false },
                    { data: 'notes', name: 'notes', className: 'text-center' },
                    { data: 'amount', name: 'amount', className: 'text-right' },
                ],
                order: [[1, 'desc']],
                "dom": 'lBfrtip',
                "buttons": datatableButtons(),
                footerCallback: function (row, data, start, end, display) {
                    var api = this.api();
                    var intVal = function (i) {
                        return typeof i === 'string' ? i.replace(/[\$,]/g, '') * 1 : typeof i === 'number' ? i : 0;
                    };
                    var total = api.column(6, { page: 'current' }).data().reduce(function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);
                    $(api.column(6).footer()).html(jsNumberFormat(total.toFixed(2)));
                }
            });

            $('#btnSearch').click(function () {
                table.ajax.reload();
            });
        });
    </script>
@endsection