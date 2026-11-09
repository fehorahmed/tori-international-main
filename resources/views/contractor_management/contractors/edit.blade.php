@extends('layouts.app')
@section('title', 'Edit Contractor')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">Edit Contractor Profile</h3>
                    <a href="{{ route('contractor_management.contractors.index') }}"
                        class="btn btn-default btn-sm float-right">Back to List</a>
                </div>
                <form action="{{ route('contractor_management.contractors.update', $contractor->id) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="row">
                            <!-- Left Column: Basic Info -->
                            <div class="col-md-6">
                                <h5 class="text-info border-bottom pb-2 mb-3">Basic Information</h5>

                                <div class="form-group">
                                    <label for="contractor_type_id">Contractor Type <span
                                            class="text-danger">*</span></label>
                                    <select name="contractor_type_id" id="contractor_type_id" class="form-control select2"
                                        required>
                                        <option value="">Select Type</option>
                                        @foreach($types as $type)
                                            <option value="{{ $type->id }}" {{ old('contractor_type_id', $contractor->contractor_type_id) == $type->id ? 'selected' : '' }}>
                                                {{ $type->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('contractor_type_id') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>

                                <div class="form-group">
                                    <label for="name">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                        id="name" value="{{ old('name', $contractor->name) }}" placeholder="Contractor Name"
                                        required>
                                    @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>

                                <div class="form-group">
                                    <label for="mobile_no">Mobile No <span class="text-danger">*</span></label>
                                    <input type="text" name="mobile_no"
                                        class="form-control @error('mobile_no') is-invalid @enderror" id="mobile_no"
                                        value="{{ old('mobile_no', $contractor->mobile_no) }}" placeholder="Ex: 017XXXXX"
                                        required>
                                    @error('mobile_no') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>

                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" name="email"
                                        class="form-control @error('email') is-invalid @enderror" id="email"
                                        value="{{ old('email', $contractor->email) }}" placeholder="Optional">
                                    @error('email') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>

                                <div class="form-group">
                                    <label for="nid_number">NID Number</label>
                                    <input type="text" name="nid_number"
                                        class="form-control @error('nid_number') is-invalid @enderror" id="nid_number"
                                        value="{{ old('nid_number', $contractor->nid_number) }}" placeholder="National ID">
                                    @error('nid_number') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>

                                <div class="form-group">
                                    <label for="company_name">Company Name</label>
                                    <input type="text" name="company_name"
                                        class="form-control @error('company_name') is-invalid @enderror" id="company_name"
                                        value="{{ old('company_name', $contractor->company_name) }}"
                                        placeholder="If applicable">
                                    @error('company_name') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>

                                <div class="form-group">
                                    <label for="status">Status <span class="text-danger">*</span></label>
                                    <select name="status" id="status" class="form-control select2" required>
                                        <option value="1" {{ old('status', $contractor->status) == '1' ? 'selected' : '' }}>
                                            Active</option>
                                        <option value="0" {{ old('status', $contractor->status) == '0' ? 'selected' : '' }}>
                                            Inactive</option>
                                    </select>
                                    @error('status') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-7">
                                        <label for="opening_balance">Opening Balance</label>
                                        <input type="number" step="0.01" name="opening_balance" class="form-control" id="opening_balance" value="{{ old('opening_balance', $openingBalance) }}" placeholder="0.00" readonly>
                                    </div>
                                    <div class="form-group col-md-5">
                                        <label for="balance_type">Balance Type</label>
                                        <select name="balance_type" id="balance_type" class="form-control select2" disabled>
                                            <option value="due" {{ old('balance_type', $balanceType) == 'due' ? 'selected' : '' }}>Due (তারা পাবে)</option>
                                            <option value="advance" {{ old('balance_type', $balanceType) == 'advance' ? 'selected' : '' }}>Advance (দিয়ে রেখেছি)</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="image">Photo</label>
                                    <input type="file" name="image" class="form-control" id="image" accept="image/*">
                                    @error('image') <span class="text-danger">{{ $message }}</span> @enderror
                                    @if($contractor->image)
                                        <div class="mt-2">
                                            <img src="{{ asset('storage/' . $contractor->image) }}" alt="Photo"
                                                class="img-thumbnail" width="100">
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Right Column: Address Info -->
                            <div class="col-md-6">
                                <h5 class="text-info border-bottom pb-2 mb-3">Address & Location</h5>

                                <div class="form-group">
                                    <label for="division">Division</label>
                                    <select name="division" id="division" class="form-control select2">
                                        <option value="">Select Division</option>
                                        @foreach($divisions as $division)
                                            <option value="{{ $division->id }}" {{ old('division', $contractor->division_id) == $division->id ? 'selected' : '' }}>
                                                {{ $division->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('division') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>

                                <div class="form-group">
                                    <label for="district">District</label>
                                    <select name="district" id="district" class="form-control select2">
                                        <option value="">Select District</option>
                                        @if($contractor->district_id)
                                            <option value="{{ $contractor->district_id }}" selected>
                                                {{ $contractor->district->name ?? 'Selected' }}</option>
                                        @endif
                                    </select>
                                    @error('district') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>

                                <div class="form-group">
                                    <label for="upazila">Upazila</label>
                                    <select name="upazila" id="upazila" class="form-control select2">
                                        <option value="">Select Upazila</option>
                                        @if($contractor->upazila_id)
                                            <option value="{{ $contractor->upazila_id }}" selected>
                                                {{ $contractor->upazila->name ?? 'Selected' }}</option>
                                        @endif
                                    </select>
                                    @error('upazila') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>

                                <div class="form-group">
                                    <label for="union">Pourashava/Union</label>
                                    <select name="union" id="union" class="form-control select2">
                                        <option value="">Select Pourashava/Union</option>
                                        @if($contractor->union_id)
                                            <option value="union_{{ $contractor->union_id }}" selected>
                                                {{ $contractor->union->name ?? '' }} (U)</option>
                                        @elseif($contractor->pourashava_id)
                                            <option value="pourashava_{{ $contractor->pourashava_id }}" selected>
                                                {{ $contractor->pourashava->name ?? '' }} (P)</option>
                                        @endif
                                    </select>
                                    @error('union') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>

                                <div class="form-group">
                                    <label for="village_id">Village</label>
                                    <select name="village_id" id="village_id" class="form-control select2 village"
                                        data-selected="{{ old('village_id', $contractor->village_id) }}">
                                        <option value="">Select Village</option>
                                        @if($contractor->village_id)
                                            <option value="{{ $contractor->village_id }}" selected>
                                                {{ $contractor->village->name ?? 'Selected' }}</option>
                                        @endif
                                    </select>
                                    @error('village_id') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>

                                <div class="form-group">
                                    <label for="address">Details Address</label>
                                    <textarea name="address" id="address" rows="3" class="form-control"
                                        placeholder="House/Road details">{{ old('address', $contractor->address) }}</textarea>
                                    @error('address') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /.card-body -->

                    <div class="card-footer text-center">
                        <button type="button" class="btn btn-primary bg-gradient-primary btn-lg px-5" id="btnPreview">Update
                            Contractor</button>
                        <button type="submit" class="btn btn-info bg-gradient-info btn-sm d-none"
                            id="btnRealSubmit">Update</button>
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
                    <h5 class="modal-title text-white"><i class="fa fa-hard-hat"></i> Contractor Profile Preview</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0">
                    <div class="p-4 text-center bg-light border-bottom">
                        <div class="d-inline-flex justify-content-center align-items-center rounded-circle bg-white shadow-sm mb-3"
                            style="width: 80px; height: 80px;">
                            <i class="fa fa-hard-hat fa-3x text-primary"></i>
                        </div>
                        <h4 class="font-weight-bold mb-1" id="p_name_header"></h4>
                        <p class="text-muted mb-2"><i class="fa fa-phone-alt"></i> <span id="p_mobile_header"></span></p>
                        <span id="p_status" class="badge px-3 py-1" style="font-size: 13px; font-weight: 500;"></span>
                    </div>

                    <div class="p-4">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-uppercase text-primary font-weight-bold mb-3"
                                    style="font-size: 13px; letter-spacing: 1px;"><i class="fa fa-info-circle mr-1"></i>
                                    Basic Info</h6>
                                <table class="table table-sm table-borderless mb-4" style="font-size: 14px;">
                                    <tr>
                                        <td width="40%" class="text-muted">Type</td>
                                        <td width="60%" class="font-weight-bold text-dark" id="p_type"></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Full Name</td>
                                        <td class="font-weight-bold text-dark" id="p_name"></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Mobile No.</td>
                                        <td class="font-weight-bold text-dark" id="p_mobile_no"></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Email</td>
                                        <td class="font-weight-bold text-dark" id="p_email"></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">NID Number</td>
                                        <td class="font-weight-bold text-dark" id="p_nid"></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Company Name</td>
                                        <td class="font-weight-bold text-dark" id="p_company"></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Opening Bal.</td>
                                        <td class="font-weight-bold text-danger" id="p_opening_balance"></td>
                                    </tr>
                                </table>
                            </div>

                            <div class="col-md-6">
                                <h6 class="text-uppercase text-primary font-weight-bold mb-3"
                                    style="font-size: 13px; letter-spacing: 1px;"><i class="fa fa-map-marker-alt mr-1"></i>
                                    Address Info</h6>
                                <table class="table table-sm table-borderless mb-4" style="font-size: 14px;">
                                    <tr>
                                        <td width="40%" class="text-muted">Division</td>
                                        <td width="60%" class="font-weight-bold text-dark" id="p_division"></td>
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
                                        <td class="text-muted">Union/Pour.</td>
                                        <td class="font-weight-bold text-dark" id="p_union"></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Village</td>
                                        <td class="font-weight-bold text-dark" id="p_village"></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Details</td>
                                        <td class="font-weight-bold text-dark" id="p_address"></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light justify-content-between">
                    <button type="button" class="btn btn-outline-danger btn-sm" data-dismiss="modal"><i
                            class="fa fa-edit"></i> Edit Data</button>
                    <button type="button" class="btn btn-success bg-gradient-success btn-sm font-weight-bold px-4"
                        id="btnConfirmSave"><i class="fa fa-check-circle"></i> Confirm & Update</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(function () {
            var initialDistrict = '{{ old('district', $contractor->district_id ?? '') }}';
            var initialUpazila = '{{ old('upazila', $contractor->upazila_id ?? '') }}';
            var initialUnion = '{{ old('union', $contractor->union_id ? 'union_'.$contractor->union_id : ($contractor->pourashava_id ? 'pourashava_'.$contractor->pourashava_id : '')) }}';
            var initialVillage = '{{ old('village_id', $contractor->village_id ?? '') }}';

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

                    // Also fetch pourashavas for this district
                    $.ajax({
                        method: "GET",
                        url: "{{ route('get_pourashavas') }}",
                        data: { districtId: districtId }
                    }).done(function( data ) {
                        $.each(data, function( index, item ) {
                            let selected = ('pourashava_'+item.id == initialUnion) ? 'selected' : '';
                            $('#union').append('<option value="pourashava_'+item.id+'" '+selected+'>'+item.name+' (P)</option>');
                        });
                        if (initialUnion.startsWith('pourashava_') && $('#union').val() != '') {
                            $('#union').trigger('change');
                        }
                    });
                }
            });

            $('#upazila').change(function () {
                let upazilaId = $(this).val();
                $('#union').html('<option value="">Select Pourashava/Union</option>');

                if (upazilaId != '') {
                    let districtId = $('#district').val();
                    $.ajax({
                        method: "GET",
                        url: "{{ route('get_pourashavas') }}",
                        data: { districtId: districtId }
                    }).done(function (data) {
                        $.each(data, function (index, item) {
                            let selected = ('pourashava_'+item.id == initialUnion) ? 'selected' : '';
                            $('#union').append('<option value="pourashava_' + item.id + '" '+selected+'>' + item.name + ' (P)</option>');
                        });
                        
                        $.ajax({
                            method: "GET",
                            url: "{{ route('get_unions') }}",
                            data: { upazilaId: upazilaId }
                        }).done(function (data) {
                            $.each(data, function (index, item) {
                                let selected = ('union_'+item.id == initialUnion) ? 'selected' : '';
                                $('#union').append('<option value="union_' + item.id + '" '+selected+'>' + item.name + ' (U)</option>');
                            });
                            if ($('#union').val() != '') {
                                $('#union').trigger('change');
                            }
                        });
                    });
                }
            });

            $('#union').change(function () {
                let val = $(this).val();
                let targetVillage = $('#village_id');

                targetVillage.html('<option value="">Loading...</option>');

                if (val) {
                    let reqData = {};
                    if (val.startsWith('union_')) {
                        reqData.union_id = val.split('_')[1];
                    } else if (val.startsWith('pourashava_')) {
                        reqData.pourashava_id = val.split('_')[1];
                    }
                    $.get("{{ route('get_villages') }}", reqData, function (data) {
                        let options = '<option value="">Select Village</option>';
                        data.forEach(function (item) {
                            let selected = (item.id == initialVillage) ? 'selected' : '';
                            options += `<option value="${item.id}" ${selected}>${item.name}</option>`;
                        });
                        targetVillage.html(options).trigger('change');
                    });
                } else {
                    targetVillage.html('<option value="">Select Village</option>').trigger('change');
                }
            });

            // Trigger initial load
            if ($('#division').val() != '') {
                $('#division').trigger('change');
            }
            $('#btnPreview').click(function () {
                let type = $('#contractor_type_id').val();
                let name = $('#name').val();
                let mobile = $('#mobile_no').val();
                let status = $('#status').val();

                if (!type || !name || !mobile || !status) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Incomplete Data',
                        text: 'Please fill all the required fields (*).',
                    });
                    return;
                }

                $('#p_name_header').text(name);
                $('#p_mobile_header').text(mobile);

                $('#p_type').text($('#contractor_type_id option:selected').text());
                $('#p_name').text(name);
                $('#p_mobile_no').text(mobile);
                $('#p_email').text($('#email').val() || 'N/A');
                $('#p_nid').text($('#nid_number').val() || 'N/A');
                $('#p_company').text($('#company_name').val() || 'N/A');

                let ob = $('#opening_balance').val();
                let bType = $('#balance_type').val();
                if(ob && ob > 0) {
                    $('#p_opening_balance').text(ob + ' (' + (bType == 'due' ? 'Due' : 'Advance') + ')');
                } else {
                    $('#p_opening_balance').text('0.00');
                }

                $('#p_division').text($('#division').val() ? $('#division option:selected').text() : 'N/A');
                $('#p_district').text($('#district').val() ? $('#district option:selected').text() : 'N/A');
                $('#p_upazila').text($('#upazila').val() ? $('#upazila option:selected').text() : 'N/A');
                $('#p_union').text($('#union').val() ? $('#union option:selected').text() : 'N/A');
                $('#p_village').text($('#village_id').val() ? $('#village_id option:selected').text() : 'N/A');
                $('#p_address').text($('#address').val() || 'N/A');

                if (status == '1') {
                    $('#p_status').removeClass('badge-danger').addClass('badge-success').text('Status: Active');
                } else {
                    $('#p_status').removeClass('badge-success').addClass('badge-danger').text('Status: Inactive');
                }

                $('#previewFormModal').modal('show');
            });

            $('#btnConfirmSave').click(function () {
                $(this).html('<i class="fa fa-spinner fa-spin"></i> Updating...').prop('disabled', true);
                $('#previewFormModal').modal('hide');
                $('#btnRealSubmit').click();
            });
        });
    </script>
@endsection