@extends('layouts.app')
@section('title','Loan Details')
@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="card card-default">
                <div class="card-header bg-gradient-info">
                    <h3 class="card-title">Loan Summary</h3>
                    <div class="card-tools">
                        @if($loan->status == 1 && $loan->borrower_concern_id == auth()->user()->activeConcernId())
                            <a href="{{ route('company_loan.edit', ['loan' => $loan->id]) }}" class="btn btn-info btn-sm"><i class="fa fa-edit"></i> Edit</a>
                        @endif
                        @if($loan->status == 1 && $loan->lender_concern_id == auth()->user()->activeConcernId())
                            <a href="{{ route('company_loan.approval_page', ['loan' => $loan->id]) }}" class="btn btn-success btn-sm"><i class="fa fa-check-circle"></i> Approve</a>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th>Lender</th>
                            <td>{{ $loan->lender->name ?? '' }}</td>
                        </tr>
                        <tr>
                            <th>Borrower</th>
                            <td>{{ $loan->borrower->name ?? '' }}</td>
                        </tr>
                        <tr>
                            <th>Amount</th>
                            <td>Tk {{ number_format($loan->amount, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Rate</th>
                            <td>{{ $loan->interest_rate }}%</td>
                        </tr>
                        <tr>
                            <th>Type</th>
                            <td>{{ $loan->loan_type }}</td>
                        </tr>
                        <tr>
                            <th>Duration</th>
                            <td>{{ $loan->duration }} Installments</td>
                        </tr>
                        <tr>
                            <th>Method</th>
                            <td>{{ $loan->calculation_method }}</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                @php
                                    $status = [
                                        1 => '<span class="badge badge-warning">Pending</span>',
                                        2 => '<span class="badge badge-success">Approved</span>',
                                        3 => '<span class="badge badge-danger">Rejected</span>',
                                        4 => '<span class="badge badge-info">Completed</span>',
                                    ];
                                    echo $status[$loan->status] ?? '';
                                @endphp
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card card-default">
                <div class="card-header bg-gradient-info">
                    <h3 class="card-title">Installment Schedule</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                            <tr>
                                <th>No</th>
                                <th>Due Date</th>
                                <th>Principal</th>
                                <th>Interest</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($loan->installments as $installment)
                                <tr>
                                    <td>{{ $installment->installment_no }}</td>
                                    <td>{{ date('d-m-Y', strtotime($installment->due_date)) }}</td>
                                    <td>{{ number_format($installment->principal_amount, 2) }}</td>
                                    <td>{{ number_format($installment->interest_amount, 2) }}</td>
                                    <td>{{ number_format($installment->total_amount, 2) }}</td>
                                    <td>
                                        @if($installment->status == 1)
                                            <span class="badge badge-warning">Unpaid</span>
                                        @else
                                            <span class="badge badge-success">Paid</span>
                                            <br><small>{{ date('d-m-Y', strtotime($installment->payment_date)) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($installment->status == 1 && $loan->borrower_concern_id == auth()->user()->activeConcernId())
                                            <button type="button" class="btn btn-primary btn-sm btn-pay" 
                                                data-id="{{ $installment->id }}" 
                                                data-no="{{ $installment->installment_no }}"
                                                data-amount="{{ number_format($installment->total_amount, 2) }}">
                                                Pay
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">Installments will be generated after approval.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Modal -->
    <div class="modal fade" id="paymentModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="paymentForm" method="POST" action="">
                    @csrf
                    <div class="modal-header bg-primary">
                        <h4 class="modal-title">Pay Installment #<span id="modalInstallmentNo"></span></h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Amount to Pay</label>
                            <input type="text" class="form-control" id="modalAmount" readonly>
                        </div>
                        <div class="form-group">
                            <label>Payment Date <span class="text-danger">*</span></label>
                            <input type="text" name="payment_date" class="form-control date-picker" value="{{ date('d-m-Y') }}" required autocomplete="off">
                        </div>
                        <div class="form-group">
                            <label>Payment Method (Cash/Bank) <span class="text-danger">*</span></label>
                            <select name="payment_account_head_id" class="form-control select2" style="width: 100%;" required>
                                <option value="">Select Account</option>
                                @foreach($paymentAccountHeads as $head)
                                    <option value="{{ $head->id }}">{{ $head->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Confirm Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(function () {
            $('.select2').select2();
            $('.date-picker').datepicker({
                autoclose: true,
                format: 'dd-mm-yyyy',
                todayHighlight: true
            });

            $('.btn-pay').on('click', function() {
                var id = $(this).data('id');
                var no = $(this).data('no');
                var amount = $(this).data('amount');
                
                $('#modalInstallmentNo').text(no);
                $('#modalAmount').val(amount);
                
                var url = "{{ route('company_loan.pay_installment', ['installment' => ':id']) }}";
                url = url.replace(':id', id);
                $('#paymentForm').attr('action', url);
                
                $('#paymentModal').modal('show');
            });

            $('#paymentForm').on('submit', function(e) {
                e.preventDefault();
                var form = this;
                
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You are about to confirm this payment!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, Confirm it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>
@endsection
