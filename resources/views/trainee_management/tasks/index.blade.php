@extends('layouts.app')
@section('title','Trainee Tasks')
@section('style')
    <style>
        .datepicker-dropdown { padding: 10px !important; }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-tasks mr-2"></i> Trainee Task Assignments</h3>
                    <div class="card-tools">
                        <a href="{{ route('trainee.tasks.distribute') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-share-alt mr-1"></i> Distribute Monthly Targets
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Filter by Month</label>
                                <div class="input-group">
                                <input type="text" class="form-control month-picker" id="filter_month" value="{{ $date ? \Carbon\Carbon::parse($date)->format('m-Y') : '' }}" placeholder="All Months" readonly>
                                    <div class="input-group-append">
                                        <button class="btn btn-default" type="button" id="btn_clear_date"><i class="fa fa-times"></i></button>
                                        <button class="btn btn-info" id="btn_filter"><i class="fa fa-filter"></i> Filter</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="table" class="table table-bordered table-striped w-100">
                            <thead>
                            <tr>
                                <th>SL</th>
                                <th>Month</th>
                                <th>Trainee Name</th>
                                <th>Task Count</th>
                                <th>Tasks Summary</th>
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
            $('#filter_month').MonthPicker({
                Button: false,
                MonthFormat: 'mm-yy',
                OnAfterChooseMonth: function() {
                    table.draw();
                }
            });

            var table = $('#table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('trainee.tasks.datatable') }}',
                    data: function (d) {
                        d.date = $('#filter_month').val();
                    }
                },
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                    {data: 'date', name: 'date'},
                    {data: 'user_name', name: 'user_name'},
                    {data: 'task_count', name: 'task_count', className: 'text-center'},
                    {data: 'tasks_summary', name: 'tasks_summary'},
                    {data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center'},
                ]
            });

            $('#btn_filter').click(function(){
                table.draw();
            });

            $('#btn_clear_date').click(function() {
                $('#filter_month').val('');
                table.draw();
            });
        });
    </script>
@endsection
