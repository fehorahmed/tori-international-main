@extends('layouts.app')
@section('title', 'Open New FDR Account')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-default">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-plus mr-2"></i> Open New FDR Account
                </h5>
                <div class="card-tools">
                    <a href="{{ route('shomriddhi-fdr.accounts.index') }}" class="btn btn-sm btn-info">
                        <i class="fa fa-list"></i> Accounts List
                    </a>
                </div>
            </div>

            <div class="card-body">
                @if(session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif
                <form action="{{ route('shomriddhi-fdr.accounts.store') }}" method="POST" id="fdrAccountForm">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label for="customer_id">Select Member <span class="text-danger">*</span></label>
                                <select class="form-control select2 @error('customer_id') is-invalid @enderror" id="customer_id" name="customer_id" required style="width: 100%;">
                                    <option value="">-- Choose Member --</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                            {{ $customer->name }} | {{ $customer->member_no }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('customer_id') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label for="scheme_id">FDR Scheme <span class="text-danger">*</span></label>
                                <select class="form-control select2 @error('scheme_id') is-invalid @enderror" id="scheme_id" name="scheme_id" required style="width: 100%;">
                                    <option value="">-- Choose Scheme --</option>
                                    @foreach($schemes as $scheme)
                                        <option value="{{ $scheme->id }}" 
                                            data-interest="{{ $scheme->interest_rate }}"
                                            data-min="{{ $scheme->min_amount }}"
                                            data-max="{{ $scheme->max_amount }}"
                                            data-duration="{{ $scheme->duration }}"
                                            {{ old('scheme_id') == $scheme->id ? 'selected' : '' }}>
                                            {{ $scheme->name }} ({{ $scheme->duration }} Months - {{ $scheme->interest_rate }}%)
                                        </option>
                                    @endforeach
                                </select>
                                <small id="scheme_hint" class="text-info"></small>
                                @error('scheme_id') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <div class="form-group">
                                <label for="principal_amount">Principal Amount <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control @error('principal_amount') is-invalid @enderror" id="principal_amount" name="principal_amount" value="{{ old('principal_amount') }}" required min="0">
                                @error('principal_amount') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <div class="form-group">
                                <label for="interest_payout_frequency">Payout Frequency <span class="text-danger">*</span></label>
                                <select class="form-control select2 @error('interest_payout_frequency') is-invalid @enderror" id="interest_payout_frequency" name="interest_payout_frequency" required style="width: 100%;">
                                    <option value="At Maturity" {{ old('interest_payout_frequency') == 'At Maturity' ? 'selected' : '' }}>At Maturity (Cumulative)</option>
                                    <option value="Monthly" {{ old('interest_payout_frequency') == 'Monthly' ? 'selected' : '' }}>Monthly</option>
                                    <option value="Quarterly" {{ old('interest_payout_frequency') == 'Quarterly' ? 'selected' : '' }}>Quarterly (3 Months)</option>
                                    <option value="Half-Yearly" {{ old('interest_payout_frequency') == 'Half-Yearly' ? 'selected' : '' }}>Half-Yearly (6 Months)</option>
                                    <option value="Yearly" {{ old('interest_payout_frequency') == 'Yearly' ? 'selected' : '' }}>Yearly (12 Months)</option>
                                </select>
                                @error('interest_payout_frequency') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <div class="form-group">
                                <label for="opening_date">Opening Date <span class="text-danger">*</span></label>
                                <input type="text" class="form-control date-picker @error('opening_date') is-invalid @enderror" id="opening_date" name="opening_date" value="{{ old('opening_date', date('d-m-Y')) }}" required>
                                @error('opening_date') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <div class="form-group">
                                <label>Maturity Date (Calculated)</label>
                                <input type="text" class="form-control bg-light font-weight-bold text-danger" id="calculated_maturity" readonly value="Select Scheme & Date">
                            </div>
                        </div>
                        
                        <div class="col-md-12 mb-3">
                            <div class="form-group mt-1">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="auto_renew" name="auto_renew" {{ old('auto_renew') ? 'checked' : '' }}>
                                    <label class="custom-control-label font-weight-normal" for="auto_renew">Auto Renew</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12 mb-3">
                            <div class="form-group">
                                <label for="notes">Notes/Remarks (Optional)</label>
                                <textarea class="form-control" id="notes" name="notes" rows="2">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Nominee Section -->
                    <div class="row mt-4 border-top pt-4">
                        <div class="col-12">
                            <h6 class="font-weight-bold text-primary mb-3"><i class="fas fa-users mr-2"></i> Nominee Information</h6>
                            <div class="table-responsive">
                                <table class="table table-bordered" id="nominee_table">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Nominee <span class="text-danger">*</span></th>
                                            <th width="20%">Share (%) <span class="text-danger">*</span></th>
                                            <th width="10%">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-start">
                                                    <div style="flex-grow: 1; width: calc(100% - 45px);">
                                                        <select name="nominees[0][id]" class="form-control nominee-select" required>
                                                            <option value="">Select a Nominee</option>
                                                        </select>
                                                    </div>
                                                    <button type="button" class="btn btn-primary btn-add-new-nominee ml-2" title="Add New Nominee" style="height: 38px; width: 38px;"><i class="fa fa-plus"></i></button>
                                                </div>
                                            </td>
                                            <td>
                                                <input type="number" name="nominees[0][share_percentage]" class="form-control share-percentage" value="100" min="1" max="100" required>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-danger btn-sm btn-remove-nominee" disabled><i class="fa fa-trash"></i></button>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="3">
                                                <button type="button" class="btn btn-success btn-sm" id="btn_add_nominee_row"><i class="fa fa-plus"></i> Add Another Nominee</button>
                                                <small class="text-danger ml-3 d-none" id="share_error">Total share percentage must be exactly 100%.</small>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-3 border-top pt-3">
                        <div class="col-12 text-right">
                            <button type="reset" class="btn btn-warning">Reset</button>
                            <button type="submit" class="btn btn-success">
                                <i class="fa fa-save"></i> Submit Application
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
{{-- Preview Modal --}}
<div class="modal fade" id="previewModal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fa fa-eye mr-2"></i> Preview FDR Account Details</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-bordered table-sm">
                            <tr><th width="40%">Member</th><td id="prev_member"></td></tr>
                            <tr><th>Scheme</th><td id="prev_scheme"></td></tr>
                            <tr><th>Amount</th><td id="prev_amount" class="font-weight-bold text-success"></td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-bordered table-sm">
                            <tr><th width="40%">Payout Freq.</th><td id="prev_freq"></td></tr>
                            <tr><th>Opening Date</th><td id="prev_date"></td></tr>
                            <tr><th>Auto Renew</th><td id="prev_auto"></td></tr>
                        </table>
                    </div>
                </div>
                <div class="alert alert-warning mt-2 mb-0">
                    <i class="fa fa-info-circle mr-1"></i> This account will be saved as <strong>Pending</strong>.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Edit</button>
                <button type="button" class="btn btn-success" id="btnFinalSubmit"><i class="fa fa-check"></i> Final Submit</button>
            </div>
        </div>
    </div>
</div>
<!-- Add New Nominee Modal -->
<div class="modal fade" id="addNomineeModal" tabindex="-1" role="dialog" aria-labelledby="addNomineeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title text-white" id="addNomineeModalLabel">Create New Nominee</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                    <form id="addNomineeForm" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nominee Name <span class="text-danger">*</span></label>
                                    <input type="text" id="modal_nominee_name" name="name" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Relation <span class="text-danger">*</span></label>
                                    <input type="text" id="modal_nominee_relation" name="relation" class="form-control" placeholder="e.g. Wife, Son, Brother" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Phone Number</label>
                                    <input type="text" id="modal_nominee_phone" name="phone" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>NID Number</label>
                                    <input type="text" id="modal_nominee_nid" name="nid_number" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nominee Image</label>
                                    <input type="file" id="modal_nominee_image" name="image" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Division</label>
                                    <select id="modal_nominee_division" name="division_id" class="form-control select2">
                                        <option value="">Select Division</option>
                                        @foreach($divisions as $division)
                                            <option value="{{ $division->id }}">{{ $division->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>District</label>
                                    <select id="modal_nominee_district" name="district_id" class="form-control select2">
                                        <option value="">Select District</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Upazila</label>
                                    <select id="modal_nominee_upazila" name="upazila_id" class="form-control select2">
                                        <option value="">Select Upazila</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Pourashava/Union</label>
                                    <select id="modal_nominee_union" name="union" class="form-control select2">
                                        <option value="">Select Pourashava/Union</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Village/Road</label>
                                    <select id="modal_nominee_village_id" name="village_id" class="form-control select2">
                                        <option value="">Select Village</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="btn_save_nominee">Save Nominee</button>
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

        function calculateMaturityDate() {
            let openingDateStr = $('#opening_date').val();
            let selectedScheme = $('#scheme_id').find(':selected');
            
            if (openingDateStr && selectedScheme.val()) {
                let durationMonths = parseInt(selectedScheme.data('duration')) || 0;
                if (durationMonths > 0) {
                    // openingDateStr is in d-m-Y
                    let parts = openingDateStr.split('-');
                    if (parts.length === 3) {
                        let date = new Date(parts[2], parts[1] - 1, parts[0]);
                        date.setMonth(date.getMonth() + durationMonths);
                        
                        let day = ("0" + date.getDate()).slice(-2);
                        let monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
                        let monthIndex = date.getMonth();
                        let year = date.getFullYear();
                        
                        $('#calculated_maturity').val(day + " " + monthNames[monthIndex] + " " + year);
                        return;
                    }
                }
            }
            $('#calculated_maturity').val('Select Scheme & Date');
        }

        $('#scheme_id').change(function() {
            let selected = $(this).find(':selected');
            if(selected.val()) {
                let min = selected.data('min');
                let max = selected.data('max');
                let hint = "Min: ৳ " + min;
                if(max) hint += " | Max: ৳ " + max;
                $('#scheme_hint').text(hint);
                $('#principal_amount').attr('min', min);
                if(max) $('#principal_amount').attr('max', max);
            } else {
                $('#scheme_hint').text('');
                $('#principal_amount').removeAttr('min max');
            }
            calculateMaturityDate();
        });

        $('#opening_date').on('change blur', calculateMaturityDate);
        
        // Trigger on load for old inputs
        if($('#scheme_id').val()) {
            $('#scheme_id').trigger('change');
            calculateMaturityDate();
        }
        $('.select2').select2();

        // Scheme selection logic
        $('#scheme_id').change(function() {
            let selected = $(this).find(':selected');
            if(selected.val()) {
                let min = selected.data('min');
                let max = selected.data('max');
                let hint = "Min: ৳ " + min;
                if(max) hint += " | Max: ৳ " + max;
                $('#scheme_hint').text(hint);
                $('#principal_amount').attr('min', min);
                if(max) $('#principal_amount').attr('max', max);
            } else {
                $('#scheme_hint').text('');
                $('#principal_amount').removeAttr('min max');
            }
        });
        
        if($('#scheme_id').val()) {
            $('#scheme_id').trigger('change');
        }

        // --- Nominee Logic ---
        var nomineeRowIndex = 1;
        var currentNomineeSelect = null;
        var allCustomerNominees = [];

        function updateAllNomineeDropdowns() {
            $('.nominee-select').each(function() {
                var select = $(this);
                
                if (select.hasClass("select2-hidden-accessible")) {
                    select.select2('destroy');
                }
                
                var currentVal = select.val();
                select.empty().append('<option value="">Select a Nominee</option>');
                $.each(allCustomerNominees, function(key, nominee) {
                    select.append('<option value="'+nominee.id+'">'+nominee.name+' ('+nominee.relation+')</option>');
                });
                
                if(currentVal) {
                    select.val(currentVal);
                }
                
                select.select2({
                    placeholder: "Select a Nominee",
                    allowClear: true
                });
            });
        }

        updateAllNomineeDropdowns();

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

        $('#btn_add_nominee_row').click(function() {
            var newRow = `
                <tr>
                    <td>
                        <div class="d-flex align-items-start">
                            <div style="flex-grow: 1; width: calc(100% - 45px);">
                                <select name="nominees[${nomineeRowIndex}][id]" class="form-control nominee-select" required>
                                    <option value="">Select a Nominee</option>
                                </select>
                            </div>
                            <button type="button" class="btn btn-primary btn-add-new-nominee ml-2" title="Add New Nominee" style="height: 38px; width: 38px;"><i class="fa fa-plus"></i></button>
                        </div>
                    </td>
                    <td>
                        <input type="number" name="nominees[${nomineeRowIndex}][share_percentage]" class="form-control share-percentage" value="0" min="1" max="100" required>
                    </td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm btn-remove-nominee"><i class="fa fa-trash"></i></button>
                    </td>
                </tr>
            `;
            $('#nominee_table tbody').append(newRow);
            updateAllNomineeDropdowns();
            nomineeRowIndex++;
            validateSharePercentage();
        });

        $(document).on('click', '.btn-remove-nominee', function() {
            $(this).closest('tr').remove();
            validateSharePercentage();
        });

        $(document).on('click', '.btn-add-new-nominee', function() {
            var customerId = $('#customer_id').val();
            if (!customerId) {
                toastr.warning('Please select a customer first.');
                return;
            }
            currentNomineeSelect = $(this).closest('td').find('.nominee-select');
            $('#addNomineeModal').modal('show');
        });

        $('#btn_save_nominee').click(function() {
            var form = document.getElementById('addNomineeForm');
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            var formData = new FormData(form);
            formData.append('_token', "{{ csrf_token() }}");
            formData.append('customer_id', $('#customer_id').val());

            $.ajax({
                url: "{{ route('shomriddhi-advance.accounts.store_nominee') }}",
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    if (response.success) {
                        $('#addNomineeModal').modal('hide');
                        toastr.success('Nominee added successfully.');
                        allCustomerNominees.push(response.nominee);
                        updateAllNomineeDropdowns();
                        currentNomineeSelect.val(response.nominee.id).trigger('change');
                        
                        form.reset();
                        $('#modal_nominee_district, #modal_nominee_upazila, #modal_nominee_union, #modal_nominee_village_id').empty().append('<option value="">Select</option>');
                    } else {
                        toastr.error('Failed to add nominee.');
                    }
                },
                error: function() {
                    toastr.error('An error occurred. Please try again.');
                }
            });
        });

        // Cascading logic for location
        $('#modal_nominee_division').change(function () {
            var divisionId = $(this).val();
            $('#modal_nominee_district').html('<option value="">Select District</option>');
            $('#modal_nominee_upazila').html('<option value="">Select Upazila</option>');
            $('#modal_nominee_union').html('<option value="">Select Pourashava/Union</option>');
            $('#modal_nominee_village_id').html('<option value="">Select Village</option>');

            if (divisionId) {
                $.ajax({
                    method: "GET",
                    url: "{{ route('get_districts') }}",
                    data: { divisionId: divisionId }
                }).done(function( response ) {
                    $.each(response, function( index, item ) {
                        $('#modal_nominee_district').append('<option value="'+item.id+'">'+item.name+'</option>');
                    });
                });
            }
        });

        $('#modal_nominee_district').change(function () {
            var districtId = $(this).val();
            $('#modal_nominee_upazila').html('<option value="">Select Upazila</option>');
            $('#modal_nominee_union').html('<option value="">Select Pourashava/Union</option>');
            $('#modal_nominee_village_id').html('<option value="">Select Village</option>');

            if (districtId) {
                $.ajax({
                    method: "GET",
                    url: "{{ route('get_upazilas') }}",
                    data: { districtId: districtId }
                }).done(function( response ) {
                    $.each(response, function( index, item ) {
                        $('#modal_nominee_upazila').append('<option value="'+item.id+'">'+item.name+'</option>');
                    });
                });
            }
        });

        $('#modal_nominee_upazila').change(function () {
            var upazilaId = $(this).val();
            var districtId = $('#modal_nominee_district').val();
            $('#modal_nominee_union').html('<option value="">Select Pourashava/Union</option>');
            $('#modal_nominee_village_id').html('<option value="">Select Village</option>');

            if (upazilaId || districtId) {
                $.ajax({
                    method: "GET",
                    url: "{{ route('get_pourashavas') }}",
                    data: { districtId: districtId }
                }).done(function( data ) {
                    $.each(data, function( index, item ) {
                        $('#modal_nominee_union').append('<option value="pourashava_'+item.id+'">'+item.name+' (P)</option>');
                    });

                    if (upazilaId != '') {
                        $.ajax({
                            method: "GET",
                            url: "{{ route('get_unions') }}",
                            data: { upazilaId: upazilaId }
                        }).done(function( data ) {
                            $.each(data, function( index, item ) {
                                $('#modal_nominee_union').append('<option value="union_'+item.id+'">'+item.name+' (U)</option>');
                            });
                        });
                    }
                });
            }
        });

        $('#modal_nominee_union').change(function () {
            var unionId = $(this).val();
            $('#modal_nominee_village_id').html('<option value="">Select Village</option>');

            if (unionId) {
                $.ajax({
                    method: "GET",
                    url: "{{ route('get_villages') }}",
                    data: { unionId: unionId }
                }).done(function( response ) {
                    $.each(response, function( index, item ) {
                        $('#modal_nominee_village_id').append('<option value="'+item.id+'">'+item.name+'</option>');
                    });
                });
            }
        });

        function validateSharePercentage() {
            var total = 0;
            $('.share-percentage').each(function() {
                var val = parseFloat($(this).val());
                if (!isNaN(val)) {
                    total += val;
                }
            });

            if (total !== 100) {
                $('#share_error').removeClass('d-none');
                return false;
            } else {
                $('#share_error').addClass('d-none');
                return true;
            }
        }

        $(document).on('input', '.share-percentage', function() {
            validateSharePercentage();
        });

        $('#fdrAccountForm').submit(function(e) {
            e.preventDefault();
            
            if (!validateSharePercentage()) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Share Percentage',
                    text: 'Total nominee share percentage must be exactly 100%.'
                });
                return;
            }

            // Populate preview modal
            $('#prev_member').text($('#customer_id option:selected').text());
            $('#prev_scheme').text($('#scheme_id option:selected').text());
            $('#prev_amount').text('৳ ' + $('#principal_amount').val());
            $('#prev_freq').text($('#interest_payout_frequency').val());
            $('#prev_date').text($('#opening_date').val());
            $('#prev_auto').text($('#auto_renew').is(':checked') ? 'Yes' : 'No');
            
            $('#previewModal').modal('show');
        });

        $('#btnFinalSubmit').on('click', function() {
            Swal.fire({
                title: 'Are you sure?',
                text: "You are about to submit this FDR Account application.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Submit it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#previewModal').modal('hide');
                    $('#fdrAccountForm')[0].submit();
                }
            });
        });
    });
</script>
@endsection
