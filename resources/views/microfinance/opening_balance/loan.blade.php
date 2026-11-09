@extends('layouts.app')
@section('title', $pageTitle)
@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card card-outline card-danger">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-database mr-2"></i>{{ $pageTitle }}</h3>
                <div class="card-tools">
                    <span class="badge badge-warning">Migration Date: {{ date('d-m-Y', strtotime(auth()->user()->business_date)) }}</span>
                </div>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-1"></i>
                    Migrate Loan account from old software. Enter principal outstanding balance and paid installments.
                    GL Entry: <strong>Dr. MFI Loan Portfolio → Cr. Opening Balance Equity</strong>
                </div>

                <form id="openingBalanceForm" action="{{ route('microfinance.opening-balance.loan.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Somiti <span class="text-danger">*</span></label>
                                <select id="somiti_id" class="form-control select2" required>
                                    <option value="">-- Select Somiti --</option>
                                    @foreach($somitis as $somiti)
                                        <option value="{{ $somiti->id }}">
                                            {{ $somiti->name }} ({{ $somiti->somiti_no }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Member <span class="text-danger">*</span></label>
                                <select name="member_id" id="member_id" class="form-control select2" required>
                                    <option value="">-- Select Member --</option>
                                    @foreach($members as $member)
                                        <option value="{{ $member->id }}" data-somiti-id="{{ $member->somiti_id }}" {{ old('member_id') == $member->id ? 'selected' : '' }}>
                                            {{ $member->name }} ({{ $member->member_no }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Loan Product <span class="text-danger">*</span></label>
                                <select name="mf_loan_product_id" class="form-control" required>
                                    <option value="">-- Select Product --</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" {{ old('mf_loan_product_id') == $product->id ? 'selected' : '' }}>
                                            {{ $product->name }} ({{ $product->interest_rate }}%)
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Disbursement Date <span class="text-danger">*</span></label>
                                <input type="text" name="disbursement_date" class="form-control date-picker" required
                                    value="{{ old('disbursement_date') }}" placeholder="dd-mm-yyyy" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Approved Amount <span class="text-danger">*</span></label>
                                <input type="number" name="approved_amount" class="form-control" required
                                    step="0.01" min="1" value="{{ old('approved_amount') }}" placeholder="0.00">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Total Installments <span class="text-danger">*</span></label>
                                <input type="number" name="total_installments" id="total_inst" class="form-control" required
                                    min="1" value="{{ old('total_installments') }}" placeholder="e.g. 50">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Already Paid Installments <span class="text-danger">*</span></label>
                                <input type="number" name="installments_paid" id="paid_inst" class="form-control" required
                                    min="0" value="{{ old('installments_paid') }}" placeholder="e.g. 20">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Remaining Installments</label>
                                <input type="text" id="remaining_inst" class="form-control" readonly
                                    value="{{ old('remaining', '—') }}" placeholder="auto">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Outstanding Principal <span class="text-danger">*</span></label>
                                <input type="number" name="outstanding_principal" class="form-control" required
                                    step="0.01" min="0.01" value="{{ old('outstanding_principal') }}" placeholder="0.00">
                                <small class="text-muted">Principal amount only — excluding interest.</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Next Due Date <span class="text-danger">*</span></label>
                                <input type="text" name="next_due_date" class="form-control date-picker" required
                                    value="{{ old('next_due_date') }}" placeholder="dd-mm-yyyy" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Loan Purpose (Optional)</label>
                                <input type="text" name="loan_purpose" class="form-control"
                                    value="{{ old('loan_purpose') }}" placeholder="Purpose of the loan">
                            </div>
                        </div>
                    </div>
                    <div class="form-group text-right">
                        <button type="submit" class="btn btn-danger px-4">
                            <i class="fas fa-save mr-1"></i> Save Opening Balance
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
@section('script')
<script>
    $(document).ready(function() {
        function calcRemaining() {
            const total = parseInt($('#total_inst').val()) || 0;
            const paid  = parseInt($('#paid_inst').val()) || 0;
            const rem   = total - paid;
            $('#remaining_inst').val(rem > 0 ? rem : '—');
            $('#paid_inst').attr('max', total - 1);
        }
        $('#total_inst, #paid_inst').on('input', calcRemaining);

        // Handle Somiti -> Member filtering
        let allMembers = $('#member_id option').clone();
        $('#somiti_id').on('change', function() {
            let somitiId = $(this).val();
            $('#member_id').empty().append('<option value="">-- Select Member --</option>');
            if(somitiId) {
                let filtered = allMembers.filter(function() {
                    return $(this).data('somiti-id') == somitiId;
                });
                $('#member_id').append(filtered);
            }
            $('#member_id').trigger('change');
        });

        // Handle Form Submission Confirmation
        $('#openingBalanceForm').on('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Are you sure?',
                text: 'Once entered, this cannot be changed!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, save it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            });
        });
    });
</script>
@endsection
