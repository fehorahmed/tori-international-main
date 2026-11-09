@extends('layouts.app')

@section('title', $pageTitle)

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">{{ $pageTitle }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('shomriddhi-advance.accounts.index') }}" class="btn btn-sm btn-default"><i class="fa fa-arrow-left"></i> Back to List</a>
                    </div>
                </div>
                <form action="{{ route('shomriddhi-advance.accounts.store') }}" method="POST" id="createAccountForm">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label for="customer_id">Select Customer <span class="text-danger">*</span></label>
                            <select name="customer_id" id="customer_id" class="form-control select2" required>
                                <option value="">Select a Customer</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->name }} | {{ $customer->member_no }}
                                    </option>
                                @endforeach
                            </select>
                            @error('customer_id')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                            <small class="form-text text-muted">Only customers without an existing Shomriddhi Account are listed here.</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="opening_balance">Opening Balance (৳)</label>
                            <input type="number" step="0.01" min="0" name="opening_balance" id="opening_balance" class="form-control" placeholder="0.00" value="{{ old('opening_balance') }}">
                            @error('opening_balance')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                            <small class="form-text text-muted">Leave empty or 0 if there is no opening balance.</small>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="date">Account Opening Date <span class="text-danger">*</span></label>
                                    <input type="text" name="date" id="date" class="form-control date-picker" value="{{ old('date', date('d-m-Y')) }}" required>
                                    @error('date')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">The original date the customer opened this account.</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="note">Notes</label>
                                    <input type="text" name="note" id="note" class="form-control" value="{{ old('note', 'Opening Balance') }}">
                                    @error('note')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <hr>
                        <h5 class="text-primary mt-4 mb-3"><i class="fas fa-user-friends"></i> Nominee Information</h5>
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
                    <div class="card-footer text-right">
                        <button type="submit" class="btn btn-info" id="btn_submit"><i class="fa fa-save"></i> Create Account</button>
                    </div>
                </form>
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
        var nomineeRowIndex = 1;
        var currentNomineeSelect = null;
        var allCustomerNominees = [];

        function updateAllNomineeDropdowns() {
            $('.nominee-select').each(function() {
                var select = $(this);
                
                // Destroy existing select2 instance if it exists
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
                
                // Re-initialize select2
                select.select2({
                    placeholder: "Select a Nominee",
                    allowClear: true
                });
            });
        }

        // Initialize select2 on load
        updateAllNomineeDropdowns();

        // Handle Customer Selection
        $('#customer_id').on('change', function() {
            var customerId = $(this).val();
            if (customerId) {
                $.ajax({
                    url: "{{ route('shomriddhi-advance.accounts.customer_details') }}",
                    type: "GET",
                    data: { customer_id: customerId },
                    success: function(response) {
                        if (response.success) {
                            $('#father_name').val(response.customer.father_name);
                            $('#mother_name').val(response.customer.mother_name);
                            $('#nid_number').val(response.customer.nid_number);
                            $('#dob').val(response.customer.dob);

                            allCustomerNominees = response.nominees;
                            updateAllNomineeDropdowns();
                        }
                    }
                });
            } else {
                $('#father_name, #mother_name, #nid_number, #dob').val('');
                allCustomerNominees = [];
                updateAllNomineeDropdowns();
            }
        });

        // Add Row
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
                        <input type="number" name="nominees[${nomineeRowIndex}][share_percentage]" class="form-control share-percentage" value="100" min="1" max="100" required>
                    </td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm btn-remove-nominee"><i class="fa fa-trash"></i></button>
                    </td>
                </tr>
            `;
            $('#nominee_table tbody').append(newRow);
            updateAllNomineeDropdowns();
            nomineeRowIndex++;
        });

        // Duplicate check on select change
        $(document).on('change', '.nominee-select', function() {
            var selectedVal = $(this).val();
            if (!selectedVal) return;
            
            var currentSelect = $(this);
            var duplicateCount = 0;
            
            $('.nominee-select').each(function() {
                if ($(this).val() == selectedVal) {
                    duplicateCount++;
                }
            });
            
            if (duplicateCount > 1) {
                toastr.error('This nominee is already selected!');
                // Reset this select
                currentSelect.val('').trigger('change.select2');
            }
        });

        // Remove Row
        $(document).on('click', '.btn-remove-nominee', function() {
            $(this).closest('tr').remove();
        });

        // Open Modal
        $(document).on('click', '.btn-add-new-nominee', function() {
            var customerId = $('#customer_id').val();
            if (!customerId) {
                toastr.error('Please select a customer first.');
                return;
            }
            currentNomineeSelect = $(this).closest('.input-group').find('.nominee-select');
            $('#addNomineeModal').modal('show');
        });

        // Save Nominee via Modal
        $('#btn_save_nominee').click(function() {
            var form = $('#addNomineeForm')[0];
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            Swal.fire({
                title: 'Are you sure?',
                text: "Do you want to save this new nominee?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Save it!'
            }).then((result) => {
                if (result.isConfirmed) {
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
                                currentNomineeSelect.val(response.nominee.id);
                                
                                // Clear modal inputs
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
                }
            });
        });

        // Location Cascading for Modal
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
                // Load Pourashavas first
                $.ajax({
                    method: "GET",
                    url: "{{ route('get_pourashavas') }}",
                    data: { districtId: districtId }
                }).done(function( data ) {
                    $.each(data, function( index, item ) {
                        $('#modal_nominee_union').append('<option value="pourashava_'+item.id+'">'+item.name+' (P)</option>');
                    });

                    // Then load Unions
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
            let val = $(this).val();
            let targetEl = $('#modal_nominee_village_id');
            targetEl.html('<option value="">Select Village</option>');
            
            if (val) {
                let data = {};
                if (val.includes('union_')) {
                    data.unionId = val.replace('union_', '');
                } else if (val.includes('pourashava_')) {
                    data.pourashavaId = val.replace('pourashava_', '');
                }
                
                $.ajax({
                    method: "GET",
                    url: "{{ route('get_villages') }}",
                    data: data
                }).done(function (response) {
                    $.each(response, function (index, item) {
                        targetEl.append('<option value="' + item.id + '">' + item.name + '</option>');
                    });
                });
            }
        });

        $('#createAccountForm').on('submit', function(e) {
            e.preventDefault();
            var form = this;
            
            // Check for duplicate nominees
            var selectedNominees = [];
            var hasDuplicate = false;
            $('.nominee-select').each(function() {
                var val = $(this).val();
                if (val) {
                    if (selectedNominees.includes(val)) {
                        hasDuplicate = true;
                    }
                    selectedNominees.push(val);
                }
            });

            if (hasDuplicate) {
                toastr.error('You cannot select the same nominee multiple times.');
                return false;
            }

            // Validate percentages
            var totalShare = 0;
            $('.share-percentage').each(function() {
                totalShare += parseFloat($(this).val()) || 0;
            });

            if (totalShare !== 100) {
                $('#share_error').removeClass('d-none');
                toastr.error('Total share percentage must be exactly 100%');
                return false;
            } else {
                $('#share_error').addClass('d-none');
            }
            
            Swal.fire({
                title: 'Are you sure?',
                text: "Do you want to create a new Shomriddhi Account?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Create it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
@endsection
