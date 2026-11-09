@extends('layouts.app')
@section('title', $pageTitle)

@section('style')
<style>
    .product-card { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
    .product-header { background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); border-radius: 20px 20px 0 0 !important; padding: 20px 25px; }
    .info-group-premium { background: #ffffff; border-radius: 15px; padding: 25px; margin-bottom: 30px; border: 1px solid #f1f5f9; box-shadow: 0 4px 6px rgba(0,0,0,0.02); transition: all 0.3s ease; height: calc(100% - 30px); position: relative; overflow: hidden; }
    .info-group-premium:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.05); border-color: #007bff; }
    .info-label { font-size: 0.75rem; color: #6c757d; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; }
    .info-value { font-size: 1.2rem; font-weight: 800; color: #1e293b; }
    .section-title { font-size: 1.1rem; font-weight: 700; color: #334155; margin-bottom: 20px; border-left: 4px solid #007bff; padding-left: 15px; }
    .penalty-box { background: #fff5f5; border: 1px solid #fed7d7; }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card product-card">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title mb-0" style="font-weight: 700;"><i class="fa fa-university mr-2"></i> Savings Scheme Details</h3>
                    <div class="card-tools">
                        <a href="{{ route('microfinance.savings-product.index') }}" class="btn btn-light btn-sm shadow-sm px-3" style="border-radius: 10px; font-weight: 600;">
                            <i class="fa fa-arrow-left mr-1"></i> Back to List
                        </a>
                    </div>
                </div>
                <div class="card-body p-4">
                    <!-- Main Identity Section -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="info-group-premium" style="border-left: 5px solid #007bff;">
                                <div class="info-label">Product Name</div>
                                <div class="info-value text-primary" style="font-size: 1.5rem;">{{ $savingsProduct->name }}</div>
                                <div class="mt-2">
                                    <span class="badge badge-info px-3 py-2" style="border-radius: 8px;">Code: {{ $savingsProduct->code }}</span>
                                    <span class="badge {{ $savingsProduct->status == 1 ? 'badge-success' : 'badge-danger' }} px-3 py-2 ml-1" style="border-radius: 8px;">
                                        {{ $savingsProduct->status == 1 ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-group-premium">
                                <div class="info-label">Product Type</div>
                                <div class="info-value">
                                    @if($savingsProduct->product_type == 1) General Savings (GS)
                                    @elseif($savingsProduct->product_type == 2) DPS
                                    @else FDR (Fixed Deposit)
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-group-premium">
                                <div class="info-label">Current Adoption</div>
                                <div class="info-value text-success">{{ $savingsProduct->accounts_count }} Accounts</div>
                            </div>
                        </div>
                    </div>

                    <h4 class="section-title mt-4">Financial Configuration</h4>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="info-group-premium">
                                <div class="info-label">Interest Rate</div>
                                <div class="info-value">{{ $savingsProduct->interest_rate }}% <small class="text-muted" style="font-size: 0.7rem;">(Annual)</small></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-group-premium">
                                <div class="info-label">Opening Fee</div>
                                <div class="info-value">৳ {{ number_format($savingsProduct->opening_fee, 2) }}</div>
                            </div>
                        </div>

                        @if(in_array($savingsProduct->product_type, [2, 3]))
                        <div class="col-md-4">
                            <div class="info-group-premium">
                                <div class="info-label">Duration</div>
                                <div class="info-value">{{ $savingsProduct->duration_months }} Months</div>
                            </div>
                        </div>
                        @endif

                        @if($savingsProduct->product_type == 2)
                        <div class="col-md-4">
                            <div class="info-group-premium">
                                <div class="info-label">Installment Amount</div>
                                <div class="info-value">৳ {{ number_format($savingsProduct->installment_amount, 2) }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-group-premium">
                                <div class="info-label">Frequency</div>
                                <div class="info-value text-capitalize">{{ $savingsProduct->installment_frequency == 1 ? 'Daily' : ($savingsProduct->installment_frequency == 2 ? 'Weekly' : 'Monthly') }}</div>
                            </div>
                        </div>
                        @endif
                    </div>

                    <h4 class="section-title mt-4 text-danger" style="border-left-color: #dc3545;">Fees & Penalties</h4>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="info-group-premium">
                                <div class="info-label">Default Closing Fee</div>
                                <div class="info-value text-danger">৳ {{ number_format($savingsProduct->closing_fee, 2) }}</div>
                            </div>
                        </div>
                        @if($savingsProduct->product_type == 2)
                        <div class="col-md-4">
                            <div class="info-group-premium penalty-box">
                                <div class="info-label">Installment Miss Fine</div>
                                <div class="info-value">
                                    {{ $savingsProduct->installment_fine_type == 'flat' ? '৳' : '' }}
                                    {{ number_format($savingsProduct->installment_fine_amount, 2) }}
                                    {{ $savingsProduct->installment_fine_type == 'percentage' ? '%' : '' }}
                                    <small class="text-muted d-block" style="font-size: 0.65rem;">(Per missed installment)</small>
                                </div>
                            </div>
                        </div>
                        @endif
                        <div class="col-md-4">
                            <div class="info-group-premium penalty-box">
                                <div class="info-label">Early Closing Fine</div>
                                <div class="info-value">
                                    {{ $savingsProduct->early_closing_fine_type == 'flat' ? '৳' : '' }}
                                    {{ number_format($savingsProduct->early_closing_fine_amount, 2) }}
                                    {{ $savingsProduct->early_closing_fine_type == 'percentage' ? '%' : '' }}
                                    <small class="text-muted d-block" style="font-size: 0.65rem;">(On principal/fixed penalty)</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-right mt-5 pt-3 border-top">
                        @if($savingsProduct->accounts_count == 0)
                        <a href="{{ route('microfinance.savings-product.edit', $savingsProduct->id) }}" class="btn btn-primary px-5" style="border-radius: 12px; font-weight: 700;">
                            <i class="fa fa-edit mr-2"></i> Edit Scheme
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
