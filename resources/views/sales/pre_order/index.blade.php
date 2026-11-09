@extends('layouts.app')
@section('title','Pre-Orders')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    <a href="{{ route('pre-orders.create') }}" class="btn btn-info bg-gradient-info btn-sm">Create Pre-Order <i class="fa fa-plus"></i></a>
                </div>
                <div class="card-header">
                    <div class="row">
                        <div class="col-12 col-md-3">
                            <div class="form-group">
                                <label for="sr" class="col-form-label">SR</label>
                                <select name="sr" id="sr" class="form-control select2">
                                    <option value="">All SR</option>
                                    @foreach($srs as $sr)
                                        <option {{ auth()->user()->sr_id == $sr->id ? 'selected' : '' }} value="{{ $sr->id }}">{{ $sr->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-md-3">
                            <div class="form-group">
                                <label for="customer" class="col-form-label">Customer</label>
                                <select name="customer" id="customer" class="form-control select2">
                                    <option value="">All Customer</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-12 col-md-2">
                            <div class="form-group">
                                <label for="start_date" class="col-form-label">Start Date</label>
                                <input required autocomplete="off" type="text" value="{{ request('start_date', auth()->user()->business_date ? \Carbon\Carbon::parse(auth()->user()->business_date)->format('d-m-Y') : '') }}"
                                       name="start_date" class="form-control date-picker" id="start_date"
                                       placeholder="Start Date">
                            </div>
                        </div>
                        <div class="col-12 col-md-2">
                            <div class="form-group">
                                <label for="end_date" class="col-form-label">End Date</label>
                                <input required autocomplete="off" type="text" value="{{ request('end_date', auth()->user()->business_date ? \Carbon\Carbon::parse(auth()->user()->business_date)->format('d-m-Y') : '') }}"
                                       name="end_date" class="form-control date-picker" id="end_date"
                                       placeholder="End Date">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <input style="margin-top: -4px;" type="button" id="search_btn" name="search"
                                       class="btn btn-info bg-gradient-info form-control" value="Search">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table" class="table table-bordered">
                            <thead>
                            <tr>
                                <th class="text-center">Order No.</th>
                                <th class="text-center">Date</th>
                                <th class="text-center">SR</th>
                                <th class="text-center">Customer</th>
                                <th class="text-center">Total</th>
                                <th class="text-center">Order Advance</th>
                                <!-- <th class="text-center">Due</th> -->
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
                    url: "{{ route('pre-orders.datatable') }}",
                    data: function (d) {
                        d.customer = $("#customer").val()
                        d.sr = $("#sr").val()
                        d.start_date = $("#start_date").val()
                        d.end_date = $("#end_date").val()
                    }
                },
                columns: [
                    {data: 'order_no', name: 'order_no'},
                    {data: 'date', name: 'date'},
                    {data: 'sr_name', name: 'sr.name'},
                    {data: 'customer_name', name: 'customer.name'},
                    {data: 'total', name: 'total', className:'text-right'},
                    {data: 'paid', name: 'paid', className:'text-right'},
                    // {data: 'due', name: 'due', className:'text-right'},
                    {data: 'action', name: 'action', orderable: false},
                ],
                order: [[0, 'desc']],
                "dom": 'lBfrtip',
                "buttons": datatableButtons(),
                "autoWidth": false,
            });

            $('#search_btn').click(function () {
                table.ajax.reload();
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
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            method: "DELETE",
                            url: "{{ url('admin/pre-orders') }}/" + id,
                            data: { _token: "{{ csrf_token() }}" }
                        }).done(function (response) {
                            if (response.success) {
                                Swal.fire('Deleted!', response.message, 'success').then(() => {
                                    table.ajax.reload();
                                });
                            } else {
                                Swal.fire('Error!', response.message, 'error');
                            }
                        }).fail(function (response) {
                            Swal.fire('Error!', 'Something went wrong!', 'error');
                        });
                    }
                });
            });
        });
    </script>
@endsection
