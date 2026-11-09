@extends('layouts.app')
@section('title', 'Contractor Payments')



@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">Filter Contractor Payments</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="contractor_id">Contractor</label>
                                <select name="contractor_id" id="contractor_id" class="form-control select2">
                                    <option value="">All Contractors</option>
                                    @foreach($contractors as $contractor)
                                        <option value="{{ $contractor->id }}">{{ $contractor->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-md-3">
                            <div class="form-group">
                                <label for="start_date" class="col-form-label">Start Date</label>
                                <input required autocomplete="off" type="text" value="{{ request('start_date', auth()->user()->business_date ? \Carbon\Carbon::parse(auth()->user()->business_date)->format('d-m-Y') : '') }}" name="start_date" class="form-control date-picker" id="start_date" placeholder="Start Date">
                            </div>
                        </div>
                        <div class="col-12 col-md-3">
                            <div class="form-group">
                                <label for="end_date" class="col-form-label">End Date</label>
                                <input required autocomplete="off" type="text" value="{{ request('end_date', auth()->user()->business_date ? \Carbon\Carbon::parse(auth()->user()->business_date)->format('d-m-Y') : '') }}" name="end_date" class="form-control date-picker" id="end_date" placeholder="End Date">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card card-default">
                <div class="card-header">
                    <a href="{{ route('contractor_management.payments.create') }}" class="btn btn-info bg-gradient-info btn-sm">Create New Payment / Advance <i class="fa fa-plus"></i></a>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <table id="table" class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th>S/L</th>
                            <th>Date</th>
                            <th>Receipt No</th>
                            <th>Type</th>
                            <th>Contractor</th>
                            <th>Payment Method</th>
                            <th>Amount</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tfoot>
                        <tr>
                            <th colspan="6" class="text-right">Total:</th>
                            <th class="text-left" id="total_amount">0.00</th>
                            <th></th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
                <!-- /.card-body -->
            </div>
            <!-- /.card -->
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
                    url: '{{ route('contractor_management.payments.datatable') }}',
                    data: function (d) {
                        d.contractor_id = $('#contractor_id').val();
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();
                    }
                },
                "pagingType": "full_numbers",
                "lengthMenu": [[10, 25, 50, -1],[10, 25, 50, "All"]],
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                    {data: 'payment_date', name: 'payment_date'},
                    {data: 'receipt_no', name: 'receipt_no'},
                    {data: 'payment_type_text', name: 'payment_type_text'},
                    {data: 'contractor_name', name: 'contractor.name'},
                    {data: 'payment_method_name', name: 'paymentMethod.name'},
                    {data: 'amount', name: 'amount', render: function(data){ return jsNumberFormat(parseFloat(data).toFixed(2)); }},
                    {data: 'action', name: 'action', orderable: false, searchable: false},
                ],
                "dom": 'lBfrtip',
                "buttons": datatableButtons(),
                "responsive": true, "autoWidth": false,"colReorder": true,
                "footerCallback": function ( row, data, start, end, display ) {
                    var api = this.api(), data;

                    var intVal = function ( i ) {
                        return typeof i === 'string' ?
                            i.replace(/[\$,]/g, '')*1 :
                            typeof i === 'number' ?
                                i : 0;
                    };

                    total = api
                        .column( 6 )
                        .data()
                        .reduce( function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0 );

                    $( api.column( 6 ).footer() ).html(
                        jsNumberFormat(total)
                    );
                }
            });

            $('#contractor_id, #start_date, #end_date').on('change', function () {
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
                            url: "{{ route('contractor_management.payments.destroy', ':id') }}".replace(':id', id),
                            data: { _token: "{{ csrf_token() }}" },
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
                });
            });
        });
    </script>
@endsection
