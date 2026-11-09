@extends('layouts.app')
@section('title','Utilize Lists')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    @if (auth()->user()->can('product_utilize_create'))
                    <a href="{{ route('product-utilizes.create') }}" class="btn btn-info bg-gradient-info btn-sm">Add Product Utilize <i class="fa fa-plus"></i></a>
                    @endif
                </div>
                <div class="card-header">
                    <div class="row">
                        <div class="col-12 col-md-4">
                            <div class="form-group">
                                <label for="start_date" class="col-form-label">Start Date <span
                                        class="text-danger">*</span></label>
                                <input required autocomplete="off" type="text" value="{{ request('start_date') }}"
                                       name="start_date" class="form-control date-picker" id="start_date"
                                       placeholder="Enter Start Date">
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="form-group">
                                <label for="end_date" class="col-form-label">End Date <span
                                        class="text-danger">*</span></label>
                                <input required autocomplete="off" type="text" value="{{ request('end_date') }}"
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
                                <th class="text-center">ID</th>
                                <th class="text-center">Order No.</th>
                                <th class="text-center">Date</th>
                                <th class="text-center">Costing Total</th>
                                <th class="text-center">Notes</th>
                                <th class="text-center">Action</th>
                            </tr>
                            </thead>
                            <tfoot>
                            <tr>
                                <th colspan="3" class="text-center">Total</th>
                                <th class="text-right"></th>
                                <th colspan="2"></th>
                            </tr>
                            </tfoot>
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
                            url: "{{ route('product-utilizes.destroy', ['product_utilize' => 'REPLACE_WITH_ID_HERE']) }}".replace('REPLACE_WITH_ID_HERE', id),
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


            var table = $('#table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('product-utilizes.datatable') }}",
                    data: function (d) {
                        d.start_date = $("#start_date").val()
                        d.end_date = $("#end_date").val()
                    }
                },
                "pagingType": "full_numbers",
                "lengthMenu": [[10, 25, 50, -1],[10, 25, 50, "All"]
                ],
                columns: [
                    {data: 'id', name: 'id',visible:false},
                    {data: 'order_no', name: 'order_no'},
                    {data: 'date', name: 'date'},
                    {
                        data: 'total',
                        name: 'total',
                        render: function (data, type, row) {
                            if (row.total > 0) {
                                var product_utilize_id = row.id; // Assuming the type is in the 'voucher_type' field of your data
                                var voucherRoute = getJvVoucherRoute(product_utilize_id);
                                return '<a href="' + voucherRoute + '">' + jsNumberFormat(parseFloat(row.total).toFixed(2)) + '</a>';
                            } else {
                                return jsNumberFormat(parseFloat(row.total).toFixed(2));
                            }
                        },className:'text-right'
                    },
                    {data: 'notes', name: 'notes'},
                    {data: 'action', name: 'action', orderable: false},
                ],
                order: [[1, 'desc']],
                "dom": 'lBfrtip',
                "buttons": datatableButtons(),
                "responsive": true, "autoWidth": false,"colReorder": true,
                footerCallback: function (row, data, start, end, display) {
                    var api = this.api();

                    // Helper function to sum the column values
                    var intVal = function (i) {
                        return typeof i === 'string' ?
                            i.replace(/[\$,]/g, '') * 1 :
                            typeof i === 'number' ?
                                i : 0;
                    };

                    // Total over this page for each column
                    var pageTotalTotal = api.column(3, { page: 'current' }).data().reduce(function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);
                    // Update footer
                    $(api.column(3).footer()).html(jsNumberFormat(pageTotalTotal.toFixed(2)));
                }
            });
            $('#start_date,#end_date,#search_btn').change(function () {
                table.ajax.reload();
            });
        });
        function getJvVoucherRoute(product_utilize_id) {
            return "{{ route('voucher.index', ['voucher_type'=>\App\Enumeration\VoucherType::$JOURNAL_VOUCHER,'product_utilize_id'=>'REPLACE_WITH_ID_HERE']) }}".replace('REPLACE_WITH_ID_HERE', product_utilize_id);
        }
    </script>
@endsection
