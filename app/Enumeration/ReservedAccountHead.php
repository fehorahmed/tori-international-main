<?php
namespace App\Enumeration;

class  ReservedAccountHead
{
    const RAW_INVENTORY = 1;//113//Original In for this App
    const FINISHED_GOODS_INVENTORY = 2;//121
    const WORK_IN_STEP_FINISHED_GOODS = 3;//193
    const INTER_COMPANY_RECEIVABLE = 4;//128
    const INTER_COMPANY_PAYABLE = 5;//180
    const ABSENT_LATE_OTHER_DEDUCTION = 6;//183
    const SALARY_EXPENSE = 7;//182
    const SALES_COMMISSION_EXPENSE = 8;//186
    const SALES_REVENUE = 9;//114
    const SALES_DISCOUNT = 10;//123
    const COST_OF_GOODS_SOLD = 11;//2
    const VAT_PAYABLE = 12;//217
    const INCOME_TAX_DEDUCTION = 13;//219
    const ADVANCE_TO_SUPPLIER = 14;
    const CUSTOMER_ADVANCE_RECEIVED = 15;
    const VAT_EXPENSE = 16;
    const PURCHASE_DISCOUNT = 17;
    const SALES_RETURN = 18;
    const PURCHASE_RETURN = 19;
    const OPENING_BALANCE_EQUITY = 20;
    const SERVICE_REVENUE = 21;
    const COMPANY_LOAN_RECEIVABLE = 22;
    const COMPANY_LOAN_PAYABLE = 23;
    const COMPANY_LOAN_INTEREST_INCOME = 24;
    const COMPANY_LOAN_INTEREST_EXPENSE = 25;
    const MFI_LOAN_PORTFOLIO = 26;
    const MFI_SAVINGS_ACCOUNT = 27;
    const MFI_SERVICE_CHARGE_INCOME = 28;
    const MFI_INTEREST_PAID_ON_SAVINGS = 29;
    const INTERNAL_TRANSFER_REVENUE = 30; // Inter-branch transfer markup/profit income
    const OTHER_INCOME = 31;
    const CARD_COMMISSION_PAYABLE = 32;
    const EMPLOYEE_ADVANCE = 33;
    const OFFICE_RENT = 34;
    const ELECTRICITY_BILL = 35;
    const INCENTIVE_EXPENSE = 36; // Incentive Management — ইনসেনটিভ খরচ (Expense) — Profit কমাবে
    const INCENTIVE_PAYABLE = 37; // Incentive Management — ইনসেনটিভ প্রদেয় দায় (Liability)
    const SHOMRIDDHI_INTEREST_EXPENSE = 38; // Shomriddhi Advance — Interest Expense
    const DEPRECIATION_EXPENSE = 39;
    const ACCUMULATED_DEPRECIATION = 40;
    const GAIN_ON_ASSET_SALE = 41;
    const LOSS_ON_ASSET_SALE = 42;
    const LOSS_ON_SCRAP = 43;
    const DPS_INTEREST_EXPENSE = 44;
    const DPS_PENALTY_INCOME = 45;
    const FDR_INTEREST_EXPENSE = 46;
    const FDR_PENALTY_INCOME = 47;
}
