@extends('layouts.app')
@section('title', $pageTitle)

@section('style')
<style>
    .ledger-card { 
        border: none; 
        border-radius: 24px; 
        box-shadow: 0 12px 40px rgba(0,0,0,0.03); 
        background: #ffffff; 
        overflow: hidden;
    }
    .ledger-header { 
        background: linear-gradient(135deg, #3f5efb 0%, #fc466b 100%); 
        border-radius: 24px 24px 0 0 !important; 
        padding: 30px; 
    }
    .info-box { 
        background: #ffffff; 
        border: 1px solid rgba(0, 0, 0, 0.05); 
        border-radius: 18px; 
        padding: 18px; 
        margin-bottom: 20px; 
        box-shadow: 0 6px 15px rgba(0,0,0,0.015);
        transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
    }
    .info-box:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.04);
        border-color: rgba(0, 123, 255, 0.15);
    }
    .info-label { 
        font-size: 0.72rem; 
        color: #8a96a3; 
        text-transform: uppercase; 
        letter-spacing: 1px; 
        font-weight: 700;
        margin-bottom: 6px;
    }
    .info-value { 
        font-size: 1.05rem; 
        font-weight: 800; 
        color: #1a202c; 
    }
    .badge-status { 
        padding: 8px 18px; 
        border-radius: 50px; 
        font-weight: 700; 
        font-size: 0.82rem; 
        letter-spacing: 0.8px;
    }
    .avatar-glow-success {
        border: 3px solid #28a745;
        box-shadow: 0 0 15px rgba(40, 167, 69, 0.2);
    }
    .avatar-glow-warning {
        border: 3px solid #ffc107;
        box-shadow: 0 0 15px rgba(255, 193, 7, 0.2);
    }
    .avatar-glow-danger {
        border: 3px solid #dc3545;
        box-shadow: 0 0 15px rgba(220, 53, 69, 0.2);
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    {{-- Status Alert for Pending Accounts --}}
    @if($savingsAccount->status == 0)
    <div class="alert alert-warning border-0 shadow-sm mb-4 d-flex align-items-center" style="border-radius: 15px;">
        <div class="mr-3"><i class="fas fa-exclamation-triangle fa-2x"></i></div>
        <div>
            <h5 class="alert-heading mb-1 font-weight-bold">Pending Approval</h5>
            <p class="mb-0">This account is currently in pending state. Review the details below before approving.</p>
        </div>
        <div class="ml-auto">
            <a href="{{ route('microfinance.savings-account.edit', $savingsAccount->id) }}" class="btn btn-dark btn-sm rounded-pill px-4 mr-2">
                <i class="fas fa-edit mr-1"></i> Edit Details
            </a>
            <button type="button" class="btn btn-success btn-sm rounded-pill px-4" onclick="approveAccount({{ $savingsAccount->id }})">
                <i class="fas fa-check-circle mr-1"></i> Approve Now
            </button>
        </div>
    </div>
    @endif

    <div class="row">
        {{-- Left Column: Member & Product Info --}}
        <div class="col-md-4">
            <div class="card ledger-card mb-4">
                <div class="card-body">
                    <div class="text-center mb-4 pb-2 border-bottom">
                        <div class="mb-3">
                            @if($savingsAccount->status == 0)
                                <span class="badge badge-warning badge-status shadow-sm"><i class="fas fa-clock mr-1"></i> PENDING</span>
                            @elseif($savingsAccount->status == 1)
                                <span class="badge badge-success badge-status shadow-sm"><i class="fas fa-check-circle mr-1"></i> ACTIVE</span>
                            @else
                                <span class="badge badge-danger badge-status shadow-sm"><i class="fas fa-times-circle mr-1"></i> CLOSED</span>
                            @endif
                        </div>
                        <h4 class="font-weight-bold text-dark mb-1" style="font-size: 1.3rem; letter-spacing: -0.5px;">{{ $savingsAccount->account_title }}</h4>
                        <span class="badge px-3 py-1.5 rounded-pill" style="background: rgba(0, 123, 255, 0.08); color: #007bff; font-weight: 700; font-size: 0.85rem;">
                            <i class="fas fa-hashtag mr-1"></i> A/C: {{ $savingsAccount->account_no }}
                        </span>
                    </div>

                    {{-- Member Details Box --}}
                    <div class="info-box">
                        <div class="info-label"><i class="fas fa-user-tie mr-1 text-primary"></i> Member Details</div>
                        <div class="d-flex align-items-center mt-3">
                            <img src="{{ $savingsAccount->member->photo ? asset($savingsAccount->member->photo) : asset('img/no-image.webp') }}" 
                                 class="rounded-circle mr-3 {{ $savingsAccount->status == 1 ? 'avatar-glow-success' : ($savingsAccount->status == 0 ? 'avatar-glow-warning' : 'avatar-glow-danger') }}" 
                                 style="width: 58px; height: 58px; object-fit: cover;">
                            <div>
                                <div class="info-value" style="font-size: 1.1rem; color: #2d3748;">{{ $savingsAccount->member->name }}</div>
                                <span class="badge px-2.5 py-1 rounded-pill mt-1.5" style="background: rgba(108,117,125,0.08); color: #495057; font-weight: 600; font-size: 0.78rem;">
                                    <i class="fas fa-fingerprint mr-1"></i> Member ID: {{ $savingsAccount->member->member_no }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Introducer Box --}}
                    @if($savingsAccount->introducer)
                    <div class="info-box" style="border-left: 5px solid #17a2b8; background: rgba(23,162,184,0.01);">
                        <div class="info-label text-info"><i class="fas fa-hands-helping mr-1"></i> Introduced By</div>
                        <div class="d-flex align-items-center mt-2">
                            <div class="mr-3 text-info"><i class="fas fa-user-check fa-2x"></i></div>
                            <div>
                                <div class="font-weight-bold text-dark" style="font-size: 0.95rem;">{{ $savingsAccount->introducer->name }}</div>
                                <div class="small text-muted"><i class="fas fa-id-badge mr-1"></i> {{ $savingsAccount->introducer->member_no }}</div>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Product & Interest Box --}}
                    <div class="info-box">
                        <div class="info-label"><i class="fas fa-university mr-1 text-primary"></i> Product & Interest</div>
                        <div class="d-flex align-items-center justify-content-between mt-3">
                            <div class="d-flex align-items-center">
                                @if($savingsAccount->product->product_type == 1)
                                    <div class="icon-circle bg-primary-light mr-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; border-radius: 12px; background: rgba(0, 123, 255, 0.08);">
                                        <i class="fas fa-university text-primary" style="font-size: 1.1rem;"></i>
                                    </div>
                                    <span class="info-value" style="font-size: 1rem; color: #2d3748;">General Savings (GS)</span>
                                @elseif($savingsAccount->product->product_type == 2)
                                    <div class="icon-circle bg-success-light mr-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; border-radius: 12px; background: rgba(40, 167, 69, 0.08);">
                                        <i class="fas fa-piggy-bank text-success" style="font-size: 1.1rem;"></i>
                                    </div>
                                    <span class="info-value" style="font-size: 1rem; color: #2d3748;">DPS Scheme</span>
                                @else
                                    <div class="icon-circle bg-warning-light mr-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; border-radius: 12px; background: rgba(255, 193, 7, 0.08);">
                                        <i class="fas fa-vault text-warning" style="font-size: 1.1rem;"></i>
                                    </div>
                                    <span class="info-value" style="font-size: 1rem; color: #2d3748;">FDR Scheme</span>
                                @endif
                            </div>
                            <span class="badge" style="background: rgba(40, 167, 69, 0.08); color: #28a745; border-radius: 50px; font-weight: 700; padding: 6px 12px; font-size: 0.8rem;">
                                <i class="fas fa-percent mr-1"></i> {{ $savingsAccount->interest_rate }}% Interest
                            </span>
                        </div>
                        <div class="text-muted small mt-2 d-flex justify-content-between align-items-center">
                            <span>Scheme:</span>
                            <span class="font-weight-bold text-dark">{{ $savingsAccount->product->name }}</span>
                        </div>

                        {{-- Opening Fee Badge --}}
                        @if($savingsAccount->product->opening_fee > 0)
                            <div class="mt-3 pt-3 border-top text-muted small d-flex justify-content-between align-items-center" style="border-top: 1px dashed #dee2e6 !important;">
                                <span class="font-weight-bold"><i class="fas fa-receipt mr-1 text-success"></i> Opening Fee:</span>
                                <span class="badge badge-success px-3 py-1.5 rounded-pill shadow-sm" style="font-weight: 700; font-size: 0.8rem; background: linear-gradient(135deg, #28a745 0%, #218838 100%);">
                                    ৳ {{ number_format($savingsAccount->product->opening_fee, 2) }} Paid (সংগৃহীত)
                                </span>
                            </div>
                        @endif
                        @if($savingsAccount->auto_renewal)
                            <div class="mt-2 text-success small font-weight-bold text-right">
                                <i class="fas fa-sync-alt mr-1"></i> Auto-Renewal Enabled
                            </div>
                        @endif
                    </div>

                    {{-- Duration & Frequency Grid --}}
                    @if($savingsAccount->product->product_type == 2)
                    <div class="row px-2">
                        <div class="col-6 mb-3">
                            <div class="info-label"><i class="fas fa-money-bill-wave mr-1 text-success"></i> Installment</div>
                            <div class="info-value" style="font-size: 1.1rem; color: #28a745;">৳ {{ number_format($savingsAccount->installment_amount, 2) }}</div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="info-label"><i class="fas fa-redo mr-1 text-primary"></i> Frequency</div>
                            <div class="info-value" style="font-size: 1rem; color: #2d3748;">
                                {{ $savingsAccount->installment_frequency == 1 ? 'Daily' : ($savingsAccount->installment_frequency == 2 ? 'Weekly' : 'Monthly') }}
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="row px-2 border-top pt-3" style="border-top: 1px dashed #dee2e6 !important;">
                        <div class="col-6 mb-3">
                            <div class="info-label"><i class="fas fa-calendar-check mr-1 text-primary"></i> Opening Date</div>
                            <div class="info-value" style="font-size: 0.92rem; color: #4a5568;">{{ date('d M, Y', strtotime($savingsAccount->opening_date)) }}</div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="info-label"><i class="fas fa-calendar-times mr-1 text-danger"></i> Maturity Date</div>
                            <div class="info-value" style="font-size: 0.92rem; color: #4a5568;">{{ $savingsAccount->maturity_date ? date('d M, Y', strtotime($savingsAccount->maturity_date)) : 'N/A' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Nominee Section --}}
            <div class="card ledger-card">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 font-weight-bold"><i class="fas fa-users mr-2 text-info"></i> Nominees</h5>
                </div>
                <div class="card-body pt-0">
                    @foreach($savingsAccount->nominees as $nominee)
                    <div class="d-flex justify-content-between align-items-center p-3 mb-2 bg-light rounded shadow-sm">
                        <div>
                            <div class="font-weight-bold">{{ $nominee->name }}</div>
                            <div class="small text-muted">{{ $nominee->relation }}</div>
                        </div>
                        <div class="badge badge-info px-3 py-2 rounded-pill">{{ $nominee->pivot->share_percentage }}%</div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Right Column: Balance & Ledger --}}
        <div class="col-md-8">
            <div class="card ledger-card mb-4">
                <div class="card-body bg-light rounded">
                    <div class="row align-items-center">
                        <div class="col-md-{{ $savingsAccount->product->product_type == 1 ? '4' : '6' }}">
                            <h5 class="text-muted mb-1">Current Balance</h5>
                            <h2 class="font-weight-bold text-success mb-0">৳ {{ number_format($savingsAccount->current_balance, 2) }}</h2>
                        </div>
                        @if($savingsAccount->product->product_type == 1)
                        <div class="col-md-4 text-md-center mt-3 mt-md-0 border-left border-right">
                            <h5 class="text-muted mb-1"><i class="fas fa-hand-holding-usd text-warning mr-1"></i> Accrued Interest (অর্জিত সুদ)</h5>
                            <h3 class="font-weight-bold text-warning mb-0">৳ {{ number_format($savingsAccount->accrued_interest ?? 0, 2) }}</h3>
                            <small class="text-muted" style="font-size: 11px;">Posted annually on June 30th</small>
                        </div>
                        @endif
                        <div class="col-md-{{ $savingsAccount->product->product_type == 1 ? '4' : '6' }} text-md-right mt-3 mt-md-0">
                            <p class="mb-1 text-muted">Intended Opening Balance</p>
                            <h4 class="font-weight-bold mb-0">৳ {{ number_format($savingsAccount->opening_balance, 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card ledger-card">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h3 class="card-title font-weight-bold text-dark mb-0"><i class="fa fa-list mr-2 text-primary"></i> Account Ledger</h3>
                    @if(in_array($savingsAccount->status, [1, 2]))
                    <div>
                        @if($savingsAccount->status == 1)
                        <button class="btn btn-outline-danger btn-sm font-weight-bold mr-1 rounded-pill" onclick="withdrawModal({{ $savingsAccount->id }})">
                            <i class="fa fa-arrow-up mr-1"></i> Withdraw
                        </button>
                        <button class="btn btn-outline-info btn-sm font-weight-bold mr-1 rounded-pill" onclick="postInterest({{ $savingsAccount->id }})">
                            <i class="fa fa-plus mr-1"></i> Post Interest
                        </button>
                        @endif

                        @if($savingsAccount->product->product_type == 3)
                        <button class="btn btn-outline-success btn-sm font-weight-bold mr-1 rounded-pill" onclick="renewFdrModal({{ $savingsAccount->id }})">
                            <i class="fas fa-sync-alt mr-1"></i> Renew FDR
                        </button>
                        @endif

                        @if($savingsAccount->status == 1)
                        <button class="btn btn-outline-dark btn-sm font-weight-bold rounded-pill" onclick="closeAccountModal({{ $savingsAccount->id }})">
                            <i class="fa fa-times mr-1"></i> Close
                        </button>
                        @endif
                    </div>
                    @endif
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="bg-light border-0">
                                <tr>
                                    <th class="border-0">Date</th>
                                    <th class="border-0">Description</th>
                                    <th class="border-0 text-right">Debit (-)</th>
                                    <th class="border-0 text-right">Credit (+)</th>
                                    <th class="border-0 text-right">Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $runningBalance = 0; @endphp
                                @forelse($savingsAccount->transactions->sortBy('date') as $transaction)
                                    @php 
                                        if(in_array($transaction->transaction_type, [1, 3])) $runningBalance += $transaction->amount;
                                        else $runningBalance -= $transaction->amount;
                                    @endphp
                                    <tr>
                                        <td>{{ date('d M, Y', strtotime($transaction->date)) }}</td>
                                        <td>
                                            @if($transaction->transaction_type == 1) <span class="badge badge-success-soft text-success px-2 py-1"><i class="fa fa-arrow-down mr-1"></i> Deposit</span>
                                            @elseif($transaction->transaction_type == 2) <span class="badge badge-danger-soft text-danger px-2 py-1"><i class="fa fa-arrow-up mr-1"></i> Withdraw</span>
                                            @elseif($transaction->transaction_type == 3) <span class="badge badge-info-soft text-info px-2 py-1"><i class="fa fa-plus mr-1"></i> Interest</span>
                                            @else 
                                                @php
                                                    $isFee = false;
                                                    if (!empty($transaction->notes) && (
                                                        str_contains(strtolower($transaction->notes), 'fee') || 
                                                        str_contains(strtolower($transaction->notes), 'deduction') || 
                                                        str_contains(strtolower($transaction->notes), 'charge')
                                                    )) {
                                                        $isFee = true;
                                                    }
                                                @endphp
                                                @if($isFee)
                                                    <span class="badge badge-warning-soft text-warning px-2 py-1"><i class="fa fa-minus mr-1"></i> Fee / Deduction</span>
                                                @else
                                                    <span class="badge badge-warning-soft text-warning px-2 py-1"><i class="fa fa-minus mr-1"></i> Penalty</span>
                                                @endif
                                            @endif
                                            <div class="small text-muted mt-1">{{ $transaction->notes }}</div>
                                        </td>
                                        <td class="text-right">
                                            {{ in_array($transaction->transaction_type, [2, 4]) ? number_format($transaction->amount, 2) : '-' }}
                                        </td>
                                        <td class="text-right">
                                            {{ in_array($transaction->transaction_type, [1, 3]) ? number_format($transaction->amount, 2) : '-' }}
                                        </td>
                                        <td class="text-right font-weight-bold text-dark">
                                            {{ number_format($runningBalance, 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            <i class="fas fa-history fa-3x mb-3 opacity-25"></i>
                                            <p>No transactions found for this account yet.</p>
                                        </td>
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
@endsection

@section('script')
<script>
    function approveAccount(id) {
        let productType = parseInt("{{ $savingsAccount->product->product_type }}") || 1;
        let openingBalance = parseFloat("{{ $savingsAccount->opening_balance }}") || 0;
        let openingFee = parseFloat("{{ $savingsAccount->product->opening_fee }}") || 0;
        let admissionFee = parseFloat("{{ $savingsAccount->member->admission_fee ?? 0 }}") || 0;
        let formFee = parseFloat("{{ $savingsAccount->member->form_fee ?? 0 }}") || 0;
        let passbookFee = parseFloat("{{ $savingsAccount->member->passbook_fee ?? 0 }}") || 0;

        let totalPayable = openingBalance + openingFee;
        if (productType === 1) {
            totalPayable += admissionFee + formFee + passbookFee;
        }

        let feesHtml = '';
        if (productType === 1) {
            feesHtml += `
                <tr class="align-middle">
                    <td class="text-muted align-middle"><i class="fas fa-user-plus mr-1 text-info"></i> ভর্তি ফি (Admission Fee):</td>
                    <td class="text-right" style="width: 130px;">
                        <input type="number" step="any" min="0" id="swal_admission_fee" class="form-control form-control-sm text-right swal-calc-input font-weight-bold text-dark" value="${admissionFee}" style="border-radius: 6px; height: 30px; padding: 2px 6px;">
                    </td>
                </tr>
                <tr class="align-middle">
                    <td class="text-muted align-middle"><i class="fas fa-file-alt mr-1 text-info"></i> ফরম ফি (Form Fee):</td>
                    <td class="text-right" style="width: 130px;">
                        <input type="number" step="any" min="0" id="swal_form_fee" class="form-control form-control-sm text-right swal-calc-input font-weight-bold text-dark" value="${formFee}" style="border-radius: 6px; height: 30px; padding: 2px 6px;">
                    </td>
                </tr>
                <tr class="align-middle">
                    <td class="text-muted align-middle"><i class="fas fa-book mr-1 text-info"></i> পাসবই ফি (Passbook Fee):</td>
                    <td class="text-right" style="width: 130px;">
                        <input type="number" step="any" min="0" id="swal_passbook_fee" class="form-control form-control-sm text-right swal-calc-input font-weight-bold text-dark" value="${passbookFee}" style="border-radius: 6px; height: 30px; padding: 2px 6px;">
                    </td>
                </tr>
            `;
        }

        Swal.fire({
            title: 'হিসাব অনুমোদন করবেন?',
            html: `
                <div class="text-left p-3 border bg-light shadow-sm" style="font-size: 14px; border-radius: 12px;">
                    <p class="mb-3 text-muted text-center" style="font-size: 13px;">অনুমোদনের পূর্বে ক্যাশ কাউন্টার থেকে নিচের হিসাব অনুযায়ী নগদ গ্রহণ নিশ্চিত করুন।</p>
                    <table class="table table-sm table-borderless mb-0">
                        <tr class="align-middle">
                            <td class="text-muted align-middle"><i class="fas fa-wallet mr-1 text-primary"></i> সঞ্চয় জমা (Savings Deposit):</td>
                            <td class="text-right" style="width: 130px;">
                                <input type="number" step="any" min="0" id="swal_opening_balance" class="form-control form-control-sm text-right swal-calc-input font-weight-bold text-dark" value="${openingBalance}" style="border-radius: 6px; height: 30px; padding: 2px 6px;">
                            </td>
                        </tr>
                        <tr class="align-middle">
                            <td class="text-muted align-middle"><i class="fas fa-receipt mr-1 text-warning"></i> হিসাব খোলার ফি (Opening Fee):</td>
                            <td class="text-right" style="width: 130px;">
                                <input type="number" step="any" min="0" id="swal_opening_fee" class="form-control form-control-sm text-right swal-calc-input font-weight-bold text-dark" value="${openingFee}" style="border-radius: 6px; height: 30px; padding: 2px 6px;">
                            </td>
                        </tr>
                        ${feesHtml}
                        <tr class="border-top" style="border-top: 2px dashed #dee2e6 !important;">
                            <td class="text-muted pt-2 font-weight-bold align-middle" style="font-size: 15px;"><i class="fas fa-coins mr-1 text-success"></i> মোট আদায়যোগ্য নগদ (Total Cash):</td>
                            <td class="text-right font-weight-bold text-success pt-2 align-middle pr-2" style="font-size: 16px;" id="swal_total_cash">৳ ${totalPayable.toFixed(2)}</td>
                        </tr>
                    </table>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'হ্যাঁ, অনুমোদন করুন!',
            cancelButtonText: 'বাতিল',
            showLoaderOnConfirm: true,
            didOpen: () => {
                const updateSum = () => {
                    let opBal = parseFloat($('#swal_opening_balance').val()) || 0;
                    let opFee = parseFloat($('#swal_opening_fee').val()) || 0;
                    let admFee = parseFloat($('#swal_admission_fee').val()) || 0;
                    let frmFee = parseFloat($('#swal_form_fee').val()) || 0;
                    let psbFee = parseFloat($('#swal_passbook_fee').val()) || 0;

                    let total = opBal + opFee + admFee + frmFee + psbFee;
                    $('#swal_total_cash').text('৳ ' + total.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                };

                $(document).on('input change', '.swal-calc-input', updateSum);
            },
            preConfirm: () => {
                let opBal = parseFloat($('#swal_opening_balance').val()) || 0;
                let opFee = parseFloat($('#swal_opening_fee').val()) || 0;
                let admFee = parseFloat($('#swal_admission_fee').val()) || 0;
                let frmFee = parseFloat($('#swal_form_fee').val()) || 0;
                let psbFee = parseFloat($('#swal_passbook_fee').val()) || 0;

                return $.ajax({
                    url: "{{ url('admin/microfinance/savings-account') }}/" + id + "/approve",
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        opening_balance: opBal,
                        opening_fee: opFee,
                        admission_fee: admFee,
                        form_fee: frmFee,
                        passbook_fee: psbFee
                    }
                }).catch(error => {
                    Swal.showValidationMessage(`Request failed: ${error.responseJSON?.message || error.statusText}`);
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed && result.value.success) {
                Swal.fire('Approved!', result.value.message, 'success').then(() => {
                    location.reload();
                });
            } else if (result.isConfirmed) {
                Swal.fire('Error!', result.value.message, 'error');
            }
        });
    }

    function withdrawModal(id) {
        Swal.fire({
            title: 'Savings Account Withdrawal',
            html: `
                <div class="text-left">
                    <div class="form-group">
                        <label>Withdrawal Amount (৳) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" id="withdraw_amount" class="form-control" placeholder="0.00" required>
                    </div>
                    <div class="form-group">
                        <label>Date <span class="text-danger">*</span></label>
                        <input type="date" id="withdraw_date" class="form-control" value="${new Date().toISOString().split('T')[0]}" required>
                    </div>
                    <div class="form-group">
                        <label>Notes</label>
                        <textarea id="withdraw_notes" class="form-control" rows="2" placeholder="Reason/remarks..."></textarea>
                    </div>
                </div>
            `,
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Withdraw Now',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                const amount = $('#withdraw_amount').val();
                const date = $('#withdraw_date').val();
                const notes = $('#withdraw_notes').val();

                if (!amount || amount <= 0) {
                    Swal.showValidationMessage('Please enter a valid withdrawal amount.');
                    return false;
                }

                return $.ajax({
                    url: "{{ url('admin/microfinance/savings-account') }}/" + id + "/withdraw",
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        amount: amount,
                        date: date,
                        notes: notes
                    }
                }).catch(error => {
                    Swal.showValidationMessage(`Request failed: ${error.responseJSON?.message || error.statusText}`);
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed && result.value.success) {
                Swal.fire('Success!', result.value.message, 'success').then(() => {
                    location.reload();
                });
            }
        });
    }

    function postInterest(id) {
        Swal.fire({
            title: 'Post Savings Interest?',
            text: "This will calculate and post the monthly interest amount to this account based on the product interest rate.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#17a2b8',
            confirmButtonText: 'Yes, Post Interest',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                return $.ajax({
                    url: "{{ url('admin/microfinance/savings-account') }}/" + id + "/post-interest",
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    }
                }).catch(error => {
                    Swal.showValidationMessage(`Request failed: ${error.responseJSON?.message || error.statusText}`);
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed && result.value.success) {
                Swal.fire('Posted!', result.value.message, 'success').then(() => {
                    location.reload();
                });
            }
        });
    }

    function closeAccountModal(id) {
        Swal.fire({
            title: 'Close Savings Account?',
            html: `
                <div class="text-left">
                    <p class="text-danger font-weight-bold">Warning: This action will close the account. Any closing fee defined in the product scheme will be automatically deducted.</p>
                    <div class="form-group">
                        <label>Reason for Closing</label>
                        <textarea id="close_reason" class="form-control" rows="3" placeholder="Reason..."></textarea>
                    </div>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#343a40',
            confirmButtonText: 'Yes, Close Account',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                const reason = $('#close_reason').val();
                return $.ajax({
                    url: "{{ url('admin/microfinance/savings-account') }}/" + id + "/close",
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        close_reason: reason
                    }
                }).catch(error => {
                    Swal.showValidationMessage(`Request failed: ${error.responseJSON?.message || error.statusText}`);
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire('Closed!', 'The account has been closed successfully.', 'success').then(() => {
                    location.reload();
                });
            }
        });
    }

    function renewFdrModal(id) {
        let optionsHtml = '';
        @if(isset($fdrProducts))
            @foreach($fdrProducts as $prod)
                let selected = '{{ $savingsAccount->mf_savings_product_id == $prod->id ? "selected" : "" }}';
                optionsHtml += `<option value="{{ $prod->id }}" ${selected}>{{ $prod->name }} ({{ $prod->interest_rate }}% Interest)</option>`;
            @endforeach
        @endif

        Swal.fire({
            title: 'FDR Manual Renewal',
            html: `
                <div class="text-left">
                    <p class="text-info font-weight-bold"><i class="fas fa-info-circle mr-1"></i> এফডিআর মেয়াদপূর্তির পর নতুন মেয়াদের জন্য নবায়ন করুন।</p>
                    <div class="form-group">
                        <label>Renewal Date <span class="text-danger">*</span></label>
                        <input type="text" id="renewal_date" class="form-control" value="{{ date('d-m-Y') }}" required readonly>
                    </div>
                    <div class="form-group">
                        <label>Renewal Mode (নবায়নের ধরণ) <span class="text-danger">*</span></label>
                        <select id="renewal_mode" class="form-control" required>
                            <option value="principal_only">Principal Only (শুধুমাত্র আসল নবায়ন - লভ্যাংশ GS-এ স্থানান্তর)</option>
                            <option value="compound">Compound Principal + Profit (আসল ও লভ্যাংশ একসাথে নবায়ন)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Select FDR Product Scheme <span class="text-danger">*</span></label>
                        <select id="renewal_product_id" class="form-control" required>
                            ${optionsHtml}
                        </select>
                    </div>
                </div>
            `,
            icon: 'sync',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            confirmButtonText: 'Confirm & Renew',
            didOpen: () => {
                if ($.isFunction($.fn.datepicker)) {
                    $('#renewal_date').datepicker({
                        dateFormat: 'dd-mm-yy',
                        changeMonth: true,
                        changeYear: true,
                        autoclose: true,
                        todayHighlight: true
                    });
                }
            },
            showLoaderOnConfirm: true,
            preConfirm: () => {
                const renewalDate = $('#renewal_date').val();
                const renewalMode = $('#renewal_mode').val();
                const productId = $('#renewal_product_id').val();

                if (!renewalDate) {
                    Swal.showValidationMessage('Please select a valid renewal date.');
                    return false;
                }

                return $.ajax({
                    url: "{{ url('admin/microfinance/savings-account') }}/" + id + "/renew",
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        renewal_date: renewalDate,
                        renewal_mode: renewalMode,
                        mf_savings_product_id: productId
                    }
                }).catch(error => {
                    Swal.showValidationMessage(`Request failed: ${error.responseJSON?.message || error.statusText}`);
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed && result.value.success) {
                Swal.fire('Renewed!', result.value.message, 'success').then(() => {
                    location.reload();
                });
            }
        });
    }
</script>
@endsection
