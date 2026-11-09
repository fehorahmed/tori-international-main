<!-- Nominee Modal -->
<div class="modal fade" id="addNomineeModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 20px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <div class="modal-header bg-primary text-white" style="border-radius: 20px 20px 0 0;">
                <h5 class="modal-title"><i class="fa fa-user-plus mr-2"></i> Add New Nominee</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form_quick_nominee">
                @csrf
                <input type="hidden" name="mf_member_id" id="modal_member_id">
                <div class="modal-body p-4" style="max-height: 70vh; overflow-y: auto;">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nominee Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="Full name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Relation With Member <span class="text-danger">*</span></label>
                            <input type="text" name="relation" class="form-control" placeholder="e.g. Spouse, Brother" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Mobile No</label>
                            <input type="text" name="phone" class="form-control" placeholder="Phone number">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Gender</label>
                            <select name="gender" class="form-control select2">
                                <option value="">Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">NID NO</label>
                            <input type="text" name="nid_no" class="form-control" placeholder="NID Number">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 p-4">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary shadow-sm">Save Nominee</button>
                </div>
            </form>
        </div>
    </div>
</div>

@section('script')
<script>
    $(document).ready(function() {
        $('.select2').select2({ width: '100%' });

        if ($.isFunction($.fn.datepicker)) {
            $('.date-picker').datepicker({
                dateFormat: 'dd-mm-yy',
                changeMonth: true,
                changeYear: true,
                autoclose: true,
                todayHighlight: true
            });
        }

        var openingFee = 0;
        var initialDeposit = 0;
        var minDeposit = 0;
        var currentProductType = 1;

        function updateTotal() {
            var total = openingFee + initialDeposit;
            $('#prev_total').text('৳ ' + total.toLocaleString(undefined, {minimumFractionDigits: 2}));
        }

        $('#member_id').on('change', function(e, isInitial) {
            var memberId = $(this).val();
            if(memberId) {
                var url = "{{ route('microfinance.savings-account.get_member_info', ':id') }}";
                url = url.replace(':id', memberId);

                $.ajax({
                    url: url,
                    method: "GET",
                    success: function(res) {
                        $('#prev_member_name').text(res.name);
                        $('#prev_member_id').text(res.member_no); // Fixed: Populate Member ID
                        $('#prev_somiti').text(res.somiti_name);
                        $('#prev_mobile').text(res.mobile);
                        $('#prev_member_photo').attr('src', res.photo);
                        $('#member_somiti_name').val(res.somiti_name);

                        // Auto-fill Account Title if empty
                        if($('#account_title').val() == "") {
                            $('#account_title').val(res.name);
                        }

                        // Update nominee dropdown if it's not initial load on edit page
                        if (!isInitial) {
                            $('#nominee_id').empty().append('<option value="" disabled hidden>Select Nominee</option>');
                            $('#nominee_shares_list').empty();
                            $('#nominee_shares_container').hide();
                            if(res.nominees && res.nominees.length > 0) {
                                $.each(res.nominees, function(i, nominee) {
                                    var displayName = nominee.name + (nominee.relation ? ' (' + nominee.relation + ')' : '');
                                    $('#nominee_id').append('<option value="'+nominee.id+'">'+displayName+'</option>');
                                });
                            }
                            $('#nominee_id').select2({ width: '100%' });
                        }
                        $('#modal_member_id').val(res.id);
                    }
                });
            }
        });

        // Product Filtering
        var allOptions = $('#product_id option').clone();
        $('#product_type').on('change', function(e, isInitial) {
            var type = $(this).val();
            var currentSelected = $('#product_id').val();
            $('#product_id').empty();
            if(type) {
                $('#scheme_container').fadeIn();
                var filtered = allOptions.filter(function() {
                    return $(this).data('type') == type || $(this).val() == "";
                });
                $('#product_id').append(filtered);
                if(currentSelected) $('#product_id').val(currentSelected);
            } else {
                $('#scheme_container').hide();
                $('#product_id').append(allOptions.clone());
            }
            if(!isInitial && !$('#product_id').val()) $('#product_id').val('').trigger('change');
            $('#product_id').select2({ width: '100%' });
        });

        function validateMinDeposit() {
            if (minDeposit > 0 && initialDeposit < minDeposit) {
                $('#min_deposit_val').text(minDeposit.toLocaleString(undefined, {minimumFractionDigits: 2}));
                $('#min_deposit_warning').slideDown();
            } else {
                $('#min_deposit_warning').slideUp();
            }
        }

        $('#product_id').on('change', function() {
            var productId = $(this).val();
            if(productId) {
                var url = "{{ route('microfinance.savings-account.get_product_info', ':id') }}";
                url = url.replace(':id', productId);
                $.ajax({
                    url: url, 
                    method: "GET",
                    success: function(res) {
                        $('#prev_type').text(res.type_label);
                        $('#prev_interest').text(res.interest_rate + '%');
                        
                        // Show/Hide Auto-Renewal for FDR
                        if(res.product_type == 3) {
                            $('#auto_renewal_container').fadeIn();
                        } else {
                            $('#auto_renewal_container').fadeOut();
                        }

                        if(res.product_type == 2) {
                            $('#deposit_label').text('Monthly DPS Amount');
                            $('#opening_balance').prop('readonly', true).addClass('bg-light');
                            if(!$('#opening_balance').val() || $('#opening_balance').val() == "0.00") {
                                $('#opening_balance').val(res.installment_amount).trigger('input');
                            }
                        } else {
                            $('#deposit_label').text(res.product_type == 3 ? 'FDR Amount' : 'Initial Deposit');
                            $('#opening_balance').prop('readonly', false).removeClass('bg-light');
                        }
                        openingFee = parseFloat(res.opening_fee) || 0;
                        minDeposit = parseFloat(res.min_deposit) || 0;
                        currentProductType = res.product_type;
                        
                        // Estimated Initial Cost sidebar summary
                        $('#prev_fee').text('৳ ' + openingFee.toLocaleString(undefined, {minimumFractionDigits: 2}));
                        
                        // Populate and show the inline Policy Info Banner
                        $('#banner_opening_fee').text(openingFee.toLocaleString(undefined, {minimumFractionDigits: 2}));
                        $('#banner_min_deposit').text(minDeposit.toLocaleString(undefined, {minimumFractionDigits: 2}));
                        $('#banner_interest_rate').text(res.interest_rate);
                        $('#product_info_banner').slideDown();

                        updateTotal();
                        validateMinDeposit();
                    }
                });
            } else {
                $('#product_info_banner').slideUp();
                $('#min_deposit_warning').slideUp();
            }
        });

        $('#opening_balance').on('input', function() {
            initialDeposit = parseFloat($(this).val()) || 0;
            $('#prev_deposit').text('৳ ' + initialDeposit.toLocaleString(undefined, {minimumFractionDigits: 2}));
            updateTotal();
            validateMinDeposit();
        });

        // Trigger initial info
        $('#product_type').trigger('change', [true]);
        if($('#member_id').val()) $('#member_id').trigger('change', [true]);
        if($('#product_id').val()) $('#product_id').trigger('change');
        if($('#opening_balance').val()) $('#opening_balance').trigger('input');

        // Nominee Logic
        $('#btn_add_nominee').on('click', function() {
            if(!$('#member_id').val()) {
                Swal.fire('Warning', 'Please select a member first!', 'warning');
                return;
            }
            $('#addNomineeModal').modal('show');
        });

        $('#form_quick_nominee').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            var btn = form.find('button[type="submit"]');
            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-2"></i> Saving...');

            $.ajax({
                url: "{{ route('microfinance.savings-account.store-nominee') }}",
                method: "POST",
                data: form.serialize(),
                success: function(res) {
                    if (res.success) {
                        var nominee = res.nominee;
                        var relationText = nominee.relation ? ' (' + nominee.relation + ')' : '';
                        var displayName = nominee.name + relationText;
                        
                        // Append and select the new nominee
                        var newOption = new Option(displayName, nominee.id, true, true);
                        $('#nominee_id').append(newOption).trigger('change');
                        
                        $('#addNomineeModal').modal('hide');
                        form.trigger('reset');
                        form.find('.select2').val('').trigger('change');
                        Swal.fire('Success', 'Nominee added successfully!', 'success');
                    } else {
                        Swal.fire('Error', res.message || 'Failed to save nominee', 'error');
                    }
                },
                error: function(err) {
                    var msg = 'An error occurred while saving the nominee.';
                    if (err.responseJSON && err.responseJSON.message) {
                        msg = err.responseJSON.message;
                    }
                    Swal.fire('Error', msg, 'error');
                },
                complete: function() {
                    btn.prop('disabled', false).text('Save Nominee');
                }
            });
        });

        $('#nominee_id').on('change', function() {
            var selectedNominees = $(this).select2('data');
            var container = $('#nominee_shares_container');
            var list = $('#nominee_shares_list');
            if(selectedNominees.length > 0) {
                container.show();
                var currentInputs = {};
                list.find('input').each(function() { currentInputs[$(this).data('id')] = $(this).val(); });
                list.empty();
                var defaultShare = (100 / selectedNominees.length).toFixed(2);
                $.each(selectedNominees, function(i, nominee) {
                    var val = currentInputs[nominee.id] || defaultShare;
                    list.append(`<div class="d-flex align-items-center bg-light p-2 rounded mb-2 border">
                        <div class="flex-grow-1 small font-weight-bold">${nominee.text}</div>
                        <div style="width: 100px;">
                            <div class="input-group input-group-sm">
                                <input type="number" name="share_percentage[${nominee.id}]" data-id="${nominee.id}" class="form-control share-input" value="${val}" step="0.01">
                                <div class="input-group-append"><span class="input-group-text">%</span></div>
                            </div>
                        </div>
                    </div>`);
                });
                updateTotalShare();
            } else {
                container.hide();
            }
        });

        function updateTotalShare() {
            var total = 0;
            $('.share-input').each(function() { total += parseFloat($(this).val()) || 0; });
            $('#total_share_display').text(total.toFixed(2) + '%');
        }
        $(document).on('input', '.share-input', updateTotalShare);
    });
</script>
@endsection
