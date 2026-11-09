@extends('layouts.app')
@section('title', 'DPS Transactions')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-default">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-exchange-alt mr-2"></i> Collection History</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3 pb-3 border-bottom">
                    <div class="col-md-4">
                        <label>Filter by Account</label>
                        <select class="form-control select2 filter-field" id="filter_account">
                            <option value="">All Accounts</option>
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->customer->member_no }} | {{ $acc->customer->name }} | {{ $acc->dps_no }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label>Filter by Status</label>
                        <select class="form-control filter-field" id="filter_status">
                            <option value="">All Statuses</option>
                            <option value="Pending">Pending</option>
                            <option value="Approved">Approved</option>
                            <option value="Rejected">Rejected</option>
                        </select>
                    </div>
                </div>

                <div class="table-responsive-sm">
                    <table class="table table-bordered table-striped" id="table" style="width:100%">
                        <thead>
                            <tr>
                                <th>S/L</th>
                                <th>DPS No</th>
                                <th>Customer</th>
                                <th>Installment #</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Penalty</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th width="10%">Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
$(document).ready(function() {
    let table = $('#table').DataTable({
        processing: true, serverSide: true,
        ajax: {
            url: '{{ route('shomriddhi-dps.transactions.datatable') }}',
            data: function (d) {
                d.account_id = $('#filter_account').val();
                d.status = $('#filter_status').val();
            }
        },
        order: [], // Disable DataTables default sorting to preserve server-side order
        pagingType: 'full_numbers',
        lengthMenu: [[25, 50, 100], [25, 50, 100]],
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'dps_no' },
            { data: 'customer' },
            { data: 'installment_no' },
            { data: 'type' },
            { data: 'amount' },
            { data: 'penalty_amount' },
            { data: 'date' },
            { data: 'status' },
            { data: 'action', orderable: false, searchable: false },
        ],
        dom: 'lBfrtip', buttons: datatableButtons(), responsive: true, autoWidth: false,
    });

    $('.select2').select2();

    $('.filter-field').on('change', function() {
        table.ajax.reload();
    });

    $('body').on('click', '.btn-approve', function() {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Approve Transaction?',
            text: "This will post the GL voucher and update the customer's account.",
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, Approve!'
        }).then((result) => {
            if (result.isConfirmed) {
                preloaderToggle(true);
                $.ajax({
                    method: 'POST',
                    url: '{{ route('shomriddhi-dps.transactions.approve', ['transaction' => 'REPLACE']) }}'.replace('REPLACE', id),
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(r) {
                        preloaderToggle(false);
                        if (r.success) {
                            Swal.fire('Approved!', r.message, 'success').then(() => $('#table').DataTable().ajax.reload(null, false));
                        } else {
                            Swal.fire('Error!', r.message, 'error');
                        }
                    },
                    error: function() {
                        preloaderToggle(false);
                        Swal.fire('Error!', 'Something went wrong.', 'error');
                    }
                });
            }
        });
    });
});
</script>
@endsection
