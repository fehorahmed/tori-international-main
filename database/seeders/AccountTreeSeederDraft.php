<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Enumeration\ReservedAccountGroup;
use App\Enumeration\ReservedAccountHead;

class AccountTreeSeederDraft extends Seeder
{
    /**
     * Run the database seeds.
     * This is a DRAFT file for future use to replace the JSON-based account seeding 
     * with a cleaner, nested PHP array structure (similar to PermissionSeeder).
     */
    public function run(): void
    {
        $chartOfAccounts = [
            // 1. ASSETS
            [
                'name' => 'Assets',
                'reserved_id' => ReservedAccountGroup::ASSETS,
                'comment' => 'Resources owned by the business',
                'children' => [
                    [
                        'name' => 'Current Assets',
                        'reserved_id' => ReservedAccountGroup::CURRENT_ASSETS,
                        'children' => [
                            [
                                'name' => 'Cash & Bank',
                                'reserved_id' => ReservedAccountGroup::CASH_AND_BANK,
                                'heads' => [
                                    ['name' => 'Cash in Hand', 'code' => '1001', 'nature' => 'Debit', 'payment_mode' => 2],
                                ]
                            ],
                            [
                                'name' => 'Accounts Receivable',
                                'reserved_id' => ReservedAccountGroup::ACCOUNTS_RECEIVABLE,
                                'heads' => [
                                    ['name' => 'MFI Loan Portfolio', 'code' => '1003', 'nature' => 'Debit', 'reserved_id' => ReservedAccountHead::MFI_LOAN_PORTFOLIO, 'is_control_account' => true],
                                ]
                            ],
                            [
                                'name' => 'Inventory',
                                'reserved_id' => ReservedAccountGroup::INVENTORY,
                                'heads' => [
                                    ['name' => 'Raw Material Stock', 'code' => '1004', 'nature' => 'Debit', 'reserved_id' => ReservedAccountHead::RAW_INVENTORY, 'is_control_account' => true],
                                    ['name' => 'Work-in-Progress (WIP)', 'code' => '1005', 'nature' => 'Debit', 'reserved_id' => ReservedAccountHead::WORK_IN_STEP_FINISHED_GOODS, 'is_control_account' => true],
                                    ['name' => 'Finished Goods Stock', 'code' => '1006', 'nature' => 'Debit', 'reserved_id' => ReservedAccountHead::FINISHED_GOODS_INVENTORY, 'is_control_account' => true],
                                ]
                            ],
                            [
                                'name' => 'Advances & Prepayments',
                                'reserved_id' => ReservedAccountGroup::ADVANCES_PREPAYMENTS,
                                'heads' => [
                                    ['name' => 'Advance to Supplier', 'code' => '1007', 'nature' => 'Debit', 'reserved_id' => ReservedAccountHead::ADVANCE_TO_SUPPLIER],
                                    ['name' => 'Employee Advance', 'code' => '1011', 'nature' => 'Debit', 'reserved_id' => ReservedAccountHead::EMPLOYEE_ADVANCE],
                                ]
                            ]
                        ]
                    ],
                    [
                        'name' => 'Non-Current Assets',
                        'reserved_id' => ReservedAccountGroup::FIXED_ASSETS,
                        'heads' => [
                            ['name' => 'Accumulated Depreciation', 'code' => '1010', 'nature' => 'Credit', 'reserved_id' => ReservedAccountHead::ACCUMULATED_DEPRECIATION, 'is_system' => true],
                        ]
                    ]
                ]
            ],

            // 2. LIABILITIES
            [
                'name' => 'Liabilities',
                'reserved_id' => ReservedAccountGroup::LIABILITIES,
                'comment' => 'Obligations of the business',
                'children' => [
                    [
                        'name' => 'Current Liabilities',
                        'reserved_id' => ReservedAccountGroup::CURRENT_LIABILITIES,
                        'children' => [
                            [
                                'name' => 'Accounts Payable',
                                'reserved_id' => ReservedAccountGroup::ACCOUNTS_PAYABLE,
                            ],
                            [
                                'name' => 'Short-term Loans',
                                'reserved_id' => ReservedAccountGroup::SHORT_TERM_LOAN,
                            ],
                            [
                                'name' => 'Accrued Expenses',
                                'reserved_id' => ReservedAccountGroup::ACCRUED_EXPENSES,
                                'heads' => [
                                    ['name' => 'Customer Advance Received', 'code' => '2003', 'nature' => 'Credit', 'reserved_id' => ReservedAccountHead::CUSTOMER_ADVANCE_RECEIVED],
                                    ['name' => 'TDS Payable', 'code' => '2005', 'nature' => 'Credit', 'reserved_id' => ReservedAccountHead::INCOME_TAX_DEDUCTION],
                                    ['name' => 'Card Commission Payable', 'code' => '2007', 'nature' => 'Credit', 'reserved_id' => ReservedAccountHead::CARD_COMMISSION_PAYABLE],
                                    ['name' => 'Incentive Payable', 'code' => '2008', 'nature' => 'Credit', 'reserved_id' => ReservedAccountHead::INCENTIVE_PAYABLE, 'is_system' => true],
                                ]
                            ],
                            [
                                'name' => 'VAT Payable',
                                'reserved_id' => ReservedAccountGroup::VAT_PAYABLE,
                                'heads' => [
                                    ['name' => 'VAT Payable', 'code' => '2004', 'nature' => 'Credit', 'reserved_id' => ReservedAccountHead::VAT_PAYABLE],
                                ]
                            ],
                            [
                                'name' => 'Customer Savings', // Adding missing group
                                'reserved_id' => ReservedAccountGroup::CUSTOMER_SAVINGS,
                                'heads' => [
                                    ['name' => 'MFI Member Savings Account', 'code' => '2002', 'nature' => 'Credit', 'reserved_id' => ReservedAccountHead::MFI_SAVINGS_ACCOUNT, 'is_control_account' => true],
                                ]
                            ]
                        ]
                    ],
                    [
                        'name' => 'Long-term Liabilities',
                        'reserved_id' => ReservedAccountGroup::LONG_TERM_LIABILITIES,
                        'heads' => [
                            ['name' => 'Long Term Loan', 'code' => '2006', 'nature' => 'Credit'],
                        ]
                    ]
                ]
            ],

            // 3. EQUITY
            [
                'name' => 'Equity',
                'reserved_id' => ReservedAccountGroup::EQUITY,
                'comment' => 'Owner\'s claim on assets',
                'children' => [
                    [
                        'name' => 'Capital',
                        'reserved_id' => ReservedAccountGroup::CAPITAL,
                        'heads' => [
                            ['name' => 'Owner\'s Capital', 'code' => '3001', 'nature' => 'Credit'],
                        ]
                    ],
                    [
                        'name' => 'Retained Earnings',
                        'reserved_id' => ReservedAccountGroup::RETAINED_EARNINGS,
                        'heads' => [
                            ['name' => 'Retained Earnings', 'code' => '3002', 'nature' => 'Credit'],
                            ['name' => 'Opening Balance Equity', 'code' => '3003', 'nature' => 'Credit', 'reserved_id' => ReservedAccountHead::OPENING_BALANCE_EQUITY, 'is_control_account' => true],
                        ]
                    ],
                    [
                        'name' => 'Drawings',
                        'reserved_id' => ReservedAccountGroup::DRAWINGS,
                    ]
                ]
            ],

            // 4. INCOME
            [
                'name' => 'Income',
                'reserved_id' => ReservedAccountGroup::REVENUE,
                'comment' => 'Revenue from operations and other sources',
                'children' => [
                    [
                        'name' => 'Operating Revenue',
                        'reserved_id' => ReservedAccountGroup::OPERATING_REVENUE,
                        'heads' => [
                            ['name' => 'Sales Revenue', 'code' => '4001', 'nature' => 'Credit', 'reserved_id' => ReservedAccountHead::SALES_REVENUE],
                            ['name' => 'MFI Service Charge Income', 'code' => '4002', 'nature' => 'Credit', 'reserved_id' => ReservedAccountHead::MFI_SERVICE_CHARGE_INCOME],
                            ['name' => 'Sales Return', 'code' => '4003', 'nature' => 'Debit', 'reserved_id' => ReservedAccountHead::SALES_RETURN, 'is_control_account' => true],
                            ['name' => 'Absent Late Other Deduction', 'code' => '4005', 'nature' => 'Credit', 'reserved_id' => ReservedAccountHead::ABSENT_LATE_OTHER_DEDUCTION],
                            ['name' => 'Sales Discount', 'code' => '4006', 'nature' => 'Debit', 'reserved_id' => ReservedAccountHead::SALES_DISCOUNT],
                            ['name' => 'Service Revenue', 'code' => '4007', 'nature' => 'Credit', 'reserved_id' => ReservedAccountHead::SERVICE_REVENUE],
                        ]
                    ],
                    [
                        'name' => 'Other Income',
                        'reserved_id' => ReservedAccountGroup::OTHER_INCOME,
                        'heads' => [
                            ['name' => 'Other Income', 'code' => '4004', 'nature' => 'Credit', 'reserved_id' => ReservedAccountHead::OTHER_INCOME],
                            ['name' => 'Gain on Asset Sale', 'code' => '4008', 'nature' => 'Credit', 'reserved_id' => ReservedAccountHead::GAIN_ON_ASSET_SALE, 'is_system' => true],
                        ]
                    ]
                ]
            ],

            // 5. EXPENSES
            [
                'name' => 'Expenses',
                'reserved_id' => ReservedAccountGroup::EXPENSES,
                'comment' => 'Costs incurred to earn revenue',
                'children' => [
                    [
                        'name' => 'Cost of Goods Sold',
                        'reserved_id' => ReservedAccountGroup::COST_OF_GOODS_SOLD,
                        'heads' => [
                            ['name' => 'Cost of Goods Sold', 'code' => '5001', 'nature' => 'Debit', 'reserved_id' => ReservedAccountHead::COST_OF_GOODS_SOLD],
                            ['name' => 'Purchase Return', 'code' => '5002', 'nature' => 'Credit', 'reserved_id' => ReservedAccountHead::PURCHASE_RETURN, 'is_control_account' => true],
                            ['name' => 'Purchase Discount', 'code' => '5003', 'nature' => 'Credit', 'reserved_id' => ReservedAccountHead::PURCHASE_DISCOUNT],
                            ['name' => 'Inventory Adjustment', 'code' => '5011', 'nature' => 'Debit', 'is_control_account' => true],
                        ]
                    ],
                    [
                        'name' => 'Operating Expenses',
                        'reserved_id' => ReservedAccountGroup::OPERATING_EXPENSES,
                        'heads' => [
                            ['name' => 'Salary Expense', 'code' => '5004', 'nature' => 'Debit', 'reserved_id' => ReservedAccountHead::SALES_COMMISSION_EXPENSE], // NOTE: Reserved ID will be updated when finalized
                            ['name' => 'Office Rent', 'code' => '5005', 'nature' => 'Debit', 'reserved_id' => ReservedAccountHead::OFFICE_RENT],
                            ['name' => 'Marketing Expense', 'code' => '5006', 'nature' => 'Debit'],
                            ['name' => 'Sales Commission Expense', 'code' => '5008', 'nature' => 'Debit', 'reserved_id' => ReservedAccountHead::SALES_COMMISSION_EXPENSE],
                            ['name' => 'Depreciation Expense', 'code' => '5010', 'nature' => 'Debit', 'reserved_id' => ReservedAccountHead::DEPRECIATION_EXPENSE, 'is_system' => true],
                            ['name' => 'Electricity Bill', 'code' => '5013', 'nature' => 'Debit', 'reserved_id' => ReservedAccountHead::ELECTRICITY_BILL],
                            ['name' => 'Incentive Expense', 'code' => '5014', 'nature' => 'Debit', 'reserved_id' => ReservedAccountHead::INCENTIVE_EXPENSE, 'is_system' => true],
                            ['name' => 'Loss on Asset Sale', 'code' => '5016', 'nature' => 'Debit', 'reserved_id' => ReservedAccountHead::LOSS_ON_ASSET_SALE, 'is_system' => true],
                            ['name' => 'Loss on Scrap', 'code' => '5017', 'nature' => 'Debit', 'reserved_id' => ReservedAccountHead::LOSS_ON_SCRAP, 'is_system' => true],
                        ]
                    ],
                    [
                        'name' => 'Financial Expenses',
                        'reserved_id' => ReservedAccountGroup::FINANCIAL_EXPENSES,
                        'heads' => [
                            ['name' => 'MFI Savings Interest Paid', 'code' => '5007', 'nature' => 'Debit', 'reserved_id' => ReservedAccountHead::MFI_INTEREST_PAID_ON_SAVINGS],
                            ['name' => 'Bank Charges', 'code' => '5012', 'nature' => 'Debit'],
                            ['name' => 'Shomriddhi Interest Expense', 'code' => '5015', 'nature' => 'Debit', 'reserved_id' => ReservedAccountHead::SHOMRIDDHI_INTEREST_EXPENSE, 'is_system' => true],
                        ]
                    ],
                    [
                        'name' => 'Tax Expenses',
                        'reserved_id' => ReservedAccountGroup::TAX_EXPENSES,
                        'heads' => [
                            ['name' => 'VAT Expense', 'code' => '5009', 'nature' => 'Debit', 'reserved_id' => ReservedAccountHead::VAT_EXPENSE],
                        ]
                    ]
                ]
            ]
        ];

        // TODO: Implement recursive processing function here when ready.
    }
}
