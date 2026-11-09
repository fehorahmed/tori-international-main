@extends('layouts.app')
@section('title','Payment History')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">Payment History for <strong>{{ $supplier->name }}</strong></h3>
                    <a href="{{ route('purchase.supplier_payments_list') }}" class="btn btn-dark btn-sm float-right">Back</a>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="start_date" class="col-form-label">Start Date</label>
                                <input required autocomplete="off" type="text" value="{{ request('start_date', auth()->user()->business_date ? \Carbon\Carbon::parse(auth()->user()->business_date)->format('d-m-Y') : '') }}" name="start_date" class="form-control date-picker" id="start_date" placeholder="Start Date">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="end_date" class="col-form-label">End Date</label>
                                <input required autocomplete="off" type="text" value="{{ request('end_date', auth()->user()->business_date ? \Carbon\Carbon::parse(auth()->user()->business_date)->format('d-m-Y') : '') }}" name="end_date" class="form-control date-picker" id="end_date" placeholder="End Date">
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="table" class="table table-bordered" style="width: 100%;">
                            <thead>
                            <tr>
                                <th class="text-center">S/L</th>
                                <th class="text-center">Date</th>
                                <th class="text-center">Payment Type</th>
                                <th class="text-center">Payment Mode</th>
                                <th class="text-center">Amount</th>
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
                    url: "{{ route('purchase.supplier_payment_history.datatable') }}",
                    data: function (d) {
                        d.supplier_id = '{{ $supplier->id }}';
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();
                    }
                },
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                    {data: 'date', name: 'date'},
                    {data: 'payment_type', name: 'payment_type', orderable: false, searchable: false},
                    {data: 'payment_mode', name: 'payment_mode', orderable: false, searchable: false},
                    {data: 'amount', name: 'amount', className: 'text-right'},
                    {data: 'action', name: 'action', orderable: false, className: 'text-center'},
                ]
            });
            $('#start_date, #end_date').change(function() {
                table.ajax.reload();
            });
        });
    </script>
@endsection
