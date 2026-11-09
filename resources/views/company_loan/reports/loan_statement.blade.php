@extends('layouts.app')
@section('title', 'Loan Statement')
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title">Filter Loan Statement</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('company_loan.reports.statement') }}" method="GET">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Select Loan</label>
                                    <select name="loan_id" class="form-control select2" required>
                                        <option value="">Select a Loan</option>
                                        @foreach($loans as $loan)
                                            <option value="{{ $loan->id }}" {{ request('loan_id') == $loan->id ? 'selected' : '' }}>
                                                ID: {{ $loan->id }} | {{ $loan->lender->name }} to {{ $loan->borrower->name }} | {{ number_format($loan->amount, 2) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <button type="submit" class="btn btn-info btn-block bg-gradient-info">
                                        <i class="fa fa-search"></i> Search
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            @if($selectedLoan)
                <div class="card card-outline card-success">
                    <div class="card-header">
                        <h3 class="card-title">Statement for Loan ID: {{ $selectedLoan->id }}</h3>
                        <div class="card-tools">
                            <button onclick="window.print()" class="btn btn-default btn-sm"><i class="fa fa-print"></i> Print</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-sm-6">
                                <strong>Lender:</strong> {{ $selectedLoan->lender->name }}<br>
                                <strong>Borrower:</strong> {{ $selectedLoan->borrower->name }}<br>
                                <strong>Amount:</strong> {{ number_format($selectedLoan->amount, 2) }}
                            </div>
                            <div class="col-sm-6 text-right">
                                <strong>Interest Rate:</strong> {{ $selectedLoan->interest_rate }}%<br>
                                <strong>Duration:</strong> {{ $selectedLoan->duration }} {{ $selectedLoan->loan_type }}<br>
                                <strong>Start Date:</strong> {{ date('d-m-Y', strtotime($selectedLoan->start_date)) }}
                            </div>
                        </div>

                        <table class="table table-bordered table-striped">
                            <thead class="bg-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Voucher No</th>
                                    <th>Account Head</th>
                                    <th>Notes</th>
                                    <th class="text-right">Debit</th>
                                    <th class="text-right">Credit</th>
                                    <th class="text-right">Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php 
                                    $balance = 0; 
                                    $isLender = (auth()->user()->activeConcernId() == $selectedLoan->lender_concern_id);
                                @endphp
                                @foreach($transactions as $transaction)
                                    @php 
                                        if($isLender) {
                                            // Receivable perspective: Debit increases balance
                                            if($transaction->transaction_type == 1) $balance += $transaction->amount;
                                            else $balance -= $transaction->amount;
                                        } else {
                                            // Payable perspective: Credit increases balance
                                            if($transaction->transaction_type == 2) $balance += $transaction->amount;
                                            else $balance -= $transaction->amount;
                                        }
                                    @endphp
                                    <tr>
                                        <td>{{ date('d-m-Y', strtotime($transaction->date)) }}</td>
                                        <td>{{ $transaction->voucher->voucher_no ?? '' }}</td>
                                        <td>{{ $transaction->accountHead->name ?? '' }}</td>
                                        <td>{{ $transaction->notes }}</td>
                                        <td class="text-right">{{ $transaction->transaction_type == 1 ? number_format($transaction->amount, 2) : '' }}</td>
                                        <td class="text-right">{{ $transaction->transaction_type == 2 ? number_format($transaction->amount, 2) : '' }}</td>
                                        <td class="text-right font-weight-bold">{{ number_format(abs($balance), 2) }} {{ $balance >= 0 ? ($isLender ? 'DR' : 'CR') : ($isLender ? 'CR' : 'DR') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
@section('script')
    <script>
        $(function () {
            $('.select2').select2();
        });
    </script>
@endsection
