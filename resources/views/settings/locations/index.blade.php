@extends('layouts.app')
@section('title', $config['title'] . ' List')

@section('content')

@include('settings.locations._tabs')

<div class="row">
    <div class="col-12">
        <div class="card card-default">
            <div class="card-header">
                <a href="{{ route('locations.create', $type) }}" class="btn btn-info bg-gradient-info">Create {{ $config['title'] }}</a>
            </div>
            
            @if(count($config['parents']) > 0)
            <div class="card-header bg-light">
                <div class="row">
                    @foreach($config['parents'] as $index => $parent)
                    <div class="col-md-3">
                        <div class="form-group mb-0">
                            <label for="filter_{{ $parent['key'] }}">{{ $parent['title'] }}</label>
                            <select id="filter_{{ $parent['key'] }}" name="{{ $parent['key'] }}" class="form-control form-control-sm select2 filter-select">
                                <option value="">All {{ $parent['title'] }}</option> 
                                @foreach($parentsData[$parent['key']] as $pData)
                                    <option value="{{ $pData->id }}">{{ $pData->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- /.card-header -->
            <div class="card-body">
                <div class="table-responsive-sm">
                    <table class="table table-bordered" id="locations-table">
                        <thead>
                            <tr>
                                <th>S/L</th>
                                <th>Name (English)</th>
                                <th>Name (Bangla)</th>
                                @foreach($config['parents'] as $parent)
                                <th>{{ $parent['title'] }}</th>
                                @endforeach
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
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
    $(document).ready(function() {
        var table = $('#locations-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('locations.datatable', $type) }}",
                data: function (d) {
                    @foreach($config['parents'] as $parent)
                    d.{{ $parent['key'] }} = $('#filter_{{ $parent['key'] }}').val();
                    @endforeach
                }
            },
            "pagingType": "full_numbers",
            "lengthMenu": [[10, 25, 50, -1],[10, 25, 50, "All"]],
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                {data: 'name', name: 'name'},
                {data: 'bn_name', name: 'bn_name', defaultContent: '-'},
                @foreach($config['parents'] as $parent)
                    @php 
                        $relation = str_replace('_id', '', $parent['key']);
                    @endphp
                    {
                        data: '{{ $relation }}.name', 
                        name: '{{ $relation }}.name', 
                        defaultContent: '-',
                        orderable: false
                    },
                @endforeach
                {data: 'action', name: 'action', orderable: false},
            ],
            "dom": 'lBfrtip',
            "buttons": datatableButtons(),
            "responsive": true, "autoWidth": false,"colReorder": true,
        });

        // Trigger datatable reload when any filter changes
        $('.filter-select').on('change', function() {
            table.ajax.reload();
        });

        @if($type === 'villages')
        // Dynamic: when Upazila changes, load Unions for that upazila
        $('#filter_upazila_id').on('change', function() {
            var upazilaId = $(this).val();
            var $unionSelect = $('#filter_union_id');

            $unionSelect.empty().append('<option value="">All Union</option>');

            if (upazilaId) {
                $.ajax({
                    url: '{{ route("locations.unions_by_upazila") }}',
                    data: { upazila_id: upazilaId },
                    success: function(data) {
                        $.each(data, function(i, union) {
                            $unionSelect.append('<option value="' + union.id + '">' + union.name + '</option>');
                        });
                        if ($.fn.select2) {
                            $unionSelect.trigger('change.select2');
                        }
                    }
                });
            }

            table.ajax.reload();
        });
        @endif
    });
</script>
@endsection
