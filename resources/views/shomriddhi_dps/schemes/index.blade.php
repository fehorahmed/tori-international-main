@extends('layouts.app')
@section('title', 'DPS Schemes')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-default">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-layer-group mr-2"></i> DPS Schemes</h5>
                <div class="card-tools">
                    <a href="{{ route('shomriddhi-dps.schemes.create') }}" class="btn btn-sm btn-success">
                        <i class="fa fa-plus"></i> Add Scheme
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        {{ session('success') }}
                    </div>
                @endif
                <div class="table-responsive-sm">
                    <table class="table table-bordered table-striped" id="table" style="width:100%">
                        <thead>
                            <tr>
                                <th>S/L</th>
                                <th>Name</th>
                                <th>Duration</th>
                                <th>Installment</th>
                                <th>Interest Rate</th>
                                <th>Missed Penalty</th>
                                <th>Premature Penalty</th>
                                <th>Status</th>
                                <th>Action</th>
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
    const table = $('#table').DataTable({
        processing: true, serverSide: true,
        ajax: '{{ route('shomriddhi-dps.schemes.datatable') }}',
        pagingType: 'full_numbers',
        lengthMenu: [[10, 25, 50], [10, 25, 50]],
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'name', name: 'name' },
            { data: 'duration_months', name: 'duration_months' },
            { data: 'installment_amount', name: 'installment_amount' },
            { data: 'interest_rate', name: 'interest_rate' },
            { data: 'penalty', name: 'penalty' },
            { data: 'premature_penalty', name: 'premature_penalty' },
            { data: 'status', name: 'status', orderable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false },
        ],
        dom: 'lBfrtip', buttons: datatableButtons(), responsive: true, autoWidth: false,
    });

    // Status Toggle
    $('body').on('change', '.status-toggle', function() {
        const id = $(this).data('id');
        $.post('{{ route('shomriddhi-dps.schemes.toggle_status', ['scheme' => 'REPLACE']) }}'.replace('REPLACE', id),
            { _token: '{{ csrf_token() }}' },
            function(r) { if (!r.success) { table.ajax.reload(null, false); Swal.fire('Error', r.message, 'error'); } }
        );
    });

    // Delete
    $('body').on('click', '.btn-delete', function() {
        const id = $(this).data('id');
        Swal.fire({ title: 'Delete Scheme?', text: 'Cannot delete if accounts are linked.', icon: 'warning',
            showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Yes, delete!'
        }).then(result => {
            if (result.isConfirmed) {
                $.ajax({
                    method: 'DELETE',
                    url: '{{ route('shomriddhi-dps.schemes.destroy', ['scheme' => 'REPLACE']) }}'.replace('REPLACE', id),
                    data: { _token: '{{ csrf_token() }}' }
                }).done(r => {
                    if (r.success) Swal.fire('Deleted!', r.message, 'success').then(() => table.ajax.reload(null, false));
                    else Swal.fire('Error!', r.message, 'error');
                });
            }
        });
    });
});
</script>
@endsection
