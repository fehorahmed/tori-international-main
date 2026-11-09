@extends('layouts.app')
@section('title', $pageTitle)

@section('style')
<style>
    .transfer-card { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
    .form-label { font-weight: 600; color: #495057; margin-bottom: 8px; }
    .form-control { border-radius: 12px; padding: 12px 15px; border: 1px solid #dee2e6; transition: all 0.3s; }
    .form-control:focus { box-shadow: 0 0 0 4px rgba(0,123,255,0.1); border-color: #007bff; }
    .section-title { font-size: 1.1rem; font-weight: 700; color: #dc3545; margin-bottom: 25px; display: flex; align-items: center; }
    .section-title::after { content: ''; flex: 1; height: 1px; background: #eee; margin-left: 15px; }
    .btn-submit { border-radius: 12px; padding: 12px 30px; font-weight: 600; transition: all 0.3s; }
    .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(220,53,69,0.3); }
    .group-box { max-height: 400px; overflow-y: auto; border: 1px solid #e9ecef; border-radius: 12px; padding: 15px; background: #fafafa; }
    .group-item { background: #fff; border: 1px solid #e9ecef; border-radius: 10px; padding: 12px 15px; margin-bottom: 10px; transition: all 0.2s; }
    .group-item:hover { border-color: #007bff; background: #fdfdfe; }
    .empty-state { text-align: center; padding: 40px 20px; color: #adb5bd; }
</style>
@endsection

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius: 12px;">
            <i class="fa fa-check-circle mr-2"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius: 12px;">
            <i class="fa fa-exclamation-circle mr-2"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-12">
            <div class="card transfer-card">
                <div class="card-header bg-info text-white" style="border-top-left-radius: 20px; border-top-right-radius: 20px;">
                    <h3 class="card-title mb-0"><i class="fa fa-exchange-alt mr-2"></i> {{ $pageTitle }}</h3>
                </div>
                
                <form action="{{ route('shomriddhi-advance.field-officer-assignment.transfer') }}" method="POST" id="transferForm">
                    @csrf
                    <div class="card-body p-5">
                        <div class="row">
                            <!-- Left Column: Input Forms -->
                            <div class="col-lg-5 mb-4">
                                <div class="section-title"><i class="fa fa-sliders-h mr-2"></i> Transfer Settings</div>
                                
                                <div class="form-group mb-4">
                                    <label class="form-label">Transfer From (বর্তমান মাঠ কর্মী) <span class="text-danger">*</span></label>
                                    <select name="transfer_from_id" id="transfer_from_id" class="form-control select2" required>
                                        <option value="">Select Old Field Officer</option>
                                        @foreach($fieldOfficers as $officer)
                                            <option value="{{ $officer->id }}" {{ old('transfer_from_id') == $officer->id ? 'selected' : '' }}>{{ $officer->id_no }} - {{ $officer->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('transfer_from_id')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group mb-4">
                                    <label class="form-label">Transfer To (নতুন মাঠ কর্মী) <span class="text-danger">*</span></label>
                                    <select name="transfer_to_id" id="transfer_to_id" class="form-control select2" required>
                                        <option value="">Select Target Field Officer</option>
                                        @foreach($fieldOfficers as $officer)
                                            <option value="{{ $officer->id }}" {{ old('transfer_to_id') == $officer->id ? 'selected' : '' }}>{{ $officer->id_no }} - {{ $officer->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('transfer_to_id')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group mb-4">
                                    <label class="form-label">Effective Date (বদলি কার্যকরের তারিখ) <span class="text-danger">*</span></label>
                                    <input type="text" name="effective_date" class="form-control date-picker" value="{{ old('effective_date', date('d-m-Y')) }}" readonly required>
                                    @error('effective_date')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <!-- Right Column: Group Checklist -->
                            <div class="col-lg-7">
                                <div class="section-title" style="color: #17a2b8;"><i class="fa fa-list-check mr-2"></i> Select Groups to Transfer</div>
                                
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="custom-control custom-checkbox select-all-wrapper" style="display: none;">
                                        <input type="checkbox" class="custom-control-input" id="selectAllGroups">
                                        <label class="custom-control-label font-weight-bold" for="selectAllGroups">Select All</label>
                                    </div>
                                    <div id="groupCountLabel" class="text-muted font-weight-bold small" style="display: none;">
                                        Total Selected: <span id="checkedCount" class="text-info">0</span>/<span id="totalCount">0</span>
                                    </div>
                                </div>

                                <div class="group-box" id="groupListContainer">
                                    <div class="empty-state">
                                        <i class="fa fa-info-circle fa-2x mb-3 text-muted"></i>
                                        <p class="mb-0">Please select a Field Officer on the left to load their Groups.</p>
                                    </div>
                                </div>
                                @error('group_ids')
                                    <span class="text-danger d-block mt-2 small">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="text-right mt-5 border-top pt-4">
                            <a href="{{ route('shomriddhi-advance.groups.index') }}" class="btn btn-secondary btn-submit mr-2">Cancel</a>
                            <button type="submit" class="btn btn-info btn-submit" id="submitTransferBtn">
                                <i class="fa fa-paper-plane mr-2"></i> Execute Bulk Transfer
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        $('.select2').select2({
            width: '100%'
        });

        if ($.isFunction($.fn.datepicker)) {
            $('.date-picker').datepicker({
                dateFormat: 'dd-mm-yy',
                changeMonth: true,
                changeYear: true,
                autoclose: true,
                todayHighlight: true
            });
        }

        $('#transfer_from_id').on('change', function() {
            let selectedVal = $(this).val();
            let toSelect = $('#transfer_to_id');
            
            toSelect.find('option').prop('disabled', false);
            if (selectedVal) {
                toSelect.find('option[value="' + selectedVal + '"]').prop('disabled', true);
            }
            toSelect.select2({ width: '100%' });
        });

        $('#transfer_to_id').on('change', function() {
            let selectedVal = $(this).val();
            let fromSelect = $('#transfer_from_id');
            
            fromSelect.find('option').prop('disabled', false);
            if (selectedVal) {
                fromSelect.find('option[value="' + selectedVal + '"]').prop('disabled', true);
            }
            fromSelect.select2({ width: '100%' });
        });

        $('#transfer_from_id').on('change', function() {
            let officerId = $(this).val();
            let container = $('#groupListContainer');
            
            if (!officerId) {
                container.html(`
                    <div class="empty-state">
                        <i class="fa fa-info-circle fa-2x mb-3 text-muted"></i>
                        <p class="mb-0">Please select a Field Officer on the left to load their Groups.</p>
                    </div>
                `);
                $('.select-all-wrapper').hide();
                $('#groupCountLabel').hide();
                return;
            }

            container.html(`
                <div class="empty-state">
                    <i class="fa fa-spinner fa-spin fa-2x mb-3 text-info"></i>
                    <p class="mb-0 text-info">Loading Groups...</p>
                </div>
            `);

            $.ajax({
                url: "{{ route('shomriddhi-advance.field-officer-assignment.get-groups') }}",
                type: "GET",
                data: { field_officer_id: officerId },
                success: function(groups) {
                    if (groups.length === 0) {
                        container.html(`
                            <div class="empty-state">
                                <i class="fa fa-folder-open fa-2x mb-3 text-warning"></i>
                                <p class="mb-0 text-warning">This officer does not have any active Groups assigned.</p>
                            </div>
                        `);
                        $('.select-all-wrapper').hide();
                        $('#groupCountLabel').hide();
                        return;
                    }

                    let html = '';
                    groups.forEach(function(group) {
                        html += `
                            <div class="group-item">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" name="group_ids[]" value="${group.id}" class="custom-control-input group-checkbox" id="group_${group.id}">
                                    <label class="custom-control-label w-100 cursor-pointer" for="group_${group.id}">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <span class="font-weight-bold text-dark">${group.name_en}</span>
                                                <span class="badge badge-light border ml-2">${group.code}</span>
                                            </div>
                                            <div class="text-right">
                                                <span class="text-muted small d-block"><i class="fa fa-users mr-1"></i> Members: <strong>${group.members_count}</strong></span>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        `;
                    });

                    container.html(html);
                    $('.select-all-wrapper').show();
                    $('#groupCountLabel').show();
                    $('#selectAllGroups').prop('checked', false);
                    updateCheckedCount();
                },
                error: function(err) {
                    container.html(`
                        <div class="empty-state">
                            <i class="fa fa-exclamation-triangle fa-2x mb-3 text-danger"></i>
                            <p class="mb-0 text-danger">Failed to load Groups. Please try again.</p>
                        </div>
                    `);
                    $('.select-all-wrapper').hide();
                    $('#groupCountLabel').hide();
                }
            });
        });

        $('#selectAllGroups').on('change', function() {
            let isChecked = $(this).is(':checked');
            $('.group-checkbox').prop('checked', isChecked);
            updateCheckedCount();
        });

        $(document).on('change', '.group-checkbox', function() {
            let totalCheckboxes = $('.group-checkbox').length;
            let checkedCheckboxes = $('.group-checkbox:checked').length;
            
            $('#selectAllGroups').prop('checked', totalCheckboxes === checkedCheckboxes);
            updateCheckedCount();
        });

        function updateCheckedCount() {
            let total = $('.group-checkbox').length;
            let checked = $('.group-checkbox:checked').length;
            
            $('#totalCount').text(total);
            $('#checkedCount').text(checked);
        }

        $('#transferForm').on('submit', function(e) {
            e.preventDefault();

            let selectedGroups = $('.group-checkbox:checked');

            if (selectedGroups.length === 0) {
                alert('Please select at least one Group to transfer!');
                return false;
            }

            if ($('#transfer_from_id').val() === $('#transfer_to_id').val()) {
                alert('Source and Target Field Officers must be different!');
                return false;
            }

            if(confirm("Are you sure you want to transfer the selected groups to the new field officer?")) {
                $(this).find('button[type="submit"]').prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-2"></i> Transferring...');
                document.getElementById('transferForm').submit();
            }
        });
    });
</script>
@endsection
