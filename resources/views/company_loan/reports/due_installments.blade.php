@extends('layouts.app')
@section('title', 'Due Installments Report')
@section('content')
    <div class="row">
        <div class="col-md-12">
            <!-- Payable Dues (What you owe) -->
            <div class="card card-outline card-danger">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-arrow-circle-up text-danger"></i> Payable Dues (Installments You Need to Pay)</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-striped">
                        <thead class="bg-light">
                            <tr>
                                <th>Loan ID</th>
                                <th>Lender (Pay To)</th>
                                <th>Installment No</th>
                                <th>Due Date</th>
                                <th class="text-right">Principal</th>
                                <th class="text-right">Interest</th>
                                <th class="text-right">Total Due</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $hasPayable = false; @endphp
                            @foreach($installments as $installment)
                                @if(auth()->user()->sister_concern_id == $installment->loan->borrower_concern_id)
                                    @php $hasPayable = true; @endphp
                                    <tr>
                                        <td>{{ $installment->loan_id }}</td>
                                        <td>{{ $installment->loan->lender->name }}</td>
                                        <td>{{ $installment->installment_no }} / {{ $installment->loan->duration }}</td>
                                        <td class="{{ $installment->due_date < date('Y-m-d') ? 'text-danger font-weight-bold' : '' }}">
                                            {{ date('d-m-Y', strtotime($installment->due_date)) }}
                                        </td>
                                        <td class="text-right">{{ number_format($installment->principal_amount, 2) }}</td>
                                        <td class="text-right">{{ number_format($installment->interest_amount, 2) }}</td>
                                        <td class="text-right font-weight-bold">{{ number_format($installment->total_amount, 2) }}</td>
                                        <td>
                                            @if($installment->due_date < date('Y-m-d'))
                                                <span class="badge badge-danger">Overdue</span>
                                            @else
                                                <span class="badge badge-warning">Pending</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                            @if(!$hasPayable)
                                <tr><td colspan="8" class="text-center text-muted">No payable installments found.</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Receivable Dues (What others owe you) -->
            <div class="card card-outline card-success mt-4">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-arrow-circle-down text-success"></i> Receivable Dues (Installments You Will Receive)</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-striped">
                        <thead class="bg-light">
                            <tr>
                                <th>Loan ID</th>
                                <th>Borrower (Collect From)</th>
                                <th>Installment No</th>
                                <th>Due Date</th>
                                <th class="text-right">Principal</th>
                                <th class="text-right">Interest</th>
                                <th class="text-right">Total Due</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $hasReceivable = false; @endphp
                            @foreach($installments as $installment)
                                @if(auth()->user()->sister_concern_id == $installment->loan->lender_concern_id)
                                    @php $hasReceivable = true; @endphp
                                    <tr>
                                        <td>{{ $installment->loan_id }}</td>
                                        <td>{{ $installment->loan->borrower->name }}</td>
                                        <td>{{ $installment->installment_no }} / {{ $installment->loan->duration }}</td>
                                        <td class="{{ $installment->due_date < date('Y-m-d') ? 'text-danger font-weight-bold' : '' }}">
                                            {{ date('d-m-Y', strtotime($installment->due_date)) }}
                                        </td>
                                        <td class="text-right">{{ number_format($installment->principal_amount, 2) }}</td>
                                        <td class="text-right">{{ number_format($installment->interest_amount, 2) }}</td>
                                        <td class="text-right font-weight-bold text-success">{{ number_format($installment->total_amount, 2) }}</td>
                                        <td>
                                            @if($installment->due_date < date('Y-m-d'))
                                                <span class="badge badge-danger">Overdue</span>
                                            @else
                                                <span class="badge badge-warning">Pending</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                            @if(!$hasReceivable)
                                <tr><td colspan="8" class="text-center text-muted">No receivable installments found.</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
