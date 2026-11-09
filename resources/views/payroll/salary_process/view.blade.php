@extends('layouts.app')
@section('title', 'Employee Payslip')
@section('style')
    <style>
        .table td, .table th {
            padding: 10px;
            vertical-align: middle;
        }
        .payslip-header {
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
    </style>
@endsection
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-default">
                <div class="card-header d-none">
                    <h3 class="card-title"><i class="fa fa-file-invoice-dollar"></i> Employee Payslip</h3>
                    <div class="card-tools">
                        <button class="btn btn-primary btn-sm" onclick="getprint('printableArea')"><i class="fa fa-print"></i> Print</button>
                    </div>
                </div>
                <div class="card-header border-bottom-0">
                    @include('layouts.partial.__export_buttons', ['printAreaId' => 'printArea', 'fileName' => 'Salary Slip' . '- {{ numberToMonthName($salary_process_item->month) }}, {{ $salary_process_item->year }}'])
                </div>
                <div class="card-body" id="printArea">
                    @include('layouts.partial.__report_header')
                    <div class="row m-0 mb-4">
                        <div class="col-12 text-center">
                            <h4>Salary Slip - {{ numberToMonthName($salary_process_item->month) }}, {{ $salary_process_item->year }}</h4>
                        </div>
                        <div class="col-8">
                            <strong>Employee Name:</strong> {{ $salary_process_item->employee->name ?? 'N/A' }}<br>
                            <strong>Employee ID:</strong> {{ $salary_process_item->employee->id_no ?? 'N/A' }}<br>
                            <strong>Designation:</strong> {{ $salary_process_item->employee->designation->name ?? 'N/A' }}<br>
                            <strong>Base Gross Salary:</strong> {{ number_format($salary_process_item->base_gross, 2) }} BDT
                        </div>
                        <div class="col-4">
                            <strong>Department:</strong> {{ $salary_process_item->employee->department->name ?? 'N/A' }}<br>
                            <strong>Process Date:</strong> {{ \Carbon\Carbon::parse($salary_process_item->salaryProcess->process_date)->format('d M, Y') }}<br>
                            <strong>Status:</strong> 
                            @if($salary_process_item->voucher_id)
                                <span class="badge badge-success">Paid</span>
                            @else
                                <span class="badge badge-warning">Pending</span>
                            @endif
                        </div>
                    </div>
                        <div class="row m-0">
                            <div class="col-md-12">
                                <table class="table table-bordered">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="text-center" colspan="2" style="width: 50%">Earnings</th>
                                            <th class="text-center" colspan="2" style="width: 50%">Deductions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Basic Salary</td>
                                            <td class="text-right">{{ number_format($salary_process_item->basic_payable, 2) }}</td>
                                            <td>Absent Deduction</td>
                                            <td class="text-right">{{ number_format($salary_process_item->absent_deduction, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td>House Rent</td>
                                            <td class="text-right">{{ number_format($salary_process_item->house_rent, 2) }}</td>
                                            <td>Late Deduction</td>
                                            <td class="text-right">{{ number_format($salary_process_item->late_deduction, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td>Medical Allowance</td>
                                            <td class="text-right">{{ number_format($salary_process_item->medical, 2) }}</td>
                                            <td>Penalty</td>
                                            <td class="text-right">{{ number_format($salary_process_item->penalty, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td>TA/DA Allowance</td>
                                            <td class="text-right">{{ number_format($salary_process_item->ta_da_allowance, 2) }}</td>
                                            <td>Provident Fund (PF)</td>
                                            <td class="text-right">{{ number_format($salary_process_item->pf_deduction, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td>Overtime Allowance</td>
                                            <td class="text-right">{{ number_format($salary_process_item->overtime_allowance, 2) }}</td>
                                            <td>TDS (Tax)</td>
                                            <td class="text-right">{{ number_format($salary_process_item->tax_deduction > 0 ? $salary_process_item->tax_deduction : $salary_process_item->income_tax_deduction, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td>Fair Allowance</td>
                                            <td class="text-right">{{ number_format($salary_process_item->fair_allowance, 2) }}</td>
                                            <td>Advance</td>
                                            <td class="text-right">{{ number_format($salary_process_item->advance, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                            <td></td>
                                            <td>Other Deduction</td>
                                            <td class="text-right">{{ number_format($salary_process_item->other_deduction, 2) }}</td>
                                        </tr>
                                        <tr class="font-weight-bold bg-light">
                                            <td>Total Earnings</td>
                                            <td class="text-right">{{ number_format($salary_process_item->gross, 2) }}</td>
                                            <td>Total Deductions</td>
                                            <td class="text-right">{{ number_format($salary_process_item->total_deduction, 2) }}</td>
                                        </tr>
                                        <tr class="font-weight-bold" style="font-size: 1.2em;">
                                            <td colspan="2" class="text-right">NET PAYABLE AMOUNT:</td>
                                            <td colspan="2" class="text-left text-success">
                                                {{ number_format($salary_process_item->net_pay, 2) }} BDT
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <div class="row m-0 mt-5 pt-5">
                            <div class="col-6 text-center">
                                <hr style="width: 50%; border-top: 1px solid #000;">
                                <strong>Employee Signature</strong>
                            </div>
                            <div class="col-6 text-center">
                                <hr style="width: 50%; border-top: 1px solid #000;">
                                <strong>Authorized Signature</strong>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-right d-print-none">
                        <a href="{{ url()->previous() }}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Back</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
@include('layouts.partial.__export_scripts')
<script>
    var APP_URL = '{!! url()->full() !!}';
    function getprint(print) {
        document.title = "Payslip_{{ $salary_process_item->employee->name ?? 'Employee' }}_{{ numberToMonthName($salary_process_item->month) }}_{{ $salary_process_item->year }}";
        $('body').html($('#' + print).html());
        window.print();
        window.location.replace(APP_URL);
    }
</script>
@endsection
