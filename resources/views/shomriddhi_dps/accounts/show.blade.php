@extends('layouts.app')
@section('title', 'DPS Account Details')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-default">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-piggy-bank mr-2"></i> DPS Account Details: <span class="text-primary">{{ $account->dps_no }}</span>
                </h5>
                <div class="card-tools">
                    @if($account->status === 'Pending')
                        <a href="{{ route('shomriddhi-dps.accounts.edit', $account->id) }}" class="btn btn-warning btn-sm mr-1"><i class="fa fa-edit"></i> Edit Account</a>
                    @endif
                    <a href="{{ route('shomriddhi-dps.accounts.index') }}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Back to List</a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">

    {{-- Customer Info Card --}}
    <div class="col-md-4">
        <div class="card card-outline card-primary shadow-sm h-100">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0"><i class="fa fa-user mr-2"></i> Customer Details</h5>
            </div>
            <div class="card-body text-center">
                @if($account->customer->image)
                    <img src="{{ asset($account->customer->image) }}" class="img-fluid rounded-circle mb-3 border shadow-sm" style="width: 120px; height: 120px; object-fit: cover;">
                @else
                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center mx-auto mb-3 border shadow-sm" style="width: 120px; height: 120px;">
                        <i class="fa fa-user fa-4x text-secondary"></i>
                    </div>
                @endif
                <h5 class="font-weight-bold text-dark">{{ $account->customer->name }}</h5>
                <p class="text-muted mb-1">Member No: <strong>{{ $account->customer->member_no ?? 'N/A' }}</strong></p>
                <p class="text-muted mb-1"><i class="fa fa-phone-alt"></i> {{ $account->customer->mobile_no ?? 'N/A' }}</p>
                @php
                    $addressParts = [];
                    if($account->customer->village) $addressParts[] = 'Vill: ' . $account->customer->village->name;
                    if($account->customer->pourashava) $addressParts[] = 'Pourashava: ' . $account->customer->pourashava->name;
                    if($account->customer->union) $addressParts[] = 'Union: ' . $account->customer->union->name;
                    if($account->customer->upazila) $addressParts[] = 'P.S: ' . $account->customer->upazila->name;
                    if($account->customer->district) $addressParts[] = 'Dist: ' . $account->customer->district->name;
                    if($account->customer->division) $addressParts[] = 'Div: ' . $account->customer->division->name;
                    $fullAddress = implode(', ', $addressParts);
                @endphp
                @if($fullAddress)
                    <p class="text-muted small"><i class="fa fa-map-marker-alt"></i> {{ $fullAddress }}</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Account Details Card --}}
    <div class="col-md-8">
        <div class="card card-outline card-info shadow-sm h-100">
            <div class="card-header bg-info text-white">
                <h5 class="card-title mb-0"><i class="fa fa-info-circle mr-2"></i> Account Information</h5>
                <div class="card-tools">
                    @php
                        $badge = 'badge-secondary';
                        if($account->status === 'Active') $badge = 'badge-success';
                        elseif($account->status === 'Pending') $badge = 'badge-warning';
                        elseif($account->status === 'Closed') $badge = 'badge-danger';
                    @endphp
                    <span class="badge {{ $badge }}" style="font-size: 0.9rem;">{{ $account->status }}</span>
                </div>
            </div>
            <div class="card-body p-0">
                <table class="table table-bordered table-striped mb-0">
                    <tbody>
                        <tr>
                            <th style="width: 30%">Scheme Name</th>
                            <td><span class="font-weight-bold text-primary">{{ $account->scheme->name }}</span></td>
                        </tr>
                        <tr>
                            <th>Installment Amount</th>
                            <td><span class="font-weight-bold text-success">৳ {{ number_format($account->installment_amount, 2) }}</span> ({{ $account->installment_type }})</td>
                        </tr>
                        <tr>
                            <th>Interest Rate</th>
                            <td>{{ number_format($account->interest_rate, 2) }}% p.a.</td>
                        </tr>
                        <tr>
                            <th>Opening Date</th>
                            <td>{{ \Carbon\Carbon::parse($account->opening_date)->format('d F, Y') }}</td>
                        </tr>
                        <tr>
                            <th>Maturity Date</th>
                            <td>
                                <span class="text-danger font-weight-bold">{{ \Carbon\Carbon::parse($account->maturity_date)->format('d F, Y') }}</span>
                            </td>
                        </tr>
                        <tr>
                            <th>Duration</th>
                            <td>{{ $account->scheme->duration_months }} Months (Total {{ $account->total_installments }} Installments)</td>
                        </tr>
                        <tr>
                            <th>Paid Installments</th>
                            <td><span class="font-weight-bold text-success">{{ floor($account->paid_installments) }}</span> out of {{ $account->total_installments }}</td>
                        </tr>
                        <tr>
                            <th>Total Deposited</th>
                            <td><span class="font-weight-bold">৳ {{ number_format($account->total_deposited, 2) }}</span></td>
                        </tr>
                        <tr>
                            <th>Next Installment Date</th>
                            <td>
                                @if($account->next_installment_date)
                                    <span class="font-weight-bold {{ \Carbon\Carbon::parse($account->next_installment_date)->isPast() ? 'text-danger' : 'text-primary' }}">
                                        {{ \Carbon\Carbon::parse($account->next_installment_date)->format('d M, Y') }}
                                    </span>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Payable at Maturity</th>
                            <td><span class="font-weight-bold text-primary">৳ {{ number_format($account->payable_at_maturity, 2) }}</span></td>
                        </tr>
                        <tr>
                            <th>Auto Renew</th>
                            <td>
                                @if($account->auto_renew)
                                    <span class="badge badge-success">Yes</span>
                                @else
                                    <span class="badge badge-secondary">No</span>
                                @endif
                            </td>
                        </tr>
                        @if($account->notes)
                        <tr>
                            <th>Notes</th>
                            <td class="text-muted">{{ $account->notes }}</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    {{-- Nominees Section --}}
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header bg-secondary text-white">
                <h5 class="card-title mb-0"><i class="fa fa-users mr-2"></i> Nominee Details</h5>
            </div>
            <div class="card-body p-0">
                @if($account->nominees->count() > 0)
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Relation</th>
                                <th>Phone</th>
                                <th>Share (%)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($account->nominees as $index => $nominee_record)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td class="font-weight-bold">{{ $nominee_record->nominee->name ?? 'N/A' }}</td>
                                    <td>{{ $nominee_record->nominee->relation ?? 'N/A' }}</td>
                                    <td>{{ $nominee_record->nominee->phone ?? 'N/A' }}</td>
                                    <td><span class="badge badge-info px-2 py-1">{{ $nominee_record->share_percentage }}%</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="p-4 text-center text-muted">
                        <i class="fa fa-user-times fa-3x mb-2 opacity-50"></i>
                        <p class="mb-0">No nominees attached to this account.</p>
                    </div>
                @endif
            </div>
        </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    {{-- Recent Transactions Section --}}
    <div class="col-md-12">
        <div class="card shadow-sm border-primary">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0"><i class="fa fa-list-alt mr-2"></i> Recent Transactions</h5>
            </div>
            <div class="card-body p-0">
                @if($account->transactions->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Installment #</th>
                                    <th>Amount</th>
                                    <th>Penalty</th>
                                    <th>Mode</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($account->transactions->sortByDesc('id')->take(10) as $txn)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($txn->date)->format('d-M-Y') }}</td>
                                        <td>
                                            @if($txn->type == 'Installment') <span class="badge badge-success">Installment</span>
                                            @elseif($txn->type == 'Penalty') <span class="badge badge-warning">Penalty</span>
                                            @elseif($txn->type == 'Encashment') <span class="badge badge-primary">Encashment</span>
                                            @else <span class="badge badge-secondary">{{ $txn->type }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $insText = '-';
                                                if($txn->installment_no) {
                                                    $count = $account->installment_amount > 0 ? floor($txn->amount / $account->installment_amount) : 1;
                                                    if ($count > 1) {
                                                        $end = $txn->installment_no + $count - 1;
                                                        $insText = "#{$txn->installment_no} - #{$end}";
                                                    } else {
                                                        $insText = "#{$txn->installment_no}";
                                                    }
                                                }
                                            @endphp
                                            {{ $insText }}
                                        </td>
                                        <td class="font-weight-bold text-success">৳ {{ number_format($txn->amount, 2) }}</td>
                                        <td class="{{ $txn->penalty_amount > 0 ? 'text-danger font-weight-bold' : '' }}">
                                            {{ $txn->penalty_amount > 0 ? '৳ ' . number_format($txn->penalty_amount, 2) : '-' }}
                                        </td>
                                        <td>{{ $txn->paymentHead->name ?? 'Cash/Bank' }}</td>
                                        <td>
                                            @if($txn->status == 'Pending') <span class="badge badge-warning">Pending</span>
                                            @elseif($txn->status == 'Approved') <span class="badge badge-success">Approved</span>
                                            @else <span class="badge badge-danger">{{ $txn->status }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-4 text-center text-muted">
                        <i class="fa fa-file-invoice-dollar fa-3x mb-2 opacity-50"></i>
                        <p class="mb-0">No transactions recorded for this account yet.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Approve Action (Only if Pending) --}}
@if($account->status === 'Pending')
<div class="row mt-3">
    <div class="col-md-12">
        <div class="card border-success shadow-lg">
            <div class="card-header bg-success text-white">
                <h5 class="card-title mb-0"><i class="fa fa-check-circle mr-2"></i> Approve Account & Collect 1st Installment</h5>
            </div>
            <div class="card-body">
                <form id="approveForm">
                    @csrf
                    <input type="hidden" id="approve_account_id" value="{{ $account->id }}">
                    <div class="row align-items-end">
                        <div class="col-md-6 form-group mb-0">
                            <label class="font-weight-bold">Payment Mode (Received To) <span class="text-danger">*</span></label>
                            <select class="form-control select2" id="approve_payment_mode" name="payment_mode" required style="width:100%">
                                <option value="">-- Select Cash/Bank --</option>
                                @foreach($paymentModes as $pm)
                                    <option value="{{ $pm->id }}">{{ $pm->name }} ({{ $pm->code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <button type="button" class="btn btn-success btn-lg w-100 shadow-sm" id="btnPreviewApprove">
                                <i class="fa fa-check mr-2"></i> Confirm Approval
                            </button>
                        </div>
                    </div>
                    <p class="text-muted mt-3 mb-0"><i class="fa fa-info-circle"></i> Note: Approving will activate the account, record the first installment of <strong>৳{{ number_format($account->installment_amount, 2) }}</strong>, and automatically generate the necessary accounting vouchers.</p>
                </form>
            </div>
        </div>
    </div>
</div>
</div>
@endif

            </div>
        </div>
    </div>
</div>

{{-- Final Confirm Modal --}}
<div class="modal fade" id="finalConfirmModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content border-success">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fa fa-check-double mr-2"></i> Final Confirmation</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to approve this account?</p>
                <table class="table table-sm table-bordered">
                    <tbody>
                        <tr><th>Member Name</th><td>{{ $account->customer->name }}</td></tr>
                        <tr><th>Scheme</th><td>{{ $account->scheme->name }}</td></tr>
                        <tr><th>First Installment</th><td class="font-weight-bold text-success">৳ {{ number_format($account->installment_amount, 2) }}</td></tr>
                        <tr><th>Received To</th><td id="pv_payment_mode" class="font-weight-bold text-primary"></td></tr>
                    </tbody>
                </table>
                <p class="text-danger small mb-0"><i class="fa fa-exclamation-triangle"></i> This action will deduct the first installment from your selected account and cannot be easily undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="btnFinalSubmit"><i class="fa fa-check mr-2"></i> Yes, Approve Now</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script>
$(document).ready(function() {
    if ($.fn.select2) {
        $('.select2').select2();
    }

    $('#btnPreviewApprove').on('click', function() {
        const form = $('#approveForm')[0];
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const paymentModeText = $('#approve_payment_mode option:selected').text();
        $('#pv_payment_mode').text(paymentModeText);
        
        $('#finalConfirmModal').modal('show');
    });

    $('#btnFinalSubmit').on('click', function() {
        const id  = $('#approve_account_id').val();
        const btn = $(this);
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-2"></i> Processing...');

        $.ajax({
            method: 'POST',
            url: '{{ route('shomriddhi-dps.accounts.approve', ['account' => 'REPLACE']) }}'.replace('REPLACE', id),
            data: { 
                _token: '{{ csrf_token() }}', 
                payment_mode: $('#approve_payment_mode').val() 
            },
            success: function(r) {
                if (r.success) {
                    $('#finalConfirmModal').modal('hide');
                    Swal.fire({
                        title: 'Approved!',
                        text: r.message,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.href = '{{ route('shomriddhi-dps.accounts.index') }}';
                    });
                } else {
                    btn.prop('disabled', false).html('<i class="fa fa-check mr-2"></i> Yes, Approve Now');
                    Swal.fire('Error!', r.message, 'error');
                }
            },
            error: function() {
                btn.prop('disabled', false).html('<i class="fa fa-check mr-2"></i> Yes, Approve Now');
                Swal.fire('Error!', 'Something went wrong.', 'error');
            }
        });
    });
});
</script>
@endsection
