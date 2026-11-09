@extends('layouts.app')
@section('title', $pageTitle)
@section('content')
@php
    $totalActivity = $savingsDeposits + $savingsWithdrawals + $savingsInterest + $loansDisbursed + $loanCollections + $openingFeesCollected + $savingsPenalties;
@endphp
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar Filter / Trigger Panel -->
        <div class="col-md-4">
            <div class="card card-default shadow-sm mb-4" style="border-radius: 15px;">
                <div class="card-header bg-dark text-white" style="border-radius: 15px 15px 0 0;">
                    <h3 class="card-title font-weight-bold mb-0"><i class="fa fa-calendar-alt mr-2"></i> Date Selection</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('microfinance.day-closing.index') }}" method="get">
                        <div class="form-group mb-3">
                            <label class="font-weight-bold">Select Date:</label>
                            <input type="date" name="date" class="form-control" value="{{ $date }}" onchange="this.form.submit()">
                        </div>
                    </form>

                    <hr>

                    @if($alreadyClosed)
                        <div class="alert alert-success border-0 text-center py-4" style="border-radius: 12px;">
                            <i class="fa fa-check-circle fa-3x mb-3"></i>
                            <h5 class="font-weight-bold">Day Already Closed</h5>
                            <p class="small mb-0">Daily summary voucher has already been successfully generated and posted to the General Ledger for this date.</p>
                        </div>
                    @else
                        @if($totalActivity > 0)
                            <form id="day-closing-form" action="{{ route('microfinance.day-closing.store') }}" method="POST">
                                @csrf
                                <input type="hidden" name="date" value="{{ $date }}">
                                
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold">Debit/Credit Cash Account: <span class="text-danger">*</span></label>
                                    <select name="cash_account_id" class="form-control select2" required>
                                        @foreach($cashAccounts as $ca)
                                            <option value="{{ $ca->id }}">{{ $ca->name }} (৳{{ number_format($ca->current_balance, 2) }})</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">The cash inflows/outflows of the day will be adjusted against this corporate cash/bank account.</small>
                                </div>

                                <button type="submit" class="btn btn-primary btn-lg btn-block font-weight-bold" style="border-radius: 12px;">
                                    <i class="fa fa-file-invoice-dollar mr-2"></i> POST TO GENERAL LEDGER
                                </button>
                            </form>
                        @else
                            <div class="alert alert-info border-0 text-center py-4" style="border-radius: 12px;">
                                <i class="fa fa-info-circle fa-3x mb-3"></i>
                                <h5 class="font-weight-bold">No Transactions</h5>
                                <p class="small mb-0">No microfinance activities (deposits, collections, disbursements, withdrawals) found for the selected date.</p>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        <!-- Summary Panel -->
        <div class="col-md-8">
            <div class="card card-outline card-primary shadow-sm" style="border-radius: 15px;">
                <div class="card-header bg-white py-3 border-0">
                    <h3 class="card-title font-weight-bold text-dark mb-0"><i class="fa fa-chart-line mr-2 text-primary"></i> Daily Activity Summary — {{ date('d M, Y', strtotime($date)) }}</h3>
                </div>
                <div class="card-body">
                    <!-- Stat Cards Row -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded shadow-sm text-center mb-3">
                                <div class="text-muted small font-weight-bold uppercase mb-1">Savings Deposits</div>
                                <h4 class="font-weight-bold text-success">৳ {{ number_format($savingsDeposits, 2) }}</h4>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded shadow-sm text-center mb-3">
                                <div class="text-muted small font-weight-bold uppercase mb-1">Savings Withdrawals</div>
                                <h4 class="font-weight-bold text-danger">৳ {{ number_format($savingsWithdrawals, 2) }}</h4>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded shadow-sm text-center mb-3">
                                <div class="text-muted small font-weight-bold uppercase mb-1">Interest Paid</div>
                                <h4 class="font-weight-bold text-warning">৳ {{ number_format($savingsInterest, 2) }}</h4>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded shadow-sm text-center mb-3">
                                <div class="text-muted small font-weight-bold uppercase mb-1">Account Opening Fees</div>
                                <h4 class="font-weight-bold text-success">৳ {{ number_format($openingFeesCollected, 2) }}</h4>
                                <small class="text-muted">Direct Revenue (নগদ আদায়)</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded shadow-sm text-center mb-3">
                                <div class="text-muted small font-weight-bold uppercase mb-1">Savings Penalties (FDR/DPS)</div>
                                <h4 class="font-weight-bold text-info">৳ {{ number_format($savingsPenalties, 2) }}</h4>
                                <small class="text-muted">Early Closure Revenue (জরিমানা আয়)</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded shadow-sm text-center mb-3">
                                <div class="text-muted small font-weight-bold uppercase mb-1">Loans Disbursed (Full Asset value)</div>
                                <h4 class="font-weight-bold text-primary">৳ {{ number_format($loansDisbursed, 2) }}</h4>
                                <small class="text-muted">Net Outflow: ৳ {{ number_format($netDisbursed, 2) }} | Fees: ৳ {{ number_format($loanFees, 2) }}</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded shadow-sm text-center mb-3">
                                <div class="text-muted small font-weight-bold uppercase mb-1">Loan Collections</div>
                                <h4 class="font-weight-bold text-success">৳ {{ number_format($loanCollections, 2) }}</h4>
                                <small class="text-muted">Principal: ৳ {{ number_format($collectedPrincipal, 2) }} | Interest: ৳ {{ number_format($collectedInterest, 2) }}</small>
                            </div>
                        </div>
                    </div>

                    <!-- Accounting Breakdown Table -->
                    <h5 class="font-weight-bold mb-3"><i class="fa fa-calculator mr-2 text-secondary"></i> Projected Double-Entry Journal Entries</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped text-center">
                            <thead class="bg-dark text-white">
                                <tr>
                                    <th>Particulars / Account Head</th>
                                    <th>GL Nature</th>
                                    <th>Debit (৳)</th>
                                    <th>Credit (৳)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Savings Deposits -->
                                @if($savingsDeposits > 0)
                                    <tr>
                                        <td class="text-left font-weight-bold">Cash/Bank Account <br><small class="text-muted">MFI Daily Savings Deposits</small></td>
                                        <td>Asset</td>
                                        <td class="font-weight-bold text-success">{{ number_format($savingsDeposits, 2) }}</td>
                                        <td>—</td>
                                    </tr>
                                    <tr>
                                        <td class="text-left">Member Savings Account Liability <br><small class="text-muted">MFI Daily Savings Deposits</small></td>
                                        <td>Liability</td>
                                        <td>—</td>
                                        <td class="font-weight-bold text-danger">{{ number_format($savingsDeposits, 2) }}</td>
                                    </tr>
                                @endif

                                <!-- Savings Withdrawals -->
                                @if($savingsWithdrawals > 0)
                                    <tr>
                                        <td class="text-left font-weight-bold">Member Savings Account Liability <br><small class="text-muted">MFI Daily Savings Withdrawals</small></td>
                                        <td>Liability</td>
                                        <td class="font-weight-bold text-success">{{ number_format($savingsWithdrawals, 2) }}</td>
                                        <td>—</td>
                                    </tr>
                                    <tr>
                                        <td class="text-left">Cash/Bank Account <br><small class="text-muted">MFI Daily Savings Withdrawals</small></td>
                                        <td>Asset</td>
                                        <td>—</td>
                                        <td class="font-weight-bold text-danger">{{ number_format($savingsWithdrawals, 2) }}</td>
                                    </tr>
                                @endif

                                <!-- Savings Interest -->
                                @if($savingsInterest > 0)
                                    <tr>
                                        <td class="text-left font-weight-bold">Interest Paid on Savings <br><small class="text-muted">MFI Monthly/Periodic Savings Interest</small></td>
                                        <td>Expense</td>
                                        <td class="font-weight-bold text-success">{{ number_format($savingsInterest, 2) }}</td>
                                        <td>—</td>
                                    </tr>
                                    <tr>
                                        <td class="text-left">Member Savings Account Liability <br><small class="text-muted">MFI Monthly/Periodic Savings Interest</small></td>
                                        <td>Liability</td>
                                        <td>—</td>
                                        <td class="font-weight-bold text-danger">{{ number_format($savingsInterest, 2) }}</td>
                                    </tr>
                                @endif

                                <!-- Loan Disbursements -->
                                @if($loansDisbursed > 0)
                                    <tr>
                                        <td class="text-left font-weight-bold">General Loan Portfolio <br><small class="text-muted">MFI Daily Loan Disbursements Summary</small></td>
                                        <td>Asset</td>
                                        <td class="font-weight-bold text-success">{{ number_format($loansDisbursed, 2) }}</td>
                                        <td>—</td>
                                    </tr>
                                    @if($netDisbursed > 0)
                                        <tr>
                                            <td class="text-left">Cash/Bank Account <br><small class="text-muted">MFI Net Disbursed Outflow</small></td>
                                            <td>Asset</td>
                                            <td>—</td>
                                            <td class="font-weight-bold text-danger">{{ number_format($netDisbursed, 2) }}</td>
                                        </tr>
                                    @endif
                                    @if($loanFees > 0)
                                        <tr>
                                            <td class="text-left">MFI Service Charge Income <br><small class="text-muted">Processing & Insurance Fees Summary</small></td>
                                            <td>Revenue</td>
                                            <td>—</td>
                                            <td class="font-weight-bold text-danger">{{ number_format($loanFees, 2) }}</td>
                                        </tr>
                                    @endif
                                @endif

                                <!-- Loan Collections -->
                                @if($loanCollections > 0)
                                    <tr>
                                        <td class="text-left font-weight-bold">Cash/Bank Account <br><small class="text-muted">MFI Daily Loan Collections Summary</small></td>
                                        <td>Asset</td>
                                        <td class="font-weight-bold text-success">{{ number_format($loanCollections, 2) }}</td>
                                        <td>—</td>
                                    </tr>
                                    @if($collectedPrincipal > 0)
                                        <tr>
                                            <td class="text-left">General Loan Portfolio <br><small class="text-muted">Collected Principal portion Summary</small></td>
                                            <td>Asset</td>
                                            <td>—</td>
                                            <td class="font-weight-bold text-danger">{{ number_format($collectedPrincipal, 2) }}</td>
                                        </tr>
                                    @endif
                                    @if($collectedInterest > 0)
                                        <tr>
                                            <td class="text-left">MFI Service Charge Income <br><small class="text-muted">Collected Interest portion Summary</small></td>
                                            <td>Revenue</td>
                                            <td>—</td>
                                            <td class="font-weight-bold text-danger">{{ number_format($collectedInterest, 2) }}</td>
                                        </tr>
                                    @endif
                                @endif

                                <!-- Savings Account Opening Fees -->
                                @if($openingFeesCollected > 0)
                                    <tr>
                                        <td class="text-left font-weight-bold">Cash/Bank Account <br><small class="text-muted">MFI Daily Savings Account Opening Fees</small></td>
                                        <td>Asset</td>
                                        <td class="font-weight-bold text-success">{{ number_format($openingFeesCollected, 2) }}</td>
                                        <td>—</td>
                                    </tr>
                                    <tr>
                                        <td class="text-left">MFI Service Charge Income <br><small class="text-muted">Daily Savings Account Opening Fees</small></td>
                                        <td>Revenue</td>
                                        <td>—</td>
                                        <td class="font-weight-bold text-danger">{{ number_format($openingFeesCollected, 2) }}</td>
                                    </tr>
                                @endif

                                <!-- Savings Penalties (Early Closure Reversals) -->
                                @if($savingsPenalties > 0)
                                    <tr>
                                        <td class="text-left font-weight-bold">Member Savings Account Liability <br><small class="text-muted">MFI Daily Savings Penalties (FDR/DPS Pre-closure)</small></td>
                                        <td>Liability</td>
                                        <td class="font-weight-bold text-success">{{ number_format($savingsPenalties, 2) }}</td>
                                        <td>—</td>
                                    </tr>
                                    <tr>
                                        <td class="text-left">MFI Service Charge Income <br><small class="text-muted">Daily Savings Penalties (FDR/DPS Pre-closure)</small></td>
                                        <td>Revenue</td>
                                        <td>—</td>
                                        <td class="font-weight-bold text-danger">{{ number_format($savingsPenalties, 2) }}</td>
                                    </tr>
                                @endif

                                @if($totalActivity == 0)
                                    <tr>
                                        <td colspan="4" class="text-muted py-4">No activities to preview.</td>
                                    </tr>
                                @endif
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
    $(document).ready(function() {
        $('#day-closing-form').on('submit', function(e) {
            e.preventDefault();
            
            const cashAccountName = $('select[name="cash_account_id"] option:selected').text();
            const dateStr = "{{ date('d M, Y', strtotime($date)) }}";
            
            Swal.fire({
                title: '<strong>নিশ্চিত দিন সমাপ্তি (EOD)?</strong>',
                icon: 'warning',
                html: `
                    <div class="text-left p-2" style="font-size: 14px; line-height: 1.6;">
                        <p class="mb-2">আপনি কি আজকের দিন বন্ধ করতে নিশ্চিত? এটি একটি অত্যন্ত গুরুত্বপূর্ণ এবং অপরিবর্তনীয় হিসাব:</p>
                        <table class="table table-bordered table-sm mb-3">
                            <tr class="bg-light">
                                <td class="font-weight-bold" style="width: 45%;">দিন বন্ধের তারিখ:</td>
                                <td class="text-primary font-weight-bold">${dateStr}</td>
                            </tr>
                            <tr class="bg-light">
                                <td class="font-weight-bold">সমন্বয় ক্যাশ লেজার:</td>
                                <td class="text-success font-weight-bold">${cashAccountName}</td>
                            </tr>
                        </table>
                        <div class="alert alert-danger border-0 mb-0 py-2.5 px-3 rounded d-flex align-items-start" style="background: rgba(220,53,69,0.05); color: #dc3545; font-size: 13px;">
                            <i class="fas fa-exclamation-triangle mr-2 mt-1" style="font-size: 15px;"></i>
                            <div>
                                <strong>গুরুত্বপূর্ণ সতর্কতা:</strong> দিন সমাপ্তি সফল হলে আজকের সমস্ত সঞ্চয় জমা, উত্তোলন, লোন বিতরণ ও কিস্তির হিসাব চূড়ান্তভাবে কোম্পানির মূল লেজারে স্থানান্তরিত হবে এবং এই তারিখের জন্য হিসাবের নতুন এন্ট্রি লক করা হবে।
                            </div>
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-check-circle mr-1"></i> হ্যাঁ, দিন বন্ধ করুন (Post)',
                cancelButtonText: 'বাতিল করুন (Cancel)',
                customClass: {
                    popup: 'rounded-lg border-0 shadow-lg',
                    confirmButton: 'px-4 py-2.5 rounded-pill font-weight-bold',
                    cancelButton: 'px-4 py-2.5 rounded-pill font-weight-bold ml-2'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'অপেক্ষা করুন...',
                        text: 'দিনের সমস্ত হিসাব খতিয়ানে পোস্টিং হচ্ছে...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    document.getElementById('day-closing-form').submit();
                }
            });
        });
    });
</script>
@endsection
