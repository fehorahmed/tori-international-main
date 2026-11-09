@extends('layouts.app')
@section('title','Review & Approve Loan')
@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="card card-default">
                <div class="card-header bg-gradient-success">
                    <h3 class="card-title">Loan Summary</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
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
                            <th>Method</th>
                            <td>{{ $loan->calculation_method }}</td>
                        </tr>
                        <tr>
                            <th>Receiving Account (Borrower)</th>
                            <td>{{ $loan->borrowerAccountHead->name ?? 'Not Selected' }}</td>
                        </tr>
                    </table>
                    
                    <div class="mt-4">
                        <form action="{{ route('company_loan.approve', ['loan' => $loan->id]) }}" method="POST" id="approve-form">
                            @csrf
                            <div class="form-group">
                                <label for="lender_account_head_id">Disbursement Account (Lender) <span class="text-danger">*</span></label>
                                <select name="lender_account_head_id" id="lender_account_head_id" class="form-control select2" required>
                                    <option value="">Select Account</option>
                                    @foreach($disbursementAccounts as $account)
                                        <option value="{{ $account->id }}">{{ $account->name }} ({{ $account->code }})</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <button type="button" class="btn btn-success btn-block bg-gradient-success btn-lg" onclick="confirmApproval()">
                                <i class="fa fa-check-circle"></i> Confirm Approval
                            </button>
                        </form>
                        <a href="{{ route('company_loan.index') }}" class="btn btn-default btn-block mt-2">Cancel</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card card-default">
                <div class="card-header bg-gradient-info">
                    <h3 class="card-title">Installment Schedule Preview</h3>
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
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($previewInstallments as $installment)
                                <tr>
                                    <td>{{ $installment->installment_no }}</td>
                                    <td>{{ $installment->due_date }}</td>
                                    <td>{{ number_format($installment->principal_amount, 2) }}</td>
                                    <td>{{ number_format($installment->interest_amount, 2) }}</td>
                                    <td>{{ number_format($installment->total_amount, 2) }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        function confirmApproval() {
            Swal.fire({
                title: 'Are you sure?',
                text: "This loan will be approved and installments will be generated!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Approve it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#approve-form').submit();
                }
            })
        }
    </script>
@endsection
