@extends('layouts.app')
@section('title', 'Stock Conversions')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-recycle mr-2"></i> Stock Conversion List</h5>
                    @if(auth()->user()->can('stock_transfer_create'))
                        <div class="card-tools">
                            <a href="{{ route('stock-conversions.create') }}" class="btn btn-primary bg-gradient-primary btn-sm">
                                <i class="fa fa-plus"></i> New Conversion
                            </a>
                        </div>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label>Start Date</label>
                            <input autocomplete="off" type="text" class="form-control date-picker start_date" value="{{ request('start_date', auth()->user()->business_date ? \Carbon\Carbon::parse(auth()->user()->business_date)->format('d-m-Y') : '') }}" placeholder="Enter Start Date">
                        </div>
                        <div class="col-md-3">
                            <label>End Date</label>
                            <input autocomplete="off" type="text" class="form-control date-picker end_date" value="{{ request('end_date', auth()->user()->business_date ? \Carbon\Carbon::parse(auth()->user()->business_date)->format('d-m-Y') : '') }}" placeholder="Enter End Date">
                        </div>
                        <div class="col-md-2">
                            <label>&nbsp;</label>
                            <button type="button" class="btn btn-info bg-gradient-info form-control search_btn">Search</button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="table" class="table table-bordered">
                            <thead>
                            <tr>
                                <th class="text-center">#</th>
                                <th class="text-center">Order No.</th>
                                <th class="text-center">Date</th>
                                <th class="text-center">Warehouse</th>
                                <th class="text-center">Notes</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Action</th>
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
            var table = $('#table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('stock-conversions.datatable') }}",
                    data: function (d) {
                        d.start_date = $(".start_date").val();
                        d.end_date = $(".end_date").val();
                    }
                },
                "pagingType": "full_numbers",
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center'},
                    {data: 'order_no', name: 'order_no', className: 'text-center'},
                    {data: 'date', name: 'date', className: 'text-center'},
                    {data: 'warehouse_name', name: 'warehouse.name', className: 'text-center'},
                    {data: 'notes', name: 'notes'},
                    {data: 'status', name: 'status', orderable: false, className: 'text-center'},
                    {data: 'action', name: 'action', orderable: false, className: 'text-center'},
                ],
                order: [[1, 'desc']],
                "dom": 'lBfrtip',
                "buttons": datatableButtons(),
                "responsive": true, "autoWidth": false,
            });

            $('.search_btn, .start_date, .end_date').on('click change', function () {
                table.ajax.reload();
            });

            $('body').on('click', '.btn-delete', function () {
                let id = $(this).data('id');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You want to delete this pending conversion?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            type: "DELETE",
                            url: "{{ url('admin/stock-conversions') }}/" + id,
                            data: {
                                "_token": "{{ csrf_token() }}"
                            },
                            success: function (response) {
                                if (response.success) {
                                    Swal.fire('Deleted!', response.message, 'success');
                                    table.ajax.reload();
                                } else {
                                    Swal.fire('Error!', response.message, 'error');
                                }
                            }
                        });
                    }
                })
            });
        });
    </script>
@endsection
