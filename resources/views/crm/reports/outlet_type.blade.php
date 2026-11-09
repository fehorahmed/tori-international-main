@extends('layouts.app')
@section('title','Outlet Type Report')
@section('style')
    <style>
        .table-bordered thead td, .table-bordered thead th {
            vertical-align: middle;
            border: 1px solid #000!important;
            padding: 5px;
        }
        .table-bordered td, .table-bordered th {
            border: 1px solid #000000!important;
            vertical-align: middle;
            padding: 5px;
        }
        .table th, .table td {
            font-size: 13px;
            padding: 0.4rem;
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
                <div class="card-header border-bottom-0">
                    @include('layouts.partial.__export_buttons', ['printAreaId' => 'printArea', 'fileName' => 'Outlet_Type_Report_' . date('d_m_Y')])
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive-md" id="printArea">
                        <div class="row header-common-info" style="border-bottom: 1.5px solid #000;">
                            <div class="col-4">
                                <h1 class="text-left m-0" style="font-size: 45px !important;font-weight: bold">
                                    <img height="110px" src="{{ asset('img/logo.png') }}" alt="">
                                </h1>
                            </div>
                            <div class="col-8 text-right">
                                <h2 class="m-0"><b>{{ config('app.name') }}</b></h2>
                                <h5 class="m-0">{{ config('app.address') }}</h5>
                                <h5 class="m-0">{{ config('app.contact') }}</h5>
                                <h5>{{ config('app.email') }}</h5>
                            </div>
                        </div>
                        <div class="row header-common-info mt-3">
                            <div class="col-12">
                                <h3 class="text-center" style="font-size: 25px"><u><b>Outlet Type Report</b></u></h3>
                            </div>
                        </div>

                        <table class="table table-bordered mt-3">
                            <thead>
                            <tr>
                                <th class="text-center" style="width: 10%">S/L</th>
                                <th class="text-left">Outlet Type Name</th>
                                <th class="text-center" style="width: 20%">Status</th>
                            </tr>
                            </thead>
                            <tbody>
                                @foreach($outletTypes as $type)
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td class="text-left">{{ $type->name }}</td>
                                        <td class="text-center">
                                            @if($type->status == 1)
                                                Active
                                            @else
                                                Inactive
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    @include('layouts.partial.__export_scripts')
@endsection
