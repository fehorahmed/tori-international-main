@extends('layouts.print')
@section('title', 'FDR Account Ledger')

@section('content')
<div class="report-header">
    <h3 class="text-center">{{ auth()->user()->active_sister_concern->name ?? 'Company Name' }}</h3>
    <h5 class="text-center">FDR Account Ledger (Passbook)</h5>
</div>

<div class="row mb-4">
    <div class="col-6">
        <strong>Account No:</strong> {{ $fdrAccount->fdr_no }} <br>
        <strong>Customer Name:</strong> {{ $fdrAccount->customer ? $fdrAccount->customer->name : 'N/A' }} <br>
        <strong>Phone:</strong> {{ $fdrAccount->customer ? $fdrAccount->customer->phone : 'N/A' }} <br>
        <strong>Scheme:</strong> {{ $fdrAccount->scheme ? $fdrAccount->scheme->name : 'N/A' }}
    </div>
    <div class="col-6 text-right">
        <strong>Opening Date:</strong> {{ \Carbon\Carbon::parse($fdrAccount->opening_date)->format('d-M-Y') }} <br>
        <strong>Maturity Date:</strong> {{ \Carbon\Carbon::parse($fdrAccount->maturity_date)->format('d-M-Y') }} <br>
        <strong>Principal Amount:</strong> ৳ {{ number_format($fdrAccount->principal_amount, 2) }} <br>
        <strong>Status:</strong> <span class="badge badge-info">{{ $fdrAccount->status }}</span>
    </div>
</div>

<table class="table table-bordered table-sm table-striped mt-3">
    <thead>
        <tr>
            <th>Date</th>
            <th>Transaction Type</th>
            <th>Note</th>
            <th class="text-right">Deposit / Addition (+)</th>
            <th class="text-right">Withdraw / Deduction (-)</th>
            <th class="text-right">Balance</th>
        </tr>
    </thead>
    <tbody>
        @php
            $balance = 0;
            $totalDeposit = 0;
            $totalWithdraw = 0;
        @endphp
        @forelse($transactions as $txn)
            @php
                $deposit = 0;
                $withdraw = 0;
                
                if (in_array($txn->type, ['Deposit', 'Interest Payout'])) {
                    $deposit = $txn->amount;
                    $balance += $deposit;
                    $totalDeposit += $deposit;
                } elseif (in_array($txn->type, ['Encashment', 'Penalty'])) {
                    $withdraw = $txn->amount;
                    $balance -= $withdraw;
                    $totalWithdraw += $withdraw;
                }
            @endphp
            <tr>
                <td>{{ \Carbon\Carbon::parse($txn->date)->format('d-M-Y') }}</td>
                <td>{{ $txn->type }}</td>
                <td>{{ $txn->note ?? '-' }}</td>
                <td class="text-right text-success">{{ $deposit > 0 ? number_format($deposit, 2) : '-' }}</td>
                <td class="text-right text-danger">{{ $withdraw > 0 ? number_format($withdraw, 2) : '-' }}</td>
                <td class="text-right font-weight-bold">{{ number_format($balance, 2) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center">No transactions found for this account.</td>
            </tr>
        @endforelse
    </tbody>
    @if($transactions->count() > 0)
    <tfoot>
        <tr>
            <th colspan="3" class="text-right">Total:</th>
            <th class="text-right text-success">{{ number_format($totalDeposit, 2) }}</th>
            <th class="text-right text-danger">{{ number_format($totalWithdraw, 2) }}</th>
            <th class="text-right">{{ number_format($balance, 2) }}</th>
        </tr>
    </tfoot>
    @endif
</table>
@endsection
