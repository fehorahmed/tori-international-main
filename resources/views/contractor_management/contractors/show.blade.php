@extends('layouts.app')
@section('title', 'Contractor Details')

@section('content')
<div class="row">
    <div class="col-md-4">
        <!-- Profile Image -->
        <div class="card card-primary card-outline">
            <div class="card-body box-profile">
                <div class="text-center mb-3">
                    @if($contractor->image)
                        <img class="profile-user-img img-fluid img-circle"
                             src="{{ asset($contractor->image) }}"
                             alt="Contractor profile picture" style="height: 150px; width: 150px; object-fit: cover;">
                    @else
                        <img class="profile-user-img img-fluid img-circle"
                             src="{{ asset('img/avatar.png') }}"
                             alt="User profile picture">
                    @endif
                </div>

                <h3 class="profile-username text-center">{{ $contractor->name }}</h3>
                <p class="text-muted text-center">{{ $contractor->contractorType->name ?? 'N/A' }}</p>

                <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item">
                        <b>Mobile</b> <a class="float-right">{{ $contractor->mobile_no }}</a>
                    </li>
                    <li class="list-group-item">
                        <b>Status</b> <a class="float-right">{!! $contractor->status == 1 ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">Inactive</span>' !!}</a>
                    </li>
                    <li class="list-group-item">
                        <b>Account Code</b> <a class="float-right">{{ $contractor->accountHead->code ?? 'N/A' }}</a>
                    </li>
                    <li class="list-group-item">
                        <b>Current Balance</b> <a class="float-right font-weight-bold text-info">{{ number_format($contractor->accountHead ? $contractor->accountHead->getCurrentBalance(auth()->user()->activeConcernId()) : 0, 2) }}</a>
                    </li>
                </ul>
            </div>
            <!-- /.card-body -->
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header p-2">
                <ul class="nav nav-pills">
                    <li class="nav-item"><a class="nav-link active" href="#details" data-toggle="tab">General Information</a></li>
                    <li class="nav-item"><a class="nav-link" href="#ledger" data-toggle="tab">Ledger (Mini Statement)</a></li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <div class="active tab-pane" id="details">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <strong><i class="fas fa-building mr-1"></i> Company Name</strong>
                                <p class="text-muted">{{ $contractor->company_name ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong><i class="fas fa-id-card mr-1"></i> NID Number</strong>
                                <p class="text-muted">{{ $contractor->nid_number ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong><i class="fas fa-envelope mr-1"></i> Email</strong>
                                <p class="text-muted">{{ $contractor->email ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong><i class="fas fa-birthday-cake mr-1"></i> Date of Birth</strong>
                                <p class="text-muted">{{ $contractor->dob ? \Carbon\Carbon::parse($contractor->dob)->format('d M, Y') : 'N/A' }}</p>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-12">
                                <strong><i class="fas fa-map-marker-alt mr-1"></i> Address</strong>
                                <p class="text-muted mt-2">
                                    @if($contractor->address) {{ $contractor->address }}<br> @endif
                                    @if($contractor->village) {{ $contractor->village->name }}, @endif
                                    @if($contractor->union) {{ $contractor->union->name }} (Union), @endif
                                    @if($contractor->pourashava) {{ $contractor->pourashava->name }} (Pourashava), @endif
                                    @if($contractor->upazila) {{ $contractor->upazila->name }}, @endif
                                    @if($contractor->district) {{ $contractor->district->name }}, @endif
                                    @if($contractor->division) {{ $contractor->division->name }} @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane" id="ledger">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Voucher No</th>
                                        <th>Particulars</th>
                                        <th class="text-right">Debit</th>
                                        <th class="text-right">Credit</th>
                                        <th class="text-right">Balance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $balance = 0;
                                    @endphp
                                    @foreach($transactions as $transaction)
                                        @php
                                            if($transaction->transaction_type == 1) { // Debit
                                                $balance -= $transaction->amount;
                                            } else { // Credit
                                                $balance += $transaction->amount;
                                            }
                                        @endphp
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($transaction->date)->format('d-m-Y') }}</td>
                                            <td>{{ $transaction->voucher_no }}</td>
                                            <td>{{ $transaction->notes }}</td>
                                            <td class="text-right">{{ $transaction->transaction_type == 1 ? number_format($transaction->amount, 2) : '-' }}</td>
                                            <td class="text-right">{{ $transaction->transaction_type == 2 ? number_format($transaction->amount, 2) : '-' }}</td>
                                            <td class="text-right">{{ number_format($balance, 2) }}</td>
                                        </tr>
                                    @endforeach
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
