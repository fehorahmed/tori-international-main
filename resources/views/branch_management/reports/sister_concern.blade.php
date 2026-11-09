@extends('layouts.app')
@section('title', 'Branch Report')
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
                    @include('layouts.partial.__export_buttons', ['printAreaId' => 'printArea', 'fileName' => 'Branch_Report_' . date('d_m_Y')])
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive-md" id="printArea">
                        @include('layouts.partial.__report_header')
                        <div class="row">
                            <div class="col-12 text-center mb-3">
                                <h3 style="font-size: 25px"><u><b>Branch Report</b></u></h3>
                            </div>
                        </div>

                        <table class="table table-bordered">
                            <thead>
                                <tr class="bg-light">
                                    <th class="text-center">S/L</th>
                                    <th class="text-center">Branch Name</th>
                                    <th class="text-center">Code</th>
                                    <th class="text-center">Contact</th>
                                    <th class="text-center">Email</th>
                                    <th class="text-center">Business Date</th>
                                    <th class="text-center">Area Manager</th>
                                    <th class="text-center">Branch Manager</th>
                                    <th class="text-center">Address</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($branches as $branch)
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td><a href="{{ route('sister-concerns.show', ['sister_concern' => $branch->id]) }}" target="_blank" class="">{{ $branch->name }}</a></td>
                                        <td class="text-center">{{ $branch->code }}</td>
                                        <td class="text-center">{{ $branch->contact ?? 'N/A' }}</td>
                                        <td>{{ $branch->email ?? 'N/A' }}</td>
                                        <td class="text-center">{{ date('d-m-Y', strtotime($branch->business_date)) }}</td>
                                        <td>{{ $branch->areaManager->name ?? 'N/A' }}</td>
                                        <td>{{ $branch->branchManager->name ?? 'N/A' }}</td>
                                        <td>{{ $branch->address ?? 'N/A' }}</td>
                                        <td class="text-center">
                                            @if($branch->status == 1)
                                                <span class="text-success font-weight-bold">Active</span>
                                            @else
                                                <span class="text-danger font-weight-bold">Inactive</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center text-danger">No branches found</td>
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
    @include('layouts.partial.__export_scripts')
@endsection
