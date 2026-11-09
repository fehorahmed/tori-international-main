@extends('layouts.app')
@section('title', $context['child_label'] . ' Task Assignments')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">{{ str_replace('_', ' ', $context['child_label']) }} Management</h3>
                    <div class="card-tools">
                        <a href="{{ route('tasks.distribute', $role) }}" class="btn btn-success bg-gradient-success btn-sm">
                            Distribute Sales Targets <i class="fa fa-share-alt"></i>
                        </a>
                    </div>
                </div>
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-3">
                            <label for="date" class="mr-2">Month:</label>
                            <div class="input-group input-group-sm">
                                <input type="text" name="date" id="date" class="form-control month-picker1" value="{{ $date ? \Carbon\Carbon::parse($date)->format('m-Y') : '' }}" placeholder="All Months" readonly>
                                <div class="input-group-append">
                                    <button class="btn btn-default" type="button" id="btn-clear-date"><i class="fa fa-times"></i></button>
                                </div>
                            </div>
                        </div>
                        {{-- Add a search/filter if needed for larger lists --}}
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table" class="table table-bordered table-striped">
                            <thead>
                            <tr>
                                <th style="width: 50px;">S/L</th>
                                <th>Month</th>
                                <th>{{ str_replace('_', ' ', $context['child_label']) }} Name</th>
                                <th>Task Count</th>
                                <th>Tasks & Targets</th>
                                <th style="width: 100px;">Action</th>
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
            let table = $('#table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('tasks.datatable', $role) }}',
                    data: function (d) {
                        d.date = $('#date').val();
                    }
                },
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                    {data: 'date', name: 'date'},
                    {data: 'user_name', name: 'user_name'},
                    {data: 'task_count', name: 'task_count', orderable: false, searchable: false},
                    {data: 'tasks_summary', name: 'tasks_summary', orderable: false, searchable: false},
                    {data: 'action', name: 'action', orderable: false, searchable: false},
                ],
                "dom": 'lBfrtip',
                "buttons": datatableButtons(),
                "responsive": true, "autoWidth": false,
            });

            $('.month-picker1').MonthPicker({
                Button: false,
                MonthFormat: 'mm-yy',
                OnAfterChooseMonth: function() {
                    table.draw();
                }
            });

            $('#btn-clear-date').click(function() {
                $('#date').val('');
                table.draw();
            });
        });
    </script>
@endsection
