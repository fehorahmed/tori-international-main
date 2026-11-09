@extends('layouts.app')
@section('title', 'FDR Accounts')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-default">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-file-invoice-dollar mr-2"></i> FDR Accounts List
                </h5>
                <div class="card-tools">
                    @if(auth()->user()->can('shomriddhi_fdr_account_create') || true)
                    <a href="{{ route('shomriddhi-fdr.accounts.create') }}" class="btn btn-sm btn-success">
                        <i class="fa fa-plus"></i> Open New Account
                    </a>
                    @endif
                </div>
            </div>

            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        {{ session('success') }}
                    </div>
                @endif
                <div class="table-responsive-sm">
                    <table class="table table-bordered table-striped" id="table" style="width: 100%">
                        <thead>
                            <tr>
                                <th>S/L</th>
                                <th>Account No</th>
                                <th>Customer Name</th>
                                <th>Scheme (Duration)</th>
                                <th>Principal Amount</th>
                                <th>Maturity Date</th>
                                <th>Payable at Maturity</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<!-- Encash Modal -->
<div class="modal fade" id="encashModal" tabindex="-1" role="dialog" aria-labelledby="encashModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="encashModalLabel">Encash FDR Account</h5>
                <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="encashForm">
                @csrf
                <div class="modal-body">
                    <p class="text-muted">Closing an active FDR account will calculate the final payout (Principal + Interest - Penalty) and generate a Payment Voucher.</p>
                    <input type="hidden" id="encash_account_id" name="account_id">
                    
                    <div class="row">
                        <div class="col-md-12 mb-2">
                            <div class="alert alert-info py-1 px-2 mb-0" id="encash_info_alert" style="display: none;"></div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Principal Amount</label>
                            <input type="text" id="encash_principal" class="form-control font-weight-bold" readonly>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Calculated Interest</label>
                            <input type="number" step="0.01" class="form-control text-success font-weight-bold bg-white" name="interest_amount" id="encash_interest" readonly value="0">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Penalty Deduction</label>
                            <input type="number" step="0.01" class="form-control text-danger font-weight-bold bg-white" name="penalty_amount" id="encash_penalty" readonly value="0">
                        </div>
                        <div class="col-md-12 mb-1">
                            <h5 class="text-right font-weight-bold border-top pt-2">Net Payable: <span id="encash_net_payable" class="text-primary">0.00</span></h5>
                        </div>
                        <div class="col-md-12 form-group mt-2">
                            <label for="encash_payment_mode">Payment Mode (Paid From) <span class="text-danger">*</span></label>
                            <select class="form-control select2" id="encash_payment_mode" name="payment_mode" required style="width: 100%;">
                                <option value="">-- Select Payment Mode --</option>
                                @foreach($paymentModes as $pm)
                                    <option value="{{ $pm->id }}">{{ $pm->name }} ({{ $pm->code }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning" id="btnConfirmEncash"><i class="fa fa-hand-holding-usd"></i> Confirm Encashment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        let table = $('#table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route('shomriddhi-fdr.accounts.datatable') }}',
            "pagingType": "full_numbers",
            "lengthMenu": [[10, 25, 50, -1],[10, 25, 50, "All"]],
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'fdr_no', name: 'fdr_no' },
                { data: 'customer', name: 'customer' },
                { data: 'scheme', name: 'scheme' },
                { data: 'principal_amount', name: 'principal_amount' },
                { data: 'maturity_date', name: 'maturity_date' },
                { data: 'payable_amount', name: 'payable_amount' },
                { data: 'status', name: 'status' },
                { data: 'action', name: 'action', orderable: false, searchable: false },
            ],
            "dom": 'lBfrtip',
            "buttons": datatableButtons(),
            "responsive": true, "autoWidth": false,
        });

        // Delete Account
        $('body').on('click', '.btn-delete', function () {
            let id = $(this).data('id');
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    preloaderToggle(true);
                    $.ajax({
                        method: "DELETE",
                        url: "{{ route('shomriddhi-fdr.accounts.destroy', ['account' => 'REPLACE_WITH_ID_HERE']) }}".replace('REPLACE_WITH_ID_HERE', id),
                        data: { id: id }
                    }).done(function( response ) {
                        preloaderToggle(false);
                        if (response.success) {
                            Swal.fire(
                                'Deleted!',
                                response.message,
                                'success'
                            ).then((result) => {
                                table.ajax.reload(null, false);
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: response.message || 'Something went wrong.',
                            });
                        }
                    });
                }
            });
        });

        if ($.fn.select2) {
            $('#encash_payment_mode').select2({
                dropdownParent: $('#encashModal')
            });
        }

        // Show Encash Modal
        $('body').on('click', '.btn-encash', function () {
            let id = $(this).data('id');
            let amount = $(this).data('amount');
            $('#encash_account_id').val(id);
            $('#encash_principal').val(amount);
            $('#encash_info_alert').hide();
            $('#encash_interest').val('0');
            $('#encash_penalty').val('0');
            $('#encash_net_payable').text('Loading...');
            $('#btnConfirmEncash').prop('disabled', true);
            
            // Fetch Calculated Data
            $.get('/admin/shomriddhi-fdr/accounts/' + id + '/encash-data', function(res) {
                if (res.success) {
                    $('#encash_interest').val(res.interest_amount);
                    $('#encash_penalty').val(res.penalty_amount);
                    $('#encash_net_payable').text('৳ ' + res.net_payable);
                    $('#btnConfirmEncash').prop('disabled', false);
                    
                    let infoMsg = '<strong>Days Passed:</strong> ' + res.days_passed;
                    if (res.is_premature) {
                        infoMsg += ' | <span class="text-danger">Premature Encashment (Penalty Applied)</span>';
                    }
                    $('#encash_info_alert').html(infoMsg).show();
                }
            });

            $('#encashModal').modal('show');
        });

        // Submit Encash Form
        $('#encashForm').submit(function(e) {
            e.preventDefault();
            let id = $('#encash_account_id').val();
            let paymentMode = $('#encash_payment_mode').val();
            let btn = $('#btnConfirmEncash');

            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');

            $.ajax({
                method: "POST",
                url: "/admin/shomriddhi-fdr/accounts/" + id + "/encash",
                data: { 
                    _token: '{{ csrf_token() }}',
                    payment_mode: paymentMode,
                    interest_amount: $('#encash_interest').val(),
                    penalty_amount: $('#encash_penalty').val()
                },
                success: function(response) {
                    if (response.success) {
                        $('#encashModal').modal('hide');
                        Swal.fire('Encashed!', response.message, 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        btn.prop('disabled', false).html('<i class="fa fa-hand-holding-usd"></i> Confirm Encashment');
                        Swal.fire('Error!', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html('<i class="fa fa-hand-holding-usd"></i> Confirm Encashment');
                    let msg = xhr.responseJSON ? xhr.responseJSON.message : 'Something went wrong.';
                    Swal.fire('Error!', msg, 'error');
                }
            });
        });

    });
</script>
@endsection
