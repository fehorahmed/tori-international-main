@extends('layouts.app')
@section('title','Corporate Sales Orders')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    @if (auth()->user()->can('sales_create'))
                    <a href="{{ route('corporate-sales.create') }}" class="btn btn-info bg-gradient-info btn-sm">Create Corporate Sale <i class="fa fa-plus"></i></a>
                    @endif
                </div>
                <div class="card-header">
                    <div class="row">
                        <div class="col-12 col-md-4">
                            <div class="form-group">
                                <label for="customer" class="col-form-label">Corporate Customer <span class="text-danger">*</span></label>
                                <select name="customer" id="customer" class="form-control select2">
                                    <option value="">All Corporate Customer</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-md-3">
                            <div class="form-group">
                                <label for="start_date" class="col-form-label">Start Date</label>
                                <input required autocomplete="off" type="text" value="{{ request('start_date', auth()->user()->business_date ? \Carbon\Carbon::parse(auth()->user()->business_date)->format('d-m-Y') : '') }}" name="start_date" class="form-control date-picker" id="start_date" placeholder="Start Date">
                            </div>
                        </div>
                        <div class="col-12 col-md-3">
                            <div class="form-group">
                                <label for="end_date" class="col-form-label">End Date</label>
                                <input required autocomplete="off" type="text" value="{{ request('end_date', auth()->user()->business_date ? \Carbon\Carbon::parse(auth()->user()->business_date)->format('d-m-Y') : '') }}" name="end_date" class="form-control date-picker" id="end_date" placeholder="End Date">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <input style="margin-top: -4px;" type="button" id="search_btn" class="btn btn-info bg-gradient-info form-control" value="Search">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table" class="table table-bordered table-hover">
                            <thead>
                            <tr>
                                <th class="text-center">Order No.</th>
                                <th class="text-center">Date</th>
                                <th class="text-center">Customer</th>
                                <th class="text-right">Total</th>
                                <th class="text-right">Paid</th>
                                <th class="text-right">Due</th>
                                <th class="text-center">Action</th>
                            </tr>
                            </thead>
                            <tfoot>
                            <tr>
                                <th colspan="3" class="text-center">Total</th>
                                <th class="text-right"></th>
                                <th class="text-right"></th>
                                <th class="text-right"></th>
                                <th></th>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Modal (Simplified Copy from pos/index) -->
    <div class="modal fade" id="modal-pay" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Payment Receive</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="modal-customer-payment-form" method="POST" action="{{ route('sales.customer_payment_receive') }}">
                        @csrf
                        <input type="hidden" id="order_id" name="order_id">
                        <div class="form-group">
                            <label>Order No.</label>
                            <input type="text" readonly id="order_no" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Payment Mode <span class="text-danger">*</span></label>
                            <select name="payment_mode" id="payment_mode" class="form-control select2">
                                <option value="">Select Payment Mode</option>
                                @foreach($paymentModes as $paymentMode)
                                    <option value="{{ $paymentMode->id }}|{{ $paymentMode->payment_mode }}">{{ $paymentMode->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Total Due</label>
                                    <input type="text" readonly id="due_hidden" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Paid Amount <span class="text-danger">*</span></label>
                                    <input type="number" step="any" id="paid" name="paid" class="form-control">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" id="customer-payment-btn" class="btn btn-info">Save</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(function () {
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
                            url: "{{ route('corporate-sales.destroy', ['corporate_sale' => 'REPLACE_WITH_ID_HERE']) }}".replace('REPLACE_WITH_ID_HERE', id),
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

            var table = $('#table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('corporate-sales.datatable') }}",
                    data: function (d) {
                        d.customer   = $("#customer").val();
                        d.start_date = $("#start_date").val();
                        d.end_date   = $("#end_date").val();
                    }
                },
                "pagingType": "full_numbers",
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                columns: [
                    {data: 'order_no',      name: 'order_no'},
                    {data: 'date',          name: 'date'},
                    {data: 'customer_name', name: 'customer.name'},
                    {data: 'total',         name: 'total',  className: 'text-right',
                        render: function (data) { return jsNumberFormat(parseFloat(data).toFixed(2)); }},
                    {data: 'paid',          name: 'paid',   className: 'text-right',
                        render: function (data) { return jsNumberFormat(parseFloat(data).toFixed(2)); }},
                    {data: 'due',           name: 'due',    className: 'text-right',
                        render: function (data) { return jsNumberFormat(parseFloat(data).toFixed(2)); }},
                    {data: 'action',        name: 'action', orderable: false},
                ],
                order: [[0, 'desc']],
                "dom": 'lBfrtip',
                "buttons": datatableButtons(),
                "autoWidth": false,
                "colReorder": true,
                footerCallback: function (row, data, start, end, display) {
                    var api = this.api();
                    var intVal = function (i) {
                        return typeof i === 'string' ? i.replace(/[,$]/g, '') * 1 : (typeof i === 'number' ? i : 0);
                    };
                    var totalCol  = api.column(3, {page: 'current'}).data().reduce(function (a, b) { return intVal(a) + intVal(b); }, 0);
                    var paidCol   = api.column(4, {page: 'current'}).data().reduce(function (a, b) { return intVal(a) + intVal(b); }, 0);
                    var dueCol    = api.column(5, {page: 'current'}).data().reduce(function (a, b) { return intVal(a) + intVal(b); }, 0);
                    $(api.column(3).footer()).html(jsNumberFormat(totalCol.toFixed(2)));
                    $(api.column(4).footer()).html(jsNumberFormat(paidCol.toFixed(2)));
                    $(api.column(5).footer()).html(jsNumberFormat(dueCol.toFixed(2)));
                }
            });

            $('#search_btn, #customer, #start_date, #end_date').on('change', function () {
                table.ajax.reload();
            });

            $('body').on('click', '.customer-pay', function () {
                $("#order_id").val($(this).data('id'));
                $("#order_no").val($(this).data('order_no'));
                $("#due_hidden").val($(this).data('due'));
                $('#modal-pay').modal('show');
            });

            $('#customer-payment-btn').click(function () {
                preloaderToggle(true);
                var formData = new FormData(document.getElementById('modal-customer-payment-form'));
                $.ajax({
                    type: 'POST',
                    url: $('#modal-customer-payment-form').attr('action'),
                    data: formData, processData: false, contentType: false,
                    success: function (response) {
                        preloaderToggle(false);
                        if (response.status) { location.reload(); }
                        else { ajaxErrorMessage(response.message); }
                    },
                    error: function () {
                        preloaderToggle(false);
                        ajaxErrorMessage('Something went wrong. Please try again.');
                    }
                });
            });
        });
    </script>
@endsection
