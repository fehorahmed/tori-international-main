@extends('layouts.app')
@section('title','SR Edit')
@section('style')
    <style>
        .section-title {
            background: #f4f6f9;
            padding: 10px;
            margin-bottom: 20px;
            font-weight: bold;
            border-left: 5px solid #17a2b8;
        }
    </style>
@endsection
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title">Edit SR: {{ $sr->name }}</h3>
                </div>
                <form enctype="multipart/form-data" action="{{ route('sr.update', ['sr' => $sr->id]) }}" method="post">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="section-title">Basic Information</div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group {{ $errors->has('name') ? 'has-error' :'' }}">
                                    <label for="name">SR Name <span class="text-danger">*</span></label>
                                    <input type="text" value="{{ old('name', $sr->name) }}" name="name" class="form-control" id="name" placeholder="Enter Name">
                                    @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group {{ $errors->has('mobile_no') ? 'has-error' :'' }}">
                                    <label for="mobile_no">Mobile No. <span class="text-danger">*</span></label>
                                    <input type="tel" value="{{ old('mobile_no', $sr->mobile_no) }}" name="mobile_no" class="form-control" id="mobile_no" placeholder="Enter Mobile No.">
                                    @error('mobile_no') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group {{ $errors->has('email') ? 'has-error' :'' }}">
                                    <label for="email">Email</label>
                                    <input type="email" value="{{ old('email', $sr->email) }}" name="email" class="form-control" id="email" placeholder="Enter Email">
                                    @error('email') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group {{ $errors->has('department') ? 'has-error' :'' }}">
                                    <label for="department">Department</label>
                                    <select name="department" class="form-control select2" id="department" disabled>
                                        <option value="">Select Department</option>
                                        @foreach($departments as $department)
                                            <option {{ old('department', $employee->department_id ?? '') == $department->id ? 'selected' : '' }} value="{{ $department->id }}">{{ $department->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('department') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group {{ $errors->has('designation') ? 'has-error' :'' }}">
                                    <label for="designation">Designation</label>
                                    <select name="designation" class="form-control select2" id="designation" disabled>
                                        <option value="">Select Designation</option>
                                        @if($employee && $employee->designation)
                                            <option value="{{ $employee->designation_id }}" selected>{{ $employee->designation->name }}</option>
                                        @endif
                                    </select>
                                    @error('designation') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group {{ $errors->has('shift') ? 'has-error' :'' }}">
                                    <label for="shift">Shift <span class="text-danger">*</span></label>
                                    <select name="shift" class="form-control select2" id="shift">
                                        <option value="">Select Shift</option>
                                        @foreach($shifts as $shift)
                                            <option {{ old('shift', $employee->shift_id ?? '') == $shift->id ? 'selected' : '' }} value="{{ $shift->id }}">
                                                {{ $shift->name }} ({{ \Carbon\Carbon::parse($shift->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($shift->end_time)->format('h:i A') }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('shift') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group {{ $errors->has('employee_status') ? 'has-error' :'' }}">
                                    <label for="employee_status">Employee Status <span class="text-danger">*</span></label>
                                    <select name="employee_status" class="form-control select2" id="employee_status">
                                        <option value="">Select Status</option>
                                        @foreach($employeeStatuses as $employeeStatus)
                                            <option {{ old('employee_status', $employee->employee_status_id ?? '') == $employeeStatus->id ? 'selected' : '' }} value="{{ $employeeStatus->id }}">
                                                {{ $employeeStatus->name }} {{ $employeeStatus->is_salary_apply == 1 ? '(Salary Applied)' : '(No Salary)' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('employee_status') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="status">Account Status <span class="text-danger">*</span></label>
                                    <select name="status" id="status" class="form-control select2">
                                        <option value="1" {{ old('status', $sr->status) == '1' ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ old('status', $sr->status) == '0' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                    @error('status') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Current Address -->
                            <div class="col-md-6">
                                <div class="section-title">Current Address</div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Division</label>
                                            <select name="current_division" class="form-control select2 division" id="current_division">
                                                <option value="">Select Division</option>
                                                @foreach($divisions as $division)
                                                    <option {{ old('current_division', $employee->current_division_id ?? $sr->division_id) == $division->id ? 'selected' : '' }} value="{{ $division->id }}">{{ $division->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>District</label>
                                            <select name="current_district" class="form-control select2 district" id="current_district">
                                                <option value="">Select District</option>
                                                @if($employee && $employee->currentDistrict)
                                                    <option value="{{ $employee->current_district_id }}" selected>{{ $employee->currentDistrict->name }}</option>
                                                @elseif($sr->district)
                                                    <option value="{{ $sr->district_id }}" selected>{{ $sr->district->name }}</option>
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Upazila</label>
                                            <select name="current_upazila" class="form-control select2 upazila" id="current_upazila">
                                                <option value="">Select Upazila</option>
                                                @if($employee && $employee->currentUpazila)
                                                    <option value="{{ $employee->current_upazila_id }}" selected>{{ $employee->currentUpazila->name }}</option>
                                                @elseif($sr->upazila)
                                                    <option value="{{ $sr->upazila_id }}" selected>{{ $sr->upazila->name }}</option>
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Union/Pourashava</label>
                                            <select name="current_union" class="form-control select2 union" id="current_union">
                                                <option value="">Select Union/Pourashava</option>
                                                @if($employee && $employee->currentUnion)
                                                    <option value="union_{{ $employee->current_union_id }}" selected>{{ $employee->currentUnion->name }} (Union)</option>
                                                @elseif($employee && $employee->currentPourashava)
                                                    <option value="pourashava_{{ $employee->current_pourashava_id }}" selected>{{ $employee->currentPourashava->name }} (Pourashava)</option>
                                                @elseif($sr->union)
                                                    <option value="union_{{ $sr->union_id }}" selected>{{ $sr->union->name }} (Union)</option>
                                                @elseif($sr->pourashava)
                                                    <option value="pourashava_{{ $sr->pourashava_id }}" selected>{{ $sr->pourashava->name }} (Pourashava)</option>
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Village</label>
                                            <select name="current_village_id" id="current_village_id" class="form-control select2 current_village" data-selected="{{ old('current_village_id', $employee->current_village_id ?? $sr->village_id) }}">
                                                <option value="">Select Village</option>
                                                @if($employee && $employee->current_village_id)
                                                    <option value="{{ $employee->current_village_id }}" selected>{{ \App\Models\Village::find($employee->current_village_id)->name ?? '' }}</option>
                                                @elseif($sr->village_id)
                                                    <option value="{{ $sr->village_id }}" selected>{{ \App\Models\Village::find($sr->village_id)->name ?? '' }}</option>
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Permanent Address -->
                            <div class="col-md-6">
                                <div class="section-title">
                                    Permanent Address
                                    <div class="icheck-primary d-inline float-right" style="font-size: 14px; font-weight: normal;">
                                        <input type="checkbox" id="same_as_current" name="same_as_current" {{ old('same_as_current') ? 'checked' : '' }}>
                                        <label for="same_as_current">Same as current</label>
                                    </div>
                                </div>
                                <div id="permanent_address_section">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Division</label>
                                                <select name="permanent_division" class="form-control select2 division" id="permanent_division">
                                                    <option value="">Select Division</option>
                                                    @foreach($divisions as $division)
                                                        <option {{ old('permanent_division', $employee->permanent_division_id ?? '') == $division->id ? 'selected' : '' }} value="{{ $division->id }}">{{ $division->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>District</label>
                                                <select name="permanent_district" class="form-control select2 district" id="permanent_district">
                                                    <option value="">Select District</option>
                                                    @if($employee && $employee->permanentDistrict)
                                                        <option value="{{ $employee->permanent_district_id }}" selected>{{ $employee->permanentDistrict->name }}</option>
                                                    @endif
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Upazila</label>
                                                <select name="permanent_upazila" class="form-control select2 upazila" id="permanent_upazila">
                                                    <option value="">Select Upazila</option>
                                                    @if($employee && $employee->permanentUpazila)
                                                        <option value="{{ $employee->permanent_upazila_id }}" selected>{{ $employee->permanentUpazila->name }}</option>
                                                    @endif
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Union/Pourashava</label>
                                                <select name="permanent_union" class="form-control select2 union" id="permanent_union">
                                                    <option value="">Select Union/Pourashava</option>
                                                    @if($employee && $employee->permanentUnion)
                                                        <option value="union_{{ $employee->permanent_union_id }}" selected>{{ $employee->permanentUnion->name }} (Union)</option>
                                                    @elseif($employee && $employee->permanentPourashava)
                                                        <option value="pourashava_{{ $employee->permanent_pourashava_id }}" selected>{{ $employee->permanentPourashava->name }} (Pourashava)</option>
                                                    @endif
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>Village</label>
                                                <select name="permanent_village_id" id="permanent_village_id" class="form-control select2 permanent_village" data-selected="{{ old('permanent_village_id', $employee->permanent_village_id ?? '') }}">
                                                    <option value="">Select Village</option>
                                                    @if($employee && $employee->permanent_village_id)
                                                        <option value="{{ $employee->permanent_village_id }}" selected>{{ \App\Models\Village::find($employee->permanent_village_id)->name ?? '' }}</option>
                                                    @endif
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="section-title">Salary & Bank Information</div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Basic Salary <span class="text-danger">*</span></label>
                                    <input type="number" step="any" name="basic_salary" value="{{ old('basic_salary', $employee->basic_salary ?? '') }}" class="form-control" placeholder="Enter Basic Salary">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Gross Salary <span class="text-danger">*</span></label>
                                    <input type="number" step="any" name="gross_salary" value="{{ old('gross_salary', $employee->gross_salary ?? '') }}" class="form-control" placeholder="Enter Gross Salary">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Bank Name</label>
                                    <input type="text" name="bank_name" value="{{ old('bank_name', $employee->bank_name ?? $sr->bank_name) }}" class="form-control" placeholder="Enter Bank Name">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Account No.</label>
                                    <input type="text" name="bank_account_no" value="{{ old('bank_account_no', $employee->bank_account_no ?? $sr->bank_account_no) }}" class="form-control" placeholder="Enter Account No.">
                                </div>
                            </div>
                        </div>

                        <div class="section-title">Documents</div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Photo</label>
                                    <input type="file" name="image" class="form-control">
                                    @if($sr->image)
                                        <small class="text-muted">Current photo: <a href="{{ asset($sr->image) }}" target="_blank">View</a></small>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Signature Photo</label>
                                    <input type="file" name="signature_photo" class="form-control">
                                    @if($employee->signature_photo)
                                        <small class="text-muted">Current signature: <a href="{{ asset($employee->signature_photo) }}" target="_blank">View</a></small>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>NID Copy</label>
                                    <input type="file" name="nid" class="form-control">
                                    @if($employee && $employee->nid)
                                        <small class="text-muted">Current NID: <a href="{{ asset($employee->nid) }}" target="_blank">View</a></small>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>CV (PDF)</label>
                                    <input type="file" name="cv" class="form-control">
                                    @if($employee && $employee->cv)
                                        <small class="text-muted">Current CV: <a href="{{ asset($employee->cv) }}" target="_blank">View</a></small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-center">
                        <button type="button" class="btn btn-info px-5" id="btnPreview">Preview & Save</button>
                        <button type="submit" class="btn btn-info px-5 d-none" id="btnRealSubmit">Update SR</button>
                        <a href="{{ route('sr.index') }}" class="btn btn-default px-5">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    @include('layouts.partial.__employee_preview_modal')
@endsection

@section('script')
    @parent
    <script>
        $(function () {
            // Same as current address logic
            $('#same_as_current').on('change', function() {
                if($(this).is(':checked')) {
                    $('#permanent_address_section').hide();
                } else {
                    $('#permanent_address_section').show();
                }
            }).trigger('change');

            // Designation Loading Logic
            var selectedDesignation = '{{ old('designation', $employee->designation_id ?? '') }}';
            $("#department").change(function () {
                let departmentId = $(this).val();
                $('#designation').html('<option value="">Select Designation</option>');
                if (departmentId != '') {
                    $.ajax({
                        method: "GET",
                        url: "{{ route('get_designation') }}",
                        data: {id: departmentId}
                    }).done(function (data) {
                        $.each(data, function (index, item) {
                            let selected = (selectedDesignation == item.id) ? 'selected' : '';
                            $('#designation').append('<option value="' + item.id + '" ' + selected + '>' + item.name + '</option>');
                        });
                    });
                }
            }).trigger('change');

            function loadLocation(type, target, parentId, selectedId) {
                let url = (target == 'district') ? "{{ route('get_districts') }}" : "{{ route('get_upazilas') }}";
                let data = (target == 'district') ? {divisionId: parentId} : {districtId: parentId};
                let targetEl = $('#' + type + '_' + target);

                $.ajax({
                    method: "GET",
                    url: url,
                    data: data
                }).done(function (data) {
                    $.each(data, function (index, item) {
                        let selected = (selectedId == item.id) ? 'selected' : '';
                        targetEl.append('<option value="' + item.id + '" ' + selected + '>' + item.name + '</option>');
                    });
                });
            }

            function loadUnions(type, upazilaId, selectedId) {
                let targetEl = $('#' + type + '_union');
                $.ajax({
                    method: "GET",
                    url: "{{ route('get_unions') }}",
                    data: {upazilaId: upazilaId}
                }).done(function (data) {
                    $.each(data, function (index, item) {
                        let val = 'union_' + item.id;
                        let selected = (selectedId == val || selectedId == item.id) ? 'selected' : '';
                        targetEl.append('<option value="' + val + '" ' + selected + '>' + item.name + ' (Union)</option>');
                    });
                });
            }

            function loadPourashavas(type, districtId, selectedId) {
                let targetEl = $('#' + type + '_union');
                $.ajax({
                    method: "GET",
                    url: "{{ route('get_pourashavas') }}",
                    data: {districtId: districtId}
                }).done(function (data) {
                    $.each(data, function (index, item) {
                        let val = 'pourashava_' + item.id;
                        let selected = (selectedId == val || selectedId == item.id) ? 'selected' : '';
                        targetEl.append('<option value="' + val + '" ' + selected + '>' + item.name + ' (Pourashava)</option>');
                    });
                });
            }

            $('.division').change(function () {
                let divisionId = $(this).val();
                let type = $(this).attr('id').split('_')[0];
                $('#' + type + '_district').html('<option value="">Select District</option>');
                $('#' + type + '_upazila').html('<option value="">Select Upazila</option>');
                $('#' + type + '_union').html('<option value="">Select Union/Pourashava</option>');
                if (divisionId != '') loadLocation(type, 'district', divisionId, null);
            });

            $('.district').change(function () {
                let districtId = $(this).val();
                let type = $(this).attr('id').split('_')[0];
                $('#' + type + '_upazila').html('<option value="">Select Upazila</option>');
                $('#' + type + '_union').html('<option value="">Select Union/Pourashava</option>');
                if (districtId != '') {
                    loadLocation(type, 'upazila', districtId, null);
                    loadPourashavas(type, districtId, null);
                }
            });

            $('.upazila').change(function () {
                let upazilaId = $(this).val();
                let type = $(this).attr('id').split('_')[0];
                $('#' + type + '_union option').filter(function() {
                    return $(this).text().includes('(Union)');
                }).remove();
                if (upazilaId != '') loadUnions(type, upazilaId, null);
            });

            function loadVillages(type, locationId, locationType) {
                let targetEl = $('#' + type + '_village_id');
                let selectedId = targetEl.data('selected');
                targetEl.html('<option value="">Select Village</option>');
                $.ajax({
                    method: "GET",
                    url: "{{ route('get_villages') }}",
                    data: locationType === 'union' ? { unionId: locationId } : { pourashavaId: locationId }
                }).done(function (data) {
                    $.each(data, function (index, item) {
                        let selected = (selectedId == item.id) ? 'selected' : '';
                        targetEl.append('<option value="' + item.id + '" ' + selected + '>' + item.name + '</option>');
                    });
                });
            }

            $('.union').change(function () {
                let val = $(this).val();
                let type = $(this).attr('id').split('_')[0];
                if (val) {
                    if (val.includes('union_')) {
                        loadVillages(type, val.replace('union_', ''), 'union');
                    } else if (val.includes('pourashava_')) {
                        loadVillages(type, val.replace('pourashava_', ''), 'pourashava');
                    }
                } else {
                    $('#' + type + '_village_id').html('<option value="">Select Village</option>');
                }
            });
        });
    </script>
@endsection
