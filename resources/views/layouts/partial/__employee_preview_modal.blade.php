<!-- Employee Profile Preview Modal -->
<div class="modal fade" id="previewFormModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content shadow-lg border-0" style="border-radius: 12px;">
            <div class="modal-header bg-gradient-info">
                <h5 class="modal-title text-white"><i class="fa fa-user"></i> Profile Data Preview</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <div class="p-4 text-center bg-light border-bottom">
                    <div class="d-inline-flex justify-content-center align-items-center rounded-circle bg-white shadow-sm mb-3" style="width: 80px; height: 80px;">
                        <i class="fa fa-user-tie fa-3x text-info"></i>
                    </div>
                    <h4 class="font-weight-bold mb-1" id="p_name_header"></h4>
                    <p class="text-muted mb-2"><i class="fa fa-phone-alt"></i> <span id="p_mobile_header"></span></p>
                    <span id="p_status" class="badge px-3 py-1" style="font-size: 13px; font-weight: 500;"></span>
                </div>
                
                <div class="p-4">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-uppercase text-info font-weight-bold mb-3" style="font-size: 13px; letter-spacing: 1px;"><i class="fa fa-info-circle mr-1"></i> Basic Info</h6>
                            <table class="table table-sm table-borderless mb-4" style="font-size: 14px;">
                                <tr><td width="40%" class="text-muted">Full Name</td><td width="60%" class="font-weight-bold text-dark" id="p_name"></td></tr>
                                <tr><td class="text-muted">Mobile No.</td><td class="font-weight-bold text-dark" id="p_mobile_no"></td></tr>
                                <tr><td class="text-muted">Email</td><td class="font-weight-bold text-dark" id="p_email"></td></tr>
                                <tr><td class="text-muted">Department</td><td class="font-weight-bold text-dark" id="p_department"></td></tr>
                                <tr><td class="text-muted">Designation</td><td class="font-weight-bold text-dark" id="p_designation"></td></tr>
                                <tr><td class="text-muted">Shift</td><td class="font-weight-bold text-dark" id="p_shift"></td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-uppercase text-info font-weight-bold mb-3" style="font-size: 13px; letter-spacing: 1px;"><i class="fa fa-university mr-1"></i> Financial Info</h6>
                            <table class="table table-sm table-borderless mb-4" style="font-size: 14px;">
                                <tr><td width="40%" class="text-muted">Basic Salary</td><td width="60%" class="font-weight-bold text-dark" id="p_basic_salary"></td></tr>
                                <tr><td class="text-muted">Gross Salary</td><td class="font-weight-bold text-dark" id="p_gross_salary"></td></tr>
                                <tr><td class="text-muted">Bank Name</td><td class="font-weight-bold text-dark" id="p_bank_name"></td></tr>
                                <tr><td class="text-muted">Account No.</td><td class="font-weight-bold text-dark" id="p_bank_account_no"></td></tr>
                            </table>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <h6 class="text-uppercase text-info font-weight-bold mb-3" style="font-size: 13px; letter-spacing: 1px;"><i class="fa fa-map-marker-alt mr-1"></i> Current Address</h6>
                            <table class="table table-sm table-borderless mb-0" style="font-size: 14px;">
                                <tr><td width="30%" class="text-muted">Division</td><td width="70%" class="font-weight-bold text-dark" id="p_c_division"></td></tr>
                                <tr><td class="text-muted">District</td><td class="font-weight-bold text-dark" id="p_c_district"></td></tr>
                                <tr><td class="text-muted">Upazila</td><td class="font-weight-bold text-dark" id="p_c_upazila"></td></tr>
                                <tr><td class="text-muted">Union/Pour.</td><td class="font-weight-bold text-dark" id="p_c_union"></td></tr>
                                <tr><td class="text-muted">Village</td><td class="font-weight-bold text-dark" id="p_c_village"></td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-uppercase text-info font-weight-bold mb-3" style="font-size: 13px; letter-spacing: 1px;"><i class="fa fa-home mr-1"></i> Permanent Address</h6>
                            <table class="table table-sm table-borderless mb-0" style="font-size: 14px;">
                                <tr><td width="30%" class="text-muted">Division</td><td width="70%" class="font-weight-bold text-dark" id="p_p_division"></td></tr>
                                <tr><td class="text-muted">District</td><td class="font-weight-bold text-dark" id="p_p_district"></td></tr>
                                <tr><td class="text-muted">Upazila</td><td class="font-weight-bold text-dark" id="p_p_upazila"></td></tr>
                                <tr><td class="text-muted">Union/Pour.</td><td class="font-weight-bold text-dark" id="p_p_union"></td></tr>
                                <tr><td class="text-muted">Village</td><td class="font-weight-bold text-dark" id="p_p_village"></td></tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light justify-content-between">
                <button type="button" class="btn btn-outline-danger btn-sm" data-dismiss="modal"><i class="fa fa-edit"></i> Edit Data</button>
                <button type="button" class="btn btn-success bg-gradient-success btn-sm font-weight-bold px-4" id="btnConfirmSave"><i class="fa fa-check-circle"></i> Confirm & Save</button>
            </div>
        </div>
    </div>
</div>

@push('script')
<script>
    $(function() {
        $('#btnPreview').click(function(e) {
            e.preventDefault();
            
            // HTML5 Form Validation Check
            let form = $(this).closest('form')[0];
            if (form && !form.checkValidity()) {
                form.reportValidity();
                return;
            }
            
            // Reset Confirm Button state just in case
            $('#btnConfirmSave').html('<i class="fa fa-check-circle"></i> Confirm & Save').prop('disabled', false);
            
            let name = $('#name').val();
            let mobile = $('#mobile_no').val();

            if (!name || !mobile) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Incomplete Data',
                    text: 'Please fill all the required fields (*).',
                });
                return;
            }
            
            $('#p_name_header').text(name);
            $('#p_mobile_header').text(mobile);

            $('#p_name').text(name);
            $('#p_mobile_no').text(mobile);
            $('#p_email').text($('#email').val() || 'N/A');
            
            $('#p_department').text($('#department').val() ? $('#department option:selected').text() : 'N/A');
            $('#p_designation').text($('#designation').val() ? $('#designation option:selected').text() : 'N/A');
            $('#p_shift').text($('#shift').val() ? $('#shift option:selected').text() : 'N/A');
            
            // Handle dynamic extra fields
            $('.p_dynamic_row').remove(); // Clear previous extra rows
            
            if ($('#branch_id').length > 0) {
                let branchText = $('#branch_id').val() ? $('#branch_id option:selected').text() : 'N/A';
                $('#p_shift').closest('tr').after('<tr class="p_dynamic_row"><td class="text-muted">Assigned Branch</td><td class="font-weight-bold text-dark">' + branchText + '</td></tr>');
            }
            
            if ($('#area_id').length > 0) {
                let areaText = $('#area_id').val() ? $('#area_id option:selected').text() : 'N/A';
                $('#p_shift').closest('tr').after('<tr class="p_dynamic_row"><td class="text-muted">Assigned Area</td><td class="font-weight-bold text-dark">' + areaText + '</td></tr>');
            }
            
            $('#p_basic_salary').text('৳ ' + ($('input[name="basic_salary"]').val() || '0'));
            $('#p_gross_salary').text('৳ ' + ($('input[name="gross_salary"]').val() || '0'));
            $('#p_bank_name').text($('input[name="bank_name"]').val() || 'N/A');
            $('#p_bank_account_no').text($('input[name="bank_account_no"]').val() || 'N/A');
            
            // Current Address
            $('#p_c_division').text($('#current_division').val() ? $('#current_division option:selected').text() : 'N/A');
            $('#p_c_district').text($('#current_district').val() ? $('#current_district option:selected').text() : 'N/A');
            $('#p_c_upazila').text($('#current_upazila').val() ? $('#current_upazila option:selected').text() : 'N/A');
            $('#p_c_union').text($('#current_union').val() ? $('#current_union option:selected').text() : 'N/A');
            $('#p_c_village').text($('#current_village_id').val() ? $('#current_village_id option:selected').text() : 'N/A');
            
            // Permanent Address
            if($('#same_as_current').is(':checked')) {
                $('#p_p_division').text($('#p_c_division').text());
                $('#p_p_district').text($('#p_c_district').text());
                $('#p_p_upazila').text($('#p_c_upazila').text());
                $('#p_p_union').text($('#p_c_union').text());
                $('#p_p_village').text($('#p_c_village').text());
            } else {
                $('#p_p_division').text($('#permanent_division').val() ? $('#permanent_division option:selected').text() : 'N/A');
                $('#p_p_district').text($('#permanent_district').val() ? $('#permanent_district option:selected').text() : 'N/A');
                $('#p_p_upazila').text($('#permanent_upazila').val() ? $('#permanent_upazila option:selected').text() : 'N/A');
                $('#p_p_union').text($('#permanent_union').val() ? $('#permanent_union option:selected').text() : 'N/A');
                $('#p_p_village').text($('#permanent_village_id').val() ? $('#permanent_village_id option:selected').text() : 'N/A');
            }
            
            let statusText = $('#employee_status option:selected').text().trim() || 'N/A';
            let statusVal = $('#employee_status').val();
            
            if(statusVal == '1' || statusText.toLowerCase().includes('active')) {
                $('#p_status').removeClass('badge-danger').addClass('badge-success').text('Status: ' + statusText);
            } else {
                $('#p_status').removeClass('badge-success').addClass('badge-danger').text('Status: ' + statusText);
            }
            
            $('#previewFormModal').modal('show');
        });
        
        $('#btnConfirmSave').click(function() {
            $(this).html('<i class="fa fa-spinner fa-spin"></i> Saving...').prop('disabled', true);
            $('#previewFormModal').modal('hide');
            $('#btnRealSubmit').click();
        });
    });
</script>
@endpush
