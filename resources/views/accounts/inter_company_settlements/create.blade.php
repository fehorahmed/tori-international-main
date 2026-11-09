@extends('layouts.app')
@section('title', 'Send Inter-Company Payment')
@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">Send Payment</h3>
            </div>
            <form id="paymentForm" action="{{ route('inter_company_settlements.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label>Date <span class="text-danger">*</span></label>
                        <input type="text" name="date" class="form-control date-picker" value="{{ date('d-m-Y') }}" required>
                    </div>

                    <div class="form-group">
                        <label>Pay To Branch <span class="text-danger">*</span></label>
                        <select name="to_sister_concern_id" id="to_sister_concern_id" class="form-control select2" required>
                            <option value="">Select Branch</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Current Payable Balance</label>
                        <input type="text" id="payable_balance" class="form-control" readonly value="0.00" style="background: #f4f6f9; font-weight: bold; color: #d33;">
                    </div>

                    <div class="form-group">
                        <label>Pay From (Bank/Cash) <span class="text-danger">*</span></label>
                        <select name="from_account_head_id" class="form-control select2" required>
                            <option value="">Select Account</option>
                            @foreach($cashBankHeads as $head)
                                <option value="{{ $head->id }}">{{ $head->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Payment Amount <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="amount" id="amount" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Notes</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="card-footer text-right">
                    <button type="button" class="btn btn-primary bg-gradient-primary" id="btn-show-modal">Dispatch Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Dispatch Payment</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to dispatch this payment?</p>
                <table class="table table-bordered">
                    <tr>
                        <th>To Branch</th>
                        <td id="modal-branch-name"></td>
                    </tr>
                    <tr>
                        <th>Amount</th>
                        <td id="modal-amount"></td>
                    </tr>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="btn-confirm-submit">Yes, Dispatch Payment</button>
            </div>
        </div>
    </div>
</div>

@endsection
@section('script')
<script>
    $(function () {
        let currentMaxBalance = 0;

        $('#to_sister_concern_id').change(function () {
            var branchId = $(this).val();
            if(branchId) {
                $.ajax({
                    url: '{{ route('inter_company_settlements.get_payable_balance') }}',
                    data: { target_concern_id: branchId },
                    success: function (res) {
                        if(res.success) {
                            currentMaxBalance = parseFloat(res.balance);
                            $('#payable_balance').val(currentMaxBalance.toFixed(2));
                        }
                    }
                });
            } else {
                currentMaxBalance = 0;
                $('#payable_balance').val('0.00');
            }
        });

        $('#btn-show-modal').click(function () {
            var amt = parseFloat($('#amount').val());
            var branchId = $('#to_sister_concern_id').val();
            var accountId = $('select[name="from_account_head_id"]').val();
            
            if (!branchId || !accountId || !amt || !$('input[name="date"]').val()) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Please fill out all required fields.'
                });
                return;
            }

            if (amt > currentMaxBalance) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Payment amount cannot exceed the payable balance of ' + currentMaxBalance.toFixed(2)
                });
                return;
            } else if (amt <= 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Amount must be greater than zero.'
                });
                return;
            }
            
            var branchName = $('#to_sister_concern_id option:selected').text();
            $('#modal-branch-name').text(branchName);
            $('#modal-amount').text(amt.toFixed(2));
            
            $('#confirmationModal').modal('show');
        });

        $('#btn-confirm-submit').click(function () {
            $(this).prop('disabled', true).text('Processing...');
            $('#paymentForm').submit();
        });
    });
</script>
@endsection
