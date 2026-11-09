@extends('layouts.app')
@section('title','Client Profile')
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
        background: linear-gradient(45deg, #1d976c 0%, #93f9b9 100%);
        color: white;
    }
    .info-box-custom {
        border-radius: 10px;
        padding: 15px;
        background: #fff;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }
    .status-badge {
        font-size: 0.9rem;
        padding: 5px 12px;
        border-radius: 20px;
    }
</style>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-4">
            <!-- Profile Image Card -->
            <div class="card profile-card shadow-lg border-0">
                <div class="card-header bg-gradient-custom text-center py-4">
                    <div class="position-relative d-inline-block">
                        <img class="img-circle elevation-3 border border-white"
                             src="{{ $customer->image ? asset($customer->image) : asset('img/no-image.webp') }}"
                             alt="Client profile picture"
                             style="width: 120px; height: 120px; object-fit: cover;">
                        @if($customer->status == 1)
                            <span class="position-absolute border border-white rounded-circle bg-success" 
                                  style="width: 20px; height: 20px; bottom: 10px; right: 10px;" title="Active"></span>
                        @else
                            <span class="position-absolute border border-white rounded-circle bg-danger" 
                                  style="width: 20px; height: 20px; bottom: 10px; right: 10px;" title="Inactive"></span>
                        @endif
                    </div>
                    <h4 class="mt-3 mb-0 font-weight-bold text-dark">{{ $customer->name }}</h4>
                    <p class="mb-0 text-dark opacity-75 small">Code: {{ $customer->code ?? 'N/A' }}</p>
                </div>
                <div class="card-body px-4">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted"><i class="fas fa-phone-alt mr-2"></i>Mobile</span>
                            <span class="font-weight-bold">{{ $customer->mobile_no }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted"><i class="fas fa-money-bill-wave mr-2"></i>Opening Bal.</span>
                            <span class="font-weight-bold">৳{{ number_format($customer->opening_balance, 2) }}</span>
                        </li>
                        @if($customer->current_balance < 0)
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span class="text-muted"><i class="fas fa-wallet mr-2"></i>Advance Bal.</span>
                                <span class="text-success font-weight-bold">৳{{ number_format(abs($customer->current_balance), 2) }}</span>
                            </li>
                        @elseif($customer->current_balance > 0)
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span class="text-muted"><i class="fas fa-exclamation-circle mr-2"></i>Outstanding</span>
                                <span class="text-danger font-weight-bold">৳{{ number_format($customer->current_balance, 2) }}</span>
                            </li>
                        @else
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span class="text-muted"><i class="fas fa-check-circle mr-2"></i>Current Bal.</span>
                                <span class="text-secondary font-weight-bold">৳0.00</span>
                            </li>
                        @endif
                    </ul>
                    <div class="mt-4">
                        <a href="{{ route('customer.edit', ['customer' => $customer->id]) }}" 
                           class="btn btn-success btn-block bg-gradient-success rounded-pill shadow">
                            <i class="fas fa-edit mr-2"></i>Edit Profile
                        </a>
                    </div>
                </div>
            </div>

            <!-- Contact Box -->
            <div class="info-box-custom">
                <h5 class="font-weight-bold border-bottom pb-2 mb-3"><i class="fas fa-id-card mr-2 text-info"></i>Contact Info</h5>
                <div class="mb-3">
                    <label class="text-muted mb-0 small d-block">Email Address</label>
                    <span class="font-weight-500">{{ $customer->email ?? 'Not Available' }}</span>
                </div>
                <div>
                    <label class="text-muted mb-0 small d-block">Full Address</label>
                    <p class="mb-0 text-dark" style="line-height: 1.4;">
                        @php
                            $fullAddress = [];
                            if ($customer->village) $fullAddress[] = $customer->village->name . ($customer->village->bn_name ? ' (' . $customer->village->bn_name . ')' : '');
                            if ($customer->union) $fullAddress[] = $customer->union->name . ' (U)';
                            if ($customer->pourashava) $fullAddress[] = $customer->pourashava->name . ' (P)';
                            if ($customer->upazila) $fullAddress[] = $customer->upazila->name;
                            if ($customer->district) $fullAddress[] = $customer->district->name;
                            if ($customer->division) $fullAddress[] = $customer->division->name;
                            echo implode(', ', $fullAddress) ?: 'N/A';
                        @endphp
                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <!-- Stats Card -->
            <div class="card shadow-lg border-0" style="border-radius: 15px;">
                <div class="card-header bg-white p-3 border-bottom">
                    <h5 class="mb-0 font-weight-bold"><i class="fas fa-chart-line mr-2 text-primary"></i>Client Overview</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="p-4 border rounded bg-light">
                                <h6 class="text-muted small uppercase font-weight-bold mb-3">Father's Name</h6>
                                <p class="h5 mb-0 font-weight-bold text-dark">{{ $customer->father_name ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="p-4 border rounded bg-light">
                                <h6 class="text-muted small uppercase font-weight-bold mb-3">Mother's Name</h6>
                                <p class="h5 mb-0 font-weight-bold text-dark">{{ $customer->mother_name ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-sm-6">
                            <div class="card bg-gradient-info border-0 shadow rounded-lg">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <p class="mb-0 opacity-75">Sales History</p>
                                            <h3 class="mb-0 font-weight-bold">View Orders</h3>
                                        </div>
                                        <i class="fas fa-shopping-cart fa-2x opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="card bg-gradient-success border-0 shadow rounded-lg">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <p class="mb-0 opacity-75">Payment History</p>
                                            <h3 class="mb-0 font-weight-bold">View Ledger</h3>
                                        </div>
                                        <i class="fas fa-money-check-alt fa-2x opacity-50"></i>
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

