@extends('layouts.app')
@section('title', 'Group Edit')
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">Edit Group</h3>
                </div>
                <form action="{{ route('shomriddhi-advance.groups.update', $group->id) }}" class="form-horizontal"
                    method="post">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group {{ $errors->has('name_en') ? 'has-error' : '' }}">
                                    <label for="name_en">Name (English) <span class="text-danger">*</span></label>
                                    <input type="text" value="{{ old('name_en', $group->name_en) }}" name="name_en"
                                        class="form-control" id="name_en" placeholder="Enter English Name">
                                    @error('name_en')
                                        <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group {{ $errors->has('name_bn') ? 'has-error' : '' }}">
                                    <label for="name_bn">Name (Bangla)</label>
                                    <input type="text" value="{{ old('name_bn', $group->name_bn) }}" name="name_bn"
                                        class="form-control" id="name_bn" placeholder="Enter Bangla Name">
                                    @error('name_bn')
                                        <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group {{ $errors->has('opening_date') ? 'has-error' :'' }}">
                                    <label for="opening_date">Opening Date</label>
                                    <input type="text" class="form-control date-picker" placeholder="Enter Opening Date"
                                           id="opening_date" name="opening_date" value="{{ old('opening_date', date('d-m-Y', strtotime($group->opening_date))) }}" readonly>
                                    @error('opening_date')
                                    <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group {{ $errors->has('meeting_time') ? 'has-error' :'' }}">
                                    <label for="meeting_time">Meeting Time</label>
                                    <input type="time" class="form-control" placeholder="Enter Meeting Time"
                                           id="meeting_time" name="meeting_time" value="{{ old('meeting_time', $group->meeting_time) }}">
                                    @error('meeting_time')
                                    <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group {{ $errors->has('collection_day') ? 'has-error' :'' }}">
                                    <label for="collection_day">Collection Day <span class="text-danger">*</span></label>
                                    <select name="collection_day" id="collection_day" class="form-control" required>
                                        <option value="0" {{ old('collection_day', $group->collection_day) == '0' ? 'selected' : '' }}>Sunday</option>
                                        <option value="1" {{ old('collection_day', $group->collection_day) == '1' ? 'selected' : '' }}>Monday</option>
                                        <option value="2" {{ old('collection_day', $group->collection_day) == '2' ? 'selected' : '' }}>Tuesday</option>
                                        <option value="3" {{ old('collection_day', $group->collection_day) == '3' ? 'selected' : '' }}>Wednesday</option>
                                        <option value="4" {{ old('collection_day', $group->collection_day) == '4' ? 'selected' : '' }}>Thursday</option>
                                        <option value="5" {{ old('collection_day', $group->collection_day) == '5' ? 'selected' : '' }}>Friday</option>
                                        <option value="6" {{ old('collection_day', $group->collection_day) == '6' ? 'selected' : '' }}>Saturday</option>
                                    </select>
                                    @error('collection_day')
                                    <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group {{ $errors->has('field_officer_id') ? 'has-error' :'' }}">
                                    <label for="field_officer_id">Field Officer</label>
                                    <select name="field_officer_id" id="field_officer_id" class="form-control select2">
                                        <option value="">Select Field Officer</option>
                                        @foreach($fieldOfficers as $officer)
                                            <option value="{{ $officer->id }}" {{ old('field_officer_id', $group->field_officer_id) == $officer->id ? 'selected' : '' }}>{{ $officer->id_no }} - {{ $officer->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('field_officer_id')
                                    <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <h6 class="mt-4 mb-4 border-bottom pb-2 font-weight-bold text-info">
                            <i class="fas fa-map-marker-alt mr-2"></i>Location Information
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group {{ $errors->has('division') ? 'has-error' : '' }}">
                                    <label for="division">Division</label>
                                    <select name="division" id="division" class="form-control select2">
                                        <option value="">Select Division</option>
                                        @foreach($divisions as $division)
                                            <option {{ old('division', $group->division_id) == $division->id ? 'selected' : '' }}
                                                value="{{ $division->id }}">{{ $division->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('division')
                                        <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group {{ $errors->has('district') ? 'has-error' : '' }}">
                                    <label for="district">District</label>
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
                                <div class="form-group {{ $errors->has('upazila') ? 'has-error' : '' }}">
                                    <label for="upazila">Upazila</label>
                                    <select name="upazila" id="upazila" class="form-control select2">
                                        <option value="">Select Upazila</option>
                                    </select>
                                    @error('upazila')
                                        <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group {{ $errors->has('union') ? 'has-error' : '' }}">
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
                                <div class="form-group {{ $errors->has('village_id') ? 'has-error' : '' }}">
                                    <label for="village_id">Village/Road</label>
                                    <select name="village_id" id="village_id" class="form-control select2"
                                        data-selected="{{ old('village_id', $group->village_id) }}">
                                        <option value="">Select Village</option>
                                    </select>
                                    @error('village_id')
                                        <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status">Status</label>
                                    <select name="status" id="status" class="form-control">
                                        <option value="1" {{ old('status', $group->status) == '1' ? 'selected' : '' }}>Active
                                        </option>
                                        <option value="0" {{ old('status', $group->status) == '0' ? 'selected' : '' }}>
                                            Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="button" class="btn btn-info bg-gradient-info btn-sm"
                            id="btnPreview">Update</button>
                        <button type="submit" class="btn btn-info bg-gradient-info btn-sm d-none"
                            id="btnRealSubmit">Update</button>
                        <a href="{{ route('shomriddhi-advance.groups.index') }}"
                            class="btn btn-danger bg-gradient-danger btn-sm float-right">Cancel</a>
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
                    <h5 class="modal-title text-white"><i class="fa fa-layer-group"></i> Group Profile Preview</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0">
                    <div class="p-4 text-center bg-light border-bottom">
                        <div class="d-inline-flex justify-content-center align-items-center rounded-circle bg-white shadow-sm mb-3"
                            style="width: 80px; height: 80px;">
                            <i class="fa fa-users fa-3x text-primary"></i>
                        </div>
                        <h4 class="font-weight-bold mb-1" id="p_name_header"></h4>
                        <span id="p_status" class="badge px-3 py-1" style="font-size: 13px; font-weight: 500;"></span>
                    </div>

                    <div class="p-4">
                        <h6 class="text-uppercase text-primary font-weight-bold mb-3"
                            style="font-size: 13px; letter-spacing: 1px;"><i class="fa fa-info-circle mr-1"></i> Basic
                            Information</h6>
                        <table class="table table-sm table-borderless mb-4" style="font-size: 14px;">
                            <tr>
                                <td width="35%" class="text-muted">Name (English)</td>
                                <td width="65%" class="font-weight-bold text-dark" id="p_name_en"></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Name (Bangla)</td>
                                <td class="font-weight-bold text-dark" id="p_name_bn"></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Field Officer</td>
                                <td class="font-weight-bold text-dark" id="p_field_officer"></td>
                            </tr>
                        </table>

                        <h6 class="text-uppercase text-primary font-weight-bold mb-3"
                            style="font-size: 13px; letter-spacing: 1px;"><i class="fa fa-map-marker-alt mr-1"></i> Location
                            Information</h6>
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
                    <button type="button" class="btn btn-outline-danger btn-sm" data-dismiss="modal"><i
                            class="fa fa-edit"></i> Edit Data</button>
                    <button type="button" class="btn btn-success bg-gradient-success btn-sm font-weight-bold px-4"
                        id="btnConfirmSave"><i class="fa fa-check-circle"></i> Confirm & Save</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        $(function () {
            var initialDistrict = "{{ old('district', $group->district_id) }}";
            var initialUpazila = "{{ old('upazila', $group->upazila_id) }}";
            var initialUnion = "{{ old('union', $group->union_id ? 'union_' . $group->union_id : ($group->pourashava_id ? 'pourashava_' . $group->pourashava_id : '')) }}";

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
                    }).done(function (data) {
                        $.each(data, function (index, item) {
                            let selected = (item.id == initialDistrict) ? 'selected' : '';
                            $('#district').append('<option value="' + item.id + '" ' + selected + '>' + item.name + '</option>');
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
                    }).done(function (data) {
                        $.each(data, function (index, item) {
                            let selected = (item.id == initialUpazila) ? 'selected' : '';
                            $('#upazila').append('<option value="' + item.id + '" ' + selected + '>' + item.name + '</option>');
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
                    }).done(function (data) {
                        $.each(data, function (index, item) {
                            let selected = ('pourashava_' + item.id == initialUnion) ? 'selected' : '';
                            $('#union').append('<option value="pourashava_' + item.id + '" ' + selected + '>' + item.name + ' (P)</option>');
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
                    }).done(function (data) {
                        $.each(data, function (index, item) {
                            let selected = ('pourashava_' + item.id == initialUnion) ? 'selected' : '';
                            $('#union').append('<option value="pourashava_' + item.id + '" ' + selected + '>' + item.name + ' (P)</option>');
                        });

                        // Then load Unions
                        if (upazilaId != '') {
                            $.ajax({
                                method: "GET",
                                url: "{{ route('get_unions') }}",
                                data: { upazilaId: upazilaId }
                            }).done(function (data) {
                                $.each(data, function (index, item) {
                                    let selected = ('union_' + item.id == initialUnion) ? 'selected' : '';
                                    $('#union').append('<option value="union_' + item.id + '" ' + selected + '>' + item.name + ' (U)</option>');
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

            if ($('#division').val() != '') {
                $('#division').trigger('change');
            }

            $('#btnPreview').click(function () {
                let name = $('#name_en').val();

                if (!name) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Incomplete Data',
                        text: 'Please fill all the required fields (*).',
                    });
                    return;
                }

                $('#p_name_header').text(name);

                $('#p_name_en').text(name);
                $('#p_name_bn').text($('#name_bn').val() || '—');
                $('#p_field_officer').text($('#field_officer_id').val() ? $('#field_officer_id option:selected').text() : '—');

                $('#p_division').text($('#division').val() ? $('#division option:selected').text() : '—');
                $('#p_district').text($('#district').val() ? $('#district option:selected').text() : '—');
                $('#p_upazila').text($('#upazila').val() ? $('#upazila option:selected').text() : '—');
                $('#p_union').text($('#union').val() ? $('#union option:selected').text() : '—');
                $('#p_village').text($('#village_id').val() ? $('#village_id option:selected').text() : '—');

                let status = $('#status').val();
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