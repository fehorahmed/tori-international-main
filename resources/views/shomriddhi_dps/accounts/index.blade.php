@extends('layouts.app')
@section('title', 'DPS Accounts')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-default">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-piggy-bank mr-2"></i> DPS Accounts List
                </h5>
                <div class="card-tools">
                    <a href="{{ route('shomriddhi-dps.accounts.create') }}" class="btn btn-sm btn-success">
                        <i class="fa fa-plus"></i> Open New DPS Account
                    </a>
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
                    <table class="table table-bordered table-striped" id="table" style="width:100%">
                        <thead>
                            <tr>
                                <th>S/L</th>
                                <th>DPS No</th>
                                <th>Customer</th>
                                <th>Scheme</th>
                                <th>Installment</th>
                                <th>Progress</th>
                                <th>Next Installment</th>
                                <th>Maturity Date</th>
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

{{-- Pay Installment Modal --}}
<div class="modal fade" id="payInstallmentModal" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fa fa-money-bill-wave mr-2"></i> Pay DPS Installment</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="payInstallmentForm">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="pay_account_id">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Base Installment Amt.</label>
                            <input type="text" id="pay_base_amount" class="form-control" readonly>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Missed & Penalties</label>
                            <input type="text" id="pay_penalty_info" class="form-control text-danger" readonly>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>No. of Installments <span class="text-danger">*</span></label>
                            <input type="number" name="number_of_installments" id="pay_number_of_installments" class="form-control" min="1" value="1" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Penalty to Collect <span class="text-danger">*</span></label>
                            <input type="number" name="penalty_collected" id="pay_penalty_collected" class="form-control" step="0.01" value="0" required>
                        </div>
                        <div class="col-md-12 mb-2">
                            <h5 class="text-right font-weight-bold border-top pt-2">Total Payable Now: <span id="pay_total_payable" class="text-primary">0.00</span></h5>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Payment Date <span class="text-danger">*</span></label>
                            <input type="text" name="date" id="pay_date" class="form-control business-date-picker" value="{{ date('d-m-Y') }}" required autocomplete="off" readonly>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Payment Mode <span class="text-danger">*</span></label>
                            <select class="form-control select2" id="pay_payment_mode" name="payment_mode" required style="width:100%">
                                <option value="">-- Select Cash/Bank --</option>
                                @foreach($paymentModes as $pm)
                                    <option value="{{ $pm->id }}">{{ $pm->name }} ({{ $pm->code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12 form-group">
                            <label>Note (Optional)</label>
                            <input type="text" name="note" id="pay_note" class="form-control" placeholder="e.g. Installment for July 2025">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="btnConfirmPay"><i class="fa fa-check"></i> Confirm Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Encash Modal --}}
<div class="modal fade" id="encashModal" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title"><i class="fa fa-hand-holding-usd mr-2"></i> Encash DPS Account</h5>
                <button type="button" class="close text-dark" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="encashForm">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info py-1 px-2 mb-2" id="encash_info_alert" style="display:none;"></div>
                    <input type="hidden" id="encash_account_id">
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label>Total Deposited</label>
                            <input type="text" id="encash_principal" class="form-control font-weight-bold" readonly>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Interest Amount</label>
                            <input type="number" step="0.01" name="interest_amount" id="encash_interest" class="form-control text-success font-weight-bold bg-white" value="0">
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Penalty Deduction</label>
                            <input type="number" step="0.01" name="penalty_amount" id="encash_penalty" class="form-control text-danger font-weight-bold bg-white" value="0">
                            <small class="text-muted">You can edit to waive penalty</small>
                        </div>
                        <div class="col-md-12 mb-2">
                            <h5 class="text-right font-weight-bold border-top pt-2">Net Payable: <span id="encash_net_payable" class="text-primary">0.00</span></h5>
                        </div>
                        <div class="col-md-12 form-group">
                            <label>Payment Mode (Paid From) <span class="text-danger">*</span></label>
                            <select class="form-control select2" id="encash_payment_mode" name="payment_mode" required style="width:100%">
                                <option value="">-- Select Cash/Bank --</option>
                                @foreach($paymentModes as $pm)
                                    <option value="{{ $pm->id }}">{{ $pm->name }} ({{ $pm->code }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning" id="btnConfirmEncash" disabled><i class="fa fa-hand-holding-usd"></i> Confirm Encashment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    const table = $('#table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('shomriddhi-dps.accounts.datatable') }}',
        pagingType: 'full_numbers',
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'All']],
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'dps_no', name: 'dps_no' },
            { data: 'customer', name: 'customer' },
            { data: 'scheme', name: 'scheme' },
            { data: 'installment_amount', name: 'installment_amount' },
            { data: 'progress', name: 'progress', orderable: false },
            { data: 'next_installment_date', name: 'next_installment_date' },
            { data: 'maturity_date', name: 'maturity_date' },
            { data: 'status', name: 'status' },
            { data: 'action', name: 'action', orderable: false, searchable: false },
        ],
        dom: 'lBfrtip',
        buttons: datatableButtons(),
        responsive: true,
        autoWidth: false,
    });

    // ─── Select2 Init ───
    if ($.fn.select2) {
        $('#pay_payment_mode').select2({ dropdownParent: $('#payInstallmentModal') });
        $('#encash_payment_mode').select2({ dropdownParent: $('#encashModal') });
    }
    // ─── Pay Installment ───
    $('body').on('click', '.btn-pay-installment', function() {
        const id = $(this).data('id');
        $('#pay_account_id').val(id);
        $('#pay_note').val('');
        $('#btnConfirmPay').prop('disabled', true);
        
        $.get('{{ route('shomriddhi-dps.accounts.installment_data', ['account' => 'REPLACE']) }}'.replace('REPLACE', id), function(res) {
            if (res.success) {
                $('#pay_base_amount').val('৳ ' + parseFloat(res.installment_amount).toFixed(2));
                $('#pay_base_amount').data('raw', res.installment_amount);
                
                let penaltyText = res.missed_installments + ' Missed | ৳ ' + parseFloat(res.total_penalty_charged).toFixed(2) + ' Due';
                $('#pay_penalty_info').val(penaltyText);
                
                // Defaults
                $('#pay_number_of_installments').val(1);
                $('#pay_penalty_collected').val(parseFloat(res.total_penalty_charged).toFixed(2));
                
                calculatePayable();
                $('#btnConfirmPay').prop('disabled', false);
                $('#payInstallmentModal').modal('show');
            }
        });
    });

    function calculatePayable() {
        const baseAmount = parseFloat($('#pay_base_amount').data('raw')) || 0;
        const count = parseInt($('#pay_number_of_installments').val()) || 0;
        const penalty = parseFloat($('#pay_penalty_collected').val()) || 0;
        const total = (baseAmount * count) + penalty;
        $('#pay_total_payable').text('৳ ' + total.toFixed(2));
        
        let note = 'Collection for ' + count + ' installment(s)';
        if (penalty > 0) {
            note += ' including ৳ ' + penalty.toFixed(2) + ' penalty';
        }
        $('#pay_note').val(note);
    }

    $('#pay_number_of_installments, #pay_penalty_collected').on('input', calculatePayable);

    $('#payInstallmentForm').submit(function(e) {
        e.preventDefault();
        
        Swal.fire({
            title: 'Confirm Payment?',
            text: "Are you sure you want to receive this amount? The total payable is " + $('#pay_total_payable').text(),
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, receive it!'
        }).then((result) => {
            if (result.isConfirmed) {
                const id  = $('#pay_account_id').val();
                const btn = $('#btnConfirmPay');
                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');

                $.ajax({
                    method: 'POST',
                    url: '{{ route('shomriddhi-dps.accounts.pay_installment', ['account' => 'REPLACE']) }}'.replace('REPLACE', id),
                    data: {
                        _token: '{{ csrf_token() }}',
                        payment_mode: $('#pay_payment_mode').val(),
                        date: $('#pay_date').val(),
                        number_of_installments: $('#pay_number_of_installments').val(),
                        penalty_collected: $('#pay_penalty_collected').val(),
                        note: $('#pay_note').val(),
                    },
                    success: function(r) {
                        if (r.success) {
                            $('#payInstallmentModal').modal('hide');
                            let printUrl = '{{ route('shomriddhi-dps.transactions.print_voucher', 'REPLACE') }}'.replace('REPLACE', r.transaction_id);
                            
                            Swal.fire('Success!', r.message, 'success').then(() => {
                                window.open(printUrl, '_blank');
                                table.ajax.reload(null, false);
                            });
                        } else {
                            btn.prop('disabled', false).html('<i class="fa fa-check"></i> Confirm Payment');
                            Swal.fire('Error!', r.message, 'error');
                        }
                    },
                    error: function() {
                        btn.prop('disabled', false).html('<i class="fa fa-check"></i> Confirm Payment');
                        Swal.fire('Error!', 'Something went wrong.', 'error');
                    }
                });
            }
        });
    });

    // ─── Encash ───
    $('body').on('click', '.btn-encash', function() {
        const id = $(this).data('id');
        $('#encash_account_id').val(id);
        $('#encash_info_alert').hide();
        $('#encash_principal, #encash_interest, #encash_penalty').val('');
        $('#encash_net_payable').text('Loading...');
        $('#btnConfirmEncash').prop('disabled', true);

        $.get('{{ route('shomriddhi-dps.accounts.encash_data', ['account' => 'REPLACE']) }}'.replace('REPLACE', id), function(res) {
            if (res.success) {
                $('#encash_principal').val('৳ ' + parseFloat(res.principal).toFixed(2));
                $('#encash_principal').data('raw', res.principal);
                $('#encash_interest').val(res.interest_amount);
                $('#encash_penalty').val(res.penalty_amount);
                $('#encash_net_payable').text('৳ ' + parseFloat(res.total_payout).toFixed(2));
                $('#btnConfirmEncash').prop('disabled', false);

                let info = '<strong>Type:</strong> ' + res.type + ' | <strong>Months Elapsed:</strong> ' + res.months_elapsed;
                if (!res.is_mature) info += ' | <span class="text-danger font-weight-bold">⚠ Premature Withdrawal</span>';
                $('#encash_info_alert').html(info).show();
            } else {
                $('#encash_net_payable').text('Error');
                Swal.fire('Error!', res.message, 'error');
            }
        });

        $('#encashModal').modal('show');
    });

    // Recalculate net payable on input change
    $('#encash_interest, #encash_penalty').on('input', function() {
        const principal = parseFloat($('#encash_principal').data('raw')) || 0;
        const interest  = parseFloat($('#encash_interest').val()) || 0;
        const penalty   = parseFloat($('#encash_penalty').val()) || 0;
        const net       = principal + interest - penalty;
        $('#encash_net_payable').text('৳ ' + net.toFixed(2));
    });

    $('#encashForm').submit(function(e) {
        e.preventDefault();
        
        Swal.fire({
            title: 'Confirm Encashment?',
            text: "Are you sure you want to encash this account? The net payout is " + $('#encash_net_payable').text(),
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, encash it!'
        }).then((result) => {
            if (result.isConfirmed) {
                const id  = $('#encash_account_id').val();
                const btn = $('#btnConfirmEncash');
                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');

                $.ajax({
                    method: 'POST',
                    url: '{{ route('shomriddhi-dps.accounts.encash', ['account' => 'REPLACE']) }}'.replace('REPLACE', id),
                    data: {
                        _token: '{{ csrf_token() }}',
                        payment_mode: $('#encash_payment_mode').val(),
                        interest_amount: $('#encash_interest').val(),
                        penalty_amount: $('#encash_penalty').val(),
                    },
                    success: function(r) {
                        if (r.success) {
                            $('#encashModal').modal('hide');
                            Swal.fire('Encashed!', r.message, 'success').then(() => location.reload());
                        } else {
                            btn.prop('disabled', false).html('<i class="fa fa-hand-holding-usd"></i> Confirm Encashment');
                            Swal.fire('Error!', r.message, 'error');
                        }
                    },
                    error: function() {
                        btn.prop('disabled', false).html('<i class="fa fa-hand-holding-usd"></i> Confirm Encashment');
                        Swal.fire('Error!', 'Something went wrong.', 'error');
                    }
                });
            }
        });
    });

    // ─── Delete ───
    $('body').on('click', '.btn-delete', function() {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?', text: "This will delete the pending DPS account.",
            icon: 'warning', showCancelButton: true,
            confirmButtonColor: '#d33', confirmButtonText: 'Yes, delete!'
        }).then(result => {
            if (result.isConfirmed) {
                preloaderToggle(true);
                $.ajax({
                    method: 'DELETE',
                    url: '{{ route('shomriddhi-dps.accounts.destroy', ['account' => 'REPLACE']) }}'.replace('REPLACE', id),
                    data: { _token: '{{ csrf_token() }}' }
                }).done(r => {
                    preloaderToggle(false);
                    if (r.success) Swal.fire('Deleted!', r.message, 'success').then(() => table.ajax.reload(null, false));
                    else Swal.fire('Error!', r.message, 'error');
                });
            }
        });
    });
});
</script>
@endsection
