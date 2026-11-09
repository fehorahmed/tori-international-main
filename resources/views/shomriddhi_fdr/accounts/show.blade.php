@extends('layouts.app')
@section('title', 'FDR Account Details')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-default">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-file-invoice-dollar mr-2"></i> FDR Account Details: <span class="text-primary">{{ $account->fdr_no }}</span>
                </h5>
                <div class="card-tools">
                    @if($account->status === 'Pending')
                        <a href="{{ route('shomriddhi-fdr.accounts.edit', $account->id) }}" class="btn btn-warning btn-sm mr-1"><i class="fa fa-edit"></i> Edit Account</a>
                    @endif
                    <a href="{{ route('shomriddhi-fdr.accounts.index') }}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Back to List</a>
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
                        elseif($account->status === 'Matured') $badge = 'badge-primary';
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
                            <th>Principal Amount</th>
                            <td><span class="font-weight-bold text-success">৳ {{ number_format($account->principal_amount, 2) }}</span></td>
                        </tr>
                        <tr>
                            <th>Interest Rate</th>
                            <td>{{ number_format($account->scheme->interest_rate, 2) }}% p.a.</td>
                        </tr>
                        <tr>
                            <th>Payout Frequency</th>
                            <td>{{ $account->interest_payout_frequency }}</td>
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
                            <td>{{ $account->scheme->duration }} Months</td>
                        </tr>
                        <tr>
                            <th>Payable at Maturity</th>
                            <td><span class="font-weight-bold text-primary">৳ {{ number_format($account->payable_amount_at_maturity, 2) }}</span></td>
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
            <div class="card-header bg-light">
                <h5 class="card-title mb-0"><i class="fa fa-users mr-2"></i> Nominee Details</h5>
            </div>
            <div class="card-body p-0">
                @if($account->nominees->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-striped mb-0 text-center">
                        <thead>
                            <tr>
                                <th>Photo</th>
                                <th>Name</th>
                                <th>Relation</th>
                                <th>NID / Birth Cert</th>
                                <th>Contact No</th>
                                <th>Share (%)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($account->nominees as $nomineePivot)
                                @php $nom = $nomineePivot->nominee; @endphp
                                <tr>
                                    <td>
                                        @if($nom->image)
                                            <img src="{{ asset($nom->image) }}" width="40" height="40" class="rounded-circle border">
                                        @else
                                            <div class="rounded-circle bg-secondary text-white d-inline-flex align-items-center justify-content-center border" style="width: 40px; height: 40px;">
                                                <i class="fa fa-user"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="align-middle text-left font-weight-bold">{{ $nom->name }}</td>
                                    <td class="align-middle">{{ $nom->relation }}</td>
                                    <td class="align-middle">{{ $nom->nid_number ?? 'N/A' }}</td>
                                    <td class="align-middle">{{ $nom->phone ?? 'N/A' }}</td>
                                    <td class="align-middle text-success font-weight-bold">{{ $nomineePivot->share_percentage }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                    <div class="p-3 text-center text-muted">No nominees added for this account.</div>
                @endif
            </div>
        </div>
    </div>
</div>

@if($account->status === 'Pending')
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card card-outline card-success shadow-sm">
            <div class="card-header bg-success text-white">
                <h5 class="card-title mb-0"><i class="fa fa-check-circle mr-2"></i> Approve Account</h5>
            </div>
            <div class="card-body">
                <p>Please review the account details above. Select the payment mode through which the principal amount was received and confirm to activate the FDR account.</p>
                <form id="approveForm">
                    @csrf
                    <input type="hidden" id="approve_account_id" value="{{ $account->id }}">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="payment_mode">Payment Mode (Received To) <span class="text-danger">*</span></label>
                                <select class="form-control select2" id="payment_mode" name="payment_mode" required style="width: 100%;">
                                    <option value="">-- Select Payment Mode --</option>
                                    @foreach($paymentModes as $pm)
                                        <option value="{{ $pm->id }}">{{ $pm->name }} ({{ $pm->code }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 mt-4 pt-1">
                            <button type="submit" class="btn btn-success" id="btnConfirmApprove"><i class="fa fa-check"></i> Confirm Approval</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

            </div>
        </div>
    </div>
</div>

{{-- Preview Approve Modal --}}
<div class="modal fade" id="previewApproveModal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fa fa-eye mr-2"></i> Preview Approval Details</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered table-sm">
                    <tr><th width="45%">FDR No</th><td>{{ $account->fdr_no }}</td></tr>
                    <tr><th>Customer</th><td>{{ $account->customer->name }}</td></tr>
                    <tr><th>Scheme</th><td>{{ $account->scheme->name }}</td></tr>
                    <tr><th>Principal Amount</th><td class="font-weight-bold text-success">৳ {{ number_format($account->principal_amount, 2) }}</td></tr>
                    <tr><th>Payment Mode</th><td id="prev_payment_mode" class="font-weight-bold text-primary"></td></tr>
                </table>
                <div class="alert alert-warning mb-0 mt-3 text-sm">
                    <i class="fa fa-info-circle mr-1"></i> Once approved, the account will be activated and an accounting voucher will be generated automatically.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Edit</button>
                <button type="button" class="btn btn-success" id="btnFinalApproveSubmit"><i class="fa fa-check-circle"></i> Final Approve</button>
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

        $('#approveForm').submit(function(e) {
            e.preventDefault();
            let paymentMode = $('#payment_mode').val();
            let paymentText = $('#payment_mode option:selected').text();
            
            if (!paymentMode) {
                Swal.fire('Error!', 'Please select a payment mode.', 'error');
                return;
            }

            $('#prev_payment_mode').text(paymentText);
            $('#previewApproveModal').modal('show');
        });

        $('#btnFinalApproveSubmit').on('click', function() {
            let id = $('#approve_account_id').val();
            let paymentMode = $('#payment_mode').val();
            let btn = $(this);

            Swal.fire({
                title: 'Are you sure?',
                text: "You are about to approve and activate this FDR account.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Activate it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#previewApproveModal').modal('hide');
                    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');
                    $('#btnConfirmApprove').prop('disabled', true);
                    
                    $.ajax({
                        method: "POST",
                        url: "/admin/shomriddhi-fdr/accounts/" + id + "/approve",
                        data: { 
                            _token: '{{ csrf_token() }}',
                            payment_mode: paymentMode
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('Approved!', response.message, 'success').then(() => {
                                    window.location.reload();
                                });
                            } else {
                                btn.prop('disabled', false).html('<i class="fa fa-check-circle"></i> Final Approve');
                                $('#btnConfirmApprove').prop('disabled', false);
                                Swal.fire('Error!', response.message, 'error');
                            }
                        },
                        error: function() {
                            btn.prop('disabled', false).html('<i class="fa fa-check-circle"></i> Final Approve');
                            $('#btnConfirmApprove').prop('disabled', false);
                            Swal.fire('Error!', 'Something went wrong.', 'error');
                        }
                    });
                }
            });
        });
    });
</script>
@endsection
