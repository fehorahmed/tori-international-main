@extends('layouts.app')
@section('title', $pageTitle)

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-list-alt mr-2"></i>{{ $pageTitle }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('incentive-payments.index') }}" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left"></i> Back to Employee List</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="ledgerDT" class="table table-bordered table-striped w-100">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Role / Action</th>
                                    <th>Amount</th>
                                    <th>Note</th>
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
$(function () {
    $('#ledgerDT').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('incentive-payments.ledger-datatable', $employee->id) }}',
        "pagingType": "full_numbers",
        "lengthMenu": [[25, 50, 100, -1],[25, 50, 100, "All"]],
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'date', name: 'created_at' },
            { data: 'type_badge', name: 'type', orderable: false, searchable: false },
            { data: 'role_type_formatted', name: 'role_type' },
            { data: 'amount_formatted', name: 'amount' },
            { data: 'note', name: 'note' }
        ],
        "dom": 'lBfrtip',
        "buttons": datatableButtons(),
        "responsive": true, "autoWidth": false, "colReorder": true,
    });
});
</script>
@endsection
