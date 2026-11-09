@extends('layouts.app')
@section('title','Corporate Client Profile')
@section('style')
<style>
    .profile-card {
        border-radius: 15px;
        overflow: hidden;
        transition: transform 0.3s;
    }
    .profile-card:hover {
        transform: translateY(-5px);
    }
    .bg-gradient-custom {
        background: linear-gradient(45deg, #4b6cb7 0%, #182848 100%);
        color: white;
    }
    .info-box-custom {
        border-radius: 10px;
        padding: 15px;
        background: #fff;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }
</style>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="card profile-card shadow-lg border-0">
                <div class="card-header bg-gradient-custom text-center py-4">
                    <div class="position-relative d-inline-block">
                        <img class="img-circle elevation-3 border border-white"
                             src="{{ $corporateCustomer->image ? asset($corporateCustomer->image) : asset('img/no-image.webp') }}"
                             alt="Profile picture"
                             style="width: 120px; height: 120px; object-fit: cover;">
                        <span class="position-absolute border border-white rounded-circle {{ $corporateCustomer->status == 1 ? 'bg-success' : 'bg-danger' }}" 
                              style="width: 20px; height: 20px; bottom: 10px; right: 10px;"></span>
                    </div>
                    <h4 class="mt-3 mb-0 font-weight-bold text-white">{{ $corporateCustomer->name }}</h4>
                    <p class="mb-0 text-white opacity-75 small">Corporate Client</p>
                </div>
                <div class="card-body px-4">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted"><i class="fas fa-phone-alt mr-2"></i>Mobile</span>
                            <span class="font-weight-bold">{{ $corporateCustomer->mobile_no }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted"><i class="fas fa-money-bill-wave mr-2"></i>Opening Bal.</span>
                            <span class="font-weight-bold">৳{{ number_format($corporateCustomer->opening_balance, 2) }}</span>
                        </li>
                        @if($corporateCustomer->current_balance < 0)
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span class="text-muted"><i class="fas fa-wallet mr-2"></i>Advance Bal.</span>
                                <span class="text-success font-weight-bold">৳{{ number_format(abs($corporateCustomer->current_balance), 2) }}</span>
                            </li>
                        @elseif($corporateCustomer->current_balance > 0)
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span class="text-muted"><i class="fas fa-exclamation-circle mr-2"></i>Outstanding</span>
                                <span class="text-danger font-weight-bold">৳{{ number_format($corporateCustomer->current_balance, 2) }}</span>
                            </li>
                        @else
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span class="text-muted"><i class="fas fa-check-circle mr-2"></i>Current Bal.</span>
                                <span class="text-secondary font-weight-bold">৳0.00</span>
                            </li>
                        @endif
                    </ul>
                    <div class="mt-4">
                        <a href="{{ route('corporate-customer.edit', ['corporate_customer' => $corporateCustomer->id]) }}" 
                           class="btn btn-primary btn-block bg-gradient-primary rounded-pill shadow">
                            <i class="fas fa-edit mr-2"></i>Edit Profile
                        </a>
                    </div>
                </div>
            </div>

            <div class="info-box-custom">
                <h5 class="font-weight-bold border-bottom pb-2 mb-3"><i class="fas fa-map-marker-alt mr-2 text-info"></i>Location</h5>
                <div>
                    <label class="text-muted mb-0 small d-block">Full Address</label>
                    <p class="mb-0 text-dark" style="line-height: 1.4;">
                        @php
                            $fullAddress = [];
                            if ($corporateCustomer->village) $fullAddress[] = $corporateCustomer->village->name . ($corporateCustomer->village->bn_name ? ' (' . $corporateCustomer->village->bn_name . ')' : '');
                            if ($corporateCustomer->union) $fullAddress[] = $corporateCustomer->union->name . ' (U)';
                            if ($corporateCustomer->pourashava) $fullAddress[] = $corporateCustomer->pourashava->name . ' (P)';
                            if ($corporateCustomer->upazila) $fullAddress[] = $corporateCustomer->upazila->name;
                            if ($corporateCustomer->district) $fullAddress[] = $corporateCustomer->district->name;
                            if ($corporateCustomer->division) $fullAddress[] = $corporateCustomer->division->name;
                            echo implode(', ', $fullAddress) ?: 'N/A';
                        @endphp
                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-lg border-0" style="border-radius: 15px;">
                <div class="card-header bg-white p-3 border-bottom">
                    <h5 class="mb-0 font-weight-bold"><i class="fas fa-building mr-2 text-primary"></i>Corporate Details</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="p-4 border rounded bg-light text-center">
                                <h6 class="text-muted small uppercase font-weight-bold mb-2">Email</h6>
                                <p class="h6 mb-0 font-weight-bold">{{ $corporateCustomer->email ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="p-4 border rounded bg-light text-center">
                                <h6 class="text-muted small uppercase font-weight-bold mb-2">Total Sales</h6>
                                <p class="h6 mb-0 font-weight-bold">৳{{ number_format($corporateCustomer->total_amount, 2) }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-2">
                        <div class="col-sm-6">
                            <div class="card bg-gradient-info border-0 shadow rounded-lg">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <p class="mb-0 opacity-75">Order History</p>
                                            <h4 class="mb-0 font-weight-bold">View List</h4>
                                        </div>
                                        <i class="fas fa-list-alt fa-2x opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="card bg-gradient-dark border-0 shadow rounded-lg">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center text-white">
                                        <div>
                                            <p class="mb-0 opacity-75">Financial Ledger</p>
                                            <h4 class="mb-0 font-weight-bold">View Report</h4>
                                        </div>
                                        <i class="fas fa-file-invoice-dollar fa-2x opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

