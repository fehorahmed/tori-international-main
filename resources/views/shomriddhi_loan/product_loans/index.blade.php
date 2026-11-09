@extends('layouts.app')
@section('title','EMI Sales Orders')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-shopping-cart mr-2"></i> Product Loans (EMI Sales)
                    </h5>
                    <div class="card-tools">
                        <a href="{{ route('shomriddhi-loan.product-loans.create') }}" class="btn btn-sm btn-success">
                            <i class="fa fa-plus"></i> Create EMI Sale
                        </a>
                    </div>
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
                                <input required autocomplete="off" type="text" value="{{ request('start_date') }}"
                                       name="start_date" class="form-control date-picker" id="start_date"
                                       placeholder="Start Date">
                            </div>
                        </div>
                        <div class="col-12 col-md-2">
                            <div class="form-group">
                                <label for="end_date" class="col-form-label">End Date</label>
                                <input required autocomplete="off" type="text" value="{{ request('end_date') }}"
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
                                <th class="text-center">Down Payment</th>
                                <th class="text-center">Due</th>
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
                    url: "{{ route('shomriddhi-loan.product-loans.datatable') }}",
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
                    {data: 'due', name: 'due', className:'text-right'},
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
        });
    </script>
@endsection
