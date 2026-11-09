@extends('layouts.app')
@section('title','Supplier Create')
@section('content')
    <div class="row">
        <!-- left column -->
        <div class="col-md-12">
            <!-- jquery validation -->
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">Supplier Information</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                <form enctype="multipart/form-data" action="{{ route('supplier.store') }}" method="post">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group {{ $errors->has('name') ? 'has-error' :'' }}">
                                    <label for="name">Name <span class="text-danger">*</span></label>
                                    <input type="text" value="{{ old('name') }}" name="name" class="form-control" id="name" placeholder="Enter Name">
                                    @error('name')
                                    <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group {{ $errors->has('mobile_no') ? 'has-error' :'' }}">
                                    <label for="mobile_no">Mobile No. <span class="text-danger">*</span></label>
                                    <input type="text" value="{{ old('mobile_no') }}" name="mobile_no" class="form-control" id="mobile_no" placeholder="Enter Mobile No.">
                                    @error('mobile_no')
                                    <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group {{ $errors->has('email') ? 'has-error' :'' }}">
                                    <label for="email">Email</label>
                                    <input type="email" value="{{ old('email') }}" name="email" class="form-control" id="email" placeholder="Enter Email">
                                    @error('email')
                                    <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group {{ $errors->has('mobile_banking_no') ? 'has-error' :'' }}">
                                    <label for="mobile_banking_no">Mobile Banking No.</label>
                                    <input type="text" value="{{ old('mobile_banking_no') }}" name="mobile_banking_no" class="form-control" id="mobile_banking_no" placeholder="Enter Mobile Banking No.">
                                    @error('mobile_banking_no')
                                    <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group {{ $errors->has('bank_name') ? 'has-error' :'' }}">
                                    <label for="bank_name">Bank Name</label>
                                    <input type="text" value="{{ old('bank_name') }}" name="bank_name" class="form-control" id="bank_name" placeholder="Enter Bank Name">
                                    @error('bank_name')
                                    <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group {{ $errors->has('branch_name') ? 'has-error' :'' }}">
                                    <label for="branch_name">Branch Name</label>
                                    <input type="text" value="{{ old('branch_name') }}" name="branch_name" class="form-control" id="branch_name" placeholder="Enter Branch Name">
                                    @error('branch_name')
                                    <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group {{ $errors->has('bank_account_no') ? 'has-error' :'' }}">
                                    <label for="bank_account_no">Bank A/C</label>
                                    <input type="text" value="{{ old('bank_account_no') }}" name="bank_account_no" class="form-control" id="bank_account_no" placeholder="Enter Bank A/C">
                                    @error('bank_account_no')
                                    <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group {{ $errors->has('routing_no') ? 'has-error' :'' }}">
                                    <label for="routing_no">Routing No.</label>
                                    <input type="text" value="{{ old('routing_no') }}" name="routing_no" class="form-control" id="routing_no" placeholder="Enter Routing No.">
                                    @error('routing_no')
                                    <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group {{ $errors->has('balance_type') ? 'has-error' :'' }}">
                                            <label for="balance_type">Balance Type <span class="text-danger">*</span></label>
                                            <select name="balance_type" class="form-control" id="balance_type">
                                                <option value="1" {{ old('balance_type') == '1' ? 'selected' : '' }}>Due (Payable)</option>
                                                <option value="2" {{ old('balance_type') == '2' ? 'selected' : '' }}>Advance (Receivable)</option>
                                            </select>
                                            @error('balance_type')
                                            <span class="help-block text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group {{ $errors->has('opening_balance') ? 'has-error' :'' }}">
                                            <label for="opening_balance">Opening Balance <span class="text-danger">*</span></label>
                                            <input type="number" step="any" value="{{ old('opening_balance', 0) }}" name="opening_balance" class="form-control" id="opening_balance" placeholder="Enter Amount">
                                            @error('opening_balance')
                                            <span class="help-block text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group {{ $errors->has('division') ? 'has-error' :'' }}">
                                    <label for="division">Division</label>
                                    <select name="division" id="division" class="form-control select2">
                                        <option value="">Select Division</option>
                                        @foreach($divisions as $division)
                                            <option value="{{ $division->id }}" {{ old('division') == $division->id ? 'selected' : '' }}>{{ $division->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('division')
                                    <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group {{ $errors->has('district') ? 'has-error' :'' }}">
                                    <label for="district">District</label>
                                    <select name="district" id="district" class="form-control select2">
                                        <option value="">Select District</option>
                                    </select>
                                    @error('district')
                                    <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group {{ $errors->has('upazila') ? 'has-error' :'' }}">
                                    <label for="upazila">Upazila</label>
                                    <select name="upazila" id="upazila" class="form-control select2">
                                        <option value="">Select Upazila</option>
                                    </select>
                                    @error('upazila')
                                    <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group {{ $errors->has('union') ? 'has-error' :'' }}">
                                    <label for="union">Pourashava/Union</label>
                                    <select name="union" id="union" class="form-control select2">
                                        <option value="">Select Pourashava/Union</option>
                                    </select>
                                    @error('union')
                                    <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group {{ $errors->has('village') ? 'has-error' :'' }}">
                                        <label for="village_id">Village</label>
                                        <select name="village_id" id="village_id" class="form-control select2 village" data-selected="{{ old('village_id') }}">
                                            <option value="">Select Village</option>
                                        </select>
                                        @error('village_id')
                                    <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group {{ $errors->has('image') ? 'has-error' :'' }}">
                                    <label for="image">Supplier Image</label>
                                    <input type="file" name="image" class="form-control" id="image">
                                    @error('image')
                                    <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group {{ $errors->has('status') ? 'has-error' :'' }}">
                                    <label>Status <span class="text-danger">*</span></label>
                                    <div>
                                        <div class="icheck-success d-inline">
                                            <input checked type="radio" id="active" name="status" value="1" {{ old('status') == '1' ? 'checked' : '' }}>
                                            <label for="active">Active</label>
                                        </div>
                                        <div class="icheck-danger d-inline ml-2">
                                            <input type="radio" id="inactive" name="status" value="0" {{ old('status') == '0' ? 'checked' : '' }}>
                                            <label for="inactive">Inactive</label>
                                        </div>
                                    </div>
                                    @error('status')
                                    <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer">
                        <button type="button" class="btn btn-primary bg-gradient-primary btn-sm" id="btnPreview">Save</button>
                        <button type="submit" class="btn btn-info bg-gradient-info btn-sm d-none" id="btnRealSubmit">Save</button>
                        <a href="{{ route('supplier.index') }}" class="btn btn-danger bg-gradient-danger btn-sm float-right">Cancel</a>
                    </div>
                    <!-- /.card-footer -->
                </form>
            </div>
            <!-- /.card -->
        </div>
        <!--/.col (left) -->
    </div>

    <!-- Form Preview Modal -->
    <div class="modal fade" id="previewFormModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
            <div class="modal-content shadow-lg border-0" style="border-radius: 12px;">
                <div class="modal-header bg-gradient-primary">
                    <h5 class="modal-title text-white"><i class="fa fa-truck"></i> Supplier Profile Preview</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0">
                    <div class="p-4 text-center bg-light border-bottom">
                        <div class="d-inline-flex justify-content-center align-items-center rounded-circle bg-white shadow-sm mb-3" style="width: 80px; height: 80px;">
                            <i class="fa fa-truck fa-3x text-primary"></i>
                        </div>
                        <h4 class="font-weight-bold mb-1" id="p_name_header"></h4>
                        <p class="text-muted mb-2"><i class="fa fa-phone-alt"></i> <span id="p_mobile_header"></span></p>
                        <span id="p_status" class="badge px-3 py-1" style="font-size: 13px; font-weight: 500;"></span>
                    </div>
                    
                    <div class="p-4">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-uppercase text-primary font-weight-bold mb-3" style="font-size: 13px; letter-spacing: 1px;"><i class="fa fa-info-circle mr-1"></i> Basic Info</h6>
                                <table class="table table-sm table-borderless mb-4" style="font-size: 14px;">
                                    <tr><td width="40%" class="text-muted">Full Name</td><td width="60%" class="font-weight-bold text-dark" id="p_name"></td></tr>
                                    <tr><td class="text-muted">Mobile No.</td><td class="font-weight-bold text-dark" id="p_mobile_no"></td></tr>
                                    <tr><td class="text-muted">Email</td><td class="font-weight-bold text-dark" id="p_email"></td></tr>
                                    <tr><td class="text-muted">Opening Bal.</td><td class="font-weight-bold text-danger" id="p_opening_balance"></td></tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-uppercase text-primary font-weight-bold mb-3" style="font-size: 13px; letter-spacing: 1px;"><i class="fa fa-university mr-1"></i> Bank Info</h6>
                                <table class="table table-sm table-borderless mb-4" style="font-size: 14px;">
                                    <tr><td width="40%" class="text-muted">Mobile Bank</td><td width="60%" class="font-weight-bold text-dark" id="p_mobile_banking_no"></td></tr>
                                    <tr><td class="text-muted">Bank Name</td><td class="font-weight-bold text-dark" id="p_bank_name"></td></tr>
                                    <tr><td class="text-muted">Branch</td><td class="font-weight-bold text-dark" id="p_branch_name"></td></tr>
                                    <tr><td class="text-muted">Account No.</td><td class="font-weight-bold text-dark" id="p_bank_account_no"></td></tr>
                                    <tr><td class="text-muted">Routing No.</td><td class="font-weight-bold text-dark" id="p_routing_no"></td></tr>
                                </table>
                            </div>
                        </div>

                        <h6 class="text-uppercase text-primary font-weight-bold mb-3 mt-2" style="font-size: 13px; letter-spacing: 1px;"><i class="fa fa-map-marker-alt mr-1"></i> Address Information</h6>
                        <table class="table table-sm table-borderless mb-0" style="font-size: 14px;">
                            <tr>
                                <td width="20%" class="text-muted">Division</td><td width="30%" class="font-weight-bold text-dark" id="p_division"></td>
                                <td width="20%" class="text-muted">District</td><td width="30%" class="font-weight-bold text-dark" id="p_district"></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Upazila</td><td class="font-weight-bold text-dark" id="p_upazila"></td>
                                <td class="text-muted">Union/Pour.</td><td class="font-weight-bold text-dark" id="p_union"></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Village/Road</td><td colspan="3" class="font-weight-bold text-dark" id="p_village"></td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="modal-footer bg-light justify-content-between">
                    <button type="button" class="btn btn-outline-danger btn-sm" data-dismiss="modal"><i class="fa fa-edit"></i> Edit Data</button>
                    <button type="button" class="btn btn-success bg-gradient-success btn-sm font-weight-bold px-4" id="btnConfirmSave"><i class="fa fa-check-circle"></i> Confirm & Save</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(function () {
            $('#division').change(function () {
                var divisionId = $(this).val();
                $('#district').html('<option value="">Select District</option>');
                $('#upazila').html('<option value="">Select Upazila</option>');
                $('#union').html('<option value="">Select Pourashava/Union</option>');

                if (divisionId != '') {
                    $.ajax({
                        method: "GET",
                        url: "{{ route('get_districts') }}",
                        data: { divisionId: divisionId }
                    }).done(function( data ) {
                        $.each(data, function( index, item ) {
                            $('#district').append('<option value="'+item.id+'">'+item.name+'</option>');
                        });
                    });
                }
            });

            $('#district').change(function () {
                var districtId = $(this).val();
                $('#upazila').html('<option value="">Select Upazila</option>');
                $('#union').html('<option value="">Select Pourashava/Union</option>');

                if (districtId != '') {
                    $.ajax({
                        method: "GET",
                        url: "{{ route('get_upazilas') }}",
                        data: { districtId: districtId }
                    }).done(function( data ) {
                        $.each(data, function( index, item ) {
                            $('#upazila').append('<option value="'+item.id+'">'+item.name+'</option>');
                        });
                    });

                    // Also fetch pourashavas for this district
                    $.ajax({
                        method: "GET",
                        url: "{{ route('get_pourashavas') }}",
                        data: { districtId: districtId }
                    }).done(function( data ) {
                        $.each(data, function( index, item ) {
                            $('#union').append('<option value="pourashava_'+item.id+'">'+item.name+' (P)</option>');
                        });
                    });
                }
            });

            $('#upazila').change(function () {
                var upazilaId = $(this).val();
                $('#union').html('<option value="">Select Pourashava/Union</option>');

                if (upazilaId != '') {
                    // Re-add pourashavas from district first
                    var districtId = $('#district').val();
                    $.ajax({
                        method: "GET",
                        url: "{{ route('get_pourashavas') }}",
                        data: { districtId: districtId }
                    }).done(function( data ) {
                        $.each(data, function( index, item ) {
                            $('#union').append('<option value="pourashava_'+item.id+'">'+item.name+' (P)</option>');
                        });
                    });

                    // Add unions for this upazila
                    $.ajax({
                        method: "GET",
                        url: "{{ route('get_unions') }}",
                        data: { upazilaId: upazilaId }
                    }).done(function( data ) {
                        $.each(data, function( index, item ) {
                            $('#union').append('<option value="union_'+item.id+'">'+item.name+' (U)</option>');
                        });
                    });
                }
            });
            // Initial triggers for old values
            @if(old('division'))
                $('#division').trigger('change');
                
                @if(old('district'))
                    setTimeout(function() {
                        $('#district').val('{{ old('district') }}').trigger('change');
                        
                        @if(old('upazila'))
                            setTimeout(function() {
                                $('#upazila').val('{{ old('upazila') }}').trigger('change');
                                
                                @if(old('union'))
                                    setTimeout(function() {
                                        $('#union').val('{{ old('union') }}').trigger('change');
                                        
                                        @if(old('village_id'))
                                            setTimeout(function() {
                                                $('#village_id').val('{{ old('village_id') }}').trigger('change');
                                            }, 500);
                                        @endif
                                    }, 1500);
                                @endif
                            }, 1000);
                        @endif
                    }, 500);
                @endif
            @endif

            $('#union').change(function() {
                let val = $(this).val();
                let targetVillage = $('#village_id');
                let selectedVal = targetVillage.data('selected');
                
                targetVillage.html('<option value="">Loading...</option>');

                if (val) {
                    let reqData = {};
                    if (val.startsWith('union_')) {
                        reqData.unionId = val.split('_')[1];
                    } else if (val.startsWith('pourashava_')) {
                        reqData.pourashavaId = val.split('_')[1];
                    }
                    $.get("{{ route('get_villages') }}", reqData, function(data) {
                        let options = '<option value="">Select Village</option>';
                        data.forEach(function(item) {
                            options += `<option value="${item.id}" ${selectedVal == item.id ? 'selected' : ''}>${item.name}</option>`;
                        });
                        targetVillage.html(options).trigger('change');
                    });
                } else {
                    targetVillage.html('<option value="">Select Village</option>').trigger('change');
                }
            });

            $('#btnPreview').click(function() {
                let name = $('#name').val();
                let mobile = $('#mobile_no').val();

                if (!name || !mobile) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Incomplete Data',
                        text: 'Please fill all the required fields (*).',
                    });
                    return;
                }
                
                $('#p_name_header').text(name);
                $('#p_mobile_header').text(mobile);

                $('#p_name').text(name);
                $('#p_mobile_no').text(mobile);
                $('#p_email').text($('#email').val() || 'N/A');
                $('#p_mobile_banking_no').text($('#mobile_banking_no').val() || 'N/A');
                $('#p_bank_name').text($('#bank_name').val() || 'N/A');
                $('#p_branch_name').text($('#branch_name').val() || 'N/A');
                $('#p_bank_account_no').text($('#bank_account_no').val() || 'N/A');
                $('#p_routing_no').text($('#routing_no').val() || 'N/A');
                
                let bType = $('#balance_type').val() == '1' ? ' (Due)' : ' (Advance)';
                $('#p_opening_balance').text('৳ ' + ($('#opening_balance').val() || '0') + bType);
                
                $('#p_division').text($('#division').val() ? $('#division option:selected').text() : 'N/A');
                $('#p_district').text($('#district').val() ? $('#district option:selected').text() : 'N/A');
                $('#p_upazila').text($('#upazila').val() ? $('#upazila option:selected').text() : 'N/A');
                $('#p_union').text($('#union').val() ? $('#union option:selected').text() : 'N/A');
                $('#p_village').text($('#village_id').val() ? $('#village_id option:selected').text() : 'N/A');
                
                let status = $('input[name="status"]:checked').val();
                if(status == '1') {
                    $('#p_status').removeClass('badge-danger').addClass('badge-success').text('Status: Active');
                } else {
                    $('#p_status').removeClass('badge-success').addClass('badge-danger').text('Status: Inactive');
                }
                
                $('#previewFormModal').modal('show');
            });
            
            $('#btnConfirmSave').click(function() {
                $(this).html('<i class="fa fa-spinner fa-spin"></i> Saving...').prop('disabled', true);
                $('#previewFormModal').modal('hide');
                $('#btnRealSubmit').click();
            });
        });
    </script>
@endsection
