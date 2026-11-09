@if(count($previews) > 0)
<div class="table-responsive mt-4" style="max-height: 600px; overflow-y: auto;">
    <table class="table table-bordered table-striped table-sm text-center table-head-fixed">
        <thead>
            <tr>
                <th style="min-width: 50px;">SL</th>
                <th style="min-width: 150px;">Employee</th>
                <th>Days</th>
                <th title="Present / Absent / Late">P/A/L</th>
                <th>Gross</th>
                <th>Overtime</th>
                <th>Fair Allow.</th>
                <th>Absent Deduct</th>
                <th>Late Deduct</th>
                <th>Penalty</th>
                <th>Advance</th>
                <th>Other Deduct</th>
                <th>Net Pay</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($previews as $key => $row)
                @php 
                    $employee = $row['employee'];
                    $salary = $row['salary'];
                    $processed = $row['already_processed'];
                @endphp
                <tr data-gross="{{ $employee->gross_salary ?? 0 }}" data-pf="{{ $salary['pf_deduction'] ?? 0 }}" data-tax="{{ $salary['tax_deduction'] ?? 0 }}">
                    <td>{{ $key + 1 }}</td>
                    <td class="text-left">
                        <b>{{ $employee->name }}</b> <br>
                        <small>{{ $employee->designation->name ?? '' }} - {{ $employee->id_no }}</small>
                    </td>
                    
                    @if($processed)
                        <td colspan="12" class="text-center text-danger font-weight-bold">
                            Already Processed
                        </td>
                        <td>
                            <span class="badge badge-success">Done</span>
                        </td>
                    @else
                        <td>{{ $salary['total_days_in_month'] ?? 0 }}</td>
                        <td>
                            <span class="text-success">{{ $salary['present'] ?? 0 }}</span> / 
                            <span class="text-danger">{{ $salary['absent'] ?? 0 }}</span> / 
                            <span class="text-warning">{{ $salary['late'] ?? 0 }}</span>
                        </td>
                        <td class="text-right align-middle">
                            <b>{{ number_format($employee->gross_salary, 2) }}</b>
                            <input type="hidden" name="employees[{{ $employee->id }}][employee_id]" value="{{ $employee->id }}">
                            <input type="hidden" name="employees[{{ $employee->id }}][present]" value="{{ $salary['present'] ?? 0 }}">
                            <input type="hidden" name="employees[{{ $employee->id }}][absent]" value="{{ $salary['absent'] ?? 0 }}">
                            <input type="hidden" name="employees[{{ $employee->id }}][late]" value="{{ $salary['late'] ?? 0 }}">
                            <input type="hidden" name="employees[{{ $employee->id }}][no_checkout]" value="{{ $salary['no_checkout'] ?? 0 }}">
                            <input type="hidden" name="employees[{{ $employee->id }}][early_checkout]" value="{{ $salary['early_checkout'] ?? 0 }}">
                            <input type="hidden" name="employees[{{ $employee->id }}][late_early_checkout]" value="{{ $salary['late_and_early_checkout'] ?? 0 }}">
                            <input type="hidden" name="employees[{{ $employee->id }}][movement]" value="{{ $salary['movement'] ?? 0 }}">
                            <input type="hidden" name="employees[{{ $employee->id }}][public_holiday]" value="{{ $salary['public_holiday'] ?? 0 }}">
                            <input type="hidden" name="employees[{{ $employee->id }}][weekend_holiday]" value="{{ $salary['weekend_holiday'] ?? 0 }}">
                            <input type="hidden" name="employees[{{ $employee->id }}][leave]" value="{{ $salary['leave'] ?? 0 }}">
                            <input type="hidden" name="employees[{{ $employee->id }}][overtime_second]" value="{{ $salary['overtime_seconds'] ?? 0 }}">
                        </td>
                        <td>
                            <input type="number" step="any" name="employees[{{ $employee->id }}][overtime_allowance]" class="form-control form-control-sm text-right calc-allowance e-{{ $employee->id }}" value="{{ old('employees.'.$employee->id.'.overtime_allowance', $salary['overtime_pay'] ?? 0) }}" title="Hours: {{ round(($salary['overtime_seconds'] ?? 0) / 3600, 2) }}">
                        </td>
                        <td>
                            <input type="number" step="any" name="employees[{{ $employee->id }}][fair_allowance]" class="form-control form-control-sm text-right calc-allowance e-{{ $employee->id }}" value="{{ old('employees.'.$employee->id.'.fair_allowance', 0) }}">
                        </td>
                        <td>
                            <input type="number" step="any" name="employees[{{ $employee->id }}][absent_deduction]" class="form-control form-control-sm text-right calc-deduction e-{{ $employee->id }}" value="{{ old('employees.'.$employee->id.'.absent_deduction', $salary['total_absent_deductions'] ?? 0) }}">
                        </td>
                        <td>
                            <input type="number" step="any" name="employees[{{ $employee->id }}][late_deduction]" class="form-control form-control-sm text-right calc-deduction e-{{ $employee->id }}" value="{{ old('employees.'.$employee->id.'.late_deduction', $salary['total_late_deductions'] ?? 0) }}">
                        </td>
                        <td>
                            <input type="number" step="any" name="employees[{{ $employee->id }}][penalty]" class="form-control form-control-sm text-right calc-deduction e-{{ $employee->id }}" value="{{ old('employees.'.$employee->id.'.penalty', 0) }}">
                        </td>
                        <td>
                            <input type="number" step="any" name="employees[{{ $employee->id }}][advance]" class="form-control form-control-sm text-right calc-deduction e-{{ $employee->id }}" value="{{ old('employees.'.$employee->id.'.advance', $salary['advance'] ?? 0) }}">
                        </td>
                        <td>
                            <input type="number" step="any" name="employees[{{ $employee->id }}][other_deduction]" class="form-control form-control-sm text-right calc-deduction e-{{ $employee->id }}" value="{{ old('employees.'.$employee->id.'.other_deduction', 0) }}">
                        </td>
                        <td>
                            <input type="number" step="any" name="employees[{{ $employee->id }}][net_pay]" class="form-control form-control-sm text-right font-weight-bold text-primary net-pay-val e-{{ $employee->id }}" value="{{ old('employees.'.$employee->id.'.net_pay', $salary['net_pay'] ?? 0) }}" readonly>
                        </td>
                        <td>
                            <span class="badge badge-warning">Pending</span>
                        </td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Bind keyup and change event to all calc-allowance and calc-deduction inputs
        $('body').on('keyup change', '.calc-allowance, .calc-deduction', function() {
            let tr = $(this).closest('tr');
            
            let gross = parseFloat(tr.attr('data-gross')) || 0;
            let pf = parseFloat(tr.attr('data-pf')) || 0;
            let tax = parseFloat(tr.attr('data-tax')) || 0;

            let overtime = parseFloat(tr.find('input[name$="[overtime_allowance]"]').val()) || 0;
            let fair = parseFloat(tr.find('input[name$="[fair_allowance]"]').val()) || 0;
            
            let absent = parseFloat(tr.find('input[name$="[absent_deduction]"]').val()) || 0;
            let late = parseFloat(tr.find('input[name$="[late_deduction]"]').val()) || 0;
            let penalty = parseFloat(tr.find('input[name$="[penalty]"]').val()) || 0;
            let advance = parseFloat(tr.find('input[name$="[advance]"]').val()) || 0;
            let other = parseFloat(tr.find('input[name$="[other_deduction]"]').val()) || 0;
            
            let totalAllowance = gross + overtime + fair;
            let totalDeduction = absent + late + penalty + advance + pf + tax + other;
            let netPay = totalAllowance - totalDeduction;
            
            tr.find('.net-pay-val').val(netPay.toFixed(2));
        });
    });
</script>

@else
<div class="alert alert-warning mt-4 text-center">
    No pending employees found for this selection!
</div>
@endif
