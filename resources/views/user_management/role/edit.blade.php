@extends('layouts.app')
@section('title','Edit Role')
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
        <div class="col-md-12">
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">Role Information</h3>
                </div>
                <!-- /.card-header -->
                <form action="{{ route('role.update', ['role' => $role->id]) }}" class="form-horizontal" method="post">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        @if($role->is_system)
                            <div class="alert alert-warning alert-dismissible shadow-sm">
                                <h5><i class="icon fas fa-exclamation-triangle"></i> System Protected Role!</h5>
                                This is a system-protected role. You cannot change its name, but you can manage its permissions.
                            </div>
                        @endif

                        <div class="form-group row {{ $errors->has('name') ? 'has-error' : '' }}">
                            <label for="name" class="col-sm-2 col-form-label">Role Name <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                @if($role->is_system)
                                    <input type="text" value="{{ old('name', str_replace('_', ' ', $role->name)) }}" class="form-control" disabled>
                                    <input type="hidden" name="name" value="{{ $role->name }}">
                                @else
                                    <input type="text" value="{{ old('name', str_replace('_', ' ', $role->name)) }}" name="name" class="form-control" id="name" placeholder="Enter Role Name (e.g. Accountant)">
                                @endif
                                @error('name')
                                <span class="help-block text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <hr>
                        <h4 class="mb-4">Assign Permissions to Role</h4>

                        @if($role->name === \App\Enumeration\RoleType::$ADMIN)
                            <div class="alert alert-info">
                                <i class="icon fas fa-info"></i> Admin role always inherits all permissions by default. You cannot modify Admin permissions.
                            </div>
                        @endif

                        <table class="table table-bordered">
                            <tr>
                                <td class="text-left">
                                    <div class="icheck-success d-inline">
                                        <input type="checkbox" id="check-all-checkbox" class="check-all-checkbox" {{ $role->name === \App\Enumeration\RoleType::$ADMIN ? 'disabled checked' : '' }}>
                                        <label style="font-size: 20px" for="check-all-checkbox">Check All</label>
                                    </div>
                                </td>
                            </tr>
                            @foreach($permissions as $permission)
                                <tr>
                                    <td style="font-size: 20px;" class="text-left">
                                        <div class="icheck-success">
                                            <input {{ in_array($permission->name, $rolePermissions) ? 'checked' : '' }} {{ $role->name === \App\Enumeration\RoleType::$ADMIN ? 'disabled' : '' }} type="checkbox" id="for_label_{{ $permission->id }}" class="parent-checkbox" name="permission[]" value="{{ $permission->name }}">
                                            <label class="parent-checkbox-label" for="for_label_{{ $permission->id }}"> {{ ucwords(str_replace('_',' ',$permission->name)) }}</label>
                                        </div>
                                        <ul>
                                            @foreach($permission->children->sortBy('sort') as $childrenItem)
                                                <li style="font-size: 19px;">
                                                    <div class="icheck-success">
                                                        <input {{ in_array($childrenItem->name, $rolePermissions) ? 'checked' : '' }} {{ $role->name === \App\Enumeration\RoleType::$ADMIN ? 'disabled' : '' }} type="checkbox" id="for_label_{{ $childrenItem->id }}" class="child-checkbox" name="permission[]" value="{{ $childrenItem->name }}">
                                                        <label class="child-checkbox-label" for="for_label_{{ $childrenItem->id }}"> {{ ucwords(str_replace('_',' ',$childrenItem->name)) }}</label>
                                                    </div>
                                                    <ul>
                                                        @foreach($childrenItem->children->sortBy('sort') as $childrenItem2)
                                                            <li style="font-size: 18px;">
                                                                <div class="icheck-success">
                                                                    <input {{ in_array($childrenItem2->name, $rolePermissions) ? 'checked' : '' }} {{ $role->name === \App\Enumeration\RoleType::$ADMIN ? 'disabled' : '' }} type="checkbox" id="for_label_{{ $childrenItem2->id }}" class="grandchild-checkbox" name="permission[]" value="{{ $childrenItem2->name }}">
                                                                    <label class="grandchild-checkbox-label" for="for_label_{{ $childrenItem2->id }}"> {{ ucwords(str_replace('_',' ',$childrenItem2->name)) }}</label>
                                                                </div>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer">
                        @if($role->name !== \App\Enumeration\RoleType::$ADMIN)
                            <button type="submit" class="btn btn-info bg-gradient-info">Save</button>
                        @endif
                        <a href="{{ route('role.index') }}" class="btn btn-danger bg-gradient-danger float-right">Cancel</a>
                    </div>
                    <!-- /.card-footer -->
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
    @if($role->name !== \App\Enumeration\RoleType::$ADMIN)
    <script>
        $(document).ready(function() {
            // Check All functionality
            $('.check-all-checkbox').change(function() {
                var isChecked = $(this).prop('checked');
                $('input[type="checkbox"]').not('[disabled]').prop('checked', isChecked);
            });

            // Parent Checkbox change
            $('.parent-checkbox').change(function() {
                var isChecked = $(this).prop('checked');
                $(this).closest('td').find('ul input[type="checkbox"]').not('[disabled]').prop('checked', isChecked);
            });

            // Child Checkbox change
            $('.child-checkbox').change(function() {
                var isChecked = $(this).prop('checked');
                $(this).closest('li').find('ul input[type="checkbox"]').not('[disabled]').prop('checked', isChecked);
                
                if (isChecked) {
                    $(this).closest('td').find('.parent-checkbox').prop('checked', true);
                }
            });

            // Grandchild Checkbox change
            $('.grandchild-checkbox').change(function() {
                var isChecked = $(this).prop('checked');
                if (isChecked) {
                    $(this).closest('li').parents('li').find('.child-checkbox').prop('checked', true);
                    $(this).closest('td').find('.parent-checkbox').prop('checked', true);
                }
            });
        });
    </script>
    @endif
@endsection
