@extends('layouts.print')
@section('title', 'DPS Account Ledger')
@section('content')
<div class="text-center mb-4">
    <h3 class="font-weight-bold">DPS Account Ledger</h3>
    <p class="mb-0">Date: {{ date('d-M-Y h:i A') }}</p>
</div>

<div class="row mb-4">
    <div class="col-6">
        <strong>DPS No:</strong> {{ $dpsAccount->dps_no }}<br>
        <strong>Customer Name:</strong> {{ $dpsAccount->customer->name ?? '' }}<br>
        <strong>Mobile:</strong> {{ $dpsAccount->customer->mobile_no ?? '' }}<br>
        <strong>Status:</strong> {{ $dpsAccount->status }}
    </div>
    <div class="col-6 text-right">
        <strong>Scheme:</strong> {{ $dpsAccount->scheme->name ?? '' }}<br>
        <strong>Opening Date:</strong> {{ \Carbon\Carbon::parse($dpsAccount->opening_date)->format('d-M-Y') }}<br>
        <strong>Maturity Date:</strong> {{ \Carbon\Carbon::parse($dpsAccount->maturity_date)->format('d-M-Y') }}<br>
        <strong>Inst. Amount:</strong> {{ number_format($dpsAccount->installment_amount, 2) }}
    </div>
</div>

<table class="table table-bordered table-sm">
    <thead class="bg-light">
        <tr>
            <th>Date</th>
            <th>Voucher No</th>
            <th>Type</th>
            <th>Deposit/Credit</th>
            <th>Withdraw/Debit</th>
            <th>Penalty Paid</th>
            <th>Balance</th>
        </tr>
    </thead>
    <tbody>
        @php
            $balance = 0;
            $totalCredit = 0;
            $totalDebit = 0;
            $totalPenalty = 0;
        @endphp
        @forelse($transactions as $txn)
            @php
                $credit = 0;
                $debit = 0;
                if(in_array($txn->type, ['Deposit', 'Installment'])) {
                    $credit = $txn->amount;
                    $balance += $credit;
                    $totalCredit += $credit;
                } else if ($txn->type == 'Encashment') {
                    $debit = $txn->amount;
                    $balance -= $debit;
                    $totalDebit += $debit;
                } else if ($txn->type == 'Penalty') {
                    // Penalty does not directly affect principal balance, it's just charged
                    $totalPenalty += $txn->penalty_amount;
                }
            @endphp
            <tr>
                <td class="text-center">{{ \Carbon\Carbon::parse($txn->date)->format('d-M-Y') }}</td>
                <td class="text-center">{{ $txn->voucher->voucher_no ?? '' }}</td>
                <td class="text-center">{{ $txn->type }}</td>
                <td class="text-right">{{ $credit > 0 ? number_format($credit, 2) : '-' }}</td>
                <td class="text-right">{{ $debit > 0 ? number_format($debit, 2) : '-' }}</td>
                <td class="text-right">{{ $txn->penalty_amount > 0 ? number_format($txn->penalty_amount, 2) : '-' }}</td>
                <td class="text-right font-weight-bold">{{ number_format($balance, 2) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center">No transactions found.</td>
            </tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr>
            <th colspan="3" class="text-right">Total:</th>
            <th class="text-right">{{ number_format($totalCredit, 2) }}</th>
            <th class="text-right">{{ number_format($totalDebit, 2) }}</th>
            <th class="text-right">{{ number_format($totalPenalty, 2) }}</th>
            <th class="text-right">{{ number_format($balance, 2) }}</th>
        </tr>
    </tfoot>
</table>
@endsection
