<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Employee Advance Report</title>
    @include('layouts.partial.__styles')
    <style>
        .table-bordered thead td,
        .table-bordered thead th {
            vertical-align: middle;
            border: 1px solid #000 !important;
            padding: 4px;
        }

        .table-bordered td,
        .table-bordered th {
            border: 1px solid #000000 !important;
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
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            min-height: 100vh;
        }

        .table th,
        .table td {
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

            .table-responsive {
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
                    @include('layouts.partial.__export_buttons', ['printAreaId' => 'printArea', 'fileName' => 'Employee_Advance_Report_' . date('Y_m_d')])

                    <div class="table-responsive" id="printArea">
                        @include('layouts.partial.__report_header')
                        <div class="row m-0">
                            <div class="col-12">
                                <h3 class="text-center" style="font-size: 22px"><u><b>Employee Advance Report</b></u>
                                </h3>
                                <h5 class="text-center mb-4" style="font-size: 15px;font-weight: bold">Reporting Date: {{ date('d-M-Y', strtotime(auth()->user()->business_date)) }}</h5>
                            </div>
                        </div>

                        <div class="row header-common-info m-0 mt-3">
                            <div class="col-12 p-0">
                                <table id="table" class="table table-bordered table-sm">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="text-center">S/L</th>
                                            <th class="text-center">ID No.</th>
                                            <th class="text-center">Name</th>
                                            <th class="text-center">Designation</th>
                                            <th class="text-center">Total Advance Taken</th>
                                            <th class="text-center">Total Repaid (Deducted)</th>
                                            <th class="text-center">Current Due Balance</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php 
                                                                                        $gAmount = 0;
                                            $gPaid = 0;
                                            $gBalance = 0; 
                                        @endphp
                                        @forelse($reportData as $index => $data)
                                            @php 
                                                                                            $gAmount += $data->total_amount;
                                                $gPaid += $data->total_paid;
                                                $gBalance += $data->balance;
                                            @endphp
                                            <tr>
                                                <td class="text-center">{{ $index + 1 }}</td>
                                                <td class="text-center">{{ $data->employee->id_no }}</td>
                                                <td class="text-left">{{ $data->employee->name }}</td>
                                                <td class="text-center">{{ $data->employee->designation->name ?? '' }}</td>
                                                <td class="text-right">{{ number_format($data->total_amount, 2) }}</td>
                                                <td class="text-right">{{ number_format($data->total_paid, 2) }}</td>
                                                <td class="text-right font-weight-bold">
                                                    {{ number_format($data->balance, 2) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center text-danger">No Advance Records Found
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    @if(count($reportData) > 0)
                                        <tfoot class="bg-light font-weight-bold">
                                            <tr>
                                                <th colspan="4" class="text-center">Total:</th>
                                                <th class="text-right">{{ number_format($gAmount, 2) }}</th>
                                                <th class="text-right">{{ number_format($gPaid, 2) }}</th>
                                                <th class="text-right">{{ number_format($gBalance, 2) }}</th>
                                            </tr>
                                        </tfoot>
                                    @endif
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('layouts.partial.__export_scripts')
</body>

</html>