@extends('layouts.app')
@section('title', 'Edit DPS Scheme')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card card-default">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-edit mr-2"></i> Edit DPS Scheme: {{ $scheme->name }}</h5>
                <div class="card-tools">
                    <a href="{{ route('shomriddhi-dps.schemes.index') }}" class="btn btn-sm btn-info"><i class="fa fa-list"></i> All Schemes</a>
                </div>
            </div>
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
                @endif
                <form id="schemeForm" action="{{ route('shomriddhi-dps.schemes.update', $scheme->id) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Scheme Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $scheme->name) }}" required>
                            @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Duration (Months) <span class="text-danger">*</span></label>
                            <input type="number" name="duration_months" class="form-control"
                                   value="{{ old('duration_months', $scheme->duration_months) }}" min="1" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Installment Type <span class="text-danger">*</span></label>
                            <select name="installment_type" class="form-control" required>
                                <option value="Monthly" {{ old('installment_type', $scheme->installment_type) == 'Monthly' ? 'selected' : '' }}>Monthly</option>
                                <option value="Weekly" {{ old('installment_type', $scheme->installment_type) == 'Weekly' ? 'selected' : '' }}>Weekly</option>
                                <option value="Daily" {{ old('installment_type', $scheme->installment_type) == 'Daily' ? 'selected' : '' }}>Daily</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Installment Amount (৳) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="installment_amount" class="form-control"
                                   value="{{ old('installment_amount', $scheme->installment_amount) }}" min="1" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Annual Interest Rate (%) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="interest_rate" class="form-control"
                                   value="{{ old('interest_rate', $scheme->interest_rate) }}" min="0" max="100" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Missed Installment Penalty</label>
                            <div class="input-group">
                                <select name="penalty_type" class="form-control" style="max-width: 150px;">
                                    <option value="Percentage" {{ old('penalty_type', $scheme->penalty_type) == 'Percentage' ? 'selected' : '' }}>Percentage (%)</option>
                                    <option value="Fixed" {{ old('penalty_type', $scheme->penalty_type) == 'Fixed' ? 'selected' : '' }}>Fixed Amount (৳)</option>
                                </select>
                                <input type="number" step="0.01" name="penalty_amount" class="form-control"
                                       value="{{ old('penalty_amount', $scheme->penalty_amount) }}" min="0">
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Premature Withdrawal Penalty</label>
                            <div class="input-group">
                                <select name="premature_penalty_type" class="form-control" style="max-width: 150px;">
                                    <option value="Percentage" {{ old('premature_penalty_type', $scheme->premature_penalty_type) == 'Percentage' ? 'selected' : '' }}>Percentage (%)</option>
                                    <option value="Fixed" {{ old('premature_penalty_type', $scheme->premature_penalty_type) == 'Fixed' ? 'selected' : '' }}>Fixed Amount (৳)</option>
                                </select>
                                <input type="number" step="0.01" name="premature_penalty_amount" class="form-control"
                                       value="{{ old('premature_penalty_amount', $scheme->premature_penalty_amount) }}" min="0">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-control" required>
                                <option value="Active" {{ $scheme->status == 'Active' ? 'selected' : '' }}>Active</option>
                                <option value="Inactive" {{ $scheme->status == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="button" class="btn btn-warning" id="btnPreview"><i class="fa fa-eye mr-1"></i> Preview & Update</button>
                        <a href="{{ route('shomriddhi-dps.schemes.index') }}" class="btn btn-secondary ml-2">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title"><i class="fa fa-list mr-2"></i> Preview Scheme Details</h5>
                <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered table-striped">
                    <tbody>
                        <tr><th>Scheme Name</th><td id="pv_name"></td></tr>
                        <tr><th>Duration (Months)</th><td id="pv_duration"></td></tr>
                        <tr><th>Installment Type</th><td id="pv_type"></td></tr>
                        <tr><th>Installment Amount</th><td id="pv_amount"></td></tr>
                        <tr><th>Annual Interest Rate</th><td id="pv_interest"></td></tr>
                        <tr><th>Missed Penalty</th><td id="pv_missed_penalty"></td></tr>
                        <tr><th>Premature Penalty</th><td id="pv_premature_penalty"></td></tr>
                        <tr><th>Status</th><td id="pv_status"></td></tr>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Edit</button>
                <button type="button" class="btn btn-warning" id="btnConfirmSave"><i class="fa fa-check mr-1"></i> Confirm & Update</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
$(document).ready(function() {
    $('#btnPreview').on('click', function() {
        const form = $('#schemeForm')[0];
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        $('#pv_name').text($('input[name="name"]').val());
        $('#pv_duration').text($('input[name="duration_months"]').val());
        $('#pv_type').text($('select[name="installment_type"]').val());
        $('#pv_amount').text('৳ ' + $('input[name="installment_amount"]').val());
        $('#pv_interest').text($('input[name="interest_rate"]').val() + ' %');

        let mType = $('select[name="penalty_type"]').val();
        let mAmount = $('input[name="penalty_amount"]').val();
        $('#pv_missed_penalty').text(mType === 'Percentage' ? mAmount + '%' : '৳ ' + mAmount);

        let pType = $('select[name="premature_penalty_type"]').val();
        let pAmount = $('input[name="premature_penalty_amount"]').val();
        $('#pv_premature_penalty').text(pType === 'Percentage' ? pAmount + '%' : '৳ ' + pAmount);

        $('#pv_status').text($('select[name="status"]').val());

        $('#previewModal').modal('show');
    });

    $('#btnConfirmSave').on('click', function() {
        $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Updating...');
        $('#schemeForm').submit();
    });
});
</script>
@endsection
