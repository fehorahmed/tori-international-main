@extends('layouts.app')
@section('title', 'Loan Account Details')
@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">Loan Summary</h3>
            </div>
            <div class="card-body box-profile">
                <h3 class="profile-username text-center">{{ $loan->loan_no }}</h3>
                <p class="text-muted text-center">{{ $loan->customer->name ?? 'N/A' }}</p>
                
                <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item">
                        <b>Type</b> <a class="float-right text-capitalize">{{ $loan->type }}</a>
                    </li>
                    <li class="list-group-item">
                        <b>Principal</b> <a class="float-right">৳ {{ number_format($loan->principal_amount, 2) }}</a>
                    </li>
                    <li class="list-group-item">
                        <b>Profit Rate</b> <a class="float-right">{{ $loan->profit_rate }}%</a>
                    </li>
                    <li class="list-group-item">
                        <b>Total Payable</b> <a class="float-right font-weight-bold">৳ {{ number_format($loan->total_payable, 2) }}</a>
                    </li>
                    <li class="list-group-item">
                        <b>Total Paid</b> <a class="float-right text-success font-weight-bold">৳ {{ number_format($loan->paid_amount, 2) }}</a>
                    </li>
                    <li class="list-group-item">
                        <b>Remaining Due</b> <a class="float-right text-danger font-weight-bold">৳ {{ number_format($loan->total_payable - $loan->paid_amount, 2) }}</a>
                    </li>
                    <li class="list-group-item">
                        <b>Status</b> 
                        <a class="float-right">
                            @if($loan->status == 'pending')
                                <span class="badge badge-warning">Pending</span>
                            @elseif($loan->status == 'active')
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-secondary">{{ ucfirst($loan->status) }}</span>
                            @endif
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-header p-2">
                <ul class="nav nav-pills">
                    <li class="nav-item"><a class="nav-link active" href="#schedule" data-toggle="tab">Amortization Schedule</a></li>
                    <li class="nav-item"><a class="nav-link" href="#collections" data-toggle="tab">Collection History</a></li>
                </ul>
            </div>
            <div class="card-body p-0">
                <div class="tab-content">
                    <div class="active tab-pane p-3" id="schedule">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="bg-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Due Date</th>
                                        <th class="text-right">Principal</th>
                                        <th class="text-right">Profit</th>
                                        <th class="text-right">Total Installment</th>
                                        <th class="text-right">Paid</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($loan->installments as $inst)
                                    <tr>
                                        <td>{{ $inst->installment_no }}</td>
                                        <td>{{ date('d M Y', strtotime($inst->due_date)) }}</td>
                                        <td class="text-right">{{ number_format($inst->principal_component, 2) }}</td>
                                        <td class="text-right">{{ number_format($inst->profit_component, 2) }}</td>
                                        <td class="text-right font-weight-bold">{{ number_format($inst->total_amount, 2) }}</td>
                                        <td class="text-right text-success">{{ number_format($inst->paid_amount, 2) }}</td>
                                        <td>
                                            @if($inst->status == 'paid')
                                                <span class="badge badge-success">Paid</span>
                                            @elseif($inst->status == 'partial')
                                                <span class="badge badge-warning">Partial</span>
                                            @elseif($inst->status == 'overdue')
                                                <span class="badge badge-danger">Overdue</span>
                                            @else
                                                <span class="badge badge-secondary">Unpaid</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane p-3" id="collections">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Voucher</th>
                                        <th>Mode</th>
                                        <th class="text-right">Amount</th>
                                        <th>Collected By</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($loan->transactions as $txn)
                                    <tr>
                                        <td>{{ date('d M Y', strtotime($txn->collection_date)) }}</td>
                                        <td>{{ $txn->voucher->voucher_no ?? 'N/A' }}</td>
                                        <td>{{ $txn->payment_mode }}</td>
                                        <td class="text-right text-success font-weight-bold">{{ number_format($txn->amount, 2) }}</td>
                                        <td>{{ $txn->collectedBy->name ?? 'N/A' }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No collections found.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
