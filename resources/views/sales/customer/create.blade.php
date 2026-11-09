@extends('layouts.app')
@section('title','Client Create')
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">Client Information</h3>
                </div>
                <form enctype="multipart/form-data" action="{{ route('customer.store') }}" class="form-horizontal" method="post">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group {{ $errors->has('name') ? 'has-error' :'' }}">
                                    <label for="name">Name <span class="text-danger">*</span></label>
                                    <input type="text" value="{{ old('name') }}" name="name" class="form-control" id="name" placeholder="Enter Name">
                                    @error('name')
                                    <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group {{ $errors->has('mobile_no') ? 'has-error' :'' }}">
                                    <label for="mobile_no">Mobile No. <span class="text-danger">*</span></label>
                                    <input type="text" value="{{ old('mobile_no') }}" name="mobile_no" class="form-control" id="mobile_no" placeholder="Enter Mobile No.">
                                    @error('mobile_no')
                                    <span class="help-block text-danger">{{ $message }}</span>
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
                                    <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group {{ $errors->has('balance_type') ? 'has-error' :'' }}">
                                    <label for="balance_type">Balance Type <span class="text-danger">*</span></label>
                                    <select name="balance_type" class="form-control" id="balance_type">
                                        <option value="1" {{ old('balance_type') == '1' ? 'selected' : '' }}>Due (Receivable)</option>
                                        <option value="2" {{ old('balance_type') == '2' ? 'selected' : '' }}>Advance (Payable)</option>
                                    </select>
                                    @error('balance_type')
                                    <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group {{ $errors->has('opening_balance') ? 'has-error' :'' }}">
                                    <label for="opening_balance">Opening Balance <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" value="{{ old('opening_balance', 0) }}" name="opening_balance" class="form-control" id="opening_balance" placeholder="Enter Amount">
                                    @error('opening_balance')
                                    <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group {{ $errors->has('division') ? 'has-error' :'' }}">
                                    <label for="division">Division <span class="text-danger">*</span></label>
                                    <select name="division" id="division" class="form-control select2">
                                        <option value="">Select Division</option>
                                        @foreach($divisions as $division)
                                            <option {{ old('division') == $division->id ? 'selected' : '' }} value="{{ $division->id }}">{{ $division->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('division')
                                    <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group {{ $errors->has('district') ? 'has-error' :'' }}">
                                    <label for="district">District <span class="text-danger">*</span></label>
                                    <select name="district" id="district" class="form-control select2">
                                        <option value="">Select District</option>
                                    </select>
                                    @error('district')
                                    <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group {{ $errors->has('upazila') ? 'has-error' :'' }}">
                                    <label for="upazila">Upazila <span class="text-danger">*</span></label>
                                    <select name="upazila" id="upazila" class="form-control select2">
                                        <option value="">Select Upazila</option>
                                    </select>
                                    @error('upazila')
                                    <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group {{ $errors->has('union') ? 'has-error' :'' }}">
                                    <label for="union">Pourashava/Union</label>
                                    <select name="union" id="union" class="form-control select2">
                                        <option value="">Select Pourashava/Union</option>
                                    </select>
                                    @error('union')
                                    <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group {{ $errors->has('village_id') ? 'has-error' :'' }}">
                                    <label for="village_id">Village/Road</label>
                                    <select name="village_id" id="village_id" class="form-control select2" data-selected="{{ old('village_id') }}">
                                        <option value="">Select Village</option>
                                    </select>
                                    @error('village_id')
                                    <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group {{ $errors->has('image') ? 'has-error' :'' }}">
                                    <label for="image">Client Image</label>
                                    <input type="file" name="image" class="form-control" id="image">
                                    @error('image')
                                    <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group {{ $errors->has('status') ? 'has-error' :'' }}">
                                    <label>Status <span class="text-danger">*</span></label>
                                    <div class="form-group">
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
                                    <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="button" class="btn btn-primary bg-gradient-primary btn-sm" id="btnPreview">Save</button>
                        <button type="submit" class="btn btn-info bg-gradient-info btn-sm d-none" id="btnRealSubmit">Save</button>
                        <a href="{{ route('customer.index') }}" class="btn btn-danger bg-gradient-danger btn-sm float-right">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Form Preview Modal -->
    <div class="modal fade" id="previewFormModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
            <div class="modal-content shadow-lg border-0" style="border-radius: 12px;">
                <div class="modal-header bg-gradient-primary">
                    <h5 class="modal-title text-white"><i class="fa fa-user-check"></i> Client Profile Preview</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0">
                    <div class="p-4 text-center bg-light border-bottom">
                        <div class="d-inline-flex justify-content-center align-items-center rounded-circle bg-white shadow-sm mb-3" style="width: 80px; height: 80px;">
                            <i class="fa fa-user fa-3x text-primary"></i>
                        </div>
                        <h4 class="font-weight-bold mb-1" id="p_name_header"></h4>
                        <p class="text-muted mb-2"><i class="fa fa-phone-alt"></i> <span id="p_mobile_header"></span></p>
                        <span id="p_status" class="badge px-3 py-1" style="font-size: 13px; font-weight: 500;"></span>
                    </div>
                    
                    <div class="p-4">
                        <h6 class="text-uppercase text-primary font-weight-bold mb-3" style="font-size: 13px; letter-spacing: 1px;"><i class="fa fa-info-circle mr-1"></i> Basic Information</h6>
                        <table class="table table-sm table-borderless mb-4" style="font-size: 14px;">
                            <tr>
                                <td width="35%" class="text-muted">Full Name</td>
                                <td width="65%" class="font-weight-bold text-dark" id="p_name"></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Mobile No.</td>
                                <td class="font-weight-bold text-dark" id="p_mobile_no"></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Email Address</td>
                                <td class="font-weight-bold text-dark" id="p_email"></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Opening Balance</td>
                                <td class="font-weight-bold text-danger" id="p_opening_balance"></td>
                            </tr>
                        </table>

                        <h6 class="text-uppercase text-primary font-weight-bold mb-3" style="font-size: 13px; letter-spacing: 1px;"><i class="fa fa-map-marker-alt mr-1"></i> Address Information</h6>
                        <table class="table table-sm table-borderless mb-0" style="font-size: 14px;">
                            <tr>
                                <td width="35%" class="text-muted">Division</td>
                                <td width="65%" class="font-weight-bold text-dark" id="p_division"></td>
                            </tr>
                            <tr>
                                <td class="text-muted">District</td>
                                <td class="font-weight-bold text-dark" id="p_district"></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Upazila</td>
                                <td class="font-weight-bold text-dark" id="p_upazila"></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Union/Pourashava</td>
                                <td class="font-weight-bold text-dark" id="p_union"></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Village/Road</td>
                                <td class="font-weight-bold text-dark" id="p_village"></td>
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
            var oldDistrict = "{{ old('district') }}";
            var oldUpazila = "{{ old('upazila') }}";
            var oldUnion = "{{ old('union') }}";

            $('#division').change(function () {
                let divisionId = $(this).val();
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
                            let selected = (item.id == oldDistrict) ? 'selected' : '';
                            $('#district').append('<option value="'+item.id+'" '+selected+'>'+item.name+'</option>');
                        });
                        if ($('#district').val() != '') {
                            $('#district').trigger('change');
                        }
                    });
                }
            });

            $('#district').change(function () {
                let districtId = $(this).val();
                $('#upazila').html('<option value="">Select Upazila</option>');
                $('#union').html('<option value="">Select Pourashava/Union</option>');

                if (districtId != '') {
                    $.ajax({
                        method: "GET",
                        url: "{{ route('get_upazilas') }}",
                        data: { districtId: districtId }
                    }).done(function( data ) {
                        $.each(data, function( index, item ) {
                            let selected = (item.id == oldUpazila) ? 'selected' : '';
                            $('#upazila').append('<option value="'+item.id+'" '+selected+'>'+item.name+'</option>');
                        });
                        if ($('#upazila').val() != '') {
                            $('#upazila').trigger('change');
                        }
                    });
                }
            });

            $('#upazila').change(function () {
                let upazilaId = $(this).val();
                let districtId = $('#district').val();
                $('#union').html('<option value="">Select Pourashava/Union</option>');

                if (districtId != '') {
                    // Load Pourashavas
                    $.ajax({
                        method: "GET",
                        url: "{{ route('get_pourashavas') }}",
                        data: { districtId: districtId }
                    }).done(function( data ) {
                        $.each(data, function( index, item ) {
                            let selected = ('pourashava_'+item.id == oldUnion) ? 'selected' : '';
                            $('#union').append('<option value="pourashava_'+item.id+'" '+selected+'>'+item.name+' (P)</option>');
                        });

                        // Then load Unions
                        if (upazilaId != '') {
                            $.ajax({
                                method: "GET",
                                url: "{{ route('get_unions') }}",
                                data: { upazilaId: upazilaId }
                            }).done(function( data ) {
                                $.each(data, function( index, item ) {
                                    let selected = ('union_'+item.id == oldUnion) ? 'selected' : '';
                                    $('#union').append('<option value="union_'+item.id+'" '+selected+'>'+item.name+' (U)</option>');
                                });
                            });
                        }
                    });
                }
            });

            $('#union').change(function () {
                let val = $(this).val();
                let targetEl = $('#village_id');
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
                        let oldVillage = targetEl.data('selected');
                        if (oldVillage) {
                            targetEl.val(oldVillage).trigger('change');
                        }
                    });
                }
            });

            if ($('#division').val() != '') {
                $('#division').trigger('change');
            }

            $('#btnPreview').click(function() {
                let name = $('#name').val();
                let mobile = $('#mobile_no').val();
                let div = $('#division').val();
                let dist = $('#district').val();
                let upz = $('#upazila').val();

                if (!name || !mobile || !div || !dist || !upz) {
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
                
                let bType = $('#balance_type').val() == '1' ? ' (Due)' : ' (Advance)';
                $('#p_opening_balance').text('৳ ' + ($('#opening_balance').val() || '0') + bType);
                
                $('#p_division').text($('#division option:selected').text());
                $('#p_district').text($('#district option:selected').text());
                $('#p_upazila').text($('#upazila option:selected').text());
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
