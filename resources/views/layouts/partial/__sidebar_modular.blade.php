@php
    $currentRoute = Route::currentRouteName();
    
    // Define prefix patterns for custom active modules (more specific patterns listed first to prevent overlap)
    $moduleMap = [
        'office' => [
            'notices.', 'meetings.', 'office_management.'
        ],
        'users' => [
            'user.', 'role.', 'user_management.'
        ],
        'branches' => [
            'sister-concerns.', 'branch_management.', 'branch.'
        ],
        'crm' => [
            'crm.dashboard', 'leads.', 'crm-statuses.', 'customer-types.', 'report.leads', 'report.outlet_types', 'report.markets', 'markets.'
        ],
        'hrm' => [
            'hrm.dashboard', 'employees.', 'departments.', 'designations.', 'employee-statuses.', 
            'holidays.', 'shifts.', 'leave-types.', 'leave.', 'employee-movements.',
            'report.live_attendance', 'report.attendance-sheet', 'report.attendance-summary', 'report.attendance-reminder',
            'report.department', 'report.designation', 'report.holiday', 'report.employee',
            'report.daily-attendance-sheet', 'hrm-settings.index'
        ],
        'payroll' => [
            'payroll.dashboard', 'salary-processes.', 'salary-structures.', 'report.salary-sheet', 'employee-advances.', 'report.employee-advance'
        ],
        'manager_management' => [
            'manager_management.', 'area-manager.', 'branch-manager.', 'area-manager-assignments.', 
            'branch-manager-assignments.', 'manager-assignments.', 'tasks.',
            'report.area_manager_history', 'report.branch_manager_history',
            'report.area_manager', 'report.branch_manager'
        ],
        'settings' => [
            'settings.dashboard', 'warehouses.', 'product-categories.', 
            'sub-category.', 'brand.', 'unit.', 'product.', 'price_book.',
            'locations.',
            'report.brand', 'report.category', 'report.sub_category', 'report.product', 'report.price_book', 'report.price_book_history'
        ],
        'utilize' => [
            'product-utilizes.'
        ],
        'purchase' => [
            'purchase.', 'supplier.', 'purchase_details.'
        ],
        'inventory' => [
            'inventory.dashboard', 'inventory.', 'report.stock', 'report.product_ledger', 'report.batch_stock', 'report.stock_valuation', 'report.batch_stock_valuation', 
        ],
        'sales' => [
            'sales.', 'customer-payment.', 'customer.', 'pre-orders.', 'advance-sales.', 'emi-sales.', 
            'sales-return.', 'corporate-customer.', 'corporate-sales.', 'dealer.', 
            'dealer-sales.', 'pos', 'report.sale', 'report.customer_due', 'report.customer', 'service.', 'tasks.',
            'report.invoice_profit_loss', 'report.product_profit_loss'
        ],
        'ecommerce' => ['product-packages.', 'package-orders.',
            'ecom_', 'filters.'
        ],
        'stock_transfer' => [
            'stock-transfers.'
        ],
        'stock_conversion' => [
            'stock-conversions.'
        ],
        'route_plan' => [
            'route_plan.dashboard', 'route-plans.',
            'report.route_leads', 'report.route_coverage', 'report.route_plans'
        ],

        'sr' => [
            'sr_management.dashboard', 'sr.', 'sr-visits.', 'sr-visit-type.', 'sr_social.', 'sr_lead_collection.', 'sr_route_plans.',
            'report.sr_performance', 'report.sr_performance_summary', 'report.sr_tasks', 'report.sr_visits', 'report.sr_social_engagements', 'report.sr_leads'
        ],
        'trainee' => [
            'trainee_management.dashboard', 'trainee.',
            'report.trainee_visits', 'report.trainee_performance', 'report.trainee_performance_summary'
        ],
        'microfinance' => [
            'microfinance.', 'collection.', 'day-closing.'
        ],
        'loan' => [
            'company_loan.'
        ],
        'monitoring' => [
            'tracking.'
        ],
        'accounts' => [
            'accounts.dashboard', 'account-group.', 'account-head.', 'voucher.', 'cashbook',
            'report.cash_or_bank_book', 'report.cash_or_bank_transaction_details', 'report.receipt_and_payment', 'report.ledger', 'report.trial_balance', 'report.income_statement', 'report.balance_sheet', 'report.inter_company_ledger', 'inter_company_settlements.', 'report.daily_transactions'
        ],
        'card_management' => [
            'card-management.'
        ],
        'shomriddhi_advance' => [
            'shomriddhi-advance.'
        ],
        'shomriddhi_fdr' => [
            'shomriddhi-fdr.'
        ],
        'shomriddhi_dps' => [
            'shomriddhi-dps.'
        ],
        'shomriddhi_loan' => [
            'shomriddhi-loan.'
        ],
        'incentive_management' => [
            'incentive-settings.', 'incentive-payments.'
        ],
        'sms_management' => [
            'sms_management.'
        ],
        'fixed_asset' => [
            'fixed-assets.'
        ],
        'employee_profile' => [
            'employee_profile.'
        ],
        'forms_hub' => [
            'forms_hub.'
        ],
        'contractor_management' => [
            'contractor-management.', 'contractor_management.'
        ],
        'returns_management' => [
            'returns-management.'
        ]
    ];
    
    $activeModule = null;

    // First pass: Check for exact route match to prevent false positives from wildcard matching
    foreach ($moduleMap as $module => $prefixes) {
        if (in_array($currentRoute, $prefixes)) {
            $activeModule = $module;
            break;
        }
    }

    // Second pass: Check for prefix and wildcard matches if no exact match found
    if (!$activeModule) {
        foreach ($moduleMap as $module => $prefixes) {
            foreach ($prefixes as $prefix) {
                $isMatch = false;
            if (str_starts_with($currentRoute, $prefix)) {
                $isMatch = true;
            } else {
                if (str_ends_with($prefix, '.')) {
                    $cleanPrefix = rtrim($prefix, '.');
                    if (request()->is('admin/' . $cleanPrefix) || request()->is('admin/' . $cleanPrefix . '/*')) {
                        $isMatch = true;
                    }
                } else {
                    if (request()->is('admin/' . str_replace('.', '*', $prefix))) {
                        $isMatch = true;
                    }
                }
            }
            if ($isMatch) {
                $activeModule = $module;
                
                // Override for dynamic task roles
                if (str_starts_with($currentRoute, 'tasks.') || request()->is('admin/tasks/*')) {
                    if (request()->is('admin/tasks/sr*')) {
                        $activeModule = 'sr';
                    } elseif (request()->is('admin/tasks/area_manager*') || request()->is('admin/tasks/branch_manager*')) {
                        $activeModule = 'manager_management';
                    }
                }
                
                break 2;
            }
        }
    }
    }
@endphp

<!-- Back to Apps Button (Shows when inside a module) -->
@if($activeModule && $activeModule != 'workspace')
    <li class="nav-header text-uppercase font-weight-bold text-muted" style="font-size: 0.75rem; letter-spacing: 0.5px; padding-left: 10px;">
        Active Workspace
    </li>
    <li class="nav-item mb-3" style="padding: 0 8px;">
        <a href="{{ route('dashboard', ['layout' => 'modular']) }}" class="nav-link bg-info text-white shadow-sm" style="border-radius: 12px; transition: all 0.3s;">
            <i class="nav-icon fas fa-th"></i>
            <p class="font-weight-bold">Apps Launcher</p>
        </a>
    </li>
@endif

<!-- Render Contextual Custom Submenus based on Active Module -->
@if($activeModule == 'office')
    @include('layouts.menu.modular.__office_management')

@elseif($activeModule == 'users')
    @include('layouts.menu.modular.__user_management')

@elseif($activeModule == 'branches')
    @include('layouts.menu.modular.__branch_management')

@elseif($activeModule == 'crm')
    @include('layouts.menu.modular.__crm')

@elseif($activeModule == 'hrm')
    @include('layouts.menu.modular.__hrm') 

@elseif($activeModule == 'payroll')
    @include('layouts.menu.modular.__payroll')

@elseif($activeModule == 'manager_management')
    @include('layouts.menu.modular.__manager_management')

@elseif($activeModule == 'settings')
    @include('layouts.menu.modular.__settings')

@elseif($activeModule == 'route_plan')
    @include('layouts.menu.modular.__route_plan')

@elseif($activeModule == 'purchase')
    @include('layouts.menu.modular.__purchase')

@elseif($activeModule == 'inventory')
    @include('layouts.menu.modular.__inventory')

@elseif($activeModule == 'sales')
    @include('layouts.menu.modular.__sales')

@elseif($activeModule == 'ecommerce')
    @include('layouts.menu.modular.__ecommerce')

@elseif($activeModule == 'stock_transfer')
    @include('layouts.menu.modular.__stock_transfer')

@elseif($activeModule == 'stock_conversion')
    @include('layouts.menu.modular.__stock_conversion')

@elseif($activeModule == 'sr')
    @include('layouts.menu.modular.__sr_management')

@elseif($activeModule == 'trainee')
    @include('layouts.menu.modular.__trainee_management')

@elseif($activeModule == 'microfinance')
    @include('layouts.menu.modular.__microfinance')

@elseif($activeModule == 'loan')
    @include('layouts.menu.modular.__company_loan')

@elseif($activeModule == 'monitoring')
    @include('layouts.menu.modular.__monitoring')

@elseif($activeModule == 'utilize')
    @include('layouts.menu.modular.__product_utilize')

@elseif($activeModule == 'accounts')
    @include('layouts.menu.modular.__accounts')
@elseif($activeModule == 'card_management')
    @include('layouts.menu.modular.__card_management')

@elseif($activeModule == 'shomriddhi_advance')
    @include('layouts.menu.modular.__shomriddhi_advance')

@elseif($activeModule == 'shomriddhi_fdr')
    @include('layouts.menu.modular.__shomriddhi_fdr')

@elseif($activeModule == 'shomriddhi_dps')
    @include('layouts.menu.modular.__shomriddhi_dps')

@elseif($activeModule == 'shomriddhi_loan')
    @include('layouts.menu.modular.__shomriddhi_loan')

@elseif($activeModule == 'incentive_management')
    @include('layouts.menu.modular.__incentive_management')

@elseif($activeModule == 'sms_management')
    @include('layouts.menu.modular.__sms_management')

@elseif($activeModule == 'fixed_asset')
    @include('layouts.menu.modular.__fixed_asset')

@elseif($activeModule == 'employee_profile')
    @include('layouts.menu.modular.__employee_profile')

@elseif($activeModule == 'forms_hub')
    @include('layouts.menu.modular.__forms_hub')

@elseif($activeModule == 'contractor_management')
    @include('layouts.menu.modular.__contractor_management')

@elseif($activeModule == 'returns_management')
    @include('layouts.menu.modular.__returns_management')

@else
    <!-- Default Workspace Main Sidebar (Shown when on Dashboard Grid) -->
    <li class="nav-item">
        <a href="{{ route('dashboard') }}" class="nav-link active">
            <i class="nav-icon fas fa-cubes text-info"></i>
            <p class="font-weight-bold">Apps Workspace</p>
        </a>
    </li>
@endif

