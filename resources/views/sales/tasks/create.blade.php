@extends('layouts.app')
@section('title','Assign SR Tasks')
@section('style')
    <style>
        .content-wrapper { overflow-x: hidden; }
    </style>
@endsection
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">Assign Tasks</h3>
                </div>
                <form action="{{ route('sr-tasks.store') }}" method="post">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="sr_id">Select SR <span class="text-danger">*</span></label>
                                    <select name="sr_id" id="sr_id" class="form-control select2" style="width: 100%;" required>
                                        <option value="">Select SR</option>
                                        @foreach($srs as $sr)
                                            <option value="{{ $sr->id }}" {{ old('sr_id') == $sr->id ? 'selected' : '' }}>{{ $sr->name }} ({{ $sr->mobile_no }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="date">Month <span class="text-danger">*</span></label>
                                    <input type="text" name="date" id="date" class="form-control month-picker" value="{{ old('date', date('m-Y')) }}" required readonly>
                                </div>
                            </div>
                        </div>

                        <hr>
                        <h5>Tasks</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                <tr>
                                    <th>Task Type</th>
                                    <th>Target Value</th>
                                    <th style="width: 50px;">Action</th>
                                </tr>
                                </thead>
                                <tbody id="task-container">
                                @if(old('tasks'))
                                    @foreach(old('tasks') as $index => $oldTask)
                                        <tr class="task-row">
                                            <td>
                                                <select name="tasks[{{ $index }}][type_id]" class="form-control task-type-select select2" style="width: 100%;" required>
                                                    <option value="">Select Type</option>
                                                    @foreach($taskTypes as $type)
                                                        <option value="{{ $type->id }}" {{ $oldTask['type_id'] == $type->id ? 'selected' : '' }}>{{ $type->name }} ({{ $type->unit }})</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <input type="number" step="any" name="tasks[{{ $index }}][target_value]" class="form-control" value="{{ $oldTask['target_value'] }}" placeholder="Enter Target" required>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-danger btn-sm btn-remove-row"><i class="fa fa-trash"></i></button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr class="task-row">
                                        <td>
                                            <select name="tasks[0][type_id]" class="form-control task-type-select select2" style="width: 100%;" required>
                                                <option value="">Select Type</option>
                                                @foreach($taskTypes as $type)
                                                    <option value="{{ $type->id }}">{{ $type->name }} ({{ $type->unit }})</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" step="any" name="tasks[0][target_value]" class="form-control" placeholder="Enter Target" required>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-danger btn-sm btn-remove-row"><i class="fa fa-trash"></i></button>
                                        </td>
                                    </tr>
                                @endif
                                </tbody>
                                <tfoot>
                                <tr>
                                    <td colspan="3">
                                        <button type="button" class="btn btn-success btn-sm" id="btn-add-row"><i class="fa fa-plus"></i> Add More</button>
                                    </td>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-info bg-gradient-info">Save Tasks</button>
                        <a href="{{ route('sr-tasks.index') }}" class="btn btn-default float-right">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <template id="task-row-template">
        <tr class="task-row">
            <td>
                <select name="tasks[REPLACE_INDEX][type_id]" class="form-control task-type-select select2" style="width: 100%;" required>
                    <option value="">Select Type</option>
                    @foreach($taskTypes as $type)
                        <option value="{{ $type->id }}">{{ $type->name }} ({{ $type->unit }})</option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="number" step="any" name="tasks[REPLACE_INDEX][target_value]" class="form-control" placeholder="Enter Target" required>
            </td>
            <td>
                <button type="button" class="btn btn-danger btn-sm btn-remove-row"><i class="fa fa-trash"></i></button>
            </td>
        </tr>
    </template>
@endsection

@section('script')
    <script>
        $(function () {
            $('.select2').select2();

            let rowCount = {{ old('tasks') ? count(old('tasks')) : 1 }};

            $('#btn-add-row').click(function () {
                let template = $('#task-row-template').html();
                let newRow = template.replace(/REPLACE_INDEX/g, rowCount);
                let $newRow = $(newRow);
                $('#task-container').append($newRow);
                $newRow.find('.select2').select2(); // Initialize select2 for the new row
                rowCount++;
            });

            $('body').on('click', '.btn-remove-row', function () {
                if ($('.task-row').length > 1) {
                    $(this).closest('tr').remove();
                } else {
                    alert('At least one task is required.');
                }
            });

            // Prevent duplicate task types in real-time
            $('body').on('change', '.task-type-select', function() {
                let currentSelect = $(this);
                let selectedValue = currentSelect.val();
                let isDuplicate = false;

                $('.task-type-select').not(currentSelect).each(function() {
                    if ($(this).val() == selectedValue && selectedValue != '') {
                        isDuplicate = true;
                        return false;
                    }
                });

                if (isDuplicate) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Duplicate Task Type',
                        text: 'This task type is already selected in another row.',
                    });
                    currentSelect.val('').trigger('change.select2');
                }
            });

            // Prevent duplicate task types on submit
            $('form').submit(function (e) {
                let selectedTypes = [];
                let duplicateFound = false;
                $('.task-type-select').each(function () {
                    let val = $(this).val();
                    if (val && selectedTypes.includes(val)) {
                        duplicateFound = true;
                        return false;
                    }
                    selectedTypes.push(val);
                });

                if (duplicateFound) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Duplicate Tasks',
                        text: 'Duplicate task types are not allowed for the same day.',
                    });
                    e.preventDefault();
                }
            });
        });
    </script>
@endsection
