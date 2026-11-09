@extends('layouts.app')
@section('title', 'Salary Structures')
@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-default">
            <div class="card-header">
                <div class="card-title"><i class="fa fa-sitemap mr-2"></i> Salary Structures</div>
                <div class="card-tools">
                    <a href="{{ route('salary-structures.create') }}" class="btn btn-sm btn-info bg-gradient-info">
                        <i class="fa fa-plus"></i> Add New Structure
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="structure-table" style="width: 100%">
                        <thead>
                            <tr>
                                <th>SL</th>
                                <th>Structure Name</th>
                                <th>Basic %</th>
                                <th>House Rent %</th>
                                <th>Medical %</th>
                                <th>TA/DA (Fixed)</th>
                                <th>PF %</th>
                                <th>Tax %</th>
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
    $(function () {
        $('#structure-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('salary-structures.datatable') }}",
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                {data: 'name', name: 'name'},
                {data: 'basic_percent', name: 'basic_percent'},
                {data: 'house_rent_percent', name: 'house_rent_percent'},
                {data: 'medical_percent', name: 'medical_percent'},
                {data: 'ta_da_fixed', name: 'ta_da_fixed'},
                {data: 'pf_percent', name: 'pf_percent'},
                {data: 'tax_percent', name: 'tax_percent'},
                {data: 'status', name: 'status'},
                {data: 'action', name: 'action', orderable: false, searchable: false}
            ]
        });

        $('body').on('click', '.btn-change-status', function () {
            let url = $(this).data('url');
            Swal.fire({
                title: 'Change Status?',
                text: "Are you sure you want to change the status?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, change it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: {_token: '{{ csrf_token() }}'},
                        success: function (res) {
                            if (res.success) {
                                Swal.fire('Changed!', res.message, 'success');
                                $('#structure-table').DataTable().ajax.reload();
                            }
                        }
                    });
                }
            })
        });
    });
</script>
@endsection
