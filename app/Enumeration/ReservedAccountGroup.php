<?php

namespace App\Enumeration;

class ReservedAccountGroup
{
    // Root Groups
    const ASSETS = 1;
    const LIABILITIES = 2;
    const EQUITY = 3;
    const REVENUE = 4;
    const EXPENSES = 5;

    // Sub Groups - Assets
    const CURRENT_ASSETS = 6;
    const CASH_AND_BANK = 7;
    const ACCOUNTS_RECEIVABLE = 8;
    const INVENTORY = 9;
    const ADVANCES_PREPAYMENTS = 10;
    const FIXED_ASSETS = 11;
    const OTHER_ASSETS = 12;

    // Sub Groups - Liabilities
    const CURRENT_LIABILITIES = 13;
    const ACCOUNTS_PAYABLE = 14;
    const SHORT_TERM_LOAN = 15;
    const ACCRUED_EXPENSES = 16;
    const VAT_PAYABLE = 17;
    const LONG_TERM_LIABILITIES = 18;
    const CUSTOMER_SAVINGS = 28;
    const DPS_DEPOSITS = 29;
    const CUSTOMER_FDR = 30;

    // Sub Groups - Equity
    const CAPITAL = 19;
    const RETAINED_EARNINGS = 20;
    const DRAWINGS = 21;

    // Sub Groups - Income
    const OPERATING_REVENUE = 22;
    const OTHER_INCOME = 23;

    // Sub Groups - Expenses
    const COST_OF_GOODS_SOLD = 24;
    const OPERATING_EXPENSES = 25;
    const FINANCIAL_EXPENSES = 26;
    const TAX_EXPENSES = 27;
}
