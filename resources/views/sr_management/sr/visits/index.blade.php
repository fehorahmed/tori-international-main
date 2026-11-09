@extends('layouts.app')

@section('title', 'SR Visit List')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label for="sr_id">SR:</label>
                            @php
                                $isSR = auth()->user()->role == 'SR';
                                $currentSRId = $isSR ? auth()->user()->sr_id : '';
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
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>SR Name</th>
                                    <th>Customer</th>
                                    <th>Live Location</th>
                                    <th>Image</th>
                                    <th>Notes</th>
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
            let table = $('#table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('sr-visits.datatable') }}',
                    data: function (d) {
                        d.sr_id = $('#sr_id').val();
                    }
                },
                columns: [
                    {data: 'date', name: 'date'},
                    {data: 'sr_name', name: 'sr.name'},
                    {data: 'customer_name', name: 'supplier.name'},
                    {data: 'location', name: 'location'},
                    {data: 'image', name: 'image', orderable: false, searchable: false},
                    {data: 'notes', name: 'notes'},
                    {data: 'gps', name: 'gps', orderable: false, searchable: false},
                    {data: 'created_by', name: 'user.name', orderable: false, searchable: false},
                ],
                order: [[0, 'desc']]
            });

            $('.select2').select2();

            $('#sr_id').on('change', function() {
                table.draw();
            });
        });
    </script>
@endsection
