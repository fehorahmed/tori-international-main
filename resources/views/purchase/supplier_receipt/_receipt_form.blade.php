<form id="modal-supplier-receipt-form" action="{{ route('purchase.supplier_receipt.store') }}" method="POST">
    @csrf
    <input type="hidden" name="supplier_id" value="{{ $supplier->id }}">
    
    <div class="row mb-4">
        <!-- Summary Cards -->
        <div class="col-md-4">
            <div class="info-box bg-gradient-success">
                <span class="info-box-icon"><i class="fas fa-hand-holding-usd"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Current Balance</span>
                    @php
                        $balance = $supplier->supplier_current_balance;
                    @endphp
                    @if($balance > 0)
                        <span class="info-box-number" style="font-size: 1.2rem;">Due: ৳ {{ number_format($balance, 2) }}</span>
                    @elseif($balance < 0)
                        <span class="info-box-number" style="font-size: 1.2rem;">Advance: ৳ {{ number_format(abs($balance), 2) }}</span>
                    @else
                        <span class="info-box-number" style="font-size: 1.2rem;">৳ 0.00</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card card-outline card-success h-100">
                <div class="card-header py-2">
                    <h3 class="card-title font-weight-bold">Receive Details</h3>
                </div>
                <div class="card-body py-2">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="receipt_date">Date *</label>
                                <input type="text" name="date" id="receipt_date" class="form-control business-date-picker" value="{{ date('Y-m-d') }}" required readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="receipt_payment_mode">Receive Mode *</label>
                                <select name="payment_mode" id="receipt_payment_mode" class="form-control select2" required>
                                    <option value="">Select Receive Mode</option>
                                    @foreach($paymentModes as $paymentMode)
                                        <option value="{{ $paymentMode->id }}|{{ $paymentMode->payment_mode }}">{{ $paymentMode->name }}- {{ $paymentMode->code }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="receipt_amount">Receive Amount *</label>
                                <input type="number" name="amount" id="receipt_amount" class="form-control font-weight-bold text-success" step="0.01" min="0" placeholder="0.00" style="font-size: 1.1rem;" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row d-none" id="receipt_bank_details_section">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="receipt_cheque_no">Cheque/Txn No</label>
                                <input type="text" name="cheque_no" id="receipt_cheque_no" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="receipt_cheque_date">Cheque Date</label>
                                <input type="text" name="cheque_date" id="receipt_cheque_date" class="form-control date-picker" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-2">
                        <div class="col-md-12">
                            <div class="form-group mb-0">
                                <label for="receipt_note">Note</label>
                                <input type="text" name="note" id="receipt_note" class="form-control" placeholder="Optional notes...">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-6 text-left">
            <button type="button" class="btn btn-secondary btn-lg" data-dismiss="modal"><i class="fas fa-times"></i> Close</button>
        </div>
        <div class="col-6 text-right">
            <button type="button" class="btn btn-success btn-lg" id="supplier-receipt-btn"><i class="fas fa-save"></i> Process Receipt</button>
        </div>
    </div>
</form>

<script>
    $(function() {
        $('#receipt_payment_mode').change(function() {
            let paymentMode = $(this).val();
            
            if(paymentMode) {
                let valuesArray = paymentMode.split('|');
                let mode = valuesArray[1];
                if (mode == 1) { 
                    $('#receipt_bank_details_section').removeClass('d-none');
                } else {
                    $('#receipt_bank_details_section').addClass('d-none');
                    $('#receipt_cheque_no, #receipt_cheque_date').val('');
                }
            } else {
                $('#receipt_bank_details_section').addClass('d-none');
            }
        });

        $('#supplier-receipt-btn').click(function() {
            let amount = parseFloat($('#receipt_amount').val()) || 0;
            if(amount <= 0) {
                alert("Please enter a valid receive amount.");
                return;
            }
            
            Swal.fire({
                title: 'Are you sure?',
                text: "Do you want to process this receipt?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#d33',
                confirmButtonText: '<i class="fas fa-check"></i> Yes, Process it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#supplier-receipt-btn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
                    preloaderToggle(true);
                    var formData = new FormData(document.getElementById('modal-supplier-receipt-form'));
                    $.ajax({
                        type: 'POST',
                        url: $('#modal-supplier-receipt-form').attr('action'),
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            preloaderToggle(false);
                            if (response.status){
                                $('#receiptModal').modal('hide');
                                ajaxSuccessMessage(response.message);
                                if ($('#table').length) {
                                    $('#table').DataTable().ajax.reload(null, false);
                                } else {
                                    location.reload();
                                }
                            } else {
                                $('#supplier-receipt-btn').prop('disabled', false).html('<i class="fas fa-save"></i> Process Receipt');
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: response.message,
                                });
                            }
                        },
                        error: function(xhr) {
                            preloaderToggle(false);
                            $('#supplier-receipt-btn').prop('disabled', false).html('<i class="fas fa-save"></i> Process Receipt');
                            if (xhr.status === 422) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Validation Error',
                                    text: 'Please check your inputs and try again.',
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'System Error',
                                    text: 'An error occurred. Please check console.',
                                });
                                console.log(xhr.responseText);
                            }
                        }
                    });
                }
            });
        });
    });
</script>
