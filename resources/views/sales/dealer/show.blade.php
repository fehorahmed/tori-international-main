@extends('layouts.app')
@section('title','Dealer Profile')
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
        background: linear-gradient(45deg, #11998e 0%, #38ef7d 100%);
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
                             src="{{ $dealer->image ? asset($dealer->image) : asset('img/no-image.webp') }}"
                             alt="Profile picture"
                             style="width: 120px; height: 120px; object-fit: cover;">
                        <span class="position-absolute border border-white rounded-circle {{ $dealer->status == 1 ? 'bg-success' : 'bg-danger' }}" 
                              style="width: 20px; height: 20px; bottom: 10px; right: 10px;"></span>
                    </div>
                    <h4 class="mt-3 mb-0 font-weight-bold text-white">{{ $dealer->name }}</h4>
                    <p class="mb-0 text-white opacity-75 small">Dealer</p>
                </div>
                <div class="card-body px-4">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted"><i class="fas fa-phone-alt mr-2"></i>Mobile</span>
                            <span class="font-weight-bold">{{ $dealer->mobile_no }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted"><i class="fas fa-money-bill-wave mr-2"></i>Opening Bal.</span>
                            <span class="font-weight-bold">৳{{ number_format($dealer->opening_balance, 2) }}</span>
                        </li>
                        @if($dealer->current_balance < 0)
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span class="text-muted"><i class="fas fa-wallet mr-2"></i>Advance Bal.</span>
                                <span class="text-success font-weight-bold">৳{{ number_format(abs($dealer->current_balance), 2) }}</span>
                            </li>
                        @elseif($dealer->current_balance > 0)
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span class="text-muted"><i class="fas fa-exclamation-circle mr-2"></i>Outstanding</span>
                                <span class="text-danger font-weight-bold">৳{{ number_format($dealer->current_balance, 2) }}</span>
                            </li>
                        @else
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span class="text-muted"><i class="fas fa-check-circle mr-2"></i>Current Bal.</span>
                                <span class="text-secondary font-weight-bold">৳0.00</span>
                            </li>
                        @endif
                    </ul>
                    <div class="mt-4">
                        <a href="{{ route('dealer.edit', ['dealer' => $dealer->id]) }}" 
                           class="btn btn-success btn-block bg-gradient-success rounded-pill shadow">
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
                            if ($dealer->village) $fullAddress[] = $dealer->village->name . ($dealer->village->bn_name ? ' (' . $dealer->village->bn_name . ')' : '');
                            if ($dealer->union) $fullAddress[] = $dealer->union->name . ' (U)';
                            if ($dealer->pourashava) $fullAddress[] = $dealer->pourashava->name . ' (P)';
                            if ($dealer->upazila) $fullAddress[] = $dealer->upazila->name;
                            if ($dealer->district) $fullAddress[] = $dealer->district->name;
                            if ($dealer->division) $fullAddress[] = $dealer->division->name;
                            echo implode(', ', $fullAddress) ?: 'N/A';
                        @endphp
                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-lg border-0" style="border-radius: 15px;">
                <div class="card-header bg-white p-3 border-bottom">
                    <h5 class="mb-0 font-weight-bold"><i class="fas fa-user-tag mr-2 text-primary"></i>Dealer Details</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="p-4 border rounded bg-light text-center">
                                <h6 class="text-muted small uppercase font-weight-bold mb-2">Email</h6>
                                <p class="h6 mb-0 font-weight-bold">{{ $dealer->email ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="p-4 border rounded bg-light text-center">
                                <h6 class="text-muted small uppercase font-weight-bold mb-2">Total Purchases</h6>
                                <p class="h6 mb-0 font-weight-bold">৳{{ number_format($dealer->total_amount, 2) }}</p>
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
                                        <i class="fas fa-history fa-2x opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="card bg-gradient-success border-0 shadow rounded-lg">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <p class="mb-0 opacity-75">Ledger Account</p>
                                            <h4 class="mb-0 font-weight-bold">Statement</h4>
                                        </div>
                                        <i class="fas fa-file-invoice fa-2x opacity-50"></i>
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

