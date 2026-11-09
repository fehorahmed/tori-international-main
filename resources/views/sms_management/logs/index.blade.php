@extends('layouts.app')
@section('title', 'SMS Logs')

@section('style')
    <link rel="stylesheet" href="{{ asset('themes/backend/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('themes/backend/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <style>
        .message-cell {
            max-width: 300px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-default">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-history text-secondary mr-2"></i> SMS Delivery Logs</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped w-100" id="logs-table">
                        <thead>
                            <tr>
                                <th>SL</th>
                                <th>Mobile No</th>
                                <th>Message</th>
                                <th>Event Trigger</th>
                                <th>Status</th>
                                <th>Gateway Response</th>
                                <th>Sent At</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(function () {
            $('#logs-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('sms_management.logs.data') }}',
                "pagingType": "full_numbers",
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                columns: [
                    {data: 'DT_RowIndex', name: 'id', searchable: false, orderable: false},
                    {data: 'mobile_no', name: 'mobile_no', className: 'font-weight-bold'},
                    {data: 'message', name: 'message', className: 'message-cell', render: function(data) {
                        return `<span title="${data}">${data}</span>`;
                    }},
                    {data: 'event_name', name: 'event_name', render: function(data) {
                        return `<span class="badge badge-info">${data || 'Custom/Manual'}</span>`;
                    }},
                    {data: 'status', name: 'status'},
                    {data: 'response_text', name: 'response_text'},
                    {data: 'sent_at', name: 'sent_at'}
                ],
                order: [[0, 'desc']],
                "dom": 'lBfrtip',
                "buttons": datatableButtons(),
                "responsive": true, 
                "autoWidth": false, 
                "colReorder": true,
            });
        });
    </script>
@endsection
