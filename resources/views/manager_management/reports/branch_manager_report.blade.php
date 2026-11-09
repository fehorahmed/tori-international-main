@extends('layouts.app')
@section('title', 'Branch Manager Report')
@section('style')
    <style>
        .table-bordered td, .table-bordered th {
            border: 1px solid #000000 !important;
            padding: 2px;
        }
        .table thead th {
            vertical-align: bottom;
            border-bottom: 2px solid #000000 !important;
        }
        .table-bordered td, .table-bordered th {
            font-size: 18px !important;
        }
        @media print {
            .table-bordered td, .table-bordered th {
                font-size: 18px !important;
            }
        }
    </style>
@endsection
@section('content')
    <div class="row">

        
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    <a role="button" onclick="getprint('printArea')" class="btn btn-primary bg-gradient-primary btn-sm"><i class="fa fa-print"></i> Print</a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12" id="printArea">
                            @include('layouts.partial.__report_header')
                            <div class="row header-common-info">
                                <div class="col-12">
                                    <h3 class="text-center" style="font-size: 25px; margin-top: 10px;"><u><b>Branch Manager Report</b></u></h3>
                                </div>
                            </div>
                            <table class="table table-bordered">
                                <thead>
                                <tr>
                                    <th class="text-center" style="width: 5%">S/L</th>
                                    <th>Name</th>
                                    <th>Employee ID</th>
                                    <th>Mobile No</th>
                                    <th>Email</th>
                                    <th>Address</th>
                                </tr>
                                </thead>
                                <tbody>
                                    @forelse($managers as $manager)
                                        <tr>
                                            <td class="text-center">{{ $loop->iteration }}</td>
                                            <td>{{ $manager->name }}</td>
                                            <td>{{ $manager->id_no ?? '' }}</td>
                                            <td>{{ $manager->mobile_no }}</td>
                                            <td>{{ $manager->email }}</td>
                                            <td>{{ $manager->address }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center">No branch managers found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
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
            document.title = "Branch_Manager_Report";
            $(".extra-remove").remove();
            $(".footer-print-area").show();
            $('.print-heading').css('display', 'block');
            $('body').html($('#' + print).html());
            window.print();
            window.location.replace(APP_URL)
        }
    </script>
@endsection
