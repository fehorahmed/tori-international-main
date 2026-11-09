@extends('layouts.app')

@section('title', 'New Shomriddhi Advance Entry')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card card-default">
                <div class="card-header bg-gradient-success text-white">
                    <h5 class="card-title mb-0">
                        <i class="fa fa-plus-circle mr-2"></i> Deposit / Withdraw Form
                    </h5>
                </div>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <form action="{{ route('shomriddhi-advance.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label for="customer_id">Select Customer <span class="text-danger">*</span></label>
                                <select name="customer_id" id="customer_id" class="form-control select2" required style="width: 100%;">
                                    <option value="">Select a Customer</option>
                                    @foreach($accounts as $account)
                                        @if($account->customer)
                                            <option value="{{ $account->customer->id }}">{{ $account->customer->name }} ({{ $account->customer->member_no ?? 'No Member ID' }})</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-md-6 form-group">
                                <label for="date">Transaction Date <span class="text-danger">*</span></label>
                                <input type="text" name="date" id="date" class="form-control business-date-picker" value="{{ date('Y-m-d') }}" required readonly>
                            </div>
                        </div>

                        <!-- Customer Info Box -->
                        <div class="alert alert-info d-none" id="customer-info-box">
                            <strong><i class="fa fa-info-circle"></i> Customer Details:</strong>
                            <div class="row mt-2">
                                <div class="col-md-4">
                                    Member No: <br> <b id="info_member_no"></b>
                                </div>
                                <div class="col-md-4">
                                    Mobile: <br> <b id="info_mobile"></b>
                                </div>
                                <div class="col-md-3">
                                    Current Principal: <br> 
                                    <b id="info_balance" class="text-dark" style="font-size: 1.1em;"></b>
                                </div>
                                <div class="col-md-3">
                                    Accrued Interest: <br> 
                                    <b id="info_interest" class="text-success" style="font-size: 1.1em;"></b>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label for="type">Transaction Type <span class="text-danger">*</span></label>
                                <select name="type" id="type" class="form-control select2" required>
                                    <option value="deposit">Deposit (Cash In)</option>
                                    <option value="withdraw">Withdraw (Cash Out)</option>
                                </select>
                            </div>

                            <div class="col-md-6 form-group">
                                <label for="amount">Amount <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">৳</span>
                                    </div>
                                    <input type="number" name="amount" id="amount" class="form-control" step="0.01" min="1" required placeholder="0.00">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="note">Note / Reference</label>
                            <textarea name="note" id="note" class="form-control" rows="2" placeholder="Write any specific note..."></textarea>
                        </div>

                        <div class="form-group text-right">
                            <a href="{{ route('shomriddhi-advance.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-success" id="btn-submit">
                                <i class="fa fa-save"></i> Save Entry
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function () {
        $('.select2').select2();

        var currentBalance = 0;

        $('#customer_id').on('change', function () {
            var customerId = $(this).val();
            
            if (!customerId) {
                $('#customer-info-box').addClass('d-none');
                return;
            }

            $.ajax({
                url: "{{ route('shomriddhi-advance.customer_info') }}",
                type: "GET",
                data: { customer_id: customerId },
                success: function(response) {
                    if (response.success) {
                        $('#info_member_no').text(response.member_no || 'N/A');
                        $('#info_mobile').text(response.mobile || 'N/A');
                        
                        currentBalance = parseFloat(response.balance);
                        var currentInterest = parseFloat(response.accrued_interest);
                        
                        var balColor = currentBalance > 0 ? 'text-success' : (currentBalance < 0 ? 'text-danger' : 'text-dark');
                        $('#info_balance').html('<span>৳ ' + currentBalance.toFixed(2) + '</span>');
                        $('#info_interest').html('<span>৳ ' + currentInterest.toFixed(2) + '</span>');
                        
                        $('#customer-info-box').removeClass('d-none');
                    }
                }
            });
        });

        // SweetAlert Confirmation on Submit
        $('form').on('submit', function (e) {
            e.preventDefault();

            var form      = this;
            var type      = $('#type').val();
            var amount    = parseFloat($('#amount').val());
            var customer  = $('#customer_id option:selected').text();
            var typeLabel = type === 'deposit'
                ? '<span style="color:#28a745;font-weight:bold;">Deposit (জমা)</span>'
                : '<span style="color:#dc3545;font-weight:bold;">Withdraw (উত্তোলন)</span>';

            // Insufficient balance check for withdrawal
            if (type === 'withdraw' && amount > currentBalance) {
                Swal.fire({
                    icon: 'error',
                    title: 'Insufficient Balance!',
                    text: 'Customer only has ৳ ' + currentBalance.toFixed(2) + ' in savings. You cannot withdraw ৳ ' + amount.toFixed(2) + '.'
                });
                return false;
            }

            Swal.fire({
                title: 'Confirm Transaction?',
                html: `
                    <table class="table table-sm table-bordered mt-2 text-left">
                        <tr><td><b>Customer</b></td><td>${customer}</td></tr>
                        <tr><td><b>Type</b></td><td>${typeLabel}</td></tr>
                        <tr><td><b>Amount</b></td><td><b>৳ ${amount.toFixed(2)}</b></td></tr>
                    </table>
                    <small class="text-muted">This will be saved as <b>Pending</b> and require approval.</small>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: type === 'deposit' ? '#28a745' : '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fa fa-check"></i> Yes, Save Entry!',
                cancelButtonText: 'Cancel',
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
@endpush
