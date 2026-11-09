@extends('layouts.app')
@section('title', 'Edit DPS Account')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-default">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-edit mr-2"></i> Edit DPS Account: {{ $account->dps_no }}</h5>
                <div class="card-tools">
                    <a href="{{ route('shomriddhi-dps.accounts.index') }}" class="btn btn-sm btn-info">
                        <i class="fa fa-list"></i> Accounts List
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">@foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach</ul>
                    </div>
                @endif

                <form id="accountForm" action="{{ route('shomriddhi-dps.accounts.update', $account->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        {{-- Customer --}}
                        <div class="col-md-6 mb-3">
                            <label>Select Member <span class="text-danger">*</span></label>
                            <select class="form-control select2 @error('customer_id') is-invalid @enderror"
                                    name="customer_id" id="customer_id" required style="width:100%">
                                <option value="">-- Choose Member --</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}" {{ old('customer_id', $account->customer_id) == $c->id ? 'selected' : '' }}>
                                        {{ $c->name }} - {{ $c->member_no ?? $c->phone }}
                                    </option>
                                @endforeach
                            </select>
                            @error('customer_id') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        {{-- Scheme --}}
                        <div class="col-md-6 mb-3">
                            <label>DPS Scheme <span class="text-danger">*</span></label>
                            <select class="form-control select2 @error('scheme_id') is-invalid @enderror"
                                    name="scheme_id" id="scheme_id" required style="width:100%">
                                <option value="">-- Choose Scheme --</option>
                                @foreach($schemes as $scheme)
                                    <option value="{{ $scheme->id }}"
                                        data-duration="{{ $scheme->duration_months }}"
                                        data-installment="{{ $scheme->installment_amount }}"
                                        data-type="{{ $scheme->installment_type }}"
                                        data-rate="{{ $scheme->interest_rate }}"
                                        {{ old('scheme_id', $account->scheme_id) == $scheme->id ? 'selected' : '' }}>
                                        {{ $scheme->name }} ({{ $scheme->duration_months }} Months — ৳{{ number_format($scheme->installment_amount,2) }}/{{ strtolower($scheme->installment_type) }} — {{ $scheme->interest_rate }}% p.a.)
                                    </option>
                                @endforeach
                            </select>
                            <small id="scheme_hint" class="text-info"></small>
                            @error('scheme_id') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        {{-- Opening Date --}}
                        <div class="col-md-4 mb-3">
                            <label>Opening Date <span class="text-danger">*</span></label>
                            <input type="text" name="opening_date" id="opening_date"
                                   class="form-control date-picker @error('opening_date') is-invalid @enderror"
                                   value="{{ old('opening_date', \Carbon\Carbon::parse($account->opening_date)->format('d-m-Y')) }}" required>
                            @error('opening_date') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        {{-- Maturity Date (calculated) --}}
                        <div class="col-md-4 mb-3">
                            <label>Maturity Date <small class="text-muted">(Auto)</small></label>
                            <input type="text" id="maturity_date_display" class="form-control bg-light" readonly placeholder="Select scheme & opening date">
                        </div>

                        {{-- Payable at Maturity (calculated) --}}
                        <div class="col-md-4 mb-3">
                            <label>Payable at Maturity <small class="text-muted">(Auto)</small></label>
                            <input type="text" id="payable_display" class="form-control bg-light font-weight-bold text-primary" readonly placeholder="--">
                        </div>

                        {{-- Auto Renew --}}
                        <div class="col-md-6 mb-3">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" name="auto_renew" id="auto_renew" value="1" {{ old('auto_renew', $account->auto_renew) ? 'checked' : '' }}>
                                <label class="form-check-label" for="auto_renew">Auto-Renew at Maturity</label>
                            </div>
                        </div>

                        {{-- Notes --}}
                        <div class="col-md-12 mb-3">
                            <label>Notes (Optional)</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Any remarks...">{{ old('notes', $account->notes) }}</textarea>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-12">
                            <button type="button" class="btn btn-primary" id="btnPreview">
                                <i class="fa fa-eye mr-1"></i> Preview & Update
                            </button>
                            <a href="{{ route('shomriddhi-dps.accounts.index') }}" class="btn btn-secondary ml-2">Cancel</a>
                        </div>
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
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fa fa-list mr-2"></i> Preview Account Details</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered table-striped">
                    <tbody>
                        <tr><th>Member</th><td id="pv_member"></td></tr>
                        <tr><th>Scheme</th><td id="pv_scheme"></td></tr>
                        <tr><th>Opening Date</th><td id="pv_opening_date"></td></tr>
                        <tr><th>Maturity Date</th><td id="pv_maturity_date"></td></tr>
                        <tr><th>Payable at Maturity</th><td id="pv_payable"></td></tr>
                        <tr><th>Auto Renew</th><td id="pv_auto_renew"></td></tr>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Edit</button>
                <button type="button" class="btn btn-primary" id="btnConfirmSave"><i class="fa fa-check mr-1"></i> Confirm & Update</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
$(document).ready(function() {
    function calculateMaturity() {
        const schemeOption = $('#scheme_id').find(':selected');
        const openingDateVal = $('#opening_date').val();
        const duration = parseInt(schemeOption.data('duration') || 0);
        const installment = parseFloat(schemeOption.data('installment') || 0);
        const rate = parseFloat(schemeOption.data('rate') || 0);
        const type = schemeOption.data('type');

        if (!duration || !openingDateVal || !type) {
            $('#maturity_date_display').val('');
            $('#payable_display').val('');
            $('#scheme_hint').text('');
            return;
        }

        const parts = openingDateVal.split('-');
        let openingDate;
        if (parts.length === 3 && parts[0].length <= 2) { // dd-mm-yyyy
            const d = parseInt(parts[0], 10);
            const m = parseInt(parts[1], 10) - 1;
            const y = parseInt(parts[2], 10);
            openingDate = new Date(y, m, d);
        } else {
            openingDate = new Date(openingDateVal);
        }

        const maturityDate = new Date(openingDate);
        maturityDate.setMonth(maturityDate.getMonth() + duration);
        
        const matY = maturityDate.getFullYear();
        const matM = String(maturityDate.getMonth() + 1).padStart(2, '0');
        const matD = String(maturityDate.getDate()).padStart(2, '0');
        $('#maturity_date_display').val(`${matD}-${matM}-${matY}`);

        // Recurring Deposit Formula: M = P × [((1+r)^n - 1) / r] × (1+r)
        let n = duration;
        let r = (rate / 100) / 12;

        if (type === 'Daily') {
            n = duration * 30;
            r = (rate / 100) / 365;
        } else if (type === 'Weekly') {
            n = duration * 4;
            r = (rate / 100) / 52;
        }

        let payable;
        if (r > 0) {
            payable = installment * (((Math.pow(1 + r, n) - 1) / r) * (1 + r));
        } else {
            payable = installment * n;
        }

        $('#payable_display').val('৳ ' + payable.toFixed(2));
        $('#scheme_hint').text('Type: ' + type + ' | Rate: ' + rate + '% p.a. | Installment: ৳' + installment.toFixed(2));
    }

    $('#scheme_id, #opening_date').on('change', calculateMaturity);

    // Select2
    if ($.fn.select2) {
        $('#customer_id, #scheme_id').select2();
    }

    calculateMaturity();

    $('#btnPreview').on('click', function() {
        const form = $('#accountForm')[0];
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        $('#pv_member').text($('#customer_id option:selected').text().trim());
        $('#pv_scheme').text($('#scheme_id option:selected').text().trim());
        $('#pv_opening_date').text($('#opening_date').val());
        $('#pv_maturity_date').text($('#maturity_date_display').val());
        $('#pv_payable').text($('#payable_display').val());
        $('#pv_auto_renew').text($('#auto_renew').is(':checked') ? 'Yes' : 'No');

        $('#previewModal').modal('show');
    });

    $('#btnConfirmSave').on('click', function() {
        $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Updating...');
        $('#accountForm').submit();
    });
});
</script>
@endsection
