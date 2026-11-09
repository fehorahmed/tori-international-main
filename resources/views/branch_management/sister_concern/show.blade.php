<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $typeName }}</title>
    
    @include('layouts.partial.__styles')

    <style>
        @page {
            margin: .3in .5in .5in .5in;
        }
        body {
            background-color: #f4f6f9;
        }
        .report-container {
            background: #fff;
            padding: 1.5rem;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            min-height: 100vh;
        }
        .table th, .table td {
            font-size: 13px;
            padding: 0.4rem;
        }
        .table-bordered thead td, .table-bordered thead th {
            vertical-align: middle;
            border: 1px solid #000!important;
        }
        .table-bordered td, .table-bordered th {
            border: 1px solid #000000!important;
            vertical-align: middle;
        }
        
        @media print {
            .report-container {
                margin: 0;
                padding: 0;
                box-shadow: none;
            }
            .extra-remove {
                display: none !important;
            }
            body {
                background-color: #fff;
            }
            .table-responsive-md {
                overflow-x: visible !important;
            }
        }
    </style>
</head>
<body class="bg-light">

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="report-container">
                    @include('layouts.partial.__export_buttons', ['printAreaId' => 'printArea', 'fileName' => 'Branch_Details_' . date('d_m_Y')])
                    
                    <div class="table-responsive-md" id="printArea">
                        <div class="row mb-4 align-items-center" style="border-bottom: 2px solid #eee; padding-bottom: 20px;">
                            <div class="col-3 text-left">
                                @if($sisterConcern->logo && file_exists(public_path($sisterConcern->logo)))
                                    <img src="{{ asset($sisterConcern->logo) }}" alt="Logo" class="img-fluid" style="max-height: 120px;">
                                @else
                                    <img src="{{ asset('img/placeholder_logo.png') }}" alt="Logo" class="img-fluid" style="max-height: 120px;">
                                @endif
                            </div>
                            <div class="col-6 text-center">
                                <h2 class="m-0" style="font-weight: bold; font-size: 28px;">{{ $sisterConcern->name }}</h2>
                                <p class="text-muted mb-2" style="font-size: 16px;">Branch Code: <b>{{ $sisterConcern->code }}</b></p>
                                <h4 class="m-0" style="font-size: 20px"><u><b>{{ $typeName }}</b></u></h4>
                            </div>
                            <div class="col-3">
                                <!-- Empty for balance -->
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-12">
                                <table class="table table-bordered table-striped">
                                    <tbody>
                                        <tr>
                                            <th>Business Date</th>
                                            <td>{{ $sisterConcern->business_date ? \Carbon\Carbon::parse($sisterConcern->business_date)->format('d-m-Y') : 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Establishment Date</th>
                                            <td>{{ $sisterConcern->establishment_date ? \Carbon\Carbon::parse($sisterConcern->establishment_date)->format('d-m-Y') : 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Certificate Expiry Date</th>
                                            <td>{{ $sisterConcern->certificate_expiry_date ? \Carbon\Carbon::parse($sisterConcern->certificate_expiry_date)->format('d-m-Y') : 'Lifetime' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Contact No</th>
                                            <td>{{ $sisterConcern->contact ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Email</th>
                                            <td>{{ $sisterConcern->email ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Trade License No</th>
                                            <td>{{ $sisterConcern->trade_license_no ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>TIN No</th>
                                            <td>{{ $sisterConcern->tin_no ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>BIN No</th>
                                            <td>{{ $sisterConcern->bin_no ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Address</th>
                                            <td>{{ $sisterConcern->address ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Upazila</th>
                                            <td>{{ $sisterConcern->upazila ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>District</th>
                                            <td>{{ $sisterConcern->district ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Post Code</th>
                                            <td>{{ $sisterConcern->post_code ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th style="width: 30%">Status</th>
                                            <td>
                                                @if($sisterConcern->status == 1)
                                                    <span class="badge badge-success">Active</span>
                                                @else
                                                    <span class="badge badge-danger">Inactive</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        var businessDate = '{{ \Carbon\Carbon::parse(auth()->user()->activeSisterConcern->business_date ?? auth()->user()->sisterConcern->business_date ?? now())->format('d-m-Y') }}';
    </script>
    @include('layouts.partial.__scripts')
    @include('layouts.partial.__export_scripts')
</body>
</html>
