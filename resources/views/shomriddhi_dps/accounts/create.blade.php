@extends('layouts.app')
@section('title', 'Open New DPS Account')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-default">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-piggy-bank mr-2"></i> Open New DPS Account</h5>
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

                <form id="accountForm" action="{{ route('shomriddhi-dps.accounts.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        {{-- Customer --}}
                        <div class="col-md-6 mb-3">
                            <label>Select Member <span class="text-danger">*</span></label>
                            <select class="form-control select2 @error('customer_id') is-invalid @enderror"
                                    name="customer_id" id="customer_id" required style="width:100%">
                                <option value="">-- Choose Member --</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}" {{ old('customer_id') == $c->id ? 'selected' : '' }}>
                                        {{ $c->name }} - {{ $c->member_no }}
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
                                        {{ old('scheme_id') == $scheme->id ? 'selected' : '' }}>
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
                                   value="{{ old('opening_date', date('d-m-Y')) }}" required>
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
                                <input class="form-check-input" type="checkbox" name="auto_renew" id="auto_renew" value="1" {{ old('auto_renew') ? 'checked' : '' }}>
                                <label class="form-check-label" for="auto_renew">Auto-Renew at Maturity</label>
                            </div>
                        </div>

                        {{-- Notes --}}
                        <div class="col-md-12 mb-3">
                            <label>Notes (Optional)</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Any remarks...">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    {{-- Nominee Section --}}
                    <hr>
                    <h6 class="font-weight-bold mb-3"><i class="fa fa-users mr-1"></i> Nominees</h6>
                    <div id="nominees-container"></div>
                    <button type="button" class="btn btn-sm btn-outline-primary mb-3" id="addNomineeRow">
                        <i class="fa fa-plus"></i> Add Nominee
                    </button>

                    <div class="row mt-3">
                        <div class="col-md-12">
                            <button type="button" class="btn btn-success" id="btnPreview">
                                <i class="fa fa-eye mr-1"></i> Preview & Save
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
            <div class="modal-header bg-info text-white">
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
                        <tr><th>Nominees Count</th><td id="pv_nominees"></td></tr>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Edit</button>
                <button type="button" class="btn btn-success" id="btnConfirmSave"><i class="fa fa-check mr-1"></i> Confirm & Save</button>
            </div>
        </div>
    </div>
</div>

{{-- Nominee Row Template --}}
<template id="nomineeRowTemplate">
    <div class="row nominee-row align-items-end mb-2 border rounded p-2">
        <div class="col-md-7 form-group mb-0">
            <label>Nominee <span class="text-danger">*</span></label>
            <select class="form-control select2-nominee nominee-select" name="nominees[IDX][id]" required style="width:100%">
                <option value="">-- Select a Nominee --</option>
            </select>
        </div>
        <div class="col-md-3 form-group mb-0">
            <label>Share % <span class="text-danger">*</span></label>
            <input type="number" name="nominees[IDX][share_percentage]" class="form-control nominee-share"
                   min="1" max="100" step="0.01" required placeholder="e.g. 50">
        </div>
        <div class="col-md-2 mb-0">
            <button type="button" class="btn btn-danger btn-sm remove-nominee-row w-100"><i class="fa fa-trash"></i></button>
        </div>
    </div>
</template>
@endsection

@section('script')
<script>
$(document).ready(function() {
    let nomineeIdx = 0;

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

    // --- Nominee Logic ---
    var currentNomineeSelect = null;
    var allCustomerNominees = [];

    $('#accountForm').submit(function(e) {
        // Prevent manual submission if not from modal, though the button type is button now.
        let totalShare = 0;
        $('.nominee-share').each(function() {
            totalShare += parseFloat($(this).val()) || 0;
        });

        if (totalShare !== 100 && $('.nominee-share').length > 0) {
            e.preventDefault();
            Swal.fire('Error', 'Total Nominee share percentage must be exactly 100%. Currently it is ' + totalShare + '%', 'error');
            // Stop spinner if from modal
            $('#btnConfirmSave').prop('disabled', false).html('<i class="fa fa-check mr-1"></i> Confirm & Save');
        }
    });

    $('#btnPreview').on('click', function() {
        const form = $('#accountForm')[0];
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        let totalShare = 0;
        $('.nominee-share').each(function() {
            totalShare += parseFloat($(this).val()) || 0;
        });

        if (totalShare !== 100 && $('.nominee-share').length > 0) {
            Swal.fire('Error', 'Total Nominee share percentage must be exactly 100%. Currently it is ' + totalShare + '%', 'error');
            return;
        }

        $('#pv_member').text($('#customer_id option:selected').text().trim());
        $('#pv_scheme').text($('#scheme_id option:selected').text().trim());
        $('#pv_opening_date').text($('#opening_date').val());
        $('#pv_maturity_date').text($('#maturity_date_display').val());
        $('#pv_payable').text($('#payable_display').val());
        $('#pv_auto_renew').text($('#auto_renew').is(':checked') ? 'Yes' : 'No');
        $('#pv_nominees').text($('.nominee-row').length);

        $('#previewModal').modal('show');
    });

    $('#btnConfirmSave').on('click', function() {
        $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Saving...');
        $('#accountForm').submit();
    });

    function updateAllNomineeDropdowns() {
        $('.nominee-select').each(function() {
            var select = $(this);
            
            if (select.hasClass("select2-hidden-accessible")) {
                select.select2('destroy');
            }
            
            var currentVal = select.val();
            select.empty().append('<option value="">-- Select a Nominee --</option>');
            $.each(allCustomerNominees, function(key, nominee) {
                select.append('<option value="'+nominee.id+'">'+nominee.name+' ('+nominee.relation+')</option>');
            });
            
            if(currentVal) {
                select.val(currentVal);
            }
            
            select.select2({
                placeholder: "-- Select a Nominee --",
                allowClear: true,
                dropdownParent: select.closest('.nominee-row').length ? select.closest('.nominee-row') : select.closest('form')
            });
        });
    }

    $('#customer_id').on('change', function() {
        var customerId = $(this).val();
        if (customerId) {
            $.ajax({
                url: "{{ route('shomriddhi-advance.accounts.customer_details') }}",
                type: "GET",
                data: { customer_id: customerId },
                success: function(response) {
                    if (response.success) {
                        allCustomerNominees = response.nominees;
                        updateAllNomineeDropdowns();
                    }
                }
            });
        } else {
            allCustomerNominees = [];
            updateAllNomineeDropdowns();
        }
    });

    // Add Nominee Row
    $('#addNomineeRow').on('click', function() {
        const template = document.getElementById('nomineeRowTemplate').innerHTML;
        const html = template.replace(/IDX/g, nomineeIdx++);
        const $row = $(html);
        $('#nominees-container').append($row);
        
        // Find the select inside the newly added row and initialize it with options
        var select = $row.find('.nominee-select');
        select.empty().append('<option value="">-- Select a Nominee --</option>');
        $.each(allCustomerNominees, function(key, nominee) {
            select.append('<option value="'+nominee.id+'">'+nominee.name+' ('+nominee.relation+')</option>');
        });
        select.select2({
            dropdownParent: $row.closest('form')
        });
    });

    // Remove Nominee Row
    $('body').on('click', '.remove-nominee-row', function() {
        $(this).closest('.nominee-row').remove();
    });

    // Select2
    if ($.fn.select2) {
        $('#customer_id, #scheme_id').select2();
    }

    calculateMaturity();
});
</script>
@endsection
