@extends('layouts.app')
@section('title','Supplier Profile')
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
        background: linear-gradient(45deg, #1e3c72 0%, #2a5298 100%);
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
                             src="{{ $supplier->image ? asset($supplier->image) : asset('img/no-image.webp') }}"
                             alt="Supplier profile picture"
                             style="width: 120px; height: 120px; object-fit: cover;">
                        @if($supplier->status == 1)
                            <span class="position-absolute border border-white rounded-circle bg-success" 
                                  style="width: 20px; height: 20px; bottom: 10px; right: 10px;" title="Active"></span>
                        @else
                            <span class="position-absolute border border-white rounded-circle bg-danger" 
                                  style="width: 20px; height: 20px; bottom: 10px; right: 10px;" title="Inactive"></span>
                        @endif
                    </div>
                    <h4 class="mt-3 mb-0 font-weight-bold">{{ $supplier->name }}</h4>
                    <p class="mb-0 opacity-75 small">Code: {{ $supplier->member_no }}</p>
                </div>
                <div class="card-body px-4">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted"><i class="fas fa-phone-alt mr-2"></i>Mobile</span>
                            <span class="font-weight-bold">{{ $supplier->mobile_no }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted"><i class="fas fa-money-bill-wave mr-2"></i>Opening Bal.</span>
                            <span class="font-weight-bold">৳{{ number_format($supplier->opening_balance, 2) }}</span>
                        </li>
                        @if($supplier->current_balance < 0)
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span class="text-muted"><i class="fas fa-wallet mr-2"></i>Advance Bal.</span>
                                <span class="text-success font-weight-bold">৳{{ number_format(abs($supplier->current_balance), 2) }}</span>
                            </li>
                        @elseif($supplier->current_balance > 0)
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span class="text-muted"><i class="fas fa-exclamation-circle mr-2"></i>Payable</span>
                                <span class="text-danger font-weight-bold">৳{{ number_format($supplier->current_balance, 2) }}</span>
                            </li>
                        @else
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span class="text-muted"><i class="fas fa-check-circle mr-2"></i>Current Bal.</span>
                                <span class="text-secondary font-weight-bold">৳0.00</span>
                            </li>
                        @endif
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted"><i class="fas fa-info-circle mr-2"></i>Status</span>
                            <span class="badge {{ $supplier->status == 1 ? 'badge-success' : 'badge-danger' }} status-badge">
                                {{ $supplier->status == 1 ? 'Active' : 'Inactive' }}
                            </span>
                        </li>
                    </ul>
                    <div class="mt-4">
                        <a href="{{ route('supplier.edit', ['supplier' => $supplier->id]) }}" 
                           class="btn btn-primary btn-block bg-gradient-primary rounded-pill shadow">
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
                    <span class="font-weight-500">{{ $supplier->email ?? 'Not Available' }}</span>
                </div>
                <div>
                    <label class="text-muted mb-0 small d-block">Full Address</label>
                    <p class="mb-0 text-dark" style="line-height: 1.4;">
                        @php
                            $fullAddress = [];
                            if ($supplier->village) $fullAddress[] = $supplier->village->name . ($supplier->village->bn_name ? ' (' . $supplier->village->bn_name . ')' : '');
                            if ($supplier->union) $fullAddress[] = $supplier->union->name . ' (U)';
                            if ($supplier->pourashava) $fullAddress[] = $supplier->pourashava->name . ' (P)';
                            if ($supplier->upazila) $fullAddress[] = $supplier->upazila->name;
                            if ($supplier->district) $fullAddress[] = $supplier->district->name;
                            if ($supplier->division) $fullAddress[] = $supplier->division->name;
                            echo implode(', ', $fullAddress) ?: 'N/A';
                        @endphp
                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <!-- Tabs Card -->
            <div class="card shadow-lg border-0" style="border-radius: 15px;">
                <div class="card-header bg-white p-0 border-bottom">
                    <ul class="nav nav-tabs border-0" id="profileTab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active px-4 py-3 font-weight-bold border-0 rounded-0" 
                               id="banking-tab" data-toggle="tab" href="#banking" role="tab">
                               <i class="fas fa-university mr-2"></i>Financial & Banking
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-4">
                    <div class="tab-content" id="profileTabContent">
                        <div class="tab-pane fade show active" id="banking" role="tabpanel">
                            <div class="row">
                                <div class="col-md-7">
                                    <div class="p-4 border rounded bg-light h-100 position-relative overflow-hidden">
                                        <i class="fas fa-university position-absolute" style="font-size: 8rem; right: -20px; bottom: -20px; color: rgba(0,0,0,0.03);"></i>
                                        <h5 class="font-weight-bold text-dark mb-4"><i class="fas fa-credit-card mr-2 text-primary"></i>Bank Account Info</h5>
                                        
                                        <div class="row mb-3">
                                            <div class="col-5 text-muted">Bank Name</div>
                                            <div class="col-7 font-weight-bold">{{ $supplier->bank_name ?? 'N/A' }}</div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-5 text-muted">Branch Name</div>
                                            <div class="col-7 font-weight-bold">{{ $supplier->branch_name ?? 'N/A' }}</div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-5 text-muted">Account No.</div>
                                            <div class="col-7 text-primary font-weight-bold">{{ $supplier->bank_account_no ?? 'N/A' }}</div>
                                        </div>
                                        <div class="row mb-0">
                                            <div class="col-5 text-muted">Routing No.</div>
                                            <div class="col-7 font-weight-bold">{{ $supplier->routing_no ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="p-4 border rounded bg-white h-100 shadow-sm border-left-primary" style="border-left: 4px solid #007bff !important;">
                                        <h5 class="font-weight-bold text-dark mb-4"><i class="fas fa-mobile-alt mr-2 text-success"></i>Mobile Banking</h5>
                                        <div class="text-center py-3">
                                            <div class="h3 font-weight-bold text-success mb-1">{{ $supplier->mobile_banking_no ?? 'N/A' }}</div>
                                            <span class="text-muted small">Registered Number</span>
                                        </div>
                                        <div class="mt-4 p-2 bg-light rounded text-center small text-muted italic">
                                            Use this number for digital payments and transactions.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="row mt-4">
                <div class="col-sm-6">
                    <div class="card bg-gradient-info border-0 shadow rounded-lg">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="mb-0 opacity-75">Recent Purchases</p>
                                    <h3 class="mb-0 font-weight-bold">View History</h3>
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
                                    <p class="mb-0 opacity-75">Ledger Statement</p>
                                    <h3 class="mb-0 font-weight-bold">Get Report</h3>
                                </div>
                                <i class="fas fa-file-invoice-dollar fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

