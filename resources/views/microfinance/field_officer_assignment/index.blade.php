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
    .somiti-box { max-height: 400px; overflow-y: auto; border: 1px solid #e9ecef; border-radius: 12px; padding: 15px; background: #fafafa; }
    .somiti-item { background: #fff; border: 1px solid #e9ecef; border-radius: 10px; padding: 12px 15px; margin-bottom: 10px; transition: all 0.2s; }
    .somiti-item:hover { border-color: #007bff; background: #fdfdfe; }
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
                <div class="card-header bg-danger text-white" style="border-top-left-radius: 20px; border-top-right-radius: 20px;">
                    <h3 class="card-title mb-0"><i class="fa fa-exchange-alt mr-2"></i> {{ $pageTitle }} (মাঠ কর্মী গণ-বদলি)</h3>
                </div>
                
                <form action="{{ route('microfinance.field-officer-assignment.transfer') }}" method="POST" id="transferForm">
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
                                            <option value="{{ $officer->id }}" {{ old('transfer_from_id') == $officer->id ? 'selected' : '' }}>{{ $officer->name }}</option>
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
                                            <option value="{{ $officer->id }}" {{ old('transfer_to_id') == $officer->id ? 'selected' : '' }}>{{ $officer->name }}</option>
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

                                <div class="form-group mb-4">
                                    <label class="form-label">Reason / Notes (বদলি সংক্রান্ত নোট)</label>
                                    <textarea name="change_reason" class="form-control" rows="4" placeholder="Enter reason for transfer (e.g. Area manager reassignment)">{{ old('change_reason') }}</textarea>
                                    @error('change_reason')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <!-- Right Column: Somiti Checklist -->
                            <div class="col-lg-7">
                                <div class="section-title" style="color: #007bff;"><i class="fa fa-list-check mr-2"></i> Select Somitis to Transfer (সমিতি নির্বাচন করুন)</div>
                                
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="custom-control custom-checkbox select-all-wrapper" style="display: none;">
                                        <input type="checkbox" class="custom-control-input" id="selectAllSomitis">
                                        <label class="custom-control-label font-weight-bold" for="selectAllSomitis">Select All (সবগুলো নির্বাচন করুন)</label>
                                    </div>
                                    <div id="somitiCountLabel" class="text-muted font-weight-bold small" style="display: none;">
                                        Total Selected: <span id="checkedCount" class="text-primary">0</span>/<span id="totalCount">0</span>
                                    </div>
                                </div>

                                <div class="somiti-box" id="somitiListContainer">
                                    <div class="empty-state">
                                        <i class="fa fa-info-circle fa-2x mb-3 text-muted"></i>
                                        <p class="mb-0">Please select a Field Officer on the left to load their Somitis.</p>
                                    </div>
                                </div>
                                @error('somiti_ids')
                                    <span class="text-danger d-block mt-2 small">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="text-right mt-5 border-top pt-4">
                            <a href="{{ route('microfinance.somiti.index') }}" class="btn btn-secondary btn-submit mr-2">Cancel</a>
                            <button type="submit" class="btn btn-danger btn-submit" id="submitTransferBtn">
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

        // Live disable matching officer option in opposite dropdowns (Prevent same person select)
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

        // Fetch Somitis when "Transfer From" officer changes
        $('#transfer_from_id').on('change', function() {
            let officerId = $(this).val();
            let container = $('#somitiListContainer');
            
            if (!officerId) {
                container.html(`
                    <div class="empty-state">
                        <i class="fa fa-info-circle fa-2x mb-3 text-muted"></i>
                        <p class="mb-0">Please select a Field Officer on the left to load their Somitis.</p>
                    </div>
                `);
                $('.select-all-wrapper').hide();
                $('#somitiCountLabel').hide();
                return;
            }

            container.html(`
                <div class="empty-state">
                    <i class="fa fa-spinner fa-spin fa-2x mb-3 text-primary"></i>
                    <p class="mb-0 text-primary">Loading Somitis...</p>
                </div>
            `);

            $.ajax({
                url: "{{ route('microfinance.field-officer-assignment.get-somitis') }}",
                type: "GET",
                data: { field_officer_id: officerId },
                success: function(somitis) {
                    if (somitis.length === 0) {
                        container.html(`
                            <div class="empty-state">
                                <i class="fa fa-folder-open fa-2x mb-3 text-warning"></i>
                                <p class="mb-0 text-warning">This officer does not have any active Somitis assigned.</p>
                            </div>
                        `);
                        $('.select-all-wrapper').hide();
                        $('#somitiCountLabel').hide();
                        return;
                    }

                    let html = '';
                    somitis.forEach(function(somiti) {
                        html += `
                            <div class="somiti-item">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" name="somiti_ids[]" value="${somiti.id}" class="custom-control-input somiti-checkbox" id="somiti_${somiti.id}">
                                    <label class="custom-control-label w-100 cursor-pointer" for="somiti_${somiti.id}">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <span class="font-weight-bold text-dark">${somiti.name}</span>
                                                <span class="badge badge-light border ml-2">${somiti.somiti_code}</span>
                                            </div>
                                            <div class="text-right">
                                                <span class="text-muted small d-block"><i class="fa fa-calendar-day mr-1"></i> Meeting: <strong>${somiti.meeting_day}</strong></span>
                                                <span class="text-muted small d-block"><i class="fa fa-users mr-1"></i> Members: <strong>${somiti.members_count}</strong></span>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        `;
                    });

                    container.html(html);
                    $('.select-all-wrapper').show();
                    $('#somitiCountLabel').show();
                    $('#selectAllSomitis').prop('checked', false);
                    updateCheckedCount();
                },
                error: function(err) {
                    container.html(`
                        <div class="empty-state">
                            <i class="fa fa-exclamation-triangle fa-2x mb-3 text-danger"></i>
                            <p class="mb-0 text-danger">Failed to load Somitis. Please try again.</p>
                        </div>
                    `);
                    $('.select-all-wrapper').hide();
                    $('#somitiCountLabel').hide();
                }
            });
        });

        // "Select All" Toggle
        $('#selectAllSomitis').on('change', function() {
            let isChecked = $(this).is(':checked');
            $('.somiti-checkbox').prop('checked', isChecked);
            updateCheckedCount();
        });

        // Individual checkbox change
        $(document).on('change', '.somiti-checkbox', function() {
            let totalCheckboxes = $('.somiti-checkbox').length;
            let checkedCheckboxes = $('.somiti-checkbox:checked').length;
            
            $('#selectAllSomitis').prop('checked', totalCheckboxes === checkedCheckboxes);
            updateCheckedCount();
        });

        function updateCheckedCount() {
            let total = $('.somiti-checkbox').length;
            let checked = $('.somiti-checkbox:checked').length;
            
            $('#totalCount').text(total);
            $('#checkedCount').text(checked);
        }

        // Form Submit via Modal Confirmation
        $('#transferForm').on('submit', function(e) {
            e.preventDefault(); // Stop default submission

            let fromOfficer = $('#transfer_from_id option:selected').text();
            let toOfficer = $('#transfer_to_id option:selected').text();
            let effectiveDate = $('.date-picker').val();
            let selectedSomitis = $('.somiti-checkbox:checked');

            if (selectedSomitis.length === 0) {
                alert('Please select at least one Somiti to transfer!');
                return false;
            }

            if ($('#transfer_from_id').val() === $('#transfer_to_id').val()) {
                alert('Source and Target Field Officers must be different!');
                return false;
            }

            // Populate Modal Content
            $('#modalFromOfficer').text(fromOfficer);
            $('#modalToOfficer').text(toOfficer);
            $('#modalEffectiveDate').text(effectiveDate);

            // Populate Selected Somitis Checklist inside Modal
            let listHtml = '<ul class="pl-3 mb-0 text-dark font-weight-bold" style="font-size: 0.9rem; list-style-type: square;">';
            selectedSomitis.each(function() {
                let item = $(this).closest('.somiti-item');
                let name = item.find('.font-weight-bold').text();
                let code = item.find('.badge').text();
                listHtml += `<li class="mb-1">${name} <span class="badge badge-light border font-weight-normal text-muted">${code}</span></li>`;
            });
            listHtml += '</ul>';
            $('#modalSomitiList').html(listHtml);

            // Open the Modal
            $('#confirmTransferModal').modal('show');
        });

        // Inside Modal: Confirm button click
        $('#confirmSubmitBtn').on('click', function() {
            $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-2"></i> Transferring...');
            // Submit form natively
            document.getElementById('transferForm').submit();
        });
    });
</script>

<!-- Premium Confirmation Modal -->
<div class="modal fade" id="confirmTransferModal" tabindex="-1" role="dialog" aria-labelledby="confirmTransferModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 20px; border: none; box-shadow: 0 15px 50px rgba(0,0,0,0.15);">
            <div class="modal-header bg-danger text-white" style="border-top-left-radius: 20px; border-top-right-radius: 20px;">
                <h5 class="modal-title font-weight-bold" id="confirmTransferModalLabel">
                    <i class="fa fa-exclamation-triangle mr-2"></i> Confirm Bulk Transfer
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted">You are about to transfer the following Somiti(s):</p>
                <div id="modalSomitiList" class="mb-4" style="max-height: 150px; overflow-y: auto; background: #f8fafc; border-radius: 12px; padding: 15px; border: 1px solid #e2e8f0;">
                    <!-- Somitis will be listed here dynamically -->
                </div>
                <div class="alert alert-warning border-0" style="border-radius: 12px; background: #fffbeb;">
                    <div class="d-flex">
                        <i class="fa fa-info-circle text-warning fa-lg mr-3 mt-1"></i>
                        <div>
                            <h6 class="font-weight-bold text-warning mb-1">Transfer Summary:</h6>
                            <p class="mb-0 text-muted small">
                                From: <strong class="text-dark" id="modalFromOfficer">Old Officer</strong><br>
                                To: <strong class="text-dark" id="modalToOfficer">New Officer</strong><br>
                                Date: <strong class="text-dark" id="modalEffectiveDate">Effective Date</strong>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top-0 px-4 pb-4">
                <button type="button" class="btn btn-secondary px-4 py-2" data-dismiss="modal" style="border-radius: 12px; font-weight: 600;">Cancel</button>
                <button type="button" id="confirmSubmitBtn" class="btn btn-danger px-4 py-2" style="border-radius: 12px; font-weight: 600; box-shadow: 0 4px 10px rgba(220,53,69,0.2);">Confirm & Transfer</button>
            </div>
        </div>
    </div>
</div>
@endsection
