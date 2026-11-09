@extends('layouts.app')

@section('title')
    Social Engagements
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label for="sr_id">SR:</label>
                            @php
                                $isSR = auth()->user()->role == \App\Enumeration\RoleType::$SR;
                                $currentSRId = $isSR ? auth()->user()->employee_id : '';
                            @endphp
                            <select name="sr_id" id="sr_id" class="form-control form-control-sm select2" {{ $isSR ? 'disabled' : '' }}>
                                @if (!$isSR)
                                 <option value="">All SR</option>
                                @endif
                                @foreach($srs as $sr)
                                    <option value="{{ $sr->id }}" {{ $currentSRId == $sr->id ? 'selected' : '' }}>{{ $sr->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="platform">Platform:</label>
                            <select name="platform" id="platform" class="form-control form-control-sm select2">
                                <option value="">All Platforms</option>
                                <option value="youtube">YouTube</option>
                                <option value="facebook">Facebook</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="start_date">Start Date:</label>
                            <input type="text" name="start_date" id="start_date" class="form-control form-control-sm date-picker" autocomplete="off" placeholder="Start Date">
                        </div>
                        <div class="col-md-2">
                            <label for="end_date">End Date:</label>
                            <input type="text" name="end_date" id="end_date" class="form-control form-control-sm date-picker" autocomplete="off" placeholder="End Date">
                        </div>
                        <div class="col-md-2 text-right">
                            @if(auth()->user()->hasPermissionTo('sr_visits'))
                                <a href="{{ route('sr_social.create') }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                                    <i class="fa fa-plus-circle mr-1"></i> New Entry
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>SR Name</th>
                                    <th>Platform</th>
                                    <th>Customer Name</th>
                                    <th>Customer Phone</th>
                                    <th>Screenshot</th>
                                    <th>GPS</th>
                                    <th>Created By</th>
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
            var table = $('#table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('sr_social.datatable') }}",
                    data: function (d) {
                        d.sr_id = $('#sr_id').val();
                        d.platform = $('#platform').val();
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();
                    }
                },
                columns: [
                    {data: 'date', name: 'date'},
                    {data: 'sr_name', name: 'sr.name'},
                    {data: 'platform_badge', name: 'platform', orderable: false, searchable: false},
                    {data: 'customer_name', name: 'customer_name'},
                    {data: 'customer_phone', name: 'customer_phone'},
                    {data: 'screenshot', name: 'screenshot', orderable: false, searchable: false},
                    {data: 'gps', name: 'gps', orderable: false, searchable: false},
                    {data: 'created_by', name: 'user.name', orderable: false, searchable: false},
                ],
                order: [[0, 'desc']]
            });

            $('.select2').select2();

            $('#sr_id, #platform, #start_date, #end_date').on('change', function () {
                table.draw();
            });
        });
    </script>
@endsection
