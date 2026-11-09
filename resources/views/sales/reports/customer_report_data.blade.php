<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Customer Report</title>
    @include('layouts.partial.__styles')
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
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="report-container">
                    @include('layouts.partial.__export_buttons', ['printAreaId' => 'printArea', 'fileName' => 'Customer_Report_' . date('d_m_Y')])
                    <div class="table-responsive-md" id="printArea">
                        @include('layouts.partial.__report_header')
                        <div class="row mx-0">
                            <div class="col-12 text-center mb-3">
                                <h3 style="font-size: 25px"><u><b>Customer Report</b></u></h3>
                                <h5><b>Branch:</b> {{ auth()->user()->activeSisterConcern->name ?? auth()->user()->sisterConcern->name ?? 'N/A' }}</h5>
                            </div>
                        </div>

                        <table class="table table-bordered">
                            <thead>
                                <tr class="bg-light">
                                    <th class="text-center">S/L</th>
                                    <th>Customer Type</th>
                                    <th>Member No.</th>
                                    <th>Customer Name</th>
                                    <th>Mobile No.</th>
                                    <th>Email</th>
                                    <th>Address</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($customers as $customer)
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td>
                                            @if($customer->type == \App\Enumeration\SupplierType::$CUSTOMER)
                                                Regular Customer
                                            @elseif($customer->type == \App\Enumeration\SupplierType::$CORPORATE_CUSTOMER)
                                                Corporate Customer
                                            @elseif($customer->type == \App\Enumeration\SupplierType::$DEALER)
                                                Dealer
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>{{ $customer->member_no ?? 'N/A' }}</td>
                                        <td>{{ $customer->name }}</td>
                                        <td>{{ $customer->mobile_no ?? 'N/A' }}</td>
                                        <td>{{ $customer->email ?? 'N/A' }}</td>
                                        <td>{{ $customer->village ? $customer->village->name : ($customer->address ?? 'N/A') }}</td>
                                        <td>
                                            @if($customer->status == 1)
                                                <span class="text-success font-weight-bold">Active</span>
                                            @else
                                                <span class="text-danger font-weight-bold">Inactive</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-danger">No customers found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
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
