@extends('layouts.app')
@section('title','Salary List')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    <div class="card-title">Data Filter</div>
                </div>
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="year">Year</label>
                                <select name="year" id="year" class="form-control select2">
                                    <option value="">All</option>
                                    @for($i=2024;$i <= date('Y');$i++)
                                        <option value="{{ $i }}">{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="month">Month</label>
                                <select  name="month" id="month" class="form-control select2">
                                    <option value="">All</option>
                                    @for ($monthIndex = 1; $monthIndex <= 12; $monthIndex++)
                                        <option value="{{ $monthIndex }}">{{ numberToMonthName($monthIndex) }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <input type="submit" name="search" class="btn btn-info bg-gradient-info form-control" value="Search">
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table" class="table table-bordered">
                            <thead>
                            <tr>
                                <th>S/L</th>
                                <th>Year</th>
                                <th>Month</th>
                                <th>Date</th>
                                <th>Total Salary</th>
                                <th>Total Deduction</th>
                                <th>Net Pay</th>
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
                    url: '{{ route('salary-processes.datatable') }}',
                    data: function (d) {
                        d.year = $("#year").val()
                        d.month = $("#month").val()
                    }
                },
                "pagingType": "full_numbers",
                "lengthMenu": [[10, 25, 50, -1],[10, 25, 50, "All"]
                ],
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false,className:'text-center'},
                    {data: 'year', name: 'year'},
                    {data: 'month', name: 'month'},
                    {data: 'process_date', name: 'process_date'},
                    {data: 'gross', name: 'gross'},
                    {data: 'total_deduction', name: 'total_deduction'},
                    {data: 'net_pay', name: 'net_pay'},
                    {data: 'action', name: 'action', orderable: false,className:'text-center'},
                ],
                "dom": 'lBfrtip',
                "buttons": datatableButtons(),
                "responsive": true, "autoWidth": false,"colReorder": true,
            });
            $('#year,#month,#department').change(function () {
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
                            url: "{{ route('salary-processes.destroy', ['salary_process' => 'REPLACE_WITH_ID_HERE']) }}".replace('REPLACE_WITH_ID_HERE', id),
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
