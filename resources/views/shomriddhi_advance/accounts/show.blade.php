@extends('layouts.app')

@section('title', $pageTitle)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">{{ $pageTitle }} - {{ $account->account_number }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('shomriddhi-advance.accounts.index') }}" class="btn btn-sm btn-default"><i class="fa fa-arrow-left"></i> Back</a>
                    </div>
                </div>
                <div class="card-body">
                    
                    <div class="row">
                        <!-- Account Information -->
                        <div class="col-md-6">
                            <h5 class="text-primary border-bottom pb-2"><i class="fas fa-file-invoice-dollar"></i> Account Information</h5>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <th width="40%">Account Number</th>
                                    <td>: {{ $account->account_number }}</td>
                                </tr>
                                <tr>
                                    <th>Opening Date</th>
                                    <td>: {{ \Carbon\Carbon::parse($account->opening_date)->format('d M, Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>: <span class="badge {{ $account->status == 'Active' ? 'badge-success' : 'badge-danger' }}">{{ $account->status }}</span></td>
                                </tr>
                                <tr>
                                    <th>Principal Balance</th>
                                    <td>: ৳{{ number_format($account->principal_balance, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Accrued Interest</th>
                                    <td>: ৳{{ number_format($account->accrued_interest, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Total Balance</th>
                                    <td>: ৳{{ number_format($account->principal_balance + $account->accrued_interest, 2) }}</td>
                                </tr>
                            </table>
                        </div>

                        <!-- Customer Information -->
                        <div class="col-md-6">
                            <h5 class="text-primary border-bottom pb-2"><i class="fas fa-user"></i> Customer Details</h5>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <th width="40%">Name</th>
                                    <td>: {{ $account->customer->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Member No</th>
                                    <td>: {{ $account->customer->member_no ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Phone Number</th>
                                    <td>: {{ $account->customer->mobile_no ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Father/Husband Name</th>
                                    <td>: {{ $account->customer->father_name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Mother Name</th>
                                    <td>: {{ $account->customer->mother_name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>NID Number</th>
                                    <td>: {{ $account->customer->nid_number ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Address</th>
                                    <td>: 
                                        {{ $account->customer->village_id ? $account->customer->village->name . ', ' : '' }}
                                        {{ $account->customer->union_id ? $account->customer->union->name . ', ' : '' }}
                                        {{ $account->customer->upazila_id ? $account->customer->upazila->name . ', ' : '' }}
                                        {{ $account->customer->district_id ? $account->customer->district->name : '' }}
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h5 class="text-primary border-bottom pb-2"><i class="fas fa-user-friends"></i> Nominees Information</h5>
                            @if($account->nominees->count() > 0)
                                <table class="table table-bordered table-striped">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Nominee Name</th>
                                            <th>Relation</th>
                                            <th>Phone Number</th>
                                            <th>NID Number</th>
                                            <th>Share (%)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($account->nominees as $index => $shomriddhiNominee)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $shomriddhiNominee->nominee->name ?? 'N/A' }}</td>
                                                <td>{{ $shomriddhiNominee->nominee->relation ?? 'N/A' }}</td>
                                                <td>{{ $shomriddhiNominee->nominee->phone ?? 'N/A' }}</td>
                                                <td>{{ $shomriddhiNominee->nominee->nid_number ?? 'N/A' }}</td>
                                                <td>{{ $shomriddhiNominee->share_percentage }}%</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <div class="alert alert-warning">
                                    No nominees found for this account.
                                </div>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
