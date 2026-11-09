@extends('layouts.app')
@section('title', 'Collect DPS Installment')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card card-default">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fa fa-hand-holding-usd mr-2"></i> Receive DPS Installment</h5>
            </div>
            <div class="card-body">
                <div class="form-group mb-4 pb-4 border-bottom">
                    <label class="font-weight-bold">Select DPS Account <span class="text-danger">*</span></label>
                    <select class="form-control select2" id="dps_account_id" style="width:100%">
                        <option value="">-- Search by Member No, Name, DPS No, or Phone --</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->customer->member_no }} | {{ $acc->customer->name }} | {{ $acc->dps_no }} | {{ $acc->customer->mobile_no }}</option>
                        @endforeach
                    </select>
                </div>

                <form id="payInstallmentForm" style="display: none;">
                    @csrf
                    <input type="hidden" id="pay_account_id">
                    
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Base Installment Amt.</label>
                            <input type="text" id="pay_base_amount" class="form-control bg-light" readonly>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Missed & Penalties</label>
                            <input type="text" id="pay_penalty_info" class="form-control bg-light text-danger" readonly>
                        </div>
                        
                        <div class="col-md-6 form-group">
                            <label>No. of Installments <span class="text-danger">*</span></label>
                            <input type="number" name="number_of_installments" id="pay_number_of_installments" class="form-control" min="1" value="1" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Penalty to Collect <span class="text-danger">*</span></label>
                            <input type="number" name="penalty_collected" id="pay_penalty_collected" class="form-control" step="0.01" value="0" required>
                        </div>
                        
                        <div class="col-md-12 mb-3">
                            <h4 class="text-right font-weight-bold p-3 bg-light rounded border">Total Payable Now: <span id="pay_total_payable" class="text-primary">0.00</span></h4>
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
                            <input type="text" name="note" id="pay_note" class="form-control" placeholder="e.g. Collection for 1 installment(s)">
                        </div>
                    </div>

                    <div class="text-right mt-3">
                        <button type="submit" class="btn btn-primary btn-lg" id="btnConfirmPay"><i class="fa fa-check-circle"></i> Receive Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Preview Modal --}}
<div class="modal fade" id="previewModal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fa fa-eye mr-2"></i> Payment Preview</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered">
                    <tr>
                        <th width="40%">Account</th>
                        <td id="prev_account"></td>
                    </tr>
                    <tr>
                        <th>Installment(s)</th>
                        <td id="prev_installments"></td>
                    </tr>
                    <tr>
                        <th>Total Base</th>
                        <td id="prev_base_total"></td>
                    </tr>
                    <tr>
                        <th>Penalty Collected</th>
                        <td id="prev_penalty"></td>
                    </tr>
                    <tr class="bg-light">
                        <th><h5 class="mb-0 font-weight-bold">Total Payable</h5></th>
                        <td><h5 class="mb-0 font-weight-bold text-primary" id="prev_total_payable"></h5></td>
                    </tr>
                    <tr>
                        <th>Payment Mode</th>
                        <td id="prev_mode"></td>
                    </tr>
                </table>
                <div class="alert alert-warning mt-2 mb-0">
                    <i class="fa fa-info-circle mr-1"></i> This transaction will be saved as <strong>Pending</strong>.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Edit</button>
                <button type="button" class="btn btn-success" id="btnFinalSubmit"><i class="fa fa-check"></i> Final Confirm</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
$(document).ready(function() {
    $('.select2').select2();

    $('#dps_account_id').on('change', function() {
        const id = $(this).val();
        if (!id) {
            $('#payInstallmentForm').slideUp();
            return;
        }

        preloaderToggle(true);
        $('#pay_account_id').val(id);
        
        $.get('{{ route('shomriddhi-dps.accounts.installment_data', ['account' => 'REPLACE']) }}'.replace('REPLACE', id), function(res) {
            preloaderToggle(false);
            if (res.success) {
                $('#pay_base_amount').val('৳ ' + parseFloat(res.installment_amount).toFixed(2));
                $('#pay_base_amount').data('raw', res.installment_amount);
                
                let penaltyText = res.missed_installments + ' Missed | ৳ ' + parseFloat(res.total_penalty_charged).toFixed(2) + ' Due';
                $('#pay_penalty_info').val(penaltyText);
                
                $('#pay_number_of_installments').val(1);
                $('#pay_penalty_collected').val(parseFloat(res.total_penalty_charged).toFixed(2));
                
                calculatePayable();
                $('#payInstallmentForm').slideDown();
            } else {
                Swal.fire('Error', 'Could not fetch account data', 'error');
            }
        }).fail(function() {
            preloaderToggle(false);
            Swal.fire('Error', 'Something went wrong', 'error');
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
        
        // Populate preview modal
        const accountText = $('#dps_account_id option:selected').text();
        const baseAmount = parseFloat($('#pay_base_amount').data('raw')) || 0;
        const count = parseInt($('#pay_number_of_installments').val()) || 0;
        const penalty = parseFloat($('#pay_penalty_collected').val()) || 0;
        const total = (baseAmount * count) + penalty;
        const modeText = $('#pay_payment_mode option:selected').text();
        
        $('#prev_account').text(accountText);
        $('#prev_installments').text(count + ' installment(s)');
        $('#prev_base_total').text('৳ ' + (baseAmount * count).toFixed(2));
        $('#prev_penalty').text('৳ ' + penalty.toFixed(2));
        $('#prev_total_payable').text('৳ ' + total.toFixed(2));
        $('#prev_mode').text(modeText);
        
        $('#previewModal').modal('show');
    });

    $('#btnFinalSubmit').on('click', function() {
        Swal.fire({
            title: 'Confirm Payment?',
            text: "Are you sure you want to finalize this transaction?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, Finalize it!'
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
                            $('#previewModal').modal('hide');
                            Swal.fire('Success!', r.message, 'success').then(() => {
                                window.location.href = '{{ route('shomriddhi-dps.transactions.index') }}';
                            });
                        } else {
                            btn.prop('disabled', false).html('<i class="fa fa-check-circle"></i> Receive Payment');
                            Swal.fire('Error!', r.message, 'error');
                        }
                    },
                    error: function() {
                        btn.prop('disabled', false).html('<i class="fa fa-check-circle"></i> Receive Payment');
                        Swal.fire('Error!', 'Something went wrong.', 'error');
                    }
                });
            }
        });
    });
});
</script>
@endsection
