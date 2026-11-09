@extends('layouts.app')
@section('title', 'FDR Schemes')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-default">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-layer-group mr-2"></i> FDR Schemes
                </h5>
                <div class="card-tools">
                    @if(auth()->user()->can('shomriddhi_fdr_scheme_create') || true)
                    <a href="{{ route('shomriddhi-fdr.schemes.create') }}" class="btn btn-sm btn-success">
                        <i class="fa fa-plus"></i> Add New Scheme
                    </a>
                    @endif
                </div>
            </div>

            <div class="card-body">
                <table class="table table-bordered table-striped" id="table" width="100%">
                    <thead>
                        <tr>
                            <th width="5%">S/L</th>
                            <th>Scheme Name</th>
                            <th>Duration</th>
                            <th>Interest Rate</th>
                            <th>Penalty Rate</th>
                            <th>Min Amount</th>
                            <th>Status</th>
                            <th width="10%" class="text-center">Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        var table = $('#table').DataTable({
            processing: true,
            serverSide: true,
            "dom": 'lBfrtip',
            "buttons": datatableButtons(),
            ajax: "{{ route('shomriddhi-fdr.schemes.datatable') }}",
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                {data: 'name', name: 'name'},
                {data: 'duration', name: 'duration'},
                {data: 'interest_rate', name: 'interest_rate'},
                {data: 'penalty_rate', name: 'penalty_rate'},
                {data: 'min_amount', name: 'min_amount'},
                {data: 'status', name: 'status', orderable: false, searchable: false},
                {data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center'}
            ]
        });

        $(document).on('change', '.status-toggle', function() {
            var schemeId = $(this).data('id');
            var isChecked = $(this).is(':checked');
            
            $.ajax({
                url: "{{ url('admin/shomriddhi-fdr/schemes') }}/" + schemeId + "/toggle-status",
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                    } else {
                        toastr.error('Failed to update status');
                        table.ajax.reload(null, false);
                    }
                },
                error: function() {
                    toastr.error('Something went wrong!');
                    table.ajax.reload(null, false);
                }
            });
        });

        $('body').on('click', '.btn-delete', function () {
            let id = $(this).data('id');
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    preloaderToggle(true);
                    $.ajax({
                        method: "DELETE",
                        url: "{{ route('shomriddhi-fdr.schemes.destroy', ['scheme' => 'REPLACE_WITH_ID_HERE']) }}".replace('REPLACE_WITH_ID_HERE', id),
                        data: { id: id }
                    }).done(function( response ) {
                        preloaderToggle(false);
                        if (response.success) {
                            Swal.fire(
                                'Deleted!',
                                response.message,
                                'success'
                            ).then((result) => {
                                table.ajax.reload(null, false);
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: response.message || 'Something went wrong.',
                            });
                        }
                    });
                }
            })
        });
    });
</script>
@endsection
