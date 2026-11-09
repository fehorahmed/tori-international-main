@extends('layouts.app')
@section('title','Salary Process Edit')
@section('style')
    <style>
        .table td, .table th {
            padding: 6px;
            vertical-align: middle;
        }
        .table td label {
            display: inline-block;
            margin-bottom: 0;
        }
    </style>
@endsection
@section('content')
    <div class="row">
        <!-- left column -->
        <div class="col-md-12">
            <!-- jquery validation -->
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">Salary Process Edit Information</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                <form enctype="multipart/form-data" action="{{ route('salary-processes.item_update',['salary_process_item'=>$salary_process_item->id]) }}" class="form-horizontal" method="post">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group  {{ $errors->has('employee') ? 'has-error' :'' }}">
                                    <label for="employee">Employee <span class="text-danger">*</span></label>
                                    <input type="text" id="employee" readonly class="form-control" value="{{ ($salary_process_item->employee->name ?? '' ).' ID No. '.($salary_process_item->employee->id_no ?? '' ) }}">
                                    @error('employee')
                                    <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group  {{ $errors->has('year') ? 'has-error' :'' }}">
                                    <label for="year">Year <span class="text-danger">*</span></label>
                                    <input type="text" id="year" readonly class="form-control" value="{{ $salary_process_item->year  }}">
                                    @error('year')
                                    <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group {{ $errors->has('month') ? 'has-error' :'' }}">
                                    <label for="month">Month <span class="text-danger">*</span></label>
                                    <input type="text" id="month" readonly class="form-control" value="{{ numberToMonthName($salary_process_item->month) }}">
                                    @error('month')
                                    <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th class="text-center" colspan="2">Allowance</th>
                                                <th class="text-center" colspan="2">Deduction</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Basic Salary</td>
                                                <td>
                                                    <div class="form-group mb-0">
                                                        <input type="number" name="basic_payable" id="basic_payable" class="form-control" step="any" value="{{ old('basic_payable',$salary_process_item->basic_payable) }}">
                                                    </div>
                                                </td>
                                                <td>Absent Deduction</td>
                                                <td>
                                                    <div class="form-group mb-0">
                                                        <input type="number" class="form-control" step="any" name="absent_deduction" id="absent_deduction" value="{{ old('absent_deduction',$salary_process_item->absent_deduction) }}">
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>House Rent</td>
                                                <td>
                                                    <div class="form-group mb-0">
                                                        <input type="number" name="house_rent" id="house_rent" class="form-control" step="any" value="{{ old('house_rent',$salary_process_item->house_rent) }}">
                                                    </div>
                                                </td>
                                                <td>Late Deduction</td>
                                                <td>
                                                    <div class="form-group mb-0">
                                                        <input type="number" class="form-control" step="any" name="late_deduction" id="late_deduction" value="{{ old('late_deduction',$salary_process_item->late_deduction) }}">
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Medical</td>
                                                <td>
                                                    <div class="form-group mb-0">
                                                        <input type="number" name="medical" id="medical" class="form-control" step="any" value="{{ old('medical',$salary_process_item->medical) }}">
                                                    </div>
                                                </td>
                                                <td>Penalty</td>
                                                <td>
                                                    <div class="form-group mb-0">
                                                        <input type="number" class="form-control" step="any" id="penalty" name="penalty" value="{{ old('penalty',$salary_process_item->penalty) }}">
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>TA/DA Allowance</td>
                                                <td>
                                                    <div class="form-group mb-0">
                                                        <input type="number" class="form-control" step="any" id="ta_da_allowance" name="ta_da_allowance" value="{{ old('ta_da_allowance',$salary_process_item->ta_da_allowance) }}">
                                                    </div>
                                                </td>
                                                <td>Provident Fund (PF)</td>
                                                <td>
                                                    <div class="form-group mb-0">
                                                        <input type="number" class="form-control" step="any" id="pf_deduction" name="pf_deduction" value="{{ old('pf_deduction',$salary_process_item->pf_deduction) }}">
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Overtime Allowance</td>
                                                <td>
                                                    <div class="form-group mb-0">
                                                        <input type="number" name="overtime_allowance" id="overtime_allowance" class="form-control" step="any" value="{{ old('overtime_allowance',$salary_process_item->overtime_allowance) }}">
                                                    </div>
                                                </td>
                                                <td>TDS (Tax)</td>
                                                <td>
                                                    <div class="form-group mb-0">
                                                        <!-- Both names used to be safe -->
                                                        <input type="number" class="form-control" step="any" id="income_tax_deduction" name="income_tax_deduction" value="{{ old('income_tax_deduction', $salary_process_item->tax_deduction > 0 ? $salary_process_item->tax_deduction : $salary_process_item->income_tax_deduction) }}">
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Fair Allowance</td>
                                                <td>
                                                    <div class="form-group mb-0">
                                                        <input type="number" name="fair_allowance" id="fair_allowance" class="form-control" step="any" value="{{ old('fair_allowance',$salary_process_item->fair_allowance) }}">
                                                    </div>
                                                </td>
                                                <td>Advance</td>
                                                <td>
                                                    <div class="form-group mb-0">
                                                        <input type="number" class="form-control" step="any" id="advance" name="advance" value="{{ old('advance',$salary_process_item->advance) }}">
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td></td>
                                                <td></td>
                                                <td>Other Deduction</td>
                                                <td>
                                                    <div class="form-group mb-0">
                                                        <input type="number" class="form-control" step="any" id="other_deduction" name="other_deduction" value="{{ old('other_deduction',$salary_process_item->other_deduction) }}">
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Gross</td>
                                                <td>
                                                    <div class="form-group mb-0">
                                                        <input readonly type="number" name="gross" id="gross" class="form-control" step="any" value="{{ old('gross',$salary_process_item->gross) }}">
                                                    </div>
                                                </td>
                                                <td></td>
                                                <td></td>
                                            </tr>

                                            <tr>
                                                <td>Net pay</td>
                                                <td>
                                                    <div class="form-group mb-0">
                                                        <input type="number" readonly class="form-control" name="net_pay" id="net_pay" step="any" value="{{ old('net_pay',$salary_process_item->net_pay) }}">
                                                    </div>
                                                </td>
                                                <td>Total Deduction</td>
                                                <td>
                                                    <div class="form-group mb-0">
                                                        <input type="number" readonly class="form-control" id="total_deduction" name="total_deduction" step="any" value="{{ old('total_deduction',$salary_process_item->total_deduction) }}">
                                                    </div>
                                                </td>
                                            </tr>

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer">
                        <button type="submit" class="btn btn-info bg-gradient-info btn-sm">Save</button>
                        <a href="{{ route('salary-processes.index') }}" class="btn btn-danger bg-gradient-danger btn-sm float-right">Cancel</a>
                    </div>
                    <!-- /.card-footer -->
                </form>
            </div>
            <!-- /.card -->
        </div>
        <!--/.col (left) -->
    </div>
@endsection

@section('script')
    <script>
        $(function (){
            $('body').on('keyup', 'input[type="number"]', function() {
                calculate();
            });
        })
        function calculate(){

            let allowanceInputFields = [
                '#basic_payable',
                '#house_rent',
                '#medical',
                '#overtime_allowance',
                '#fair_allowance',
                '#ta_da_allowance',
            ];
            let allowanceValues = allowanceInputFields.map(function(selector) {
                let value = parseFloat($(selector).val());
                return isNaN(value) || value < 0 ? 0 : value;
            });

            let totalAllowanceSum = allowanceValues.reduce(function(accumulator, currentValue) {
                return accumulator + currentValue;
            }, 0);

            $("#gross").val((totalAllowanceSum.toFixed(2)));


            let totalDeductionFields = [
                '#absent_deduction',
                '#late_deduction',
                '#other_deduction',
                '#penalty',
                '#pf_deduction',
                '#income_tax_deduction',
                '#advance',
            ];

            let totalDeductionValues = totalDeductionFields.map(function(selector) {
                let value = parseFloat($(selector).val());
                return isNaN(value) || value < 0 ? 0 : value;
            });

            let totalDeduction = totalDeductionValues.reduce(function(accumulator, currentValue) {
                return accumulator + currentValue;
            }, 0);


            $("#total_deduction").val((totalDeduction.toFixed(2)));

            let net_pay = totalAllowanceSum - totalDeduction ;
            $("#net_pay").val((net_pay.toFixed(2)));

        }
    </script>
@endsection
