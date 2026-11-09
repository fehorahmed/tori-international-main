<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Salary Sheet - {{ $salaryMonth }}</title>
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
            size: landscape;
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
                    @include('layouts.partial.__export_buttons', ['printAreaId' => 'printArea', 'fileName' => 'Salary_Sheet_' . $salaryMonth . '_' . date('Y')])
                    
                    @if(count($salaries) > 0)
                        <div class="" id="printArea">
                            @include('layouts.partial.__report_header')
                            <div class="row m-0">
                                <div class="col-12">
                                    <h3 class="text-center" style="font-size: 25px"><u><b>Salary Sheet</b></u></h3>
                                    <h5 class="text-center" style="font-size: 15px;font-weight: bold">Month: {{ $salaryMonth }}</h5>
                                </div>
                            </div>
                            <div class="row header-common-info m-0 mt-3">
                                <div class="col-12 p-0">
                                    <table id="table" class="table table-bordered table-sm">
                                    <thead class="text-center bg-light">
                                        <tr>
                                            <th rowspan="2">S/L</th>
                                            <th rowspan="2">ID No.</th>
                                            <th rowspan="2">Name</th>
                                            <th rowspan="2">Designation</th>
                                            <th rowspan="2">Basic Salary</th>
                                            <th colspan="4">Earnings</th>
                                            <th rowspan="2">Gross Salary</th>
                                            <th colspan="6">Deductions</th>
                                            <th rowspan="2">Total Deduction</th>
                                            <th rowspan="2">Net Payable</th>
                                            <th rowspan="2">Signature</th>
                                        </tr>
                                        <tr>
                                            <th>Fair Allw.</th>
                                            <th>OT Min</th>
                                            <th>OT Pay</th>
                                            <th>Others</th>

                                            <th>Leave</th>
                                            <th>Absent Ded.</th>
                                            <th>Late Day</th>
                                            <th>Late Ded.</th>
                                            <th>Penalty</th>
                                            <th>TDS/Adv.</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $otMinSum = 0;
                                        @endphp
                                        @foreach($salaries as $salary)
                                            @php
                                                $otMin = $salary->overtime_second > 0 ? ($salary->overtime_second / 60) : 0;
                                                $otMinSum += $otMin;
                                                // other earnings
                                                $otherEarnings = $salary->gross - $salary->basic_payable - $salary->fair_allowance - $salary->overtime_allowance;
                                                // tds and advance
                                                $tdsAdv = $salary->income_tax_deduction + $salary->advance;
                                            @endphp
                                            <tr>
                                                <td class="text-center">{{ $loop->iteration }}</td>
                                                <td class="text-center">{{ $salary->employee->id_no ?? '' }}</td>
                                                <td>{{ $salary->employee->name ?? '' }}</td>
                                                <td class="text-center">{{ $salary->employee->designation->name ?? '' }}</td>
                                                <td class="text-right">{{ number_format($salary->basic_payable, 2) }}</td>
                                                
                                                <!-- Earnings -->
                                                <td class="text-right">{{ number_format($salary->fair_allowance, 2) }}</td>
                                                <td class="text-right">{{ number_format($otMin, 2) }}</td>
                                                <td class="text-right">{{ number_format($salary->overtime_allowance, 2) }}</td>
                                                <td class="text-right">{{ number_format($otherEarnings, 2) }}</td>
                                                <td class="text-right font-weight-bold">{{ number_format($salary->gross, 2) }}</td>
                                                
                                                <!-- Deductions -->
                                                <td class="text-center">{{ $salary->leave }}</td>
                                                <td class="text-right">{{ number_format($salary->absent_deduction, 2) }}</td>
                                                <td class="text-center">{{ $salary->late }}</td>
                                                <td class="text-right">{{ number_format($salary->late_deduction, 2) }}</td>
                                                <td class="text-right">{{ number_format($salary->penalty, 2) }}</td>
                                                <td class="text-right">{{ number_format($tdsAdv, 2) }}</td>
                                                <td class="text-right font-weight-bold">{{ number_format($salary->total_deduction, 2) }}</td>
                                                
                                                <!-- Net -->
                                                <td class="text-right font-weight-bold">{{ number_format($salary->net_pay, 2) }}</td>
                                                <td></td>
                                            </tr>
                                        @endforeach
                                        <tr class="font-weight-bold bg-light">
                                            <td class="text-center" colspan="4">Total</td>
                                            <td class="text-right">{{ number_format($salaries->sum('basic_payable'), 2) }}</td>
                                            
                                            <td class="text-right">{{ number_format($salaries->sum('fair_allowance'), 2) }}</td>
                                            <td class="text-right">{{ number_format($otMinSum, 2) }}</td>
                                            <td class="text-right">{{ number_format($salaries->sum('overtime_allowance'), 2) }}</td>
                                            <td class="text-right">{{ number_format($salaries->sum('gross') - $salaries->sum('basic_payable') - $salaries->sum('fair_allowance') - $salaries->sum('overtime_allowance'), 2) }}</td>
                                            <td class="text-right">{{ number_format($salaries->sum('gross'), 2) }}</td>
                                            
                                            <td class="text-center">{{ $salaries->sum('leave') }}</td>
                                            <td class="text-right">{{ number_format($salaries->sum('absent_deduction'), 2) }}</td>
                                            <td class="text-center">{{ $salaries->sum('late') }}</td>
                                            <td class="text-right">{{ number_format($salaries->sum('late_deduction'), 2) }}</td>
                                            <td class="text-right">{{ number_format($salaries->sum('penalty'), 2) }}</td>
                                            <td class="text-right">{{ number_format($salaries->sum('income_tax_deduction') + $salaries->sum('advance'), 2) }}</td>
                                            <td class="text-right">{{ number_format($salaries->sum('total_deduction'), 2) }}</td>
                                            
                                            <td class="text-right">{{ number_format($salaries->sum('net_pay'), 2) }}</td>
                                            <td></td>
                                        </tr>
                                    </tbody>
                                </table>
                                </div>
                            </div>
                            <div class="row mt-5 pt-4">
                                <div class="col-4 text-center">
                                    <hr style="border-top: 1px solid #000; width: 70%;">
                                    <strong>Prepared By</strong>
                                </div>
                                <div class="col-4 text-center">
                                    <hr style="border-top: 1px solid #000; width: 70%;">
                                    <strong>Checked By</strong>
                                </div>
                                <div class="col-4 text-center">
                                    <hr style="border-top: 1px solid #000; width: 70%;">
                                    <strong>Authorized Signature</strong>
                                </div>
                            </div>
                        </div>
                    @else
                        <h4 class="text-danger text-center">No data available for the selected month/year.</h4>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @include('layouts.partial.__export_scripts')
</body>
</html>
