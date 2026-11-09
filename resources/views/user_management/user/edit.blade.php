@extends('layouts.app')
@section('title','User Edit')
@section('style')
    <style>
        ul{
            list-style: none;
        }
        label:not(.form-check-label):not(.custom-file-label) {
            font-weight: normal;
        }
        label.child-checkbox-label {
            font-size: 18px;
        }
        label.grandchild-checkbox-label {
            font-size: 16px;
        }
    </style>
@endsection
@section('content')
    <div class="row">
        <!-- left column -->
        <div class="col-md-12">
            <!-- jquery validation -->
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">User Information</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                <form enctype="multipart/form-data" action="{{ route('user.update',['user'=>$user->id]) }}"
                      class="form-horizontal" method="post">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="form-group row {{ $errors->has('name') ? 'has-error' :'' }}">
                            <label for="name" class="col-sm-2 col-form-label">Name <span
                                    class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <input type="text" value="{{ old('name',$user->name) }}" name="name"
                                       class="form-control" id="name" placeholder="Enter Name">
                                @error('name')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row {{ $errors->has('username') ? 'has-error' :'' }}">
                            <label for="username" class="col-sm-2 col-form-label">Username <span
                                    class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <input type="text" value="{{ old('username',$user->username) }}" name="username"
                                       class="form-control" id="username" placeholder="Enter Username">
                                @error('username')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row {{ $errors->has('email') ? 'has-error' :'' }}">
                            <label for="email" class="col-sm-2 col-form-label">Email</label>
                            <div class="col-sm-10">
                                <input type="email" value="{{ old('email',$user->email) }}" name="email"
                                       class="form-control" id="email" placeholder="Enter Email">
                                @error('email')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row {{ $errors->has('mobile_no') ? 'has-error' :'' }}">
                            <label for="mobile_no" class="col-sm-2 col-form-label">Mobile No.</label>
                            <div class="col-sm-10">
                                <input type="text" value="{{ old('mobile_no',$user->mobile_no) }}" name="mobile_no"
                                       class="form-control" id="mobile_no" placeholder="Enter Mobile No.">
                                @error('mobile_no')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row {{ $errors->has('password') ? 'has-error' :'' }}">
                            <label for="password" class="col-sm-2 col-form-label">Password</label>
                            <div class="col-sm-10">
                                <input type="password" autocomplete="new-password" name="password" class="form-control"
                                       id="password" placeholder="Enter Password">
                                @error('password')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row {{ $errors->has('password_confirmation') ? 'has-error' :'' }}">
                            <label for="password_confirmation" class="col-sm-2 col-form-label">Password
                                Confirmation</label>
                            <div class="col-sm-10">
                                <input type="password" name="password_confirmation" class="form-control"
                                       id="password_confirmation" placeholder="Enter Password Confirmation">
                                @error('password_confirmation')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row {{ $errors->has('role') ? 'has-error' :'' }}">
                            <label for="role" class="col-sm-2 col-form-label">Primary Role <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                @if($isSystemUser)
                                    <select class="form-control select2" disabled>
                                        <option value="{{ $user->role }}" selected>{{ str_replace('_', ' ', $user->role) }}</option>
                                    </select>
                                    <input type="hidden" name="role" id="role" value="{{ $user->role }}">
                                    <span class="help-block text-warning font-weight-bold">
                                        <i class="fa fa-exclamation-triangle"></i> This user holds a system-managed role ({{ str_replace('_', ' ', $user->role) }}). To manage their role, please use their respective module.
                                    </span>
                                @else
                                    <select name="role" class="form-control select2" id="role">
                                        <option value="">Select Primary Role</option>
                                        @foreach($roles as $roleKey => $roleValue)
                                            <option value="{{ $roleKey }}" {{ old('role', $user->role) == $roleKey ? 'selected' : '' }}>
                                                {{ str_replace('_', ' ', $roleValue) }}
                                            </option>
                                        @endforeach
                                    </select>
                                @endif
                                @error('role')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row {{ $errors->has('roles') ? 'has-error' :'' }}" id="additional_roles_container">
                            <label for="roles" class="col-sm-2 col-form-label">Additional Roles</label>
                            <div class="col-sm-10">
                                    <select name="roles[]" class="form-control select2" id="roles" multiple="multiple" data-placeholder="Select Additional Roles">
                                        @foreach($roles as $roleKey => $roleValue)
                                            <option value="{{ $roleKey }}" {{ in_array($roleKey, old('roles', $secondaryRoles)) ? 'selected' : '' }}>
                                                {{ str_replace('_', ' ', $roleValue) }}
                                            </option>
                                        @endforeach
                                    </select>
                                @error('roles')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row {{ $errors->has('employee_id') ? 'has-error' :'' }}" id="employee_container" style="display: none;">
                            <label for="employee_id" class="col-sm-2 col-form-label">Employee</label>
                            <div class="col-sm-10">
                                <select name="employee_id" class="form-control select2" id="employee_id">
                                    <option value="">Select Employee</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}" {{ old('employee_id', $user->employee_id) == $employee->id ? 'selected' : '' }}>
                                            {{ $employee->name }} ({{ $employee->id_no }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('employee_id')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row {{ $errors->has('status') ? 'has-error' :'' }}">
                            <label class="col-sm-2 col-form-label">Status <span class="text-danger">*</span></label>

                            <div class="col-sm-10">

                                <div class="icheck-success d-inline">
                                    <input checked type="radio" id="active" name="status" value="1" {{ empty(old('status')) ? ($errors->has('status') ? '' : ($user->status == '1' ? 'checked' : '')) :
                                            (old('status') == '1' ? 'checked' : '') }}>
                                    <label for="active">
                                        Active
                                    </label>
                                </div>

                                <div class="icheck-danger d-inline">
                                    <input type="radio" id="inactive" name="status" value="0" {{ empty(old('status')) ? ($errors->has('status') ? '' : ($user->status == '0' ? 'checked' : '')) :
                                            (old('status') == '0' ? 'checked' : '') }}>
                                    <label for="inactive">
                                        Inactive
                                    </label>
                                </div>

                                @error('status')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer">
                        <button type="submit" class="btn btn-info bg-gradient-info">Save</button>
                        <a href="{{ route('user.index') }}"
                           class="btn btn-danger bg-gradient-danger float-right">Cancel</a>
                    </div>
                    <!-- /.card-footer -->
                </form>
            </div>
            <!-- /.card -->
        </div>
        <!--/.col (left) -->
    </div>
@endsection

@section('script')
    <script>
        $(function () {
            // Initialize Select2 if loaded
            if ($.fn.select2) {
                $('.select2').select2();
            }

            function toggleEmployeeDropdown() {
                var selectedRole = $('#role').val();
                if (selectedRole && selectedRole !== 'Customer') {
                    $('#employee_container').show();
                } else {
                    $('#employee_container').hide();
                    $('#employee_id').val('').trigger('change');
                }
            }

            function updateAdditionalRolesOptions() {
                var selectedPrimary = $('#role').val();
                
                // Reset disabled status for all options first
                $('#roles option').prop('disabled', false);
                
                if (selectedPrimary) {
                    // Disable the selected primary role in the additional roles dropdown
                    $('#roles option[value="' + selectedPrimary + '"]').prop('disabled', true);
                    
                    // If the additional roles select has the primary role selected, deselect it
                    var currentSelected = $('#roles').val() || [];
                    var index = currentSelected.indexOf(selectedPrimary);
                    if (index > -1) {
                        currentSelected.splice(index, 1);
                        $('#roles').val(currentSelected).trigger('change');
                    }
                }
                
                // Re-initialize select2 to reflect disabled state
                if ($.fn.select2) {
                    $('#roles').select2();
                }
            }

            // On change of Role
            $('#role').change(function () {
                toggleEmployeeDropdown();
                updateAdditionalRolesOptions();
            });

            // On Page Load
            toggleEmployeeDropdown();
            updateAdditionalRolesOptions();
        });
    </script>
@endsection
