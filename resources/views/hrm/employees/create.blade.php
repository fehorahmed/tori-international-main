@extends('layouts.app')
@section('title','Employee Create')
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
                    <h3 class="card-title">New Employee Registration</h3>
                </div>
                <form enctype="multipart/form-data" action="{{ route('employees.store') }}" method="post">
                    @csrf
                    <div class="card-body">
                        <div class="section-title">Basic Information</div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group {{ $errors->has('id_no') ? 'has-error' :'' }}">
                                    <label for="id_no">ID No. <span class="text-danger">*</span></label>
                                    <input type="text" value="{{ old('id_no', $nextId) }}" name="id_no" class="form-control" id="id_no" readonly>
                                    @error('id_no') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group {{ $errors->has('name') ? 'has-error' :'' }}">
                                    <label for="name">Employee Name <span class="text-danger">*</span></label>
                                    <input type="text" value="{{ old('name') }}" name="name" class="form-control" id="name" placeholder="Enter Name">
                                    @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group {{ $errors->has('mobile_no') ? 'has-error' :'' }}">
                                    <label for="mobile_no">Mobile No. <span class="text-danger">*</span></label>
                                    <input type="tel" value="{{ old('mobile_no') }}" name="mobile_no" class="form-control" id="mobile_no" placeholder="Enter Mobile No.">
                                    @error('mobile_no') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group {{ $errors->has('gender') ? 'has-error' :'' }}">
                                    <label for="gender">Gender <span class="text-danger">*</span></label>
                                    <select name="gender" class="form-control select2" id="gender">
                                        <option value="">Select Gender</option>
                                        <option value="1" {{ old('gender') == '1' ? 'selected' : '' }}>Male</option>
                                        <option value="2" {{ old('gender') == '2' ? 'selected' : '' }}>Female</option>
                                        <option value="3" {{ old('gender') == '3' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    @error('gender') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group {{ $errors->has('blood_group') ? 'has-error' :'' }}">
                                    <label for="blood_group">Blood Group</label>
                                    <select name="blood_group" class="form-control select2" id="blood_group">
                                        <option value="">Select</option>
                                        <option value="A+" {{ old('blood_group') == 'A+' ? 'selected' : '' }}>A+</option>
                                        <option value="A-" {{ old('blood_group') == 'A-' ? 'selected' : '' }}>A-</option>
                                        <option value="B+" {{ old('blood_group') == 'B+' ? 'selected' : '' }}>B+</option>
                                        <option value="B-" {{ old('blood_group') == 'B-' ? 'selected' : '' }}>B-</option>
                                        <option value="AB+" {{ old('blood_group') == 'AB+' ? 'selected' : '' }}>AB+</option>
                                        <option value="AB-" {{ old('blood_group') == 'AB-' ? 'selected' : '' }}>AB-</option>
                                        <option value="O+" {{ old('blood_group') == 'O+' ? 'selected' : '' }}>O+</option>
                                        <option value="O-" {{ old('blood_group') == 'O-' ? 'selected' : '' }}>O-</option>
                                    </select>
                                    @error('blood_group') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group {{ $errors->has('date_of_birth') ? 'has-error' :'' }}">
                                    <label for="date_of_birth">Date of Birth <span class="text-danger">*</span></label>
                                    <input type="text" value="{{ old('date_of_birth') }}" name="date_of_birth" class="form-control date-picker" id="date_of_birth" placeholder="dd-mm-yyyy">
                                    @error('date_of_birth') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group {{ $errors->has('marital_status') ? 'has-error' :'' }}">
                                    <label for="marital_status">Marital Status <span class="text-danger">*</span></label>
                                    <select name="marital_status" class="form-control select2" id="marital_status">
                                        <option value="">Select Status</option>
                                        <option value="1" {{ old('marital_status') == '1' ? 'selected' : '' }}>Married</option>
                                        <option value="2" {{ old('marital_status') == '2' ? 'selected' : '' }}>Single</option>
                                        <option value="3" {{ old('marital_status') == '3' ? 'selected' : '' }}>Widowed</option>
                                        <option value="4" {{ old('marital_status') == '4' ? 'selected' : '' }}>Divorced</option>
                                    </select>
                                    @error('marital_status') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group {{ $errors->has('email') ? 'has-error' :'' }}">
                                    <label for="email">Email</label>
                                    <input type="email" value="{{ old('email') }}" name="email" class="form-control" id="email" placeholder="Enter Email">
                                    @error('email') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group {{ $errors->has('department') ? 'has-error' :'' }}">
                                    <label for="department">Department <span class="text-danger">*</span></label>
                                    <select name="department" class="form-control select2" id="department">
                                        <option value="">Select Department</option>
                                        @foreach($departments as $department)
                                            <option {{ old('department') == $department->id ? 'selected' : '' }} value="{{ $department->id }}">{{ $department->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('department') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group {{ $errors->has('designation') ? 'has-error' :'' }}">
                                    <label for="designation">Designation <span class="text-danger">*</span></label>
                                    <select name="designation" class="form-control select2" id="designation">
                                        <option value="">Select Designation</option>
                                    </select>
                                    @error('designation') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group {{ $errors->has('shift') ? 'has-error' :'' }}">
                                    <label for="shift">Shift <span class="text-danger">*</span></label>
                                    <select name="shift" class="form-control select2" id="shift">
                                        <option value="">Select Shift</option>
                                        @foreach($shifts as $shift)
                                            <option {{ old('shift') == $shift->id ? 'selected' : '' }} value="{{ $shift->id }}">
                                                {{ $shift->name }} ({{ \Carbon\Carbon::parse($shift->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($shift->end_time)->format('h:i A') }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('shift') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group {{ $errors->has('employee_status') ? 'has-error' :'' }}">
                                    <label for="employee_status">Employee Status <span class="text-danger">*</span></label>
                                    <select name="employee_status" class="form-control select2" id="employee_status">
                                        <option value="">Select Status</option>
                                        @foreach($employeeStatuses as $employeeStatus)
                                            <option {{ old('employee_status') == $employeeStatus->id ? 'selected' : '' }} value="{{ $employeeStatus->id }}">
                                                {{ $employeeStatus->name }} {{ $employeeStatus->is_salary_apply == 1 ? '(Salary Applied)' : '(No Salary)' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('employee_status') <span class="text-danger">{{ $message }}</span> @enderror
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
                                                    <option value="{{ $division->id }}">{{ $division->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>District</label>
                                            <select name="current_district" class="form-control select2 district" id="current_district">
                                                <option value="">Select District</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Upazila</label>
                                            <select name="current_upazila" class="form-control select2 upazila" id="current_upazila">
                                                <option value="">Select Upazila</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Union/Pourashava</label>
                                            <select name="current_union" class="form-control select2 union" id="current_union">
                                                <option value="">Select Union/Pourashava</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Village</label>
                                            <select name="current_village_id" id="current_village_id" class="form-control select2 village" data-selected="{{ old('current_village_id') }}">
                                                <option value="">Select Village</option>
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
                                        <input type="checkbox" id="same_as_current" name="same_as_current">
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
                                                        <option {{ old('permanent_division') == $division->id ? 'selected' : '' }} value="{{ $division->id }}">{{ $division->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>District</label>
                                                <select name="permanent_district" class="form-control select2 district" id="permanent_district">
                                                    <option value="">Select District</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Upazila</label>
                                                <select name="permanent_upazila" class="form-control select2 upazila" id="permanent_upazila">
                                                    <option value="">Select Upazila</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Union/Pourashava</label>
                                                <select name="permanent_union" class="form-control select2 union" id="permanent_union">
                                                    <option value="">Select Union/Pourashava</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>Village</label>
                                                <select name="permanent_village_id" id="permanent_village_id" class="form-control select2 village" data-selected="{{ old('permanent_village_id') }}">
                                                    <option value="">Select Village</option>
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
                                <div class="form-group {{ $errors->has('salary_structure_id') ? 'has-error' :'' }}">
                                    <label>Salary Structure <span class="text-danger">*</span></label>
                                    <select name="salary_structure_id" class="form-control select2" required>
                                        <option value="">Select Structure</option>
                                        @foreach($salaryStructures as $structure)
                                            <option {{ old('salary_structure_id') == $structure->id ? 'selected' : '' }} value="{{ $structure->id }}">{{ $structure->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('salary_structure_id') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Gross Salary <span class="text-danger">*</span></label>
                                    <input type="number" step="any" name="gross_salary" class="form-control" value="{{ old('gross_salary') }}" placeholder="Enter Gross Salary" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Bank Name</label>
                                    <input type="text" name="bank_name" class="form-control" placeholder="Enter Bank Name">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Account No.</label>
                                    <input type="text" name="bank_account_no" class="form-control" placeholder="Enter Account No.">
                                </div>
                            </div>
                        </div>

                        <div class="section-title">Documents</div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Photo</label>
                                    <input type="file" name="photo" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Signature Photo</label>
                                    <input type="file" name="signature_photo" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>NID Copy</label>
                                    <input type="file" name="nid" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>CV (PDF)</label>
                                    <input type="file" name="cv" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-center">
                        <button type="button" class="btn btn-info px-5" id="btnPreview">Preview & Save</button>
                        <button type="submit" class="btn btn-info px-5 d-none" id="btnRealSubmit">Save Employee</button>
                        <a href="{{ route('employees.index') }}" class="btn btn-default px-5">Cancel</a>
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
            });

            // Designation Loading Logic
            var selectedDesignation = '{{ old('designation') }}';
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
                targetEl.html('<option value="">Select Union/Pourashava</option>');
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

            // Initial Load for Validation Fail
            @if(old('current_division')) loadLocation('current', 'district', {{ old('current_division') }}, {{ old('current_district', 'null') }}); @endif
            @if(old('current_district')) loadLocation('current', 'upazila', {{ old('current_district') }}, {{ old('current_upazila', 'null') }}); loadPourashavas('current', {{ old('current_district') }}, '{{ old('current_union') }}'); @endif
            @if(old('current_upazila')) loadUnions('current', {{ old('current_upazila') }}, '{{ old('current_union') }}'); @endif

            @if(old('permanent_division')) loadLocation('permanent', 'district', {{ old('permanent_division') }}, {{ old('permanent_district', 'null') }}); @endif
            @if(old('permanent_district')) loadLocation('permanent', 'upazila', {{ old('permanent_district') }}, {{ old('permanent_upazila', 'null') }}); loadPourashavas('permanent', {{ old('permanent_district') }}, '{{ old('permanent_union') }}'); @endif
            @if(old('permanent_upazila')) loadUnions('permanent', {{ old('permanent_upazila') }}, '{{ old('permanent_union') }}'); @endif

            $('.union').change(function() {
                let type = $(this).attr('id').split('_')[0];
                let val = $(this).val();
                let targetVillage = $('#' + type + '_village_id');
                let selectedVal = targetVillage.data('selected');
                
                targetVillage.html('<option value="">Loading...</option>');

                if (val && val.startsWith('union_')) {
                    let unionId = val.split('_')[1];
                    $.get("{{ route('get_villages') }}", { union_id: unionId }, function(data) {
                        let options = '<option value="">Select Village</option>';
                        data.forEach(function(item) {
                            options += `<option value="${item.id}" ${selectedVal == item.id ? 'selected' : ''}>${item.name}</option>`;
                        });
                        targetVillage.html(options).trigger('change');
                    });
                } else if (val && val.startsWith('pourashava_')) {
                    let pourashavaId = val.split('_')[1];
                    $.get("{{ route('get_villages') }}", { pourashava_id: pourashavaId }, function(data) {
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

            @if(old('current_union')) $('#current_union').trigger('change'); @endif
            @if(old('permanent_union')) $('#permanent_union').trigger('change'); @endif

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
        });
    </script>
@endsection


