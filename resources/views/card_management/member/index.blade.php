@extends('layouts.app')
@section('title', 'Member Card Management')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <a href="{{ route('card-management.member.create') }}" class="btn btn-info bg-gradient-info btn-sm mr-2">
                            <i class="fa fa-plus-circle mr-1"></i> Issue Card (কার্ড ইস্যু)
                        </a>
                        <a href="{{ route('card-management.member.logs') }}" class="btn btn-secondary bg-gradient-secondary btn-sm mr-2">
                            <i class="fa fa-history mr-1"></i> Print History Logs (প্রিন্ট লগ)
                        </a>
                        <!-- <button id="btnBulkPrint" class="btn btn-primary bg-gradient-primary btn-sm d-none">
                            <i class="fa fa-print mr-1"></i> Print Selected (সিলেক্টেড প্রিন্ট) (<span id="selectedCount">0</span>)
                        </button> -->
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="font-weight-bold">Filter by Member</label>
                            <select class="form-control select2" id="filter_member_id">
                                <option value="">All Members </option>
                                @foreach($members as $member)
                                    <option value="{{ $member->id }}">{{ $member->member_no }} | {{ $member->name }} </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="font-weight-bold">Filter by Card Creator</label>
                            <select class="form-control select2" id="filter_user_id">
                                <option value="">All Users </option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->username }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="cardTable" class="table table-bordered table-striped" style="width: 100%;">
                            <thead>
                            <tr>
                                <th style="width: 30px; text-align: center;"><input type="checkbox" id="selectAllCards"></th>
                                <th>Card No</th>
                                <th>Holder Name</th>
                                <th>Holder Type</th>
                                <th>Mobile No</th>
                                <th>Status</th>
                                <th>Payment</th>
                                <th>Amount</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Print Reason Modal (Dual Mode: Single/Bulk) -->
    <div class="modal fade" id="printReasonModal" tabindex="-1" role="dialog" aria-labelledby="printReasonModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-gradient-info text-white">
                    <h5 class="modal-title" id="printReasonModalLabel"><i class="fa fa-print mr-2"></i> Card Issuance Reason</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="printForm">
                    @csrf
                    <div class="modal-body p-4">
                        <input type="hidden" id="modal_is_bulk" name="is_bulk" value="0">
                        <input type="hidden" id="modal_card_id" name="card_id">
                        
                        <div class="form-group mb-3" id="single_holder_group">
                            <label class="small font-weight-bold text-dark">Card Holder</label>
                            <input type="text" id="modal_holder_name" class="form-control bg-light" readonly>
                        </div>

                        <div class="form-group mb-3 d-none" id="bulk_count_group">
                            <label class="small font-weight-bold text-dark">Selected Cards Count</label>
                            <input type="text" id="modal_bulk_count" class="form-control bg-light" readonly>
                        </div>

                        <div class="form-group mb-3">
                            <label class="small font-weight-bold text-dark">Reason for Printing (প্রিন্ট করার কারণ) <span class="text-danger">*</span></label>
                            <select name="print_reason" id="print_reason" class="form-control" required>
                                <option value="First Issue">First Issue (প্রথমবার ইস্যু)</option>
                                <option value="Lost Replacement">Lost Replacement (হারানো কার্ডের বিকল্প)</option>
                                <option value="Damaged Replacement">Damaged Replacement (নষ্ট কার্ডের বিকল্প)</option>
                                <option value="Renewal">Renewal (কার্ড নবায়ন)</option>
                            </select>
                        </div>

                        <div class="form-group mb-0">
                            <label class="small font-weight-bold text-dark">Notes / Remarks (মন্তব্য)</label>
                            <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="Enter optional notes..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer" style="background-color: #f8f9fa;">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-info bg-gradient-info btn-sm"><i class="fa fa-check-circle mr-1"></i> Confirm & Print</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(function () {
            $('.select2').select2(); // Initialize select2 if available
            
            let table = $('#cardTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('card-management.member.datatable') }}',
                    data: function (d) {
                        d.user_id = $('#filter_user_id').val();
                        d.member_id = $('#filter_member_id').val();
                    }
                },
                "pagingType": "full_numbers",
                "lengthMenu": [[10, 25, 50, -1],[10, 25, 50, "All"]],
                order: [[1, 'desc']],
                columns: [
                    {data: 'checkbox', name: 'checkbox', orderable: false, searchable: false, class: 'text-center'},
                    {data: 'card_number', name: 'card_number'},
                    {data: 'holder_name', name: 'holder_name'},
                    {data: 'holder_type', name: 'holder_type'},
                    {data: 'holder_mobile', name: 'holder_mobile'},
                    {data: 'status', name: 'status'},
                    {data: 'payment_status', name: 'payment_status'},
                    {data: 'amount', name: 'amount'},
                    {data: 'action', name: 'action', orderable: false, searchable: false},
                ],
                "dom": 'lBfrtip',
                "buttons": datatableButtons(),
                "responsive": true, "autoWidth": false,"colReorder": true,
                "drawCallback": function() {
                    $('#selectAllCards').prop('checked', false);
                    updateBulkPrintVisibility();
                }
            });

            $('#selectAllCards').change(function() {
                $('.card-checkbox').prop('checked', this.checked);
                updateBulkPrintVisibility();
            });

            $('#filter_user_id, #filter_member_id').change(function() {
                table.ajax.reload();
            });

            $('body').on('change', '.card-checkbox', function() {
                let totalCheckbox = $('.card-checkbox').length;
                let checkedCheckbox = $('.card-checkbox:checked').length;
                
                $('#selectAllCards').prop('checked', totalCheckbox === checkedCheckbox);
                updateBulkPrintVisibility();
            });

            function updateBulkPrintVisibility() {
                let checkedCount = $('.card-checkbox:checked').length;
                if (checkedCount > 0) {
                    $('#selectedCount').text(checkedCount);
                    $('#btnBulkPrint').removeClass('d-none');
                } else {
                    $('#btnBulkPrint').addClass('d-none');
                }
            }

            $('#btnBulkPrint').click(function() {
                let checkedCount = $('.card-checkbox:checked').length;
                
                $('#modal_is_bulk').val('1');
                $('#single_holder_group').addClass('d-none');
                $('#bulk_count_group').removeClass('d-none');
                $('#modal_bulk_count').val(checkedCount + ' Cards Selected');
                
                $('#printReasonModalLabel').html('<i class="fa fa-print mr-2"></i> Bulk Card Issuance Reason');
                $('#print_reason').val('First Issue');
                $('#notes').val('');
                
                $('#printReasonModal').modal('show');
            });

            $('body').on('click', '.btn-print-card', function() {
                let id = $(this).data('id');
                let name = $(this).data('name');
                
                $('#modal_is_bulk').val('0');
                $('#modal_card_id').val(id);
                $('#modal_holder_name').val(name);
                
                $('#single_holder_group').removeClass('d-none');
                $('#bulk_count_group').addClass('d-none');
                
                $('#printReasonModalLabel').html('<i class="fa fa-print mr-2"></i> Card Issuance Reason');
                $('#print_reason').val('First Issue');
                $('#notes').val('');
                
                $('#printReasonModal').modal('show');
            });

            $('#printForm').submit(function(e) {
                e.preventDefault();
                let isBulk = $('#modal_is_bulk').val() === '1';
                let printReason = $('#print_reason').val();
                let notes = $('#notes').val();
                
                preloaderToggle(true);
                
                if (isBulk) {
                    let cardIds = [];
                    $('.card-checkbox:checked').each(function() {
                        cardIds.push($(this).val());
                    });
                    
                    $.ajax({
                        method: "POST",
                        url: "{{ route('card-management.member.bulk_log_print') }}",
                        data: {
                            _token: "{{ csrf_token() }}",
                            card_ids: cardIds,
                            print_reason: printReason,
                            notes: notes
                        }
                    }).done(function(response) {
                        preloaderToggle(false);
                        $('#printReasonModal').modal('hide');
                        
                        if (response.success) {
                            table.ajax.reload(null, false);
                            let printUrl = "{{ route('card-management.member.bulk_print_preview') }}?ids=" + cardIds.join(',');
                            window.open(printUrl, '_blank');
                        } else {
                            Swal.fire({ icon: 'error', title: 'Error', text: response.message });
                        }
                    }).fail(function(xhr) {
                        preloaderToggle(false);
                        let errMsg = 'Failed to record bulk print history.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errMsg = xhr.responseJSON.message;
                        }
                        Swal.fire({ icon: 'error', title: 'Error', text: errMsg });
                    });
                } else {
                    let cardId = $('#modal_card_id').val();
                    $.ajax({
                        method: "POST",
                        url: "{{ route('card-management.member.log_print', ['card' => 'REPLACE_ID']) }}".replace('REPLACE_ID', cardId),
                        data: {
                            _token: "{{ csrf_token() }}",
                            print_reason: printReason,
                            notes: notes
                        }
                    }).done(function(response) {
                        preloaderToggle(false);
                        $('#printReasonModal').modal('hide');
                        
                        if (response.success) {
                            table.ajax.reload(null, false);
                            let printUrl = "{{ route('card-management.member.print_preview', ['card' => 'REPLACE_ID']) }}".replace('REPLACE_ID', cardId);
                            window.open(printUrl, '_blank');
                        } else {
                            Swal.fire({ icon: 'error', title: 'Error', text: response.message });
                        }
                    }).fail(function(xhr) {
                        preloaderToggle(false);
                        let errMsg = 'Failed to record print history.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errMsg = xhr.responseJSON.message;
                        }
                        Swal.fire({ icon: 'error', title: 'Error', text: errMsg });
                    });
                }
            });

            $('body').on('click', '.change-status-btn', function(e) {
                e.preventDefault();
                let id = $(this).data('id');
                let status = $(this).data('status');
                
                Swal.fire({
                    title: 'Update Status?',
                    text: "Are you sure you want to change card status to " + status + "?",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, update status!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        preloaderToggle(true);
                        $.ajax({
                            method: "POST",
                            url: "{{ route('card-management.member.update_status', ['card' => 'REPLACE_ID']) }}".replace('REPLACE_ID', id),
                            data: {
                                _token: "{{ csrf_token() }}",
                                status: status
                            }
                        }).done(function(response) {
                            preloaderToggle(false);
                            if (response.success) {
                                table.ajax.reload(null, false);
                                Swal.fire('Updated!', response.message, 'success');
                            } else {
                                Swal.fire({ icon: 'error', title: 'Error', text: response.message });
                            }
                        }).fail(function() {
                            preloaderToggle(false);
                            Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to update status.' });
                        });
                    }
                });
            });

            // Delete Card and Reverse Voucher
            $('body').on('click', '.btn-delete-card', function(e) {
                e.preventDefault();
                let id = $(this).data('id');
                
                Swal.fire({
                    title: 'Are you sure?',
                    text: "Deleting this card will completely reverse its associated payment voucher. If multiple cards were paid together in the same voucher, they will all be reverted to 'Pending' status. This cannot be undone!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, Delete & Reverse Voucher!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        preloaderToggle(true);
                        $.ajax({
                            method: "DELETE",
                            url: "{{ route('card-management.member.destroy', ['card' => 'REPLACE_ID']) }}".replace('REPLACE_ID', id),
                            data: {
                                _token: "{{ csrf_token() }}"
                            }
                        }).done(function(response) {
                            preloaderToggle(false);
                            if (response.success) {
                                table.ajax.reload(null, false);
                                Swal.fire('Deleted & Reversed!', response.message, 'success');
                            } else {
                                Swal.fire({ icon: 'error', title: 'Error', text: response.message });
                            }
                        }).fail(function() {
                            preloaderToggle(false);
                            Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to delete card.' });
                        });
                    }
                });
            });
        });
    </script>
@endsection
