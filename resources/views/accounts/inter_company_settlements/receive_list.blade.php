@extends('layouts.app')
@section('title', 'Pending Incoming Payments')
@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-default">
            <div class="card-header">
                <div class="row">
                    <div class="col-12 col-md-3">
                        <div class="form-group">
                            <label for="start_date" class="col-form-label">Start Date <span class="text-danger">*</span></label>
                            <input required autocomplete="off" type="text" value="{{ request('start_date', auth()->user()->business_date ? \Carbon\Carbon::parse(auth()->user()->business_date)->format('d-m-Y') : '') }}" name="start_date" class="form-control date-picker" id="start_date" placeholder="Enter Start Date">
                        </div>
                    </div>
                    <div class="col-12 col-md-3">
                        <div class="form-group">
                            <label for="end_date" class="col-form-label">End Date <span class="text-danger">*</span></label>
                            <input required autocomplete="off" type="text" value="{{ request('end_date', auth()->user()->business_date ? \Carbon\Carbon::parse(auth()->user()->business_date)->format('d-m-Y') : '') }}" name="end_date" class="form-control date-picker" id="end_date" placeholder="Enter End Date">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <input style="margin-top: -4px;" type="button" id="search_btn" name="search" class="btn btn-info bg-gradient-info form-control" value="Search">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <table id="table" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th class="text-center">S/L</th>
                            <th>Date</th>
                            <th>Payment No</th>
                            <th>Received From</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Receive Modal -->
<div class="modal fade" id="receiveModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('inter_company_settlements.receive_store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Receive Payment</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="settlement_id" id="modal_settlement_id">
                    <div class="form-group">
                        <label>Receive To (Bank/Cash) <span class="text-danger">*</span></label>
                        <select name="to_account_head_id" class="form-control select2" required style="width: 100%">
                            <option value="">Select Account</option>
                            @php
                                $activeConcernId = auth()->user()->activeConcernId();
                                $cashBankHeads = \App\Models\Account\AccountHead::where(function($q) use ($activeConcernId) {
                                        $q->whereNull('sister_concern_id')
                                          ->orWhere('sister_concern_id', $activeConcernId);
                                    })
                                    ->where('payment_mode', '>', 0)
                                    ->where('status', 1)
                                    ->get();
                            @endphp
                            @foreach($cashBankHeads as $head)
                                <option value="{{ $head->id }}">{{ $head->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Confirm Receive</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('inter_company_settlements.reject') }}" method="POST">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Reject Payment</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="settlement_id" id="reject_modal_settlement_id">
                    <div class="form-group">
                        <label>Reason for Rejection <span class="text-danger">*</span></label>
                        <textarea name="reject_reason" class="form-control" rows="3" required placeholder="Why are you rejecting this payment?"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger">Confirm Reject & Reverse</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@section('script')
<script>
    $(function () {
        var table = $('#table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('inter_company_settlements.receive_datatable') }}',
                data: function (d) {
                    d.start_date = $("#start_date").val()
                    d.end_date = $("#end_date").val()
                }
            },
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center'},
                {data: 'date', name: 'date'},
                {data: 'payment_no', name: 'payment_no'},
                {data: 'from_sister_concern.name', name: 'fromSisterConcern.name'},
                {data: 'amount', name: 'amount'},
                {data: 'status', name: 'status'},
                {data: 'action', name: 'action', orderable: false, searchable: false}
            ],
            order: [[1, 'desc']]
        });

        $('#start_date,#end_date,#search_btn').change(function () {
            table.ajax.reload();
        });

        $('body').on('click', '.btn-receive', function () {
            var id = $(this).data('id');
            $('#modal_settlement_id').val(id);
            $('#receiveModal').modal('show');
        });
        
        $('#receiveModal form').submit(function(e) {
            e.preventDefault();
            var form = $(this);
            $.ajax({
                url: form.attr('action'),
                method: 'POST',
                data: form.serialize(),
                success: function(response) {
                    if(response.success) {
                        $('#receiveModal').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message,
                        });
                        table.ajax.reload();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message,
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred while processing your request.',
                    });
                }
            });
        });
        $('body').on('click', '.btn-reject', function () {
            var id = $(this).data('id');
            $('#reject_modal_settlement_id').val(id);
            $('#rejectModal').modal('show');
        });

        $('#rejectModal form').submit(function(e) {
            e.preventDefault();
            var form = $(this);
            var btn = form.find('button[type="submit"]');
            var originalText = btn.text();
            btn.prop('disabled', true).text('Reversing...');

            $.ajax({
                url: form.attr('action'),
                method: 'POST',
                data: form.serialize(),
                success: function(response) {
                    btn.prop('disabled', false).text(originalText);
                    if(response.success) {
                        $('#rejectModal').modal('hide');
                        form[0].reset();
                        Swal.fire({
                            icon: 'success',
                            title: 'Rejected',
                            text: response.message,
                        });
                        table.ajax.reload();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message,
                        });
                    }
                },
                error: function() {
                    btn.prop('disabled', false).text(originalText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred while processing your request.',
                    });
                }
            });
        });
    });
</script>
@endsection
