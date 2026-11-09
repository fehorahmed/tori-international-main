@extends('layouts.app')
@section('title','Notifications')
@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-outline card-default">
            <div class="card-header">
                <a href="{{ route('notification_mark_read') }}" class="btn btn-info bg-gradient-info">Mark All Read</a>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
                <div class="table-responsive-sm">
                    <table id="table" class="table table-bordered">
                        <thead>
                        <tr>
                            <th>Title</th>
                            <th>Content</th>
                            <th>Created At</th>
                        </tr>
                        </thead>

{{--                        <tbody>--}}
{{--                        @foreach (auth()->user()->notifications as $notification)--}}
{{--                            <tr>--}}
{{--                                <td style="background: {{ $notification->read_at ? '#fff' : '#F2F2F2' }}"><i class="{{ $notification->read_at ? ' ' : 'fa fa-solid fa-circle text-success' }}"></i>  {{ $notification->data['title'] }}</td>--}}
{{--                                <td style="background: {{ $notification->read_at ? '#fff' : '#F2F2F2' }}"><a href="{{ $notification->data['url'] }}">{!! $notification->data['content'] !!}</a></td>--}}
{{--                                <td style="background: {{ $notification->read_at ? '#fff' : '#F2F2F2' }}">{{ $notification->created_at->diffForHumans() }}</td>--}}
{{--                            </tr>--}}
{{--                        @endforeach--}}
{{--                        </tbody>--}}
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
            $(function () {
                $("#table").DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: '{{ route('notification.datatable') }}',
                    "pagingType": "full_numbers",
                    "lengthMenu": [[10, 25, 50, -1],[10, 25, 50, "All"]
                    ],
                    columns: [
                        { data: 'title', name: 'title' },
                        { data: 'content', name: 'content' },
                        { data: 'created_at', name: 'created_at' }
                    ],
                    "dom": 'lBfrtip',
                    "buttons": datatableButtons(),
                    "responsive": true, "autoWidth": false,"colReorder": true,

                });
            });
        });
    </script>
@endsection
