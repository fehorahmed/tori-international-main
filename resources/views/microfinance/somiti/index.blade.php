@extends('layouts.app')
@section('title', $pageTitle)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white" style="border-radius: 15px 15px 0 0;">
                    <h3 class="card-title mb-0"><i class="fa fa-users-cog mr-2"></i> {{ $pageTitle }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('microfinance.somiti.create') }}" class="btn btn-default btn-sm">
                            <i class="fa fa-plus-circle mr-1"></i> Add New Somiti
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover data-table" style="width: 100%;">
                            <thead class="bg-light">
                                <tr>
                                    <th>SL</th>
                                    <th>Code</th>
                                    <th>Name (English)</th>
                                    <th>Name (Bengali)</th>
                                    <th>Meeting Day</th>
                                    <th>Meeting Time</th>
                                    <th>Field Officer</th>
                                    <th>Status</th>
                                    <th width="100px">Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(function () {
        var table = $('.data-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('microfinance.somiti.index') }}",
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                {data: 'somiti_code', name: 'somiti_code'},
                {data: 'name', name: 'name'},
                {data: 'name_bn', name: 'name_bn'},
                {data: 'meeting_day', name: 'meeting_day'},
                {data: 'meeting_time', name: 'meeting_time'},
                {data: 'field_officer', name: 'field_officer'},
                {data: 'status', name: 'status'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });

        $('body').on('click', '.delete-btn', function () {
            var id = $(this).data("id");
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: "DELETE",
                        url: "{{ route('microfinance.somiti.index') }}" + '/' + id,
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function (data) {
                            if (data.success) {
                                table.draw();
                                toastr.success(data.message);
                            } else {
                                toastr.error(data.message);
                            }
                        }
                    });
                }
            });
        });
    });
</script>
@endsection
