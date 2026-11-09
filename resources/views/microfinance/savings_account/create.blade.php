@extends('layouts.app')
@section('title', $pageTitle)

@section('style')
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #17a2b8 0%, #117a8b 100%);
        --card-shadow: 0 10px 30px rgba(0,0,0,0.05);
    }
    .acc-card { border: none; border-radius: 20px; box-shadow: var(--card-shadow); }
    .acc-header { background: var(--primary-gradient); border-radius: 20px 20px 0 0 !important; padding: 20px 25px; }
    .preview-card { border: none; border-radius: 20px; background: #f8fafc; border: 1px solid #e2e8f0; height: 100%; }
    .preview-header { padding: 15px 20px; border-bottom: 1px solid #e2e8f0; font-weight: 700; color: #1e293b; }
    .preview-item { display: flex; justify-content: space-between; padding: 10px 20px; border-bottom: 1px dashed #cbd5e1; }
    .preview-label { color: #64748b; font-size: 0.85rem; }
    .preview-value { font-weight: 600; color: #334155; }
    .form-label { font-weight: 600; color: #334155; margin-bottom: 8px; }
    .form-control { border-radius: 12px; padding: 12px 15px; border: 1px solid #e2e8f0; }
    .form-control:focus { box-shadow: 0 0 0 4px rgba(23, 162, 184, 0.1); border-color: #17a2b8; }
    .select2-container--default .select2-selection--single { border-radius: 12px !important; height: 36px !important; padding: 7px !important; border-color: #e2e8f0 !important; }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-7">
            <div class="card acc-card">
                <div class="card-header acc-header text-white">
                    <h3 class="card-title mb-0"><i class="fa fa-user-plus mr-2"></i> {{ $pageTitle }}</h3>
                </div>
                <form action="{{ route('microfinance.savings-account.store') }}" method="POST" id="account_form">
                    @csrf
                    <div class="card-body p-4">
                        <div class="row">
                            <div class="col-md-12 mb-4">
                                <label class="form-label">Search Member <span class="text-danger">*</span></label>
                                <select name="member_id" id="member_id" class="form-control select2" required>
                                    <option value="">Select Member (Name or ID)</option>
                                    @foreach($members as $member)
                                        <option value="{{ $member->id }}" {{ old('member_id') == $member->id ? 'selected' : '' }}>{{ $member->name }} - {{ $member->member_no }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-12 mb-4">
                                <label class="form-label">Account Title <span class="text-danger">*</span></label>
                                <input type="text" name="account_title" id="account_title" class="form-control" value="{{ old('account_title') }}" placeholder="Account Title (Defaults to Member Name)" required>
                            </div>
                            <div class="col-md-12 mb-4">
                                <label class="form-label">Introducer (Optional)</label>
                                <select name="introducer_id" id="introducer_id" class="form-control select2">
                                    <option value="">Select Introducer Member</option>
                                    @foreach($members as $member)
                                        <option value="{{ $member->id }}" {{ old('introducer_id') == $member->id ? 'selected' : '' }}>{{ $member->name }} - {{ $member->member_no }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-12 mb-4" id="auto_renewal_container" style="display: none;">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" name="auto_renewal" class="custom-control-input" id="auto_renewal" value="1" {{ old('auto_renewal') ? 'checked' : '' }}>
                                    <label class="custom-control-label font-weight-bold" for="auto_renewal">Enable Auto-Renewal at Maturity</label>
                                </div>
                                <small class="text-muted">Mainly for FDR products.</small>
                            </div>
                            <div class="col-md-12 mb-4">
                                <label class="form-label text-muted">Member's Somiti (Auto-fetched)</label>
                                <input type="text" id="member_somiti_name" class="form-control bg-light" placeholder="---" readonly>
                            </div>
                            <div class="col-md-12 mb-4">
                                <label class="form-label">Select Savings Type <span class="text-danger">*</span></label>
                                <select id="product_type" name="product_type" class="form-control select2" required>
                                    <option value="">Select Type</option>
                                    {{-- <option value="1" {{ old('product_type') == 1 ? 'selected' : '' }}>General Savings (GS)</option> --}}
                                    <option value="2" {{ old('product_type') == 2 ? 'selected' : '' }}>DPS (Monthly Deposit)</option>
                                    <option value="3" {{ old('product_type') == 3 ? 'selected' : '' }}>FDR (Fixed Deposit)</option>
                                </select>
                            </div>
                            
                            <div class="col-md-12 mb-4" id="scheme_container" style="display: none;">
                                <label class="form-label">Select Savings Scheme <span class="text-danger">*</span></label>
                                <select name="mf_savings_product_id" id="product_id" class="form-control select2" required>
                                    <option value="">Choose a Savings/DPS Product</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" data-type="{{ $product->product_type }}" {{ old('mf_savings_product_id') == $product->id ? 'selected' : '' }}>{{ $product->name }} ({{ $product->code }})</option>
                                    @endforeach
                                </select>
                                <div id="product_info_banner" class="alert alert-light border mt-2 py-2 px-3 small" style="display: none; border-radius: 12px; background-color: #f8fafc;">
                                    <i class="fa fa-info-circle text-info mr-1"></i> 
                                    <b>খোলার ফি:</b> ৳ <span id="banner_opening_fee">0.00</span> | 
                                    <b>সর্বনিম্ন জমা:</b> ৳ <span id="banner_min_deposit">0.00</span> | 
                                    <b>সুদের হার:</b> <span id="banner_interest_rate">0.00</span>%
                                </div>
                            </div>

                            <div class="col-md-12 mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label mb-0">Select Nominee <span class="text-danger">*</span></label>
                                    <button type="button" class="btn btn-xs btn-primary shadow-sm" id="btn_add_nominee" title="Add New Nominee" style="border-radius: 50%; width: 25px; height: 25px; padding: 0;">
                                        <i class="fa fa-plus"></i>
                                    </button>
                                </div>
                                <select name="nominee_id[]" id="nominee_id" class="form-control select2" multiple required>
                                    <option value="" disabled hidden>Select Member First</option>
                                    @if(old('nominee_id'))
                                        @php 
                                            $oldNominees = \App\Models\Microfinance\MfNominee::whereIn('id', old('nominee_id'))->get();
                                        @endphp
                                        @foreach($oldNominees as $nominee)
                                            <option value="{{ $nominee->id }}" selected>{{ $nominee->name }} ({{ $nominee->relation }})</option>
                                        @endforeach
                                    @endif
                                </select>
                                <div id="nominee_shares_container" class="mt-3" style="display:none;">
                                    <label class="form-label small text-primary font-weight-bold">Set Share Percentage (%)</label>
                                    <div id="nominee_shares_list" class="space-y-2">
                                        <!-- Dynamic share inputs will appear here -->
                                    </div>
                                    <div class="alert alert-info mt-2 py-2 px-3 small">
                                        <i class="fa fa-info-circle mr-1"></i> Total share must be <b>100%</b>. Current: <b id="total_share_display">0%</b>
                                    </div>
                                </div>
                                <small class="text-muted"><i class="fa fa-info-circle mr-1"></i> You can select multiple nominees. Nominees registered during admission will be listed here.</small>
                            </div>

                            <div class="col-md-12 mb-4">
                                <label class="form-label">Opening Date <span class="text-danger">*</span></label>
                                <input type="text" name="opening_date" id="opening_date" class="form-control date-picker" value="{{ old('opening_date', date('d-m-Y')) }}" required readonly>
                            </div>

                            <div class="col-md-12 mb-4">
                                <label class="form-label" id="deposit_label">Initial Deposit Amount (Opening Balance)</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" style="border-radius: 12px 0 0 12px; background: #f8fafc;">৳</span>
                                    </div>
                                <input type="number" step="0.01" name="opening_balance" id="opening_balance" class="form-control" value="{{ old('opening_balance', '0.00') }}" style="border-radius: 0 12px 12px 0;">
                                </div>
                                <div id="min_deposit_warning" class="text-danger small mt-1" style="display: none;">
                                    <i class="fa fa-exclamation-triangle mr-1"></i> Minimum deposit for this scheme is ৳ <span id="min_deposit_val">0</span>
                                </div>
                                <small class="text-muted" id="deposit_help">This amount will be added to the member's current balance.</small>
                            </div>
                        </div>

                        <div class="mt-4 pt-4 border-top text-right">
                            <a href="{{ route('microfinance.savings-account.index') }}" class="btn btn-secondary mr-2" style="border-radius: 12px; padding: 12px 35px;">Cancel</a>
                            <button type="submit" class="btn btn-info px-5 shadow-sm" style="border-radius: 12px; padding: 12px 35px; font-weight: 700; color: white;">
                                <i class="fa fa-check-circle mr-2"></i> CONFIRM & OPEN ACCOUNT
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card preview-card">
                <div class="preview-header">
                    <i class="fa fa-clipboard-list mr-2 text-info"></i> Account Opening Summary
                </div>
                <div class="card-body p-0">
                    <!-- Member Info Preview -->
                    <div class="p-3 bg-white mb-0 border-bottom">
                        <h6 class="text-uppercase font-weight-bold text-muted small mb-3">Member Details</h6>
                        <div class="text-center mb-3">
                            <img id="prev_member_photo" src="{{ asset('img/no-image.webp') }}" class="rounded-circle shadow-sm" style="width: 100px; height: 100px; object-fit: cover; border: 3px solid #fff;">
                        </div>
                        <div id="member_preview">
                            <div class="preview-item">
                                <span class="preview-label">Member Name</span>
                                <span class="preview-value" id="prev_member_name">---</span>
                            </div>
                            <div class="preview-item">
                                <span class="preview-label">Member ID</span>
                                <span class="preview-value" id="prev_member_id">---</span>
                            </div>
                            <div class="preview-item">
                                <span class="preview-label">Somiti (Group)</span>
                                <span class="preview-value" id="prev_somiti">---</span>
                            </div>
                            <div class="preview-item">
                                <span class="preview-label">Mobile</span>
                                <span class="preview-value" id="prev_mobile">---</span>
                            </div>
                        </div>
                    </div>

                    <!-- Product Policy Preview -->
                    <div class="p-3 bg-white border-bottom">
                        <h6 class="text-uppercase font-weight-bold text-muted small mb-3">Scheme Policy</h6>
                        <div id="product_preview">
                            <div class="preview-item">
                                <span class="preview-label">Scheme Type</span>
                                <span class="preview-value" id="prev_type">---</span>
                            </div>
                            <div class="preview-item">
                                <span class="preview-label">Interest Rate</span>
                                <span class="preview-value" id="prev_interest">---</span>
                            </div>
                            <div class="preview-item" id="duration_row" style="display: none;">
                                <span class="preview-label">Duration</span>
                                <span class="preview-value" id="prev_duration">---</span>
                            </div>
                            <div class="preview-item" id="installment_row" style="display: none;">
                                <span class="preview-label">Installment</span>
                                <span class="preview-value" id="prev_installment">---</span>
                            </div>
                            <div class="preview-item" id="frequency_row" style="display: none;">
                                <span class="preview-label">Frequency</span>
                                <span class="preview-value" id="prev_frequency">---</span>
                            </div>
                        </div>
                    </div>

                    <!-- Financial Summary -->
                    <div class="p-4" style="background: #eef2ff;">
                        <h6 class="text-uppercase font-weight-bold text-indigo small mb-3">Estimated Initial Cost</h6>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Account Opening Fee</span>
                            <span class="font-weight-bold" id="prev_fee">৳ 0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3 pb-3 border-bottom border-indigo">
                            <span id="prev_deposit_label">Initial Deposit</span>
                            <span class="font-weight-bold" id="prev_deposit">৳ 0.00</span>
                        </div>
                        <div class="d-flex justify-content-between h5 font-weight-bold text-indigo mb-0">
                            <span>Total Payable</span>
                            <span id="prev_total">৳ 0.00</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('microfinance.savings_account.partials.scripts')
@endsection
