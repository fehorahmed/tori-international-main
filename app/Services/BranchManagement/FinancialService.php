<?php

namespace App\Services\BranchManagement;

use App\Models\Account\AccountGroup;
use App\Models\Account\AccountHead;
use App\Enumeration\ReservedAccountGroup;
use App\Enumeration\ReservedAccountHead;
use App\Models\BranchManagement\SisterConcern;
use App\Models\Hrm\Employee;
use Carbon\Carbon;

class FinancialService
{
    /**
     * Calculate Daily Income Statement details
     * 
     * @param string $startDate
     * @param string $endDate
     * @param int|null $branchId
     * @return array
     */
    public static function calculateDailyIncomeStatement($startDate, $endDate, $branchId = null)
    {
        $operatingRevenue = AccountGroup::where('reserved_id', ReservedAccountGroup::OPERATING_REVENUE)->first();
        $otherIncome = AccountGroup::where('reserved_id', ReservedAccountGroup::OTHER_INCOME)->first();
        $costOfGoodsSold = AccountGroup::where('reserved_id', ReservedAccountGroup::COST_OF_GOODS_SOLD)->first();
        $operatingExpenses = AccountGroup::where('reserved_id', ReservedAccountGroup::OPERATING_EXPENSES)->first();
        $financialExpenses = AccountGroup::where('reserved_id', ReservedAccountGroup::FINANCIAL_EXPENSES)->first();
        $taxExpenses = AccountGroup::where('reserved_id', ReservedAccountGroup::TAX_EXPENSES)->first();

        // 1. Calculate Daily Allocated Costs
        if ($branchId) {
            $branch = SisterConcern::find($branchId);
            $dailyRent = $branch ? ($branch->office_rent / 24) : 0;
            $dailyElectricity = $branch ? ($branch->electricity_bill / 24) : 0;
            
            $totalSalary = Employee::where('sister_concern_id', $branchId)
                ->where('status', 1)
                ->sum('gross_salary');
            $dailySalary = $totalSalary / 24;
        } else {
            $dailyRent = SisterConcern::where('status', 1)->sum('office_rent') / 24;
            $dailyElectricity = SisterConcern::where('status', 1)->sum('electricity_bill') / 24;
            
            $totalSalary = Employee::where('status', 1)->sum('gross_salary');
            $dailySalary = $totalSalary / 24;
        }

        // 2. Determine Days Count
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $daysCount = $start->diffInDays($end) + 1;

        $allocatedRent = $dailyRent * $daysCount;
        $allocatedElectricity = $dailyElectricity * $daysCount;
        $allocatedSalary = $dailySalary * $daysCount;
        $totalAllocatedExpense = $allocatedRent + $allocatedElectricity + $allocatedSalary;

        // 3. Calculate Actual Incomes
        $operatingRevBal = $operatingRevenue ? abs(accountGroupBalanceIncomeStatement($operatingRevenue->id, $startDate, $endDate, 1, $branchId)) : 0;
        $otherIncBal = $otherIncome ? abs(accountGroupBalanceIncomeStatement($otherIncome->id, $startDate, $endDate, 1, $branchId)) : 0;
        $totalIncome = $operatingRevBal + $otherIncBal;

        // 4. Calculate Actual Expenses
        $cogsBal = $costOfGoodsSold ? abs(accountGroupBalanceIncomeStatement($costOfGoodsSold->id, $startDate, $endDate, 1, $branchId)) : 0;
        $opExpBal = $operatingExpenses ? abs(accountGroupBalanceIncomeStatement($operatingExpenses->id, $startDate, $endDate, 1, $branchId)) : 0;
        
        // Exclude fixed costs (Rent, Electricity, Salary) from operating expenses to prevent double counting
        $excludedFixedCostsHeadIds = [
            ReservedAccountHead::OFFICE_RENT,
            ReservedAccountHead::ELECTRICITY_BILL,
            ReservedAccountHead::SALARY_EXPENSE
        ];

        $fixedCostDeduction = 0;
        foreach ($excludedFixedCostsHeadIds as $headId) {
            $head = AccountHead::where('reserved_id', $headId)->first();
            if ($head) {
                $fixedCostDeduction += abs(accountHeadBalanceIncomeStatement($head->id, $startDate, $endDate, 1, $branchId));
            }
        }
        
        $opExpBal = max(0, $opExpBal - $fixedCostDeduction);

        $finExpBal = $financialExpenses ? abs(accountGroupBalanceIncomeStatement($financialExpenses->id, $startDate, $endDate, 1, $branchId)) : 0;
        $taxExpBal = $taxExpenses ? abs(accountGroupBalanceIncomeStatement($taxExpenses->id, $startDate, $endDate, 1, $branchId)) : 0;
        
        $totalActualExpense = $cogsBal + $opExpBal + $finExpBal + $taxExpBal;

        $totalExpense = $totalActualExpense + $totalAllocatedExpense;
        $netProfit = $totalIncome - $totalExpense;

        return [
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
            'netProfit' => $netProfit,
            
            // Detailed breakdowns needed for the report
            'operatingRevBal' => $operatingRevBal,
            'otherIncBal' => $otherIncBal,
            
            'cogsBal' => $cogsBal,
            'opExpBal' => $opExpBal,
            'finExpBal' => $finExpBal,
            'taxExpBal' => $taxExpBal,
            
            'allocatedRent' => $allocatedRent,
            'allocatedElectricity' => $allocatedElectricity,
            'allocatedSalary' => $allocatedSalary,
            
            'daysCount' => $daysCount,
            'fixedCostDeduction' => $fixedCostDeduction,
            'dailyRent' => $dailyRent,
            'dailyElectricity' => $dailyElectricity,
            'dailySalary' => $dailySalary,
        ];
    }
}
