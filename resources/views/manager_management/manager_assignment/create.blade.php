@extends('layouts.app')
@section('title','Assign/Transfer Manager')
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">Assign or Transfer Manager</h3>
                </div>
                <form action="{{ route('manager-assignments.store') }}" class="form-horizontal" method="post">
                    @csrf
                    <div class="card-body">
                        <div class="form-group row {{ $errors->has('manager_type') ? 'has-error' :'' }}">
                            <label for="manager_type" class="col-sm-2 col-form-label">Manager Type <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <select name="manager_type" id="manager_type" class="form-control select2">
                                    <option value="">Select Type</option>
                                    <option value="Area_Manager" {{ old('manager_type') == 'Area_Manager' ? 'selected' : '' }}>Area Manager</option>
                                    <option value="Branch_Manager" {{ old('manager_type') == 'Branch_Manager' ? 'selected' : '' }}>Branch Manager</option>
                                </select>
                                @error('manager_type')
                                <span class="help-block text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row {{ $errors->has('manager_id') ? 'has-error' :'' }}" id="manager_container">
                            <label for="manager_id" class="col-sm-2 col-form-label">Select Manager <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <select name="manager_id" id="manager_id" class="form-control select2">
                                    <option value="">Select Manager</option>
                                    {{-- Will be populated via JS based on type --}}
                                </select>
                                @error('manager_id')
                                <span class="help-block text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row {{ $errors->has('sister_concern_id') ? 'has-error' :'' }}">
                            <label for="sister_concern_id" class="col-sm-2 col-form-label">Select Branch <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <select name="sister_concern_id" id="sister_concern_id" class="form-control select2">
                                    <option value="">Select Branch</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ old('sister_concern_id') == $branch->id ? 'selected' : '' }}>{{ $branch->display_name }}</option>
                                    @endforeach
                                </select>
                                @error('sister_concern_id')
                                <span class="help-block text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row {{ $errors->has('assigned_date') ? 'has-error' :'' }}">
                            <label for="assigned_date" class="col-sm-2 col-form-label">Assignment/Transfer Date <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <input type="text" value="{{ old('assigned_date', date('d-m-Y')) }}" name="assigned_date" class="form-control date-picker" id="assigned_date" autocomplete="off">
                                @error('assigned_date')
                                <span class="help-block text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-info bg-gradient-info btn-sm">Save Assignment</button>
                        @php
                            $cancelRoute = request()->get('type') == 'Area_Manager' ? 'area-manager-assignments.index' : 'branch-manager-assignments.index';
                        @endphp
                        <a href="{{ route($cancelRoute) }}" class="btn btn-danger bg-gradient-danger btn-sm float-right">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Data for JS --}}
    <script>
        var areaManagers = @json($areaManagers);
        var branchManagers = @json($branchManagers);
    </script>
@endsection

@section('script')
    <script>
        $(function () {
            $('#manager_type').change(function() {
                var type = $(this).val();
                var managerSelect = $('#manager_id');
                var branchSelect = $('#sister_concern_id');
                
                // 1. Populate Managers
                managerSelect.empty().append('<option value="">Select Manager</option>');
                var managers = [];
                if (type === 'Area_Manager') {
                    managers = areaManagers;
                } else if (type === 'Branch_Manager') {
                    managers = branchManagers;
                }
                $.each(managers, function(index, manager) {
                    managerSelect.append('<option value="'+ manager.id +'">'+ manager.name + ' (' + manager.mobile_no + ')</option>');
                });
                managerSelect.trigger('change');

                // 2. Fetch Vacant Branches via AJAX
                if (type !== '') {
                    branchSelect.empty().append('<option value="">Loading...</option>');
                    $.ajax({
                        url: "{{ route('manager-assignments.create') }}",
                        method: "GET",
                        data: { type: type },
                        success: function(response) {
                            branchSelect.empty().append('<option value="">Select Branch</option>');
                            $.each(response.branches, function(index, branch) {
                                branchSelect.append('<option value="'+ branch.id +'">'+ (branch.code ? branch.code + ' - ' + branch.name : branch.name) + '</option>');
                            });
                        }
                    });
                }
            });

            // Trigger change if old value exists or type is passed via URL
            var urlType = '{{ request()->get('type') }}';
            var oldType = '{{ old('manager_type') }}';

            if (oldType !== '') {
                $('#manager_type').val(oldType).trigger('change');
            } else if (urlType !== '') {
                $('#manager_type').val(urlType).trigger('change');
            }

            setTimeout(function() {
                if (oldType !== '') {
                    $('#manager_id').val('{{ old('manager_id') }}').trigger('change');
                    $('#sister_concern_id').val('{{ old('sister_concern_id') }}').trigger('change');
                }
            }, 500);
        });
    </script>
@endsection
