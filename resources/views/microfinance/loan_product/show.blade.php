@extends('layouts.app')
@section('title', $pageTitle)

@section('style')
<style>
    .product-card { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
    .product-header { background: linear-gradient(135deg, #28a745 0%, #218838 100%); border-radius: 20px 20px 0 0 !important; padding: 20px 25px; }
    .info-group-premium { background: #ffffff; border-radius: 15px; padding: 25px; margin-bottom: 30px; border: 1px solid #f1f5f9; box-shadow: 0 4px 6px rgba(0,0,0,0.02); transition: all 0.3s ease; height: calc(100% - 30px); position: relative; overflow: hidden; }
    .info-group-premium:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.05); border-color: #28a745; }
    .info-label { font-size: 0.75rem; color: #6c757d; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; }
    .info-value { font-size: 1.2rem; font-weight: 800; color: #1e293b; }
    .section-title { font-size: 1.1rem; font-weight: 700; color: #334155; margin-bottom: 20px; border-left: 4px solid #28a745; padding-left: 15px; }
    .fee-box { background: #f0fdf4; border: 1px solid #bbf7d0; }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card product-card">
                <div class="card-header text-white" style="background: linear-gradient(135deg, #28a745 0%, #218838 100%);">
                    <h3 class="card-title mb-0" style="font-weight: 700;"><i class="fa fa-hand-holding-usd mr-2"></i> Loan Scheme Details</h3>
                    <div class="card-tools">
                        <a href="{{ route('microfinance.loan-product.index') }}" class="btn btn-light btn-sm shadow-sm px-3" style="border-radius: 10px; font-weight: 600;">
                            <i class="fa fa-arrow-left mr-1"></i> Back to List
                        </a>
                    </div>
                </div>
                <div class="card-body p-4">
                    <!-- Main Identity Section -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="info-group-premium" style="border-left: 5px solid #28a745;">
                                <div class="info-label">Product/Scheme Name</div>
                                <div class="info-value text-success" style="font-size: 1.5rem;">{{ $loanProduct->name }}</div>
                                <div class="mt-2">
                                    <span class="badge badge-info px-3 py-2" style="border-radius: 8px;">Code: {{ $loanProduct->code }}</span>
                                    <span class="badge {{ $loanProduct->status == 1 ? 'badge-success' : 'badge-danger' }} px-3 py-2 ml-1" style="border-radius: 8px;">
                                        {{ $loanProduct->status == 1 ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-group-premium">
                                <div class="info-label">Frequency</div>
                                <div class="info-value">
                                    @if($loanProduct->installment_frequency == 1) Daily (দৈনিক)
                                    @elseif($loanProduct->installment_frequency == 2) Weekly (সাপ্তাহিক)
                                    @elseif($loanProduct->installment_frequency == 3) Monthly (মাসিক)
                                    @elseif($loanProduct->installment_frequency == 4) Quarterly (ত্রৈমাসিক)
                                    @elseif($loanProduct->installment_frequency == 5) Half-Yearly (ষান্মাসিক)
                                    @elseif($loanProduct->installment_frequency == 6) One-Time / Maturity (এককালীন)
                                    @else N/A
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-group-premium">
                                <div class="info-label">Total Enrollment</div>
                                <div class="info-value text-primary">{{ $loanProduct->loans_count }} Active Loans</div>
                            </div>
                        </div>
                    </div>

                    <h4 class="section-title mt-4">Interest Configuration & Penalties</h4>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="info-group-premium">
                                <div class="info-label">Interest Rate</div>
                                <div class="info-value text-success">{{ $loanProduct->interest_rate }}% <small class="text-muted" style="font-size: 0.75rem;">(Applied to Principal)</small></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-group-premium">
                                <div class="info-label">Interest Method / Type</div>
                                <div class="info-value">
                                    @if($loanProduct->interest_type == 1) Flat (ফ্ল্যাট রেট)
                                    @elseif($loanProduct->interest_type == 2) Reducing Balance (ক্রমহ্রাসমান পদ্ধতি)
                                    @else N/A
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-group-premium">
                                <div class="info-label">Late Penalty Rate</div>
                                <div class="info-value text-danger">
                                    @if(($loanProduct->late_penalty_type ?? 'percentage') == 'flat')
                                        ৳ {{ number_format($loanProduct->late_penalty_rate, 2) }} <small class="text-muted font-weight-normal" style="font-size: 0.75rem;">(Flat Penalty)</small>
                                    @else
                                        {{ number_format($loanProduct->late_penalty_rate, 2) }}% <small class="text-muted font-weight-normal" style="font-size: 0.75rem;">(On Overdue Installment)</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <h4 class="section-title mt-4 text-success" style="border-left-color: #28a745;">Scheme Fees & Deductions</h4>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="info-group-premium fee-box">
                                <div class="info-label">Loan Insurance Fee</div>
                                <div class="info-value text-success">
                                    @if(($loanProduct->insurance_fee_type ?? 'percentage') == 'flat')
                                        ৳ {{ number_format($loanProduct->insurance_fee_percentage, 2) }} <small class="text-muted font-weight-normal">(Flat Fee)</small>
                                    @else
                                        {{ number_format($loanProduct->insurance_fee_percentage, 2) }}% <small class="text-muted font-weight-normal">(Percentage of Principal)</small>
                                    @endif
                                </div>
                                <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">Deducted automatically during the disbursement phase.</small>
                            </div>
                        </div>
                    </div>

                    <div class="text-right mt-5 pt-3 border-top">
                        @if($loanProduct->loans_count == 0)
                        <a href="{{ route('microfinance.loan-product.edit', $loanProduct->id) }}" class="btn btn-primary px-5" style="border-radius: 12px; font-weight: 700; background-color: #28a745; border-color: #28a745;">
                            <i class="fa fa-edit mr-2"></i> Edit Scheme settings
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
