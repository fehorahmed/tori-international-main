<?php

namespace Database\Seeders;

use App\Models\UserManagement\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissionsGroup = [
            // 1. Dashboard
            [
                'name' => 'dashboard',
                'sort' => 1,
            ],

            // 2. Office Management
            [
                'name' => 'office_management',
                'sort' => 2,
                'children' => [
                    [
                        'name' => 'notice_management',
                        'sort' => 1,
                        'children' => [
                            ['name' => 'notice_view',   'sort' => 1],
                            ['name' => 'notice_create', 'sort' => 2],
                            ['name' => 'notice_edit',   'sort' => 3],
                            ['name' => 'notice_publish','sort' => 4],
                            ['name' => 'notice_delete', 'sort' => 5],
                        ]
                    ],
                    [
                        'name' => 'meeting_management',
                        'sort' => 2,
                        'children' => [
                            ['name' => 'meeting_view',   'sort' => 1],
                            ['name' => 'meeting_create', 'sort' => 2],
                            ['name' => 'meeting_edit',   'sort' => 3],
                            ['name' => 'meeting_delete', 'sort' => 4],
                        ]
                    ]
                ]
            ],

            // 3. User Management
            [
                'name' => 'user_management',
                'sort' => 3,
                'children' => [
                    [
                        'name' => 'users',
                        'sort' => 1,
                        'children' => [
                            ['name' => 'user_view',   'sort' => 1],
                            ['name' => 'user_create', 'sort' => 2],
                            ['name' => 'user_edit',   'sort' => 3],
                            ['name' => 'user_delete', 'sort' => 4],
                            ['name' => 'user_permission_manage', 'sort' => 5],
                        ]
                    ],
                    [
                        'name' => 'role_management',
                        'sort' => 2,
                        'children' => [
                            ['name' => 'role_view',   'sort' => 1],
                            ['name' => 'role_create', 'sort' => 2],
                            ['name' => 'role_edit',   'sort' => 3],
                            ['name' => 'role_delete', 'sort' => 4],
                        ]
                    ],
                    [
                        'name' => 'user_management_reports',
                        'sort' => 3,
                        'children' => [
                            ['name' => 'users_report', 'sort' => 1],
                        ]
                    ]
                ]
            ],

            // 4. Branch Management
            [
                'name' => 'branch_management',
                'sort' => 4,
                'children' => [
                    [
                        'name' => 'branches',
                        'sort' => 1,
                        'children' => [
                            ['name' => 'branch_view', 'sort' => 1],
                            ['name' => 'branch_create', 'sort' => 2],
                            ['name' => 'branch_edit', 'sort' => 3],
                            ['name' => 'branch_business_date_change', 'sort' => 4],
                        ]
                    ],
                    [
                        'name' => 'branch_management_reports',
                        'sort' => 2,
                        'children' => [
                            ['name' => 'branch_report', 'sort' => 1],
                        ]
                    ]
                ]
            ],

            // 5. CRM
            [
                'name' => 'crm',
                'sort' => 5,
                'children' => [

                    [
                        'name' => 'customer_type',
                        'sort' => 2,
                        'children' => [
                            ['name' => 'customer_type_create', 'sort' => 1],
                            ['name' => 'customer_type_edit', 'sort' => 2],
                            ['name' => 'customer_type_delete', 'sort' => 3],
                        ]
                    ],
                    [
                        'name' => 'manage_markets',
                        'sort' => 3,
                        'children' => [
                            ['name' => 'market_create', 'sort' => 1],
                            ['name' => 'market_edit', 'sort' => 2],
                            ['name' => 'market_delete', 'sort' => 3],
                        ]
                    ],
                    [
                        'name' => 'lead_entry',
                        'sort' => 2,
                        'children' => [
                            ['name' => 'lead_entry_edit', 'sort' => 1],
                            ['name' => 'lead_entry_delete', 'sort' => 2],
                            ['name' => 'lead_entry_feedback_update', 'sort' => 3],
                            ['name' => 'lead_entry_promoted_to_prospect', 'sort' => 4],
                        ]
                    ],
                    [
                        'name' => 'crm_reports',
                        'sort' => 3,
                        'children' => [
                            ['name' => 'leads_report', 'sort' => 1],
                        ]
                    ],
                ]
            ],

            // 5. HRM
            [
                'name' => 'hrm',
                'sort' => 5,
                'children' => [
                    [
                        'name' => 'department',
                        'sort' => 1,
                        'children' => [
                            ['name' => 'department_create', 'sort' => 1],
                            ['name' => 'department_edit', 'sort' => 2],
                            ['name' => 'department_delete', 'sort' => 3],
                        ]
                    ],
                    [
                        'name' => 'designation',
                        'sort' => 2,
                        'children' => [
                            ['name' => 'designation_create', 'sort' => 1],
                            ['name' => 'designation_edit', 'sort' => 2],
                            ['name' => 'designation_delete', 'sort' => 3],
                        ]
                    ],
                    [
                        'name' => 'employee_status',
                        'sort' => 3,
                        'children' => [
                            ['name' => 'employee_status_create', 'sort' => 1],
                            ['name' => 'employee_status_edit', 'sort' => 2],
                            ['name' => 'employee_status_delete', 'sort' => 3],
                        ]
                    ],
                    [
                        'name' => 'holiday',
                        'sort' => 4,
                        'children' => [
                            ['name' => 'holiday_create', 'sort' => 1],
                            ['name' => 'holiday_edit', 'sort' => 2],
                            ['name' => 'holiday_delete', 'sort' => 3],
                        ]
                    ],
                    [
                        'name' => 'shift',
                        'sort' => 5,
                        'children' => [
                            ['name' => 'shift_create', 'sort' => 1],
                            ['name' => 'shift_edit', 'sort' => 2],
                            ['name' => 'shift_delete', 'sort' => 3],
                        ]
                    ],
                    [
                        'name' => 'employee',
                        'sort' => 6,
                        'children' => [
                            ['name' => 'employee_create', 'sort' => 1],
                            ['name' => 'employee_edit', 'sort' => 2],
                            ['name' => 'employee_delete', 'sort' => 3],
                            ['name' => 'employee_attendance', 'sort' => 4],
                        ]
                    ],
                    [
                        'name' => 'leave_type',
                        'sort' => 7,
                        'children' => [
                            ['name' => 'leave_type_create', 'sort' => 1],
                            ['name' => 'leave_type_edit', 'sort' => 2],
                            ['name' => 'leave_type_delete', 'sort' => 3],
                        ]
                    ],
                    [
                        'name' => 'leave',
                        'sort' => 8,
                        'children' => [
                            ['name' => 'leave_create', 'sort' => 1],
                            ['name' => 'leave_edit', 'sort' => 2],
                            ['name' => 'leave_delete', 'sort' => 3],
                        ]
                    ],
                    [
                        'name' => 'employee_movement',
                        'sort' => 9,
                        'children' => [
                            ['name' => 'employee_movement_create', 'sort' => 1],
                            ['name' => 'employee_movement_edit', 'sort' => 2],
                            ['name' => 'employee_movement_delete', 'sort' => 3],
                        ]
                    ],
                    [
                        'name' => 'hrm_reports',
                        'sort' => 10,
                        'children' => [
                            ['name' => 'attendance_sheet', 'sort' => 1],
                            ['name' => 'attendance_summary', 'sort' => 2],
                            ['name' => 'attendance_reminder', 'sort' => 3],
                            ['name' => 'live_attendance', 'sort' => 4],
                            ['name' => 'department_report', 'sort' => 5],
                            ['name' => 'designation_report', 'sort' => 6],
                            ['name' => 'holiday_report', 'sort' => 7],
                            ['name' => 'employee_report', 'sort' => 8],
                        ]
                    ],
                    ['name' => 'hrm_settings', 'sort' => 11],
                ]
            ],

            // 6. Payroll
            [
                'name' => 'payroll',
                'sort' => 6,
                'children' => [
                    [
                        'name' => 'salary_process',
                        'sort' => 1,
                        'children' => [
                            ['name' => 'salary_process_items', 'sort' => 1],
                            ['name' => 'salary_process_item_delete', 'sort' => 2],
                            ['name' => 'salary_process_item_edit', 'sort' => 3],
                        ]
                    ],
                    ['name' => 'salary_process_lists', 'sort' => 2],
                    ['name' => 'salary_process_delete', 'sort' => 3],
                    [
                        'name' => 'payroll_reports',
                        'sort' => 4,
                        'children' => [
                            ['name' => 'salary_sheet', 'sort' => 1],
                        ]
                    ],
                ]
            ],

            // 7. Manager Management
            [
                'name' => 'manager_management',
                'sort' => 7,
                'children' => [
                    [
                        'name' => 'area_manager', 
                        'sort' => 1,
                        'children' => [
                            ['name' => 'area_manager_view', 'sort' => 1],
                            ['name' => 'area_manager_create', 'sort' => 2],
                            ['name' => 'area_manager_edit', 'sort' => 3],
                            ['name' => 'area_manager_delete', 'sort' => 4],
                        ]
                    ],
                    [
                        'name' => 'branch_manager', 
                        'sort' => 2,
                        'children' => [
                            ['name' => 'branch_manager_view', 'sort' => 1],
                            ['name' => 'branch_manager_create', 'sort' => 2],
                            ['name' => 'branch_manager_edit', 'sort' => 3],
                            ['name' => 'branch_manager_delete', 'sort' => 4],
                        ]
                    ],
                    [
                        'name' => 'area_manager_assignments', 
                        'sort' => 3,
                        'children' => [
                            ['name' => 'area_manager_assignment_view', 'sort' => 1],
                            ['name' => 'area_manager_assignment_create', 'sort' => 2],
                            ['name' => 'area_manager_assignment_delete', 'sort' => 3],
                        ]
                    ],
                    [
                        'name' => 'branch_manager_assignments', 
                        'sort' => 4,
                        'children' => [
                            ['name' => 'branch_manager_assignment_view', 'sort' => 1],
                            ['name' => 'branch_manager_assignment_create', 'sort' => 2],
                            ['name' => 'branch_manager_assignment_delete', 'sort' => 3],
                        ]
                    ],
                    ['name' => 'area_manager_targets', 'sort' => 5],
                    ['name' => 'branch_manager_targets', 'sort' => 6],
                    [
                        'name' => 'manager_management_reports',
                        'sort' => 7,
                        'children' => [
                            ['name' => 'area_manager_list_report', 'sort' => 1],
                            ['name' => 'branch_manager_list_report', 'sort' => 2],
                            ['name' => 'area_manager_history_report', 'sort' => 3],
                            ['name' => 'branch_manager_history_report', 'sort' => 4],
                        ]
                    ],
                ]
            ],

            // 8. Settings Module (Unified Configuration)
            [
                'name' => 'settings',
                'sort' => 7,
                'children' => [
                    [
                        'name' => 'warehouse',
                        'sort' => 1,
                        'children' => [
                            ['name' => 'warehouse_create', 'sort' => 1],
                            ['name' => 'warehouse_edit', 'sort' => 2],
                            ['name' => 'warehouse_delete', 'sort' => 3],
                        ]
                    ],
                    [
                        'name' => 'product_unit',
                        'sort' => 3,
                        'children' => [
                            ['name' => 'product_unit_create', 'sort' => 1],
                            ['name' => 'product_unit_edit', 'sort' => 2],
                            ['name' => 'product_unit_delete', 'sort' => 3],
                        ]
                    ],
                    [
                        'name' => 'product_category',
                        'sort' => 4,
                        'children' => [
                            ['name' => 'product_category_create', 'sort' => 1],
                            ['name' => 'product_category_edit', 'sort' => 2],
                            ['name' => 'product_category_delete', 'sort' => 3],
                        ]
                    ],
                    [
                        'name' => 'product',
                        'sort' => 5,
                        'children' => [
                            ['name' => 'product_create', 'sort' => 1],
                            ['name' => 'product_edit', 'sort' => 2],
                            ['name' => 'product_delete', 'sort' => 3],
                            ['name' => 'product_images', 'sort' => 4],
                        ]
                    ],
                    [
                        'name' => 'brand',
                        'sort' => 6,
                        'children' => [
                            ['name' => 'brand_create', 'sort' => 1],
                            ['name' => 'brand_edit', 'sort' => 2],
                            ['name' => 'brand_delete', 'sort' => 3],
                        ]
                    ],
                    [
                        'name' => 'price_book',
                        'sort' => 6,
                        'children' => [
                            ['name' => 'price_book_edit', 'sort' => 1]
                        ]
                    ],
                    [
                        'name' => 'location_setup', 
                        'sort' => 7,
                        'children' => [
                            ['name' => 'location_setup_create', 'sort' => 1],
                            ['name' => 'location_setup_edit', 'sort' => 2],
                            ['name' => 'location_setup_delete', 'sort' => 3],
                        ]
                    ],
                    [
                        'name' => 'settings_report',
                        'sort' => 8,
                        'children' => [
                            ['name' => 'report_brand', 'sort' => 1],
                            ['name' => 'report_category', 'sort' => 2],
                            ['name' => 'report_sub_category', 'sort' => 3],
                            ['name' => 'report_product', 'sort' => 4],
                            ['name' => 'report_price_book_history', 'sort' => 5],
                        ]
                    ],
                ]
            ],

            // 8. Purchase
            [
                'name' => 'purchase',
                'sort' => 8,
                'children' => [
                    [
                        'name' => 'supplier',
                        'sort' => 1,
                        'children' => [
                            ['name' => 'supplier_create', 'sort' => 1],
                            ['name' => 'supplier_edit', 'sort' => 2],
                            ['name' => 'supplier_delete', 'sort' => 3],
                            ['name' => 'supplier_payment', 'sort' => 4],
                        ]
                    ],
                    [
                        'name' => 'purchase_list',
                        'sort' => 2,
                        'children' => [
                            ['name' => 'purchase_create', 'sort' => 1],
                            ['name' => 'purchase_edit', 'sort' => 2],
                            ['name' => 'purchase_delete', 'sort' => 3],
                            ['name' => 'purchase_payment', 'sort' => 4],
                        ]
                    ],
                    [
                        'name' => 'purchase_reports',
                        'sort' => 3,
                        'children' => [
                            ['name' => 'supplier_report', 'sort' => 1],
                            ['name' => 'purchase_report', 'sort' => 2],
                            ['name' => 'purchase_details_report', 'sort' => 3],
                        ]
                    ]
                ]
            ],

            // 9. Inventory
            [
                'name' => 'inventory',
                'sort' => 9,
                'children' => [
                    ['name' => 'inventory_log', 'sort' => 1],
                    ['name' => 'stock_utilized', 'sort' => 2],
                    [
                        'name' => 'inventory_reports',
                        'sort' => 3,
                        'children' => [
                            ['name' => 'stock_report', 'sort' => 1],
                            ['name' => 'batch_stock_report', 'sort' => 2],
                            ['name' => 'stock_valuation_report', 'sort' => 3],
                            ['name' => 'batch_stock_valuation_report', 'sort' => 4],
                        ]
                    ]
                ]
            ],

            // 10. Sales
            [
                'name' => 'sales_management',
                'sort' => 10,
                'children' => [
                    [
                        'name' => 'sales',
                        'sort' => 1,
                        'children' => [
                            ['name' => 'sales_create', 'sort' => 1],
                            ['name' => 'sales_edit', 'sort' => 2],
                            ['name' => 'sales_delete', 'sort' => 3],
                            ['name' => 'sales_payment_receive', 'sort' => 4],
                        ]
                    ],
                    [
                        'name' => 'customer',
                        'sort' => 2,
                        'children' => [
                            ['name' => 'customer_create', 'sort' => 1],
                            ['name' => 'customer_edit', 'sort' => 2],
                            ['name' => 'customer_delete', 'sort' => 3],
                        ]
                    ],
                    ['name' => 'sales_settings', 'sort' => 3],
                    ['name' => 'daily_collection_approved', 'sort' => 4],
                    [
                        'name' => 'pre_orders',
                        'sort' => 5,
                        'children' => [
                            ['name' => 'pre_orders_create', 'sort' => 1],
                            ['name' => 'pre_orders_edit', 'sort' => 2],
                            ['name' => 'pre_orders_delete', 'sort' => 3],
                        ]
                    ],
                    [
                        'name' => 'corporate_customer',
                        'sort' => 7,
                        'children' => [
                            ['name' => 'corporate_customer_create', 'sort' => 1],
                            ['name' => 'corporate_customer_edit', 'sort' => 2],
                            ['name' => 'corporate_customer_delete', 'sort' => 3],
                        ]
                    ],
                    [
                        'name' => 'corporate_sales',
                        'sort' => 8,
                        'children' => [
                            ['name' => 'corporate_sales_create', 'sort' => 1],
                            ['name' => 'corporate_sales_edit', 'sort' => 2],
                            ['name' => 'corporate_sales_delete', 'sort' => 3],
                        ]
                    ],
                    [
                        'name' => 'dealer',
                        'sort' => 9,
                        'children' => [
                            ['name' => 'dealer_create', 'sort' => 1],
                            ['name' => 'dealer_edit', 'sort' => 2],
                            ['name' => 'dealer_delete', 'sort' => 3],
                        ]
                    ],
                    [
                        'name' => 'dealer_sales',
                        'sort' => 10,
                        'children' => [
                            ['name' => 'dealer_sales_create', 'sort' => 1],
                            ['name' => 'dealer_sales_edit', 'sort' => 2],
                            ['name' => 'dealer_sales_delete', 'sort' => 3],
                        ]
                    ],
                    [
                        'name' => 'service_request',
                        'sort' => 11,
                        'children' => [
                            ['name' => 'service_request_create', 'sort' => 1],
                            ['name' => 'service_request_edit', 'sort' => 2],
                            ['name' => 'service_request_delete', 'sort' => 3],
                            ['name' => 'service_request_approve', 'sort' => 4],
                        ]
                    ],
                    [
                        'name' => 'technician',
                        'sort' => 12,
                        'children' => [
                            ['name' => 'technician_create', 'sort' => 1],
                            ['name' => 'technician_edit', 'sort' => 2],
                            ['name' => 'technician_delete', 'sort' => 3],
                        ]
                    ],
                    [
                        'name' => 'service_panel',
                        'sort' => 13,
                        'children' => [
                            ['name' => 'service_panel_update', 'sort' => 1],
                        ]
                    ],
                    [
                        'name' => 'sales_reports',
                        'sort' => 14,
                        'children' => [
                            ['name' => 'sale_report', 'sort' => 1],
                            ['name' => 'customer_due_report', 'sort' => 2],
                            ['name' => 'customer_report', 'sort' => 3],
                        ]
                    ]
                ]
            ],

            // 10.5 Returns Management
            [
                'name' => 'returns_management',
                'sort' => 10,
                'children' => [
                    [
                        'name' => 'sales_return',
                        'sort' => 1,
                        'children' => [
                            ['name' => 'sales_return_create', 'sort' => 1],
                            ['name' => 'sales_return_edit', 'sort' => 2],
                            ['name' => 'sales_return_delete', 'sort' => 3],
                            ['name' => 'sales_return_approve', 'sort' => 4],
                        ]
                    ],
                    [
                        'name' => 'purchase_return',
                        'sort' => 2,
                        'children' => [
                            ['name' => 'purchase_return_create', 'sort' => 1],
                            ['name' => 'purchase_return_edit', 'sort' => 2],
                            ['name' => 'purchase_return_delete', 'sort' => 3],
                            ['name' => 'purchase_return_approve', 'sort' => 4],
                        ]
                    ]
                ]
            ],

            // 11. Ecommerce
            [
                'name' => 'ecommerce',
                'sort' => 11,
                'children' => [
                    [
                        'name' => 'ecommerce_settings',
                        'sort' => 1,
                        'children' => [
                            ['name' => 'ecommerce_settings_edit', 'sort' => 1],
                        ]
                    ],
                    [
                        'name' => 'ecommerce_banner',
                        'sort' => 2,
                        'children' => [
                            ['name' => 'ecommerce_banner_create', 'sort' => 1],
                            ['name' => 'ecommerce_banner_edit', 'sort' => 2],
                            ['name' => 'ecommerce_banner_delete', 'sort' => 3],
                        ]
                    ],
                    [
                        'name' => 'ecommerce_gallery',
                        'sort' => 3,
                        'children' => [
                            ['name' => 'ecommerce_gallery_create', 'sort' => 1],
                            ['name' => 'ecommerce_gallery_edit', 'sort' => 2],
                            ['name' => 'ecommerce_gallery_delete', 'sort' => 3],
                        ]
                    ],
                    [
                        'name' => 'ecommerce_page',
                        'sort' => 4,
                        'children' => [
                            ['name' => 'ecommerce_page_create', 'sort' => 1],
                            ['name' => 'ecommerce_page_edit', 'sort' => 2],
                            ['name' => 'ecommerce_page_delete', 'sort' => 3],
                        ]
                    ],
                    [
                        'name' => 'ecommerce_coupon',
                        'sort' => 5,
                        'children' => [
                            ['name' => 'ecommerce_coupon_create', 'sort' => 1],
                            ['name' => 'ecommerce_coupon_edit', 'sort' => 2],
                            ['name' => 'ecommerce_coupon_delete', 'sort' => 3],
                        ]
                    ],
                    [
                        'name' => 'ecommerce_product',
                        'sort' => 6,
                        'children' => [
                            ['name' => 'ecommerce_product_create', 'sort' => 1],
                            ['name' => 'ecommerce_product_edit', 'sort' => 2],
                            ['name' => 'ecommerce_product_delete', 'sort' => 3],
                        ]
                    ],
                    [
                        'name' => 'ecommerce_filter',
                        'sort' => 7,
                        'children' => [
                            ['name' => 'ecommerce_filter_create', 'sort' => 1],
                            ['name' => 'ecommerce_filter_edit', 'sort' => 2],
                            ['name' => 'ecommerce_filter_delete', 'sort' => 3],
                        ]
                    ],
                    [
                        'name' => 'ecommerce_order',
                        'sort' => 8,
                        'children' => [
                            ['name' => 'ecommerce_order_confirm', 'sort' => 1],
                            ['name' => 'ecommerce_order_edit', 'sort' => 2],
                            ['name' => 'ecommerce_order_delete', 'sort' => 3],
                        ]
                    ],
                    [
                        'name' => 'ecommerce_blog',
                        'sort' => 9,
                        'children' => [
                            ['name' => 'ecommerce_blog_create', 'sort' => 1],
                            ['name' => 'ecommerce_blog_edit', 'sort' => 2],
                            ['name' => 'ecommerce_blog_delete', 'sort' => 3],
                        ]
                    ],
                    [
                        'name' => 'ecommerce_contact',
                        'sort' => 10,
                        'children' => [
                            ['name' => 'ecommerce_contact_edit', 'sort' => 1],
                            ['name' => 'ecommerce_contact_delete', 'sort' => 2],
                        ]
                    ],
                ]
            ],

            // 12. Stock Transfer
            [
                'name' => 'stock_transfer_management',
                'sort' => 12,
                'children' => [
                    [
                        'name' => 'stock_transfer',
                        'sort' => 1,
                        'children' => [
                            ['name' => 'stock_transfer_create', 'sort' => 1],
                            ['name' => 'stock_transfer_edit', 'sort' => 2],
                            ['name' => 'stock_transfer_delete', 'sort' => 3],
                            ['name' => 'stock_transfer_receive', 'sort' => 4],
                        ]
                    ],
                    [
                        'name' => 'stock_transfer_reports',
                        'sort' => 2,
                        'children' => [
                            ['name' => 'stock_transfer_report', 'sort' => 1],
                        ]
                    ]
                ]
            ],
    
            // 13. Stock Conversion
            [
                'name' => 'stock_conversion_management',
                'sort' => 13,
                'children' => [
                    [
                        'name' => 'stock_conversion',
                        'sort' => 1,
                        'children' => [
                            ['name' => 'stock_conversion_create', 'sort' => 1],
                            ['name' => 'stock_conversion_edit', 'sort' => 2],
                            ['name' => 'stock_conversion_delete', 'sort' => 3],
                            ['name' => 'stock_conversion_approve', 'sort' => 4],
                        ]
                    ],
                    [
                        'name' => 'stock_conversion_reports',
                        'sort' => 2,
                        'children' => [
                            ['name' => 'stock_conversion_report', 'sort' => 1],
                        ]
                    ]
                ]
            ],

            // 14. SR Management
            [
                'name' => 'sr_management',
                'sort' => 14,
                'children' => [
                    [
                        'name' => 'sr',
                        'sort' => 1,
                        'children' => [
                            ['name' => 'sr_create', 'sort' => 1],
                            ['name' => 'sr_edit', 'sort' => 2],
                            ['name' => 'sr_delete', 'sort' => 3],
                        ]
                    ],
                    [
                        'name' => 'sr_tasks',
                        'sort' => 2,
                        'children' => [
                            ['name' => 'sr_tasks_create', 'sort' => 1],
                            ['name' => 'sr_tasks_edit', 'sort' => 2],
                            ['name' => 'sr_tasks_delete', 'sort' => 3],
                        ]
                    ],
                    [
                        'name' => 'sr_visits',
                        'sort' => 3,
                        'children' => [
                            ['name' => 'sr_visits_create', 'sort' => 1],
                            ['name' => 'sr_visits_edit', 'sort' => 2],
                            ['name' => 'sr_visits_delete', 'sort' => 3],
                        ]
                    ],
                    [
                        'name' => 'sr_reports',
                        'sort' => 4,
                        'children' => [
                            ['name' => 'sr_performance_report', 'sort' => 1],
                            ['name' => 'sr_performance_summary_report', 'sort' => 2],
                            ['name' => 'sr_tasks_report', 'sort' => 3],
                            ['name' => 'sr_visits_report', 'sort' => 4],
                            ['name' => 'sr_leads_report', 'sort' => 5],
                        ]
                    ],
                    [
                        'name' => 'sr_lead_collection',
                        'sort' => 5,
                        'children' => [
                            ['name' => 'sr_lead_collection_create', 'sort' => 1],
                            ['name' => 'sr_lead_collection_edit', 'sort' => 2],
                            ['name' => 'sr_lead_collection_delete', 'sort' => 3],
                            ['name' => 'sr_lead_collection_feedback_update', 'sort' => 4],
                        ]
                    ],
                    [
                        'name' => 'sr_route_plan',
                        'sort' => 6,
                        'children' => []
                    ],
                ]
            ],

            // 15. Route Plan
            [
                'name' => 'route_plan_management',
                'sort' => 15,
                'children' => [
                    [
                        'name' => 'route_plans',
                        'sort' => 2,
                        'children' => [
                            ['name' => 'route_plan_create', 'sort' => 1],
                            ['name' => 'route_plan_edit', 'sort' => 2],
                            ['name' => 'route_plan_delete', 'sort' => 3],
                        ]
                    ],
                    [
                        'name' => 'route_plan_reports',
                        'sort' => 3,
                        'children' => [
                            ['name' => 'report_route_leads', 'sort' => 1],
                            ['name' => 'report_route_coverage', 'sort' => 2],
                            ['name' => 'report_weekly_route_plans', 'sort' => 3],
                        ]
                    ],
                ]
            ],

            // 16. Trainee Management
            [
                'name' => 'trainee_management',
                'sort' => 15,
                'children' => [
                    [
                        'name' => 'trainee',
                        'sort' => 1,
                        'children' => [
                            ['name' => 'trainee_create', 'sort' => 1],
                            ['name' => 'trainee_edit', 'sort' => 2],
                            ['name' => 'trainee_delete', 'sort' => 3],
                        ]
                    ],
                    [
                        'name' => 'trainee_tasks',
                        'sort' => 2,
                        'children' => [
                            ['name' => 'trainee_tasks_create', 'sort' => 1],
                            ['name' => 'trainee_tasks_edit', 'sort' => 2],
                            ['name' => 'trainee_tasks_delete', 'sort' => 3],
                        ]
                    ],
                    [
                        'name' => 'trainee_visits',
                        'sort' => 3,
                        'children' => [
                            ['name' => 'trainee_visits_create', 'sort' => 1],
                            ['name' => 'trainee_visits_edit', 'sort' => 2],
                            ['name' => 'trainee_visits_delete', 'sort' => 3],
                        ]
                    ],
                    [
                        'name' => 'trainee_reports',
                        'sort' => 4,
                        'children' => [
                            ['name' => 'trainee_performance_report', 'sort' => 1],
                            ['name' => 'trainee_performance_summary_report', 'sort' => 2],
                            ['name' => 'trainee_visits_report', 'sort' => 3],
                        ]
                    ],
                ]
            ],
            
            // 17. Microfinance
            [
                'name' => 'microfinance',
                'sort' => 17,
                'children' => [
                    [
                        'name' => 'mf_field_officer',
                        'sort' => 1,
                        'children' => [
                            ['name' => 'mf_field_officer_create', 'sort' => 1],
                            ['name' => 'mf_field_officer_edit', 'sort' => 2],
                            ['name' => 'mf_field_officer_delete', 'sort' => 3],
                            ['name' => 'mf_field_officer_assignment', 'sort' => 4],
                        ]
                    ],
                    [
                        'name' => 'mf_somiti',
                        'sort' => 2,
                        'children' => [
                            ['name' => 'mf_somiti_create', 'sort' => 1],
                            ['name' => 'mf_somiti_edit', 'sort' => 2],
                            ['name' => 'mf_somiti_delete', 'sort' => 3],
                        ]
                    ],
                    [
                        'name' => 'mf_member',
                        'sort' => 3,
                        'children' => [
                            ['name' => 'mf_member_create', 'sort' => 1],
                            ['name' => 'mf_member_edit', 'sort' => 2],
                            ['name' => 'mf_member_delete', 'sort' => 3],
                        ]
                    ],
                    [
                        'name' => 'mf_savings_product',
                        'sort' => 4,
                        'children' => [
                            ['name' => 'mf_savings_product_create', 'sort' => 1],
                            ['name' => 'mf_savings_product_edit', 'sort' => 2],
                            ['name' => 'mf_savings_product_delete', 'sort' => 3],
                        ]
                    ],
                    [
                        'name' => 'mf_savings_account',
                        'sort' => 5,
                        'children' => [
                            ['name' => 'mf_savings_account_create', 'sort' => 1],
                            ['name' => 'mf_savings_account_edit', 'sort' => 2],
                            ['name' => 'mf_savings_account_delete', 'sort' => 3],
                            ['name' => 'mf_savings_account_approve', 'sort' => 4],
                            ['name' => 'mf_savings_account_withdraw', 'sort' => 5],
                            ['name' => 'mf_savings_account_interest', 'sort' => 6],
                            ['name' => 'mf_savings_account_close', 'sort' => 7],
                            ['name' => 'mf_savings_account_renew', 'sort' => 8],
                        ]
                    ],
                    [
                        'name' => 'mf_loan_product',
                        'sort' => 6,
                        'children' => [
                            ['name' => 'mf_loan_product_create', 'sort' => 1],
                            ['name' => 'mf_loan_product_edit', 'sort' => 2],
                            ['name' => 'mf_loan_product_delete', 'sort' => 3],
                        ]
                    ],
                    [
                        'name' => 'mf_loan',
                        'sort' => 7,
                        'children' => [
                            ['name' => 'mf_loan_create', 'sort' => 1],
                            ['name' => 'mf_loan_edit', 'sort' => 2],
                            ['name' => 'mf_loan_delete', 'sort' => 3],
                            ['name' => 'mf_loan_approve', 'sort' => 4],
                            ['name' => 'mf_loan_disburse', 'sort' => 5],
                            ['name' => 'mf_loan_reject', 'sort' => 6],
                            ['name' => 'mf_loan_close', 'sort' => 7],
                        ]
                    ],
                    [
                        'name' => 'mf_collection',
                        'sort' => 8,
                        'children' => [
                            ['name' => 'mf_collection_view', 'sort' => 1],
                            ['name' => 'mf_collection_collect', 'sort' => 2],
                        ]
                    ],
                    [
                        'name' => 'mf_reports',
                        'sort' => 9,
                        'children' => [
                            ['name' => 'mf_report_member_ledger', 'sort' => 1],
                            ['name' => 'mf_report_collection_sheet', 'sort' => 2],
                            ['name' => 'mf_report_loan_portfolio', 'sort' => 3],
                            ['name' => 'mf_report_savings_portfolio', 'sort' => 4],
                            ['name' => 'mf_report_due_overdue', 'sort' => 5],
                            ['name' => 'mf_report_loan_disbursement', 'sort' => 6],
                        ]
                    ],
                    [
                        'name' => 'mf_day_closing',
                        'sort' => 10,
                    ],
                    [
                        'name' => 'mf_opening_balance',
                        'sort' => 11,
                    ],
                ]
            ],

            // 18. Company Loan
            [
                'name' => 'company_loan',
                'sort' => 18,
                'children' => [
                    [
                        'name' => 'manage_company_loan',
                        'sort' => 1,
                        'children' => [
                            ['name' => 'company_loan_create', 'sort' => 1],
                            ['name' => 'company_loan_edit', 'sort' => 2],
                            ['name' => 'company_loan_approve', 'sort' => 3],
                            ['name' => 'company_loan_payment', 'sort' => 4],
                        ]
                    ],
                    [
                        'name' => 'company_loan_reports',
                        'sort' => 2,
                        'children' => [
                            ['name' => 'company_loan_report_statement', 'sort' => 1],
                            ['name' => 'company_loan_report_receivable', 'sort' => 2],
                            ['name' => 'company_loan_report_payable', 'sort' => 3],
                            ['name' => 'company_loan_report_due_installments', 'sort' => 4],
                        ]
                    ]
                ]
            ],

            // 19. Monitoring
            [
                'name' => 'monitoring',
                'sort' => 19,
                'children' => [
                    ['name' => 'live_location', 'sort' => 1],
                    ['name' => 'location_history', 'sort' => 2],
                ]
            ],

            // 20. Product Utilize
            [
                'name' => 'product_utilize',
                'sort' => 20,
                'children' => [
                    ['name' => 'product_utilize_create', 'sort' => 1],
                    ['name' => 'product_utilize_edit', 'sort' => 2],
                    ['name' => 'product_utilize_delete', 'sort' => 3],
                    ['name' => 'product_utilize_list', 'sort' => 4],
                    [
                        'name' => 'product_utilize_reports',
                        'sort' => 5,
                        'children' => [
                            ['name' => 'product_utilize_report', 'sort' => 1],
                        ]
                    ],
                ]
            ],


            // 21. Card Management
            [
                'name' => 'card_management',
                'sort' => 21,
                'children' => [
                    [
                        'name' => 'member_card',
                        'sort' => 1,
                        'children' => [
                            ['name' => 'member_card_issue',  'sort' => 1],
                            ['name' => 'member_card_print',  'sort' => 2],
                            ['name' => 'member_card_status', 'sort' => 3],
                            ['name' => 'member_card_logs',   'sort' => 4],
                        ]
                    ],
                    [
                        'name' => 'employee_card',
                        'sort' => 2,
                        'children' => [
                            ['name' => 'employee_card_issue',  'sort' => 1],
                            ['name' => 'employee_card_print',  'sort' => 2],
                            ['name' => 'employee_card_status', 'sort' => 3],
                            ['name' => 'employee_card_logs',   'sort' => 4],
                        ]
                    ],
                    [
                        'name' => 'card_management_reports',
                        'sort' => 3,
                        'children' => [
                            ['name' => 'member_card_report', 'sort' => 1],
                            ['name' => 'employee_card_report', 'sort' => 2],
                        ]
                    ],
                ]
            ],

            // 22. Accounts
            [
                'name' => 'accounts',
                'sort' => 22,
                'children' => [
                    [
                        'name' => 'account_group',
                        'sort' => 1,
                        'children' => [
                            ['name' => 'account_group_create', 'sort' => 1],
                            ['name' => 'account_group_edit', 'sort' => 2],
                            ['name' => 'account_group_delete', 'sort' => 3],
                        ]
                    ],
                    [
                        'name' => 'account_head',
                        'sort' => 2,
                        'children' => [
                            ['name' => 'account_head_create', 'sort' => 1],
                            ['name' => 'account_head_edit', 'sort' => 2],
                            ['name' => 'account_head_delete', 'sort' => 3],
                        ]
                    ],
                    [
                        'name' => 'payment_modes',
                        'sort' => 3,
                        'children' => [
                            ['name' => 'payment_modes_create', 'sort' => 1],
                            ['name' => 'payment_modes_edit', 'sort' => 2],
                            ['name' => 'payment_modes_delete', 'sort' => 3],
                        ]
                    ],
                    [
                        'name' => 'journal_voucher',
                        'sort' => 4,
                        'children' => [
                            ['name' => 'journal_voucher_create', 'sort' => 1],
                            ['name' => 'journal_voucher_edit', 'sort' => 2],
                            ['name' => 'journal_voucher_delete', 'sort' => 3],
                        ]
                    ],
                    [
                        'name' => 'payment_voucher',
                        'sort' => 5,
                        'children' => [
                            ['name' => 'payment_voucher_create', 'sort' => 1],
                            ['name' => 'payment_voucher_edit', 'sort' => 2],
                            ['name' => 'payment_voucher_delete', 'sort' => 3],
                        ]
                    ],
                    [
                        'name' => 'receipt_voucher',
                        'sort' => 6,
                        'children' => [
                            ['name' => 'receipt_voucher_create', 'sort' => 1],
                            ['name' => 'receipt_voucher_edit', 'sort' => 2],
                            ['name' => 'receipt_voucher_delete', 'sort' => 3],
                        ]
                    ],
                    [
                        'name' => 'contra_voucher',
                        'sort' => 7,
                        'children' => [
                            ['name' => 'contra_voucher_create', 'sort' => 1],
                            ['name' => 'contra_voucher_edit', 'sort' => 2],
                            ['name' => 'contra_voucher_delete', 'sort' => 3],
                        ]
                    ],
                    [
                        'name' => 'cashbook',
                        'sort' => 8,
                        'children' => [
                            ['name' => 'cashbook_add_fund', 'sort' => 1],
                            ['name' => 'cashbook_add_fund_delete', 'sort' => 2],
                            ['name' => 'cashbook_add_expense', 'sort' => 3],
                            ['name' => 'cashbook_add_expense_delete', 'sort' => 4],
                        ]
                    ],
                    [
                        'name' => 'accounts_reports',
                        'sort' => 9,
                        'children' => [
                            ['name' => 'cash_book', 'sort' => 1],
                            ['name' => 'receipt_and_payment', 'sort' => 2],
                            ['name' => 'ledger', 'sort' => 3],
                            ['name' => 'trial_balance', 'sort' => 4],
                            ['name' => 'schedule_of_accounts', 'sort' => 5],
                            ['name' => 'income_statement', 'sort' => 6],
                            ['name' => 'balance_sheet', 'sort' => 7],
                            ['name' => 'daily_transactions_report', 'sort' => 8],
                        ]
                    ],
                ]
            ],

            // 15. Shomriddhi Advance
            [
                'name' => 'shomriddhi_advance',
                'sort' => 15,
                'children' => [
                    ['name' => 'shomriddhi_advance_dashboard', 'sort' => 1],
                    [
                        'name' => 'shomriddhi_advance_transactions',
                        'sort' => 2,
                        'children' => [
                            ['name' => 'shomriddhi_advance_transaction_create', 'sort' => 1],
                            ['name' => 'shomriddhi_advance_transaction_approve', 'sort' => 2],
                            ['name' => 'shomriddhi_advance_transaction_reject', 'sort' => 3],
                        ]
                    ],
                    [
                        'name' => 'shomriddhi_advance_reports',
                        'sort' => 3,
                        'children' => [
                            ['name' => 'shomriddhi_advance_ledger_report', 'sort' => 1],
                            ['name' => 'shomriddhi_advance_summary_report', 'sort' => 2],
                        ]
                    ]
                ]
            ],

            // 15.5 Shomriddhi FDR
            [
                'name' => 'shomriddhi_fdr',
                'sort' => 15,
                'children' => [
                    ['name' => 'shomriddhi_fdr_dashboard', 'sort' => 1],
                    [
                        'name' => 'shomriddhi_fdr_schemes',
                        'sort' => 2,
                        'children' => [
                            ['name' => 'shomriddhi_fdr_scheme_create', 'sort' => 1],
                            ['name' => 'shomriddhi_fdr_scheme_edit', 'sort' => 2],
                            ['name' => 'shomriddhi_fdr_scheme_delete', 'sort' => 3],
                        ]
                    ],
                    [
                        'name' => 'shomriddhi_fdr_accounts',
                        'sort' => 3,
                        'children' => [
                            ['name' => 'shomriddhi_fdr_account_create', 'sort' => 1],
                            ['name' => 'shomriddhi_fdr_account_approve', 'sort' => 2],
                            ['name' => 'shomriddhi_fdr_account_reject', 'sort' => 3],
                        ]
                    ],
                    [
                        'name' => 'shomriddhi_fdr_reports',
                        'sort' => 4,
                        'children' => [
                            ['name' => 'shomriddhi_fdr_maturity_report', 'sort' => 1],
                            ['name' => 'shomriddhi_fdr_transaction_report', 'sort' => 2],
                            ['name' => 'shomriddhi_fdr_ledger_report', 'sort' => 3],
                            ['name' => 'shomriddhi_fdr_active_accounts_report', 'sort' => 4],
                            ['name' => 'shomriddhi_fdr_closed_accounts_report', 'sort' => 5],
                            ['name' => 'shomriddhi_fdr_scheme_summary_report', 'sort' => 6],
                        ]
                    ]
                ]
            ],

            // 15.6 Shomriddhi DPS
            [
                'name' => 'shomriddhi_dps',
                'sort' => 16,
                'children' => [
                    ['name' => 'shomriddhi_dps_dashboard', 'sort' => 1],
                    [
                        'name' => 'shomriddhi_dps_schemes',
                        'sort' => 2,
                        'children' => [
                            ['name' => 'shomriddhi_dps_scheme_create', 'sort' => 1],
                            ['name' => 'shomriddhi_dps_scheme_edit', 'sort' => 2],
                            ['name' => 'shomriddhi_dps_scheme_delete', 'sort' => 3],
                        ]
                    ],
                    [
                        'name' => 'shomriddhi_dps_accounts',
                        'sort' => 3,
                        'children' => [
                            ['name' => 'shomriddhi_dps_account_view', 'sort' => 1],
                            ['name' => 'shomriddhi_dps_account_create', 'sort' => 2],
                            ['name' => 'shomriddhi_dps_account_edit', 'sort' => 3],
                            ['name' => 'shomriddhi_dps_account_approve', 'sort' => 4],
                            ['name' => 'shomriddhi_dps_account_encash', 'sort' => 5],
                            ['name' => 'shomriddhi_dps_account_pay_installment', 'sort' => 6],
                            ['name' => 'shomriddhi_dps_account_delete', 'sort' => 7],
                        ]
                    ],
                    [
                        'name' => 'shomriddhi_dps_reports',
                        'sort' => 4,
                        'children' => [
                            ['name' => 'shomriddhi_dps_active_accounts_report', 'sort' => 1],
                            ['name' => 'shomriddhi_dps_matured_accounts_report', 'sort' => 2],
                            ['name' => 'shomriddhi_dps_defaulted_accounts_report', 'sort' => 3],
                            ['name' => 'shomriddhi_dps_closed_accounts_report', 'sort' => 4],
                            ['name' => 'shomriddhi_dps_transaction_report', 'sort' => 5],
                            ['name' => 'shomriddhi_dps_ledger_report', 'sort' => 6],
                            ['name' => 'shomriddhi_dps_scheme_summary_report', 'sort' => 7],
                        ]
                    ]
                ]
            ],

            // 15.7 Shomriddhi Loan
            [
                'name' => 'shomriddhi_loan',
                'sort' => 17,
                'children' => [
                    ['name' => 'shomriddhi_loan_dashboard', 'sort' => 1],
                    [
                        'name' => 'shomriddhi_loan_accounts',
                        'sort' => 2,
                        'children' => [
                            ['name' => 'shomriddhi_loan_account_create', 'sort' => 1],
                            ['name' => 'shomriddhi_loan_account_edit', 'sort' => 2],
                            ['name' => 'shomriddhi_loan_account_view', 'sort' => 3],
                            ['name' => 'shomriddhi_loan_account_approve', 'sort' => 4],
                            ['name' => 'shomriddhi_loan_account_delete', 'sort' => 5],
                        ]
                    ],
                    [
                        'name' => 'shomriddhi_loan_collections',
                        'sort' => 3,
                        'children' => [
                            ['name' => 'shomriddhi_loan_collection_create', 'sort' => 1],
                            ['name' => 'shomriddhi_loan_collection_history', 'sort' => 2],
                        ]
                    ],
                    ['name' => 'shomriddhi_loan_reschedule', 'sort' => 4],
                    [
                        'name' => 'shomriddhi_loan_reports',
                        'sort' => 5,
                        'children' => [
                            ['name' => 'shomriddhi_loan_ledger_report', 'sort' => 1],
                            ['name' => 'shomriddhi_loan_collection_summary_report', 'sort' => 2],
                            ['name' => 'shomriddhi_loan_overdue_report', 'sort' => 3],
                        ]
                    ],
                    [
                        'name' => 'product_loans',
                        'sort' => 6,
                        'children' => [
                            ['name' => 'product_loans_create', 'sort' => 1],
                            ['name' => 'product_loans_edit', 'sort' => 2],
                            ['name' => 'product_loans_delete', 'sort' => 3],
                        ]
                    ],
                ]
            ],

            // 16. Incentive Management
            [
                'name' => 'incentive_management',
                'sort' => 16,
                'children' => [
                    ['name' => 'incentive_settings', 'sort' => 1],
                    ['name' => 'incentive_payment', 'sort' => 2],
                ]
            ],

            // 17. SMS Management
            [
                'name' => 'sms_management',
                'sort' => 17,
                'children' => [
                    ['name' => 'sms_dashboard', 'sort' => 1],
                    ['name' => 'sms_gateways', 'sort' => 2],
                    ['name' => 'sms_templates', 'sort' => 3],
                    ['name' => 'sms_campaign', 'sort' => 4],
                    ['name' => 'sms_logs', 'sort' => 5],
                ]
            ],

            // 18. Fixed Asset Management
            [
                'name' => 'fixed_asset',
                'sort' => 18,
                'children' => [
                    ['name' => 'fixed_asset_view', 'sort' => 1],
                    ['name' => 'fixed_asset_create', 'sort' => 2],
                    ['name' => 'fixed_asset_edit', 'sort' => 3],
                    ['name' => 'fixed_asset_sell', 'sort' => 4],
                    ['name' => 'fixed_asset_scrap', 'sort' => 5],
                    ['name' => 'fixed_asset_depreciation_run', 'sort' => 6],
                    ['name' => 'fixed_asset_category_view', 'sort' => 7],
                    ['name' => 'fixed_asset_category_create', 'sort' => 8],
                    ['name' => 'fixed_asset_category_edit', 'sort' => 9],
                    ['name' => 'fixed_asset_category_delete', 'sort' => 10],
                    ['name' => 'fixed_asset_report', 'sort' => 11],
                ]
            ],

            // 19. Contractor Management
            [
                'name' => 'contractor_management',
                'sort' => 19,
                'children' => [
                    [
                        'name' => 'contractor_type',
                        'sort' => 1,
                        'children' => [
                            ['name' => 'contractor_type_view', 'sort' => 1],
                            ['name' => 'contractor_type_create', 'sort' => 2],
                            ['name' => 'contractor_type_edit', 'sort' => 3],
                            ['name' => 'contractor_type_delete', 'sort' => 4],
                        ]
                    ],
                    [
                        'name' => 'contractor',
                        'sort' => 2,
                        'children' => [
                            ['name' => 'contractor_view', 'sort' => 1],
                            ['name' => 'contractor_create', 'sort' => 2],
                            ['name' => 'contractor_edit', 'sort' => 3],
                            ['name' => 'contractor_delete', 'sort' => 4],
                        ]
                    ],
                    [
                        'name' => 'contractor_expense_head',
                        'sort' => 3,
                        'children' => [
                            ['name' => 'contractor_expense_head_view', 'sort' => 1],
                            ['name' => 'contractor_expense_head_create', 'sort' => 2],
                            ['name' => 'contractor_expense_head_edit', 'sort' => 3],
                            ['name' => 'contractor_expense_head_delete', 'sort' => 4],
                        ]
                    ],
                    [
                        'name' => 'contractor_income_head',
                        'sort' => 3,
                        'children' => [
                            ['name' => 'contractor_income_head_view', 'sort' => 1],
                            ['name' => 'contractor_income_head_create', 'sort' => 2],
                            ['name' => 'contractor_income_head_edit', 'sort' => 3],
                            ['name' => 'contractor_income_head_delete', 'sort' => 4],
                        ]
                    ],
                    [
                        'name' => 'contractor_bill',
                        'sort' => 4,
                        'children' => [
                            ['name' => 'contractor_bill_view', 'sort' => 1],
                            ['name' => 'contractor_bill_create', 'sort' => 2],
                            ['name' => 'contractor_bill_edit', 'sort' => 3],
                            ['name' => 'contractor_bill_delete', 'sort' => 4],
                        ]
                    ],
                    [
                        'name' => 'contractor_payment',
                        'sort' => 5,
                        'children' => [
                            ['name' => 'contractor_payment_view', 'sort' => 1],
                            ['name' => 'contractor_payment_create', 'sort' => 2],
                            ['name' => 'contractor_payment_edit', 'sort' => 3],
                            ['name' => 'contractor_payment_delete', 'sort' => 4],
                        ]
                    ],
                    [
                        'name' => 'contractor_report',
                        'sort' => 6,
                        'children' => [
                            ['name' => 'contractor_ledger', 'sort' => 1],
                            ['name' => 'contractor_expense_report', 'sort' => 2],
                            ['name' => 'contractor_income_report', 'sort' => 3],
                            ['name' => 'contractor_outstanding_report', 'sort' => 4],
                            ['name' => 'contractor_bill_summary_report', 'sort' => 5],
                        ]
                    ]
                ]
            ],

            // 20. Forms Hub
            [
                'name' => 'forms_hub',
                'sort' => 20,
            ],
        ];

        $processedNames = [];
        DB::transaction(function () use ($permissionsGroup, &$processedNames) {
            $this->seedPermissions($permissionsGroup, null, $processedNames);

            // Clean up any permissions from the database that are no longer defined in the seeder
            DB::table('permissions')
                ->whereNotIn('name', $processedNames)
                ->delete();
        });

        // Forget cached permissions once at the end of the seeding process
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        // Assign all permissions to Admin role
        $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $allPermissions = Permission::all();
        $adminRole->syncPermissions($allPermissions);
    }

    /**
     * Recursively seed permissions with hierarchical parent_id mapping.
     */
    private function seedPermissions(array $permissions, ?int $parentId, array &$processedNames): void
    {
        foreach ($permissions as $index => $item) {
            $name = $item['name'];
            $sort = $item['sort'] ?? ($index + 1);
            $guardName = $item['guard_name'] ?? 'web';

            // Safe direct DB update/insert to bypass redundant Eloquent events and cache clearing
            $existing = DB::table('permissions')
                ->where('name', $name)
                ->where('guard_name', $guardName)
                ->first();

            if ($existing) {
                DB::table('permissions')
                    ->where('id', $existing->id)
                    ->update([
                        'parent_id' => $parentId,
                        'sort' => $sort,
                        'updated_at' => now(),
                    ]);
                $permissionId = $existing->id;
            } else {
                $permissionId = DB::table('permissions')->insertGetId([
                    'name' => $name,
                    'guard_name' => $guardName,
                    'parent_id' => $parentId,
                    'sort' => $sort,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $processedNames[] = $name;

            if (isset($item['children']) && is_array($item['children'])) {
                $this->seedPermissions($item['children'], $permissionId, $processedNames);
            }
        }
    }
}
