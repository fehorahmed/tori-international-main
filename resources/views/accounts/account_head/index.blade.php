@extends('layouts.app')
@section('title')
    {{ $accountHeadTitle }}
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    @if(auth()->user()->can($permissionCreate))
                    <a href="{{ route('account-head.create',['payment_mode'=>$paymentMode]) }}" class="btn btn-info bg-gradient-info btn-sm">Create {{ $accountHeadTitle }} <i class="fa fa-plus"></i></a>
                    @endif
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table" class="table table-bordered">
                            <thead>
                            <tr>
                                <th>S/L</th>
                                <th>Account Group</th>
                                <th>Name</th>
                                <th>Bank Commission</th>
                                <th>Opening Balance</th>
                                <th>Current Balance</th>
                                <th>Code</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
                <!-- /.card-body -->
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        $(function () {

           let table = $('#table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('account-head.datatable') }}",
                    data: function (d) {
                        d.sister_concern = $('#sister_concern').val()
                        d.payment_mode = '{{ request('payment_mode') }}'
                    }
                },
                "pagingType": "full_numbers",
                "lengthMenu": [[10, 25, 50, -1],[10, 25, 50, "All"]
                ],
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                    {data: 'account_group_name', name: 'accountGroup.name'},
                    {data: 'name', name: 'name'},
                    {data: 'bank_commission_percent', name: 'bank_commission_percent',
                        render: function(data) {
                            return data > 0 ? jsNumberFormat(parseFloat(data).toFixed(1))+'%' : '';
                        }
                    },
                    {data: 'opening_balance', name: 'opening_balance', searchable: false, orderable: false,
                        render: function(data) {
                            return jsNumberFormat(parseFloat(data).toFixed(2));
                        }
                    },
                    {data: 'current_balance', name: 'current_balance', searchable: false, orderable: false,
                        render: function(data) {
                            return jsNumberFormat(parseFloat(data).toFixed(2));
                        }
                    },
                    {data: 'code', name: 'code'},
                    {data: 'action', name: 'action', orderable: false},
                ],
                "dom": 'lBfrtip',
               "buttons": datatableButtons(),
                "responsive": true, "autoWidth": false,"colReorder": true,
            });

            if ({{ request('payment_mode')}} == 1) {
                table.column(3).visible(true); // Show Age column (index 2)
            } else {
                table.column(3).visible(false); // Hide Age column (index 2)
            }
            $('#sister_concern').change(function(){
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
                    confirmButtonText: 'Yes, Delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        preloaderToggle(true);
                        $.ajax({
                            method: "DELETE",
                            url: "{{ route('account-head.destroy', ['account_head' => 'REPLACE_WITH_ID_HERE']) }}".replace('REPLACE_WITH_ID_HERE', id),
                            data: { id: id }
                        }).done(function( response ) {
                            preloaderToggle(false);
                            if (response.success) {
                                Swal.fire(
                                    'Deleted!',
                                    response.message,
                                    'success'
                                ).then((result) => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Oops...',
                                    text: response.message,
                                });
                            }
                        });

                    }
                })

            });
        });
    </script>
@endsection
