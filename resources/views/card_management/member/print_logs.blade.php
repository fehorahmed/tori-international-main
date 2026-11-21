@extends('layouts.app')
@section('title', 'Member Card Print Logs')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fa fa-history mr-2"></i> Member Card Print History Logs
                    </h5>
                    <div class="card-tools">
                        <a href="{{ route('card-management.member.index') }}" class="btn btn-secondary bg-gradient-secondary btn-sm">
                            <i class="fa fa-arrow-left mr-1"></i> Back to Dashboard
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form id="print-log-filters" class="row mb-3">
                        <div class="col-md-4 mb-2">
                            <label for="start_date">Start Date (Printed At)</label>
                            <input type="date" id="start_date" class="form-control">
                        </div>
                        <div class="col-md-4 mb-2">
                            <label for="end_date">End Date (Printed At)</label>
                            <input type="date" id="end_date" class="form-control">
                        </div>
                        <div class="col-md-4 mb-2 align-self-end">
                            <button type="submit" class="btn btn-primary">Search</button>
                            <button type="reset" class="btn btn-secondary">Reset</button>
                        </div>
                    </form>
                    <div id="print-log-error" class="alert alert-danger d-none" role="alert"></div>
                    <div class="table-responsive">
                        <table id="logsTable" class="table table-bordered table-striped" style="width: 100%;">
                            <thead>
                            <tr>
                                <th>Card No</th>
                                <th>Holder Name</th>
                                <th>Holder Type</th>
                                <th>Reason</th>
                                <th>Printed By</th>
                                <th>Printed At</th>
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
            let filters = {};
            const table = $('#logsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('card-management.member.logs.datatable') }}',
                    data: function (data) { Object.assign(data, filters); }
                },
                "pagingType": "full_numbers",
                "lengthMenu": [[10, 25, 50, -1],[10, 25, 50, "All"]],
                columns: [
                    {data: 'card_number', name: 'card.card_number'},
                    {data: 'holder_name', name: 'card.cardable.name', orderable: false},
                    {data: 'holder_type', name: 'holder_type', orderable: false, searchable: false},
                    {
                        data: 'print_reason', 
                        name: 'print_reason',
                        render: function(data) {
                            let badges = {
                                'First Issue': 'badge-success',
                                'Lost Replacement': 'badge-danger',
                                'Damaged Replacement': 'badge-warning',
                                'Renewal': 'badge-info'
                            };
                            let badge = badges[data] || 'badge-light';
                            return '<span class="badge ' + badge + '">' + data + '</span>';
                        }
                    },
                    {data: 'printed_by_name', name: 'printer.name'},
                    {data: 'printed_at', name: 'printed_at'},
                ],
                "dom": 'lBfrtip',
                "buttons": datatableButtons(),
                "responsive": true, "autoWidth": false,"colReorder": true,
            });
            $('#print-log-filters').on('submit', function (event) {
                event.preventDefault();
                const start = $('#start_date').val(), end = $('#end_date').val();
                $('#print-log-error').addClass('d-none').empty();
                if (start && end && end < start) {
                    $('#print-log-error').text('End date must be on or after start date.').removeClass('d-none');
                    return;
                }
                filters = {start_date:start, end_date:end};
                table.ajax.reload();
            });
            $('#print-log-filters').on('reset', function () {
                filters = {};
                $('#print-log-error').addClass('d-none').empty();
                table.search('').columns().search('');
                table.ajax.reload();
            });
        });
    </script>
@endsection
