@extends('layouts.print')
@section('title', 'Overdue Installments Report')
@section('content')
<div class="text-center mb-4">
    <h3 class="font-weight-bold">Overdue Installments Report</h3>
    <p class="mb-0">As of {{ date('d-M-Y') }}</p>
    <p class="mb-0">Printed On: {{ date('d-M-Y h:i A') }}</p>
</div>

<table class="table table-bordered table-sm">
    <thead class="bg-light">
        <tr>
            <th>Due Date</th>
            <th>Loan Account</th>
            <th>Customer</th>
            <th>Inst. No</th>
            <th class="text-right">Inst. Amount</th>
            <th class="text-right">Paid</th>
            <th class="text-right">Due Amount</th>
            <th class="text-center">Days Overdue</th>
        </tr>
    </thead>
    <tbody>
        @php $totalDue = 0; @endphp
        @forelse($overdues as $overdue)
        @php 
            $dueAmount = $overdue->total_amount - $overdue->paid_amount;
            $totalDue += $dueAmount;
            $daysOverdue = \Carbon\Carbon::parse($overdue->due_date)->diffInDays(\Carbon\Carbon::today());
        @endphp
        <tr>
            <td class="text-center">{{ date('d-M-Y', strtotime($overdue->due_date)) }}</td>
            <td class="text-center">{{ $overdue->loanAccount->loan_no ?? 'N/A' }}</td>
            <td>{{ $overdue->loanAccount->customer->name ?? 'N/A' }}</td>
            <td class="text-center">#{{ $overdue->installment_no }}</td>
            <td class="text-right">{{ number_format($overdue->total_amount, 2) }}</td>
            <td class="text-right">{{ number_format($overdue->paid_amount, 2) }}</td>
            <td class="text-right font-weight-bold">{{ number_format($dueAmount, 2) }}</td>
            <td class="text-center">{{ $daysOverdue }} days</td>
        </tr>
        @empty
        <tr>
            <td colspan="8" class="text-center">No overdue installments found.</td>
        </tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr>
            <th colspan="6" class="text-right">Total Overdue Amount:</th>
            <th class="text-right font-weight-bold">{{ number_format($totalDue, 2) }}</th>
            <th></th>
        </tr>
    </tfoot>
</table>
@endsection
