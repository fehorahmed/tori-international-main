@extends('layouts.app')

@section('title', 'Card Payment Collection')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header bg-gradient-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="fa fa-money mr-2"></i> Card Payment Collection
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="collection-table" width="100%">
                            <thead class="bg-light">
                                <tr>
                                    <th width="10%">SL</th>
                                    <th>User Name</th>
                                    <th width="20%">Pending Cards</th>
                                    <th width="20%">Pending Amount</th>
                                    <th width="15%" class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- DataTables will populate this -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Receive Payment Modal -->
<div class="modal fade" id="receivePaymentModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-gradient-success text-white">
                <h5 class="modal-title">
                    <i class="fa fa-check-circle mr-2"></i> Receive Cash
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="receivePaymentForm">
                @csrf
                <input type="hidden" name="user_id" id="modal_user_id">
                <div class="modal-body">
                    <p><strong>User:</strong> <span id="modal_employee_name" class="text-primary"></span></p>
                    <p><strong>Total Pending Cards:</strong> <span id="modal_pending_cards" class="badge badge-warning"></span></p>
                    <p><strong>Total Amount to Receive:</strong> <span id="modal_pending_amount" class="badge badge-danger" style="font-size: 1.1em;"></span></p>
                    <hr>
                    <div class="form-group">
                        <label for="payment_mode">Receive In (Account) <span class="text-danger">*</span></label>
                        <select name="payment_mode" id="payment_mode" class="form-control select2" required style="width: 100%;">
                            <option value="">Select Account</option>
                            @foreach($paymentModes as $mode)
                                <option value="{{ $mode->id }}|{{ $mode->payment_mode }}">{{ $mode->name }} - {{ $mode->code }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success" id="btn-receive">
                        <i class="fa fa-check"></i> Confirm Receive
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('style')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css">
@endpush

@push('script')
<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function () {
        $('.select2').select2();

        var table = $('#collection-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('card-management.collection.datatable') }}",
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                {data: 'employee_name', name: 'name'},
                {data: 'pending_cards', name: 'pending_cards_count', searchable: false},
                {data: 'pending_amount', name: 'pending_amount_sum', searchable: false},
                {data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center'}
            ]
        });

        // Open Modal
        $(document).on('click', '.receive-btn', function () {
            var userId = $(this).data('id');
            var name = $(this).data('name');
            var count = $(this).data('count');
            var amount = $(this).data('amount');

            $('#modal_user_id').val(userId);
            $('#modal_employee_name').text(name);
            $('#modal_pending_cards').text(count + ' Cards');
            $('#modal_pending_amount').text('৳ ' + parseFloat(amount).toFixed(2));
            $('#payment_mode').val('').trigger('change');

            $('#receivePaymentModal').modal('show');
        });

        // Submit Form
        $('#receivePaymentForm').on('submit', function (e) {
            e.preventDefault();
            
            Swal.fire({
                title: 'Are you sure?',
                text: "You are about to receive this payment. This action cannot be undone!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Receive it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    var btn = $('#btn-receive');
                    var originalHtml = btn.html();
                    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');

                    $.ajax({
                        url: "{{ route('card-management.collection.receive') }}",
                        type: "POST",
                        data: $('#receivePaymentForm').serialize(),
                        success: function (response) {
                            if (response.success) {
                                $('#receivePaymentModal').modal('hide');
                                table.ajax.reload();
                                Swal.fire('Success!', response.message, 'success');
                            } else {
                                Swal.fire('Error!', response.message, 'error');
                            }
                        },
                        error: function (xhr) {
                            Swal.fire('Error!', 'Something went wrong.', 'error');
                        },
                        complete: function () {
                            btn.prop('disabled', false).html(originalHtml);
                        }
                    });
                }
            });
        });
    });
</script>
@endpush
