<form id="modal-supplier-payment-form" action="{{ route('purchase.supplier_payment.store') }}" method="POST">
    @csrf
    <input type="hidden" name="supplier_id" value="{{ $supplier->id }}">
    
    <div class="row mb-4">
        <!-- Summary Cards -->
        <div class="col-md-4">
            <div class="info-box bg-gradient-info">
                <span class="info-box-icon"><i class="fas fa-money-bill-wave"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Available Advance</span>
                    <span class="info-box-number" style="font-size: 1.5rem;">৳ {{ number_format($available_advance, 2) }}</span>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card card-outline card-primary h-100">
                <div class="card-header py-2">
                    <h3 class="card-title font-weight-bold">Payment Details</h3>
                </div>
                <div class="card-body py-2">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="payment_date">Date *</label>
                                <input type="text" name="date" id="payment_date" class="form-control business-date-picker" value="{{ date('Y-m-d') }}" required readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="payment_mode">Payment Mode *</label>
                                <select name="payment_mode" id="payment_mode" class="form-control select2" required>
                                    <option value="" data-balance="0">Select Payment Mode</option>
                                    @foreach($paymentModes as $paymentMode)
                                        <option value="{{ $paymentMode->id }}|{{ $paymentMode->payment_mode }}" data-balance="0">{{ $paymentMode->name }}- {{ $paymentMode->code }}</option>
                                    @endforeach
                                </select>
                                <!-- <small id="available_balance_display" class="form-text text-info font-weight-bold d-none"></small> -->
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="total_receive_amount">Total Payment Amount *</label>
                                <input type="number" name="total_receive_amount" id="total_receive_amount" class="form-control font-weight-bold text-success" step="0.01" min="0" placeholder="0.00" style="font-size: 1.1rem;">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row d-none" id="bank_details_section">

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="cheque_no">Cheque/Txn No</label>
                                <input type="text" name="cheque_no" id="cheque_no" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="cheque_date">Cheque Date</label>
                                <input type="text" name="cheque_date" id="cheque_date" class="form-control date-picker" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-2">
                        <div class="col-md-12">
                            <div class="form-group mb-0">
                                <label for="note">Note</label>
                                <input type="text" name="note" id="note" class="form-control" placeholder="Optional notes...">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Allocation Section -->
    <div class="card card-outline card-success">
        <div class="card-header py-2">
            <h3 class="card-title font-weight-bold">Payment Allocation</h3>
            <div class="card-tools">
                <span class="badge badge-info" style="font-size: 1rem;">Unallocated: ৳ <span id="unallocated_amount_display">0.00</span></span>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered table-striped m-0">
                <thead class="bg-light">
                    <tr>
                        <th width="5%" class="text-center">Select</th>
                        <th>Reference</th>
                        <th class="text-right">Total Amount</th>
                        <th class="text-right">Paid</th>
                        <th class="text-right">Due Amount</th>
                        <th class="text-right" width="20%">Allocated Payment</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Opening Due Row -->
                    @if($remaining_opening_due > 0)
                        <tr class="bg-warning-light" id="row_opening_due">
                            <td class="text-center">
                                <div class="custom-control custom-checkbox">
                                    <input class="custom-control-input allocation-checkbox" type="checkbox" id="chk_opening_due" checked>
                                    <label for="chk_opening_due" class="custom-control-label"></label>
                                </div>
                            </td>
                            <td class="font-weight-bold">Opening Due</td>
                            <td class="text-right">{{ number_format($remaining_opening_due, 2) }}</td>
                            <td class="text-right">0.00</td>
                            <td class="text-right font-weight-bold text-danger">{{ number_format($remaining_opening_due, 2) }}</td>
                            <td>
                                <input type="number" name="allocate_opening_due" id="allocate_opening_due" class="form-control form-control-sm text-right allocate-input" 
                                       data-due="{{ $remaining_opening_due }}" step="0.01" min="0" max="{{ $remaining_opening_due }}" placeholder="0.00">
                            </td>
                        </tr>
                    @endif
                    
                    <!-- Sales Orders Rows -->
                    @forelse($unpaidOrders as $order)
                        <tr class="order-row {{ $remaining_opening_due > 0 ? 'opacity-50' : '' }}">
                            <td class="text-center">
                                <div class="custom-control custom-checkbox">
                                    <input class="custom-control-input allocation-checkbox order-checkbox" type="checkbox" id="order_chk_{{ $order->id }}" {{ $remaining_opening_due > 0 ? 'disabled' : '' }}>
                                    <label for="order_chk_{{ $order->id }}" class="custom-control-label"></label>
                                </div>
                            </td>
                            <td>
                                <a href="{{ route('purchase.details', $order->id) }}" target="_blank">Order #{{ $order->order_no }}</a><br>
                                <small class="text-muted">{{ \Carbon\Carbon::parse($order->date)->format('d M Y') }}</small>
                            </td>
                            <td class="text-right">{{ number_format($order->total, 2) }}</td>
                            <td class="text-right">{{ number_format($order->paid, 2) }}</td>
                            <td class="text-right font-weight-bold text-danger">{{ number_format($order->due, 2) }}</td>
                            <td>
                                <input type="hidden" name="orders[{{ $order->id }}][id]" value="{{ $order->id }}">
                                <input type="number" name="orders[{{ $order->id }}][amount]" class="form-control form-control-sm text-right allocate-input order-allocate-input" 
                                       data-due="{{ $order->due }}" step="0.01" min="0" max="{{ $order->due }}" placeholder="0.00" readonly>
                            </td>
                        </tr>
                    @empty
                        @if($remaining_opening_due <= 0)
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <h5 class="text-success"><i class="fas fa-check-circle"></i> No pending orders due found!</h5>
                                </td>
                            </tr>
                        @endif
                    @endforelse
                    
                </tbody>
                <tfoot>
                    <tr class="bg-light">
                        <td colspan="5" class="text-right font-weight-bold">Total Allocated:</td>
                        <td class="text-right font-weight-bold text-primary" id="total_allocated_display">0.00</td>
                    </tr>
                    <tr>
                        <td colspan="5" class="text-right font-weight-bold text-success">Goes to Advance:</td>
                        <td class="text-right font-weight-bold text-success" id="goes_to_advance_display">0.00</td>
                    </tr>
                </tfoot>
            </table>
            @if($remaining_opening_due > 0)
                <div class="alert alert-warning m-2 text-center">
                    <i class="fas fa-exclamation-triangle"></i> <strong>Please clear Opening Due first.</strong> Order payments are locked until opening due is 0.
                </div>
            @endif
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-6 text-left">
            <button type="button" class="btn btn-secondary btn-lg" data-dismiss="modal"><i class="fas fa-times"></i> Close</button>
        </div>
        <div class="col-6 text-right">
            <button type="button" class="btn btn-success btn-lg" id="supplier-payment-btn"><i class="fas fa-save"></i> Process Payment</button>
        </div>
    </div>
</form>

<script>
    $(function() {
        let hasOpeningDue = {{ $remaining_opening_due > 0 ? 'true' : 'false' }};
        let openingDueAmount = {{ $remaining_opening_due }};
        
        function calculateAllocation() {
            let totalReceive = parseFloat($('#total_receive_amount').val()) || 0;
            
            let totalAllocated = 0;
            $('.allocate-input').each(function() {
                totalAllocated += parseFloat($(this).val()) || 0;
            });
            
            let unallocated = totalReceive - totalAllocated;
            let goesToAdvance = 0;
            
            if (unallocated < 0) {
                $('#unallocated_amount_display').removeClass('text-success text-white').addClass('text-danger');
                $('#supplier-payment-btn').prop('disabled', true);
                $('#goes_to_advance_display').html('0.00');
                $('#hidden_advance_amount').remove();
            } else {
                $('#unallocated_amount_display').removeClass('text-danger').addClass('text-white');
                
                // Check if they are trying to keep advance without clearing dues
                let totalDue = 0;
                $('.allocate-input').each(function() {
                    totalDue += parseFloat($(this).data('due')) || 0;
                });

                if (unallocated > 0.01 && totalAllocated < totalDue - 0.01) { 
                    $('#supplier-payment-btn').prop('disabled', true);
                    $('#goes_to_advance_display').html('<span class="text-danger" style="font-size: 0.8rem;">Clear dues first!</span>');
                    $('#hidden_advance_amount').remove();
                } else {
                    $('#supplier-payment-btn').prop('disabled', false);
                    goesToAdvance = unallocated;
                    $('#goes_to_advance_display').text(goesToAdvance.toFixed(2));
                    
                    // Add hidden input for advance amount
                    $('#hidden_advance_amount').remove();
                    if (goesToAdvance > 0) {
                        $('#modal-supplier-payment-form').append('<input type="hidden" id="hidden_advance_amount" name="advance_amount" value="' + goesToAdvance.toFixed(2) + '">');
                    }
                }
            }
            
            $('#total_allocated_display').text(totalAllocated.toFixed(2));
        }

        $('#payment_mode').change(function() {
            let paymentMode = $(this).val();
            let balance = $(this).find(':selected').data('balance');
            
            if(paymentMode) {
                // $('#available_balance_display').html('Available Balance: ৳ ' + parseFloat(balance).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})).removeClass('d-none');
                let valuesArray = paymentMode.split('|');
                let mode = valuesArray[1];
                if (mode == 1) { 
                    $('#bank_details_section').removeClass('d-none');
                } else {
                    $('#bank_details_section').addClass('d-none');
                    $('#cheque_no, #cheque_date').val('');
                }
            } else {
                // $('#available_balance_display').addClass('d-none');
                $('#bank_details_section').addClass('d-none');
            }
        });

        function autoAllocate() {
            let totalReceive = parseFloat($('#total_receive_amount').val()) || 0;
            let remainingToAllocate = totalReceive;

            // 1. Allocate to Opening Due if it exists
            if (hasOpeningDue) {
                if (remainingToAllocate >= openingDueAmount) {
                    $('#allocate_opening_due').val(openingDueAmount.toFixed(2));
                    $('#chk_opening_due').prop('checked', true);
                    remainingToAllocate -= openingDueAmount;
                    
                    // If opening due is fully covered, unlock orders
                    if (remainingToAllocate >= 0) {
                        $('.order-checkbox').prop('disabled', false);
                        $('.order-row').removeClass('opacity-50');
                    }
                } else {
                    if (remainingToAllocate > 0) {
                        $('#allocate_opening_due').val(remainingToAllocate.toFixed(2));
                        $('#chk_opening_due').prop('checked', true);
                    } else {
                        $('#allocate_opening_due').val('');
                        $('#chk_opening_due').prop('checked', false);
                    }
                    remainingToAllocate = 0;
                    
                    // Keep orders locked
                    $('.order-checkbox').prop('disabled', true).prop('checked', false);
                    $('.order-row').addClass('opacity-50');
                    $('.order-allocate-input').val('');
                }
            }

            // 2. Allocate to Orders sequentially
            $('.order-allocate-input').each(function() {
                if ($(this).closest('.order-row').find('.order-checkbox').is(':disabled')) return;
                
                let due = parseFloat($(this).data('due')) || 0;
                if (remainingToAllocate >= due) {
                    if(due > 0) {
                        $(this).val(due.toFixed(2));
                        $(this).closest('.order-row').find('.order-checkbox').prop('checked', true);
                    }
                    remainingToAllocate -= due;
                } else if (remainingToAllocate > 0) {
                    $(this).val(remainingToAllocate.toFixed(2));
                    $(this).closest('.order-row').find('.order-checkbox').prop('checked', true);
                    remainingToAllocate = 0;
                } else {
                    $(this).val('');
                    $(this).closest('.order-row').find('.order-checkbox').prop('checked', false);
                }
            });
            
            // 3. Remove manual Advance allocation row logic
            calculateAllocation();
        }

        $('#total_receive_amount').on('input', function() {
            autoAllocate();
        });

        $('.allocate-input').on('input', function() {
            let val = parseFloat($(this).val()) || 0;
            let due = parseFloat($(this).data('due')) || 0;
            
            if (val > due && due !== 999999999) { // 999999999 is for advance
                $(this).val(due);
                val = due;
            }
            
            let cb = $(this).closest('tr').find('.allocation-checkbox');
            if (cb.length) {
                cb.prop('checked', val > 0);
            }
            
            if ($(this).attr('id') === 'allocate_opening_due') {
                if (val >= openingDueAmount) {
                    $('.order-checkbox').prop('disabled', false);
                    $('.order-row').removeClass('opacity-50');
                } else {
                    $('.order-checkbox').prop('disabled', true).prop('checked', false);
                    $('.order-row').addClass('opacity-50');
                    $('.order-allocate-input').val('');
                }
            }
            
            calculateAllocation();
        });

        $('.allocation-checkbox').change(function() {
            let row = $(this).closest('tr');
            let input = row.find('.allocate-input');
            
            if ($(this).is(':checked')) {
                input.prop('readonly', false);
                let totalAvailable = parseFloat($('#total_receive_amount').val()) || 0;
                
                let currentAllocated = 0;
                $('.allocate-input').not(input).each(function() {
                    currentAllocated += parseFloat($(this).val()) || 0;
                });
                
                let unallocated = totalAvailable - currentAllocated;
                let due = parseFloat(input.data('due'));
                
                if (unallocated > 0) {
                    let fillAmount = Math.min(unallocated, due);
                    input.val(fillAmount.toFixed(2));
                }
                
                // If it's opening due and it's fully checked, unlock orders
                if ($(this).attr('id') === 'chk_opening_due' && parseFloat(input.val()) >= openingDueAmount) {
                    $('.order-checkbox').prop('disabled', false);
                    $('.order-row').removeClass('opacity-50');
                }
            } else {
                input.val('');
                if(!$(this).is('#chk_advance')) {
                    input.prop('readonly', true);
                }
                
                // If it's opening due and unchecked, lock orders
                if ($(this).attr('id') === 'chk_opening_due') {
                    $('.order-checkbox').prop('disabled', true).prop('checked', false);
                    $('.order-row').addClass('opacity-50');
                    $('.order-allocate-input').val('');
                }
            }
            calculateAllocation();
        });
        
        // Form submit logic handles saving the button via AJAX
        $('#supplier-payment-btn').click(function() {
            let totalReceive = parseFloat($('#total_receive_amount').val()) || 0;
            if(totalReceive <= 0) {
                alert("Please enter a valid receive amount.");
                return;
            }
            
            // Removed strict unallocated check because it's handled by 'goes to advance' now
            
            Swal.fire({
                title: 'Are you sure?',
                text: "Do you want to process this payment?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#d33',
                confirmButtonText: '<i class="fas fa-check"></i> Yes, Process it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#supplier-payment-btn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
                    preloaderToggle(true);
                    var formData = new FormData(document.getElementById('modal-supplier-payment-form'));
                    $.ajax({
                        type: 'POST',
                        url: $('#modal-supplier-payment-form').attr('action'),
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            preloaderToggle(false);
                            if (response.status){
                                $('#paymentModal').modal('hide');
                                ajaxSuccessMessage(response.message);
                                if ($('#table').length) {
                                    $('#table').DataTable().ajax.reload(null, false);
                                } else {
                                    location.reload();
                                }
                            } else {
                                $('#supplier-payment-btn').prop('disabled', false).html('<i class="fas fa-save"></i> Process Payment');
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: response.message,
                                });
                            }
                        },
                        error: function(xhr) {
                            preloaderToggle(false);
                            $('#supplier-payment-btn').prop('disabled', false).html('<i class="fas fa-save"></i> Process Payment');
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
