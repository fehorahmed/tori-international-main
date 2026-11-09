@extends('layouts.app')
@section('title','Member Edit')
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">Member Information</h3>
                </div>
                <form enctype="multipart/form-data" action="{{ route('shomriddhi-advance.members.update',['member'=>$member->id]) }}" class="form-horizontal" method="post">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <h6 class="mt-2 mb-4 border-bottom pb-2 font-weight-bold text-primary">
                            <i class="fas fa-user-circle mr-2"></i>Personal Information
                        </h6>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group {{ $errors->has('shomriddhi_group_id') ? 'has-error' :'' }}">
                                    <label for="shomriddhi_group_id">Select Group <span class="text-danger">*</span></label>
                                    <select name="shomriddhi_group_id" id="shomriddhi_group_id" class="form-control select2">
                                        <option value="">Select Group</option>
                                        @foreach($groups as $group)
                                            <option {{ old('shomriddhi_group_id', $member->shomriddhi_group_id) == $group->id ? 'selected' : '' }} value="{{ $group->id }}">{{ $group->code }} - {{ $group->name_en }}</option>
                                        @endforeach
                                    </select>
                                    @error('shomriddhi_group_id')
                                    <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group {{ $errors->has('name') ? 'has-error' :'' }}">
                                    <label for="name">Name <span class="text-danger">*</span></label>
                                    <input type="text" value="{{ old('name',$member->name) }}" name="name" class="form-control" id="name" placeholder="Enter Name">
                                    @error('name')
                                    <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group {{ $errors->has('mobile_no') ? 'has-error' :'' }}">
                                    <label for="mobile_no">Mobile No. <span class="text-danger">*</span></label>
                                    <input type="text" value="{{ old('mobile_no',$member->mobile_no) }}" name="mobile_no" class="form-control" id="mobile_no" placeholder="Enter Mobile No.">
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
                                    <input type="email" value="{{ old('email', $member->email) }}" name="email" class="form-control" id="email" placeholder="Enter Email">
                                    @error('email')
                                    <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group {{ $errors->has('nid_number') ? 'has-error' :'' }}">
                                    <label for="nid_number">NID Number</label>
                                    <input type="text" value="{{ old('nid_number', $member->nid_number) }}" name="nid_number" class="form-control" id="nid_number" placeholder="Enter NID Number">
                                    @error('nid_number')
                                    <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group {{ $errors->has('father_name') ? 'has-error' :'' }}">
                                    <label for="father_name">Father Name</label>
                                    <input type="text" value="{{ old('father_name', $member->father_name) }}" name="father_name" class="form-control" id="father_name" placeholder="Enter Father Name">
                                    @error('father_name')
                                    <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group {{ $errors->has('mother_name') ? 'has-error' :'' }}">
                                    <label for="mother_name">Mother Name</label>
                                    <input type="text" value="{{ old('mother_name', $member->mother_name) }}" name="mother_name" class="form-control" id="mother_name" placeholder="Enter Mother Name">
                                    @error('mother_name')
                                    <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group {{ $errors->has('spouse_name') ? 'has-error' :'' }}">
                                    <label for="spouse_name">Spouse Name</label>
                                    <input type="text" value="{{ old('spouse_name', $member->spouse_name) }}" name="spouse_name" class="form-control" id="spouse_name" placeholder="Enter Spouse Name">
                                    @error('spouse_name')
                                    <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group {{ $errors->has('gender') ? 'has-error' :'' }}">
                                    <label for="gender">Gender</label>
                                    <select name="gender" id="gender" class="form-control">
                                        <option value="">Select Gender</option>
                                        <option value="Male" {{ old('gender', $member->gender) == 'Male' ? 'selected' : '' }}>Male</option>
                                        <option value="Female" {{ old('gender', $member->gender) == 'Female' ? 'selected' : '' }}>Female</option>
                                        <option value="Other" {{ old('gender', $member->gender) == 'Other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    @error('gender')
                                    <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group {{ $errors->has('dob') ? 'has-error' :'' }}">
                                    <label for="dob">Date of Birth</label>
                                    <input type="text" value="{{ old('dob', $member->dob) }}" name="dob" class="form-control date-picker" id="dob" placeholder="DD-MM-YYYY">
                                    @error('dob')
                                    <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group {{ $errors->has('blood_group') ? 'has-error' :'' }}">
                                    <label for="blood_group">Blood Group</label>
                                    <select name="blood_group" id="blood_group" class="form-control">
                                        <option value="">Select Blood Group</option>
                                        <option value="A+" {{ old('blood_group', $member->blood_group) == 'A+' ? 'selected' : '' }}>A+</option>
                                        <option value="A-" {{ old('blood_group', $member->blood_group) == 'A-' ? 'selected' : '' }}>A-</option>
                                        <option value="B+" {{ old('blood_group', $member->blood_group) == 'B+' ? 'selected' : '' }}>B+</option>
                                        <option value="B-" {{ old('blood_group', $member->blood_group) == 'B-' ? 'selected' : '' }}>B-</option>
                                        <option value="AB+" {{ old('blood_group', $member->blood_group) == 'AB+' ? 'selected' : '' }}>AB+</option>
                                        <option value="AB-" {{ old('blood_group', $member->blood_group) == 'AB-' ? 'selected' : '' }}>AB-</option>
                                        <option value="O+" {{ old('blood_group', $member->blood_group) == 'O+' ? 'selected' : '' }}>O+</option>
                                        <option value="O-" {{ old('blood_group', $member->blood_group) == 'O-' ? 'selected' : '' }}>O-</option>
                                    </select>
                                    @error('blood_group')
                                    <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <h6 class="mt-4 mb-4 border-bottom pb-2 font-weight-bold text-info">
                            <i class="fas fa-map-marker-alt mr-2"></i>Address Information
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group {{ $errors->has('division') ? 'has-error' :'' }}">
                                    <label for="division">Division <span class="text-danger">*</span></label>
                                    <select name="division" id="division" class="form-control select2">
                                        <option value="">Select Division</option>
                                        @foreach($divisions as $division)
                                            <option {{ old('division',$member->division_id) == $division->id ? 'selected' : '' }} value="{{ $division->id }}">{{ $division->name }}</option>
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
                                    <select name="village_id" id="village_id" class="form-control select2" data-selected="{{ old('village_id', $member->village_id) }}">
                                        <option value="">Select Village</option>
                                    </select>
                                    @error('village_id')
                                    <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group {{ $errors->has('image') ? 'has-error' :'' }}">
                                    <label for="image">Member Image</label>
                                    <input type="file" name="image" class="form-control" id="image">
                                    @error('image')
                                    <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                    @if($member->image)
                                        <div class="mt-2">
                                            <img src="{{ asset($member->image) }}" width="100" class="img-thumbnail" alt="Current Image">
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>


                    </div>
                    <div class="card-footer">
                        <button type="button" class="btn btn-primary bg-gradient-primary btn-sm" id="btnPreview">Save</button>
                        <button type="submit" class="btn btn-info bg-gradient-info btn-sm d-none" id="btnRealSubmit">Save</button>
                        <a href="{{ route('shomriddhi-advance.members.index') }}" class="btn btn-danger bg-gradient-danger btn-sm float-right">Cancel</a>
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
                    <h5 class="modal-title text-white"><i class="fa fa-user-check"></i> Member Profile Preview</h5>
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
            // Initial values
            var initialDistrict = "{{ old('district', $member->district_id) }}";
            var initialUpazila = "{{ old('upazila', $member->upazila_id) }}";
            var initialUnion = "{{ old('union', $member->union_id ? 'union_'.$member->union_id : ($member->pourashava_id ? 'pourashava_'.$member->pourashava_id : '')) }}";

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
                            let selected = (item.id == initialDistrict) ? 'selected' : '';
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
                            let selected = (item.id == initialUpazila) ? 'selected' : '';
                            $('#upazila').append('<option value="'+item.id+'" '+selected+'>'+item.name+'</option>');
                        });
                        if ($('#upazila').val() != '') {
                            $('#upazila').trigger('change');
                        }
                    });

                    // Load Pourashavas
                    $.ajax({
                        method: "GET",
                        url: "{{ route('get_pourashavas') }}",
                        data: { districtId: districtId }
                    }).done(function( data ) {
                        $.each(data, function( index, item ) {
                            let selected = ('pourashava_'+item.id == initialUnion) ? 'selected' : '';
                            $('#union').append('<option value="pourashava_'+item.id+'" '+selected+'>'+item.name+' (P)</option>');
                        });
                    });
                }
            });

            $('#upazila').change(function () {
                let upazilaId = $(this).val();
                let districtId = $('#district').val();
                
                $('#union').html('<option value="">Select Pourashava/Union</option>');
                
                if (districtId != '') {
                    // Load Pourashavas first
                    $.ajax({
                        method: "GET",
                        url: "{{ route('get_pourashavas') }}",
                        data: { districtId: districtId }
                    }).done(function( data ) {
                        $.each(data, function( index, item ) {
                            let selected = ('pourashava_'+item.id == initialUnion) ? 'selected' : '';
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
                                    let selected = ('union_'+item.id == initialUnion) ? 'selected' : '';
                                    $('#union').append('<option value="union_'+item.id+'" '+selected+'>'+item.name+' (U)</option>');
                                });
                                if ($('#union').val() != '') {
                                    $('#union').trigger('change');
                                }
                            });
                        } else {
                            if ($('#union').val() != '') {
                                $('#union').trigger('change');
                            }
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

            // Trigger initial change
            if ($('#division').val() != '') {
                $('#division').trigger('change');
            }



            $('#btnPreview').click(function() {
                let group = $('#shomriddhi_group_id').val();
                let name = $('#name').val();
                let mobile = $('#mobile_no').val();
                let div = $('#division').val();
                let dist = $('#district').val();
                let upz = $('#upazila').val();

                if (!group || !name || !mobile || !div || !dist || !upz) {
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
