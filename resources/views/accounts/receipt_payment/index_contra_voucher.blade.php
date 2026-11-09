@extends('layouts.app')
@section('title')
    {{ $voucherTitle }}
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                @if(auth()->user()->can($permissionCreate))
                    <div class="card-header">
                        <a href="{{ route('voucher.create', ['voucher_type' => $voucherType]) }}" class="btn btn-info bg-gradient-info">Create {{ $voucherTitle }}</a>
                    </div>
                @endif
                <div class="card-header">
                    <div class="card-title">Filter</div>
                </div>
                <div class="card-header">
                    <div class="row">

                        <div class="col-12 col-md-3">
                            <div class="form-group">
                                <label for="start_date" class="col-form-label">Start Date <span
                                        class="text-danger">*</span></label>
                                <input required autocomplete="off" type="text" value="{{ request('start_date', auth()->user()->business_date ? \Carbon\Carbon::parse(auth()->user()->business_date)->format('d-m-Y') : '') }}"
                                       name="start_date" class="form-control date-picker" id="start_date"
                                       placeholder="Enter Start Date">
                            </div>
                        </div>
                        <div class="col-12 col-md-3">
                            <div class="form-group">
                                <label for="end_date" class="col-form-label">End Date <span
                                        class="text-danger">*</span></label>
                                <input required autocomplete="off" type="text" value="{{ request('end_date', auth()->user()->business_date ? \Carbon\Carbon::parse(auth()->user()->business_date)->format('d-m-Y') : '') }}"
                                       name="end_date" class="form-control date-picker" id="end_date"
                                       placeholder="Enter End Date">
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
                                <th class="text-center">Voucher No.</th>
                                <th class="text-center">Voucher SL</th>
                                <th class="text-center">Date</th>
                                <th class="text-center">Debit Account Head</th>
                                <th class="text-center">Credit Account Head</th>
                                <th class="text-center">Debit Amount</th>
                                <th class="text-center">Credit Amount</th>
                                <th class="text-center">Notes</th>
                                <th class="text-center">Action</th>
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
                    url: "{{ route('voucher.datatable') }}",
                    data: function (d) {
                        d.sister_concern = $('#sister_concern').val()
                        d.voucher_type = '{{ request('voucher_type') }}'
                        d.purchase_order_id = '{{ request('purchase_order_id') }}'
                        d.sale_order_id = '{{ request('sale_order_id') }}'
                        d.start_date = $("#start_date").val()
                        d.end_date = $("#end_date").val()
                    }
                },
                "pagingType": "full_numbers",
                "lengthMenu": [[10, 25, 50, -1],[10, 25, 50, "All"]
                ],
                columns: [
                    {data: 'voucher_no', name: 'voucher_no',className:'text-center'},
                    {data: 'voucher_no_group_sl', name: 'voucher_no_group_sl',visible:false},
                    {data: 'date', name: 'date',className:'text-center'},
                    {data: 'debit_account_heads', name: 'debit_account_heads',orderable:false,searchable:false},
                    {data: 'credit_account_heads', name: 'credit_account_heads',orderable:false,searchable:false},
                    {data: 'debit_amounts', name: 'debit_amounts',orderable:false,searchable:false,className:'text-right'},
                    {data: 'debit_amounts', name: 'debit_amounts',orderable:false,searchable:false,className:'text-right'},
                    {data: 'notes', name: 'notes'},
                    {data: 'action', name: 'action', orderable: false},
                ],
                order: [[1, 'desc']],
                "dom": 'lBfrtip',
                "buttons": datatableButtons(),
                "responsive": true, "autoWidth": false,"colReorder": true,
            });
            $('#start_date,#end_date,#sister_concern,#search_btn').change(function () {
                table.ajax.reload();
                let start_date = $("#start_date").val();
                let end_date = $("#end_date").val();
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
                            url: "{{ route('voucher.destroy', ['voucher' => 'REPLACE_WITH_ID_HERE']) }}".replace('REPLACE_WITH_ID_HERE', id),
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
