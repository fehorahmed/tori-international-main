@extends('layouts.app')
@section('title', 'Users Report')
@section('style')
    <style>
        .table-bordered thead td, .table-bordered thead th {
            vertical-align: middle;
            border: 1px solid #000!important;
            padding: 4px;
        }
        .table-bordered td, .table-bordered th {
            border: 1px solid #000000!important;
            vertical-align: middle;
            padding: 4px;
        }
        
        @page{
            margin: .3in .5in .5in .5in;
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    <a role="button" onclick="getprint('printArea')" class="btn btn-info bg-gradient-info btn-sm text-white">
                        <i class="fa fa-print"></i> Print Report
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive-md" id="printArea">
                        @include('layouts.partial.__report_header')
                        <div class="row">
                            <div class="col-12 text-center mb-3">
                                <h3 style="font-size: 25px"><u><b>Users Report</b></u></h3>
                            </div>
                        </div>

                        <table class="table table-bordered">
                            <thead>
                                <tr class="bg-light">
                                    <th class="text-center">S/L</th>
                                    <th class="text-center">Name</th>
                                    <th class="text-center">Username</th>
                                    <th class="text-center">Email</th>
                                    <th class="text-center">Mobile No.</th>
                                    <th class="text-center">Role</th>
                                    <th class="text-center">Tracking Status</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->username }}</td>
                                        <td>{{ $user->email ?? 'N/A' }}</td>
                                        <td class="text-center">{{ $user->mobile_no ?? 'N/A' }}</td>
                                        <td class="text-center">{{ $user->role }}</td>
                                        <td class="text-center">
                                            @if($user->is_tracking_enabled == 1)
                                                <span class="text-success font-weight-bold">Enabled</span>
                                            @else
                                                <span class="text-muted">Disabled</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($user->status == 1)
                                                <span class="text-success font-weight-bold">Active</span>
                                            @else
                                                <span class="text-danger font-weight-bold">Inactive</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-danger">No users found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        var APP_URL = '{!! url()->full()  !!}';
        function getprint(print) {
            document.title = "Users_Report_{{ date('d_m_Y') }}";
            $('body').html($('#' + print).html());
            window.print();
            window.location.replace(APP_URL)
        }
    </script>
@endsection
