<?php

namespace App\Http\Controllers;

use App\Enumeration\VoucherType;
use App\Models\Account\AccountHead;
use App\Models\Settings\Product;
use App\Models\Purchase\PurchaseOrder;
use App\Models\Sales\SaleOrder;
use App\Models\BranchManagement\SisterConcern;
use App\Models\Supplier;
use App\Models\Account\Transaction;
use App\Models\Account\Voucher;
use App\Models\Sales\SalePayment;
use App\Models\Sales\TaskAssignment;
use App\Models\Sales\TaskItem;
use App\Models\SrManagement\SrVisit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Enumeration\RoleType;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        if (auth()->user()->hasPermissionTo('dashboard')) {
            $user = auth()->user();
            $activeConcernId = $user->activeConcernId();
            $activeSisterConcern = SisterConcern::find($activeConcernId);
            $isHeadOffice = ($activeConcernId == 1);
            
            // --- Business Date Logic ---
            $businessDate = $activeSisterConcern && $activeSisterConcern->business_date ? Carbon::parse($activeSisterConcern->business_date) : Carbon::now();
            $currentMonthStart = $businessDate->copy()->startOfMonth()->format('Y-m-d');
            $currentMonthEnd = $businessDate->copy()->endOfMonth()->format('Y-m-d');

            if ($request->start_date != '' && $request->end_date != ''){
                $startDate = Carbon::parse($request->start_date)->format('Y-m-d');
                $endDate = Carbon::parse($request->end_date)->format('Y-m-d');
            }else{
                $startDate = $businessDate->copy()->startOfMonth()->format('Y-m-d');
                $endDate = $businessDate->copy()->format('Y-m-d');
            }

            if ($user->role == 'SR') {
                $totalSales = SaleOrder::where('sr_id', $user->employee_id)->whereBetween('date', [$startDate, $endDate])->sum('total');
                $totalDues = SaleOrder::where('sr_id', $user->employee_id)->whereBetween('date', [$startDate, $endDate])->sum('due');
                $totalDiscounts = SaleOrder::where('sr_id', $user->employee_id)->whereBetween('date', [$startDate, $endDate])->sum('discount');
                
                $totalCollection = SalePayment::where('sr_id', $user->employee_id)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->sum('amount');

                $totalDueCollection = SalePayment::where('sr_id', $user->employee_id)
                    ->where('type', 2)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->sum('amount');
            } else {
                $totalSales = SaleOrder::where('sister_concern_id', $activeConcernId)->whereBetween('date', [$startDate, $endDate])->sum('total');
                $totalDues = SaleOrder::where('sister_concern_id', $activeConcernId)->whereBetween('date', [$startDate, $endDate])->sum('due');
                $totalDiscounts = SaleOrder::where('sister_concern_id', $activeConcernId)->whereBetween('date', [$startDate, $endDate])->sum('discount');
                
                $totalCollection = SalePayment::where('sister_concern_id', $activeConcernId)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->sum('amount');

                $totalDueCollection = SalePayment::where('sister_concern_id', $activeConcernId)
                    ->where('type', 2)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->sum('amount');
            }
            
            $totalExpenses = Transaction::where('sister_concern_id', $activeConcernId)
                ->where('voucher_type', VoucherType::$PAYMENT_VOUCHER)
                ->whereBetween('date', [$startDate, $endDate])
                ->whereColumn('payment_account_head_id', '!=', 'account_head_id')
                ->sum('amount');

            $paymentModes = AccountHead::where(function($q) use ($activeConcernId) { $q->whereNull('sister_concern_id')->orWhere('sister_concern_id', $activeConcernId); })->where('payment_mode', '>', 0)->get();
            $products = Product::with('category')->where('status', 1)->take(4)->latest()->get();    
            $customers = Supplier::where('type', 2)->where('status', 1)->take(4)->latest()->get();

            $purchaseOrders = PurchaseOrder::where('sister_concern_id', $activeConcernId)->take(4)->latest()->get();   
            
            $saleOrders = SaleOrder::where('sister_concern_id', $activeConcernId);
            if ($user->role == 'SR') {
                $saleOrders->where('sr_id', $user->sr_id);
            }
            $saleOrders = $saleOrders->take(4)->latest()->get();   

            // --- Unified Target vs Achievement Logic ---
            $targetAmount = 0;
            $achievementAmount = 0;
            $branchPerformances = [];
            $srTasks = [];
            $currentMonthStart = Carbon::now()->startOfMonth()->format('Y-m-d');

            if ($user->role == 'Admin' && $isHeadOffice) {
                // Admin sees sum of all Area Manager targets
                $targetAmount = TaskAssignment::where('date', $currentMonthStart)
                    ->where('target_role', 'Area_Manager')
                    ->join('task_items', 'task_assignments.id', '=', 'task_items.task_assignment_id')
                    ->where('task_items.task_type_id', 1) // Sales Target
                    ->sum('task_items.target_value');
                
                $achievementAmount = SaleOrder::whereMonth('date', $businessDate->month)->whereYear('date', $businessDate->year)->sum('total');

                // Performance by Area Manager
                $areaManagers = \App\Models\Hrm\Employee::areaManagers()->where('status', 1)->get();
                foreach ($areaManagers as $am) {
                    $amTarget = TaskAssignment::where('employee_id', $am->id)
                        ->where('date', $currentMonthStart)
                        ->where('target_role', 'Area_Manager')
                        ->join('task_items', 'task_assignments.id', '=', 'task_items.task_assignment_id')
                        ->where('task_items.task_type_id', 1)
                        ->sum('task_items.target_value');

                    $amAchievement = SaleOrder::where('area_manager_id', $am->id)->whereMonth('date', $businessDate->month)->whereYear('date', $businessDate->year)->sum('total');

                    $branchPerformances[] = [
                        'name' => $am->name,
                        'target' => $amTarget,
                        'sales' => $amAchievement,
                        'percentage' => $amTarget > 0 ? ($amAchievement / $amTarget) * 100 : 0
                    ];
                }
            } elseif ($user->role == 'Area_Manager') {
                // Area Manager sees their own target assigned by Admin
                $targetAmount = TaskAssignment::where('employee_id', $user->employee_id)
                    ->where('date', $currentMonthStart)
                    ->where('target_role', 'Area_Manager')
                    ->join('task_items', 'task_assignments.id', '=', 'task_items.task_assignment_id')
                    ->where('task_items.task_type_id', 1)
                    ->sum('task_items.target_value');

                $achievementAmount = SaleOrder::where('area_manager_id', $user->employee_id)->whereMonth('date', $businessDate->month)->whereYear('date', $businessDate->year)->sum('total');

                // Breakdown by assigned branches
                $branches = SisterConcern::where('area_manager_id', $user->employee_id)->get();
                foreach ($branches as $branch) {
                    $bTarget = TaskAssignment::where('sister_concern_id', $branch->id)
                        ->where('date', $currentMonthStart)
                        ->where('target_role', 'Branch_Manager')
                        ->join('task_items', 'task_assignments.id', '=', 'task_items.task_assignment_id')
                        ->where('task_items.task_type_id', 1)
                        ->sum('task_items.target_value');

                    $bSales = SaleOrder::where('sister_concern_id', $branch->id)->whereMonth('date', $businessDate->month)->whereYear('date', $businessDate->year)->sum('total');

                    $branchPerformances[] = [
                        'name' => $branch->name,
                        'target' => $bTarget,
                        'sales' => $bSales,
                        'percentage' => $bTarget > 0 ? ($bSales / $bTarget) * 100 : 0
                    ];
                }
            } elseif ($user->role == 'SR') {
                // SR sees their own target assigned by Branch Manager
                $assignment = TaskAssignment::where('employee_id', $user->employee_id)
                    ->where('date', $currentMonthStart)
                    ->where('target_role', 'SR')
                    ->with('items')
                    ->first();

                if ($assignment) {
                    foreach ($assignment->items as $item) {
                        $achievement = 0;
                        if ($item->task_type_id == 1) { // Sales Target
                            $achievement = SaleOrder::where('sr_id', $user->employee_id)->whereMonth('date', $businessDate->month)->whereYear('date', $businessDate->year)->sum('total');
                            $targetAmount = $item->target_value;
                            $achievementAmount = $achievement;
                        } elseif ($item->task_type_id == 2) { // Visit Count
                            $achievement = SrVisit::where('sr_id', $user->employee_id)->whereMonth('date', $businessDate->month)->whereYear('date', $businessDate->year)->count();
                        } elseif ($item->task_type_id == 3) { // Due Payment Collection
                            $achievement = SalePayment::where('sr_id', $user->employee_id)->where('type', 2)->whereMonth('date', $businessDate->month)->whereYear('date', $businessDate->year)->sum('amount');
                        } elseif ($item->task_type_id == 4) { // Sales Order Count
                            $achievement = SaleOrder::where('sr_id', $user->employee_id)->whereMonth('date', Carbon::now()->month)->whereYear('date', Carbon::now()->year)->count();
                        }

                        $srTasks[] = [
                            'name' => ($item->task_type_id == 1 ? 'Sales' : ($item->task_type_id == 2 ? 'Visits' : ($item->task_type_id == 3 ? 'Collections' : 'Orders'))),
                            'target' => $item->target_value,
                            'achievement' => $achievement,
                            'percentage' => $item->target_value > 0 ? ($achievement / $item->target_value) * 100 : 0,
                            'type' => $item->task_type_id
                        ];
                    }
                }
            } else {
                // Branch Context
                $targetAmount = TaskAssignment::where('sister_concern_id', $activeConcernId)
                    ->where('date', $currentMonthStart)
                    ->where('target_role', 'Branch_Manager')
                    ->join('task_items', 'task_assignments.id', '=', 'task_items.task_assignment_id')
                    ->where('task_items.task_type_id', 1)
                    ->sum('task_items.target_value');

                $achievementAmount = SaleOrder::where('sister_concern_id', $activeConcernId)->whereMonth('date', $businessDate->month)->whereYear('date', $businessDate->year)->sum('total');
            }

            $achievementPercentage = $targetAmount > 0 ? ($achievementAmount / $targetAmount) * 100 : 0;

            // --- Monthly Sales Trend ---
            $salesTrendLabels = []; $salesTrendData = [];
            for ($i = 5; $i >= 0; $i--) {
                $monthDate = $businessDate->copy()->subMonths($i);
                $salesTrendLabels[] = $monthDate->format('M Y');
                $monthSales = SaleOrder::whereMonth('date', $monthDate->month)->whereYear('date', $monthDate->year);
                if ($user->role == 'SR') {
                    $monthSales->where('sr_id', $user->employee_id);
                } elseif (!$isHeadOffice) {
                    $monthSales->where('sister_concern_id', $activeConcernId);
                }
                $salesTrendData[] = $monthSales->sum('total');
            }

            // --- Inventory Insights ---
            $inventoryQuery = DB::table('inventories')->join('products', 'inventories.product_id', '=', 'products.id');
            if (!$isHeadOffice) { $inventoryQuery->where('inventories.sister_concern_id', $activeConcernId); }
            $totalStockValue = (clone $inventoryQuery)->sum(DB::raw('inventories.quantity * inventories.unit_price'));
            $lowStockProducts = (clone $inventoryQuery)->where('inventories.quantity', '<', 10)->select('products.name', 'inventories.quantity', 'products.image')->take(5)->get();

            // --- Actionable Stats ---
            $pendingOrdersCount = SaleOrder::where('is_delivered', 0);
            if ($user->role == 'SR') {
                $pendingOrdersCount->where('sr_id', $user->employee_id);
            } elseif (!$isHeadOffice) {
                $pendingOrdersCount->where('sister_concern_id', $activeConcernId);
            }
            $pendingOrdersCount = $pendingOrdersCount->count();

            // --- Daily Attendance Reminders ---
            $dailyReminders = collect();
            if ($user->employee_id) {
                // First resolve any pending reminders that are expired (like we do in the reminder service/report)
                \App\Models\Hrm\AttendanceReminderLog::where('employee_id', $user->employee_id)
                    ->where('status', 'pending')
                    ->where('window_closes_at', '<', Carbon::now())
                    ->update(['status' => 'missed']);

                $dailyReminders = \App\Models\Hrm\AttendanceReminderLog::where('employee_id', $user->employee_id)
                    ->whereDate('notified_at', Carbon::today())
                    ->orderBy('notified_at', 'asc')
                    ->get();
            }

            return view('dashboard', compact(
                'totalSales', 'totalDues', 'totalDiscounts', 'totalExpenses', 'totalDueCollection', 
                'totalCollection', 'paymentModes', 'products', 'customers', 'purchaseOrders', 'saleOrders', 
                'targetAmount', 'achievementAmount', 'achievementPercentage', 'branchPerformances', 'srTasks',
                'salesTrendLabels', 'salesTrendData', 'totalStockValue', 'lowStockProducts', 'pendingOrdersCount', 'businessDate', 'dailyReminders'
            ));
        }
        return view('blank_dashboard');
    }

    public function globalSearch(Request $request)
    {
        $q = trim($request->q);
        $results = [];

        // 1. Static Pages & Quick Actions
        $pages = [
            ['title' => 'Create Payment Voucher', 'route' => 'voucher.create', 'params' => ['voucher_type' => 1], 'permission' => 'payment_voucher_create', 'icon' => 'fas fa-plus-circle', 'category' => 'Actions'],
            ['title' => 'Create Receipt Voucher', 'route' => 'voucher.create', 'params' => ['voucher_type' => 2], 'permission' => 'receipt_voucher_create', 'icon' => 'fas fa-plus-circle', 'category' => 'Actions'],
            ['title' => 'Create Contra Voucher', 'route' => 'voucher.create', 'params' => ['voucher_type' => 3], 'permission' => 'contra_voucher_create', 'icon' => 'fas fa-plus-circle', 'category' => 'Actions'],
            ['title' => 'Create Journal Voucher', 'route' => 'voucher.create', 'params' => ['voucher_type' => 4], 'permission' => 'journal_voucher_create', 'icon' => 'fas fa-plus-circle', 'category' => 'Actions'],
            
            ['title' => 'Accounts Dashboard', 'route' => 'accounts.dashboard', 'permission' => 'accounts', 'icon' => 'fas fa-tachometer-alt', 'category' => 'Accounts'],
            ['title' => 'Account Groups', 'route' => 'account-group.index', 'permission' => 'account_group', 'icon' => 'fas fa-layer-group', 'category' => 'Accounts'],
            ['title' => 'Payment Modes', 'route' => 'account-head.index', 'params' => ['payment_mode' => 1], 'permission' => 'payment_modes', 'icon' => 'fas fa-list-alt', 'category' => 'Accounts'],
            ['title' => 'Payment Vouchers', 'route' => 'voucher.index', 'params' => ['voucher_type' => 1], 'permission' => 'payment_voucher', 'icon' => 'fas fa-file-invoice-dollar', 'category' => 'Accounts'],
            ['title' => 'Receipt Vouchers', 'route' => 'voucher.index', 'params' => ['voucher_type' => 2], 'permission' => 'receipt_voucher', 'icon' => 'fas fa-cash-register', 'category' => 'Accounts'],
            ['title' => 'Contra Vouchers', 'route' => 'voucher.index', 'params' => ['voucher_type' => 3], 'permission' => 'contra_voucher', 'icon' => 'fas fa-exchange-alt', 'category' => 'Accounts'],
            ['title' => 'Journal Vouchers', 'route' => 'voucher.index', 'params' => ['voucher_type' => 4], 'permission' => 'journal_voucher', 'icon' => 'fas fa-journal-whills', 'category' => 'Accounts'],
            
            ['title' => 'Office Utility & Notices', 'route' => 'notices.index', 'permission' => 'notice_management', 'icon' => 'fas fa-building', 'category' => 'Navigation'],
            ['title' => 'Meeting Management', 'route' => 'meetings.index', 'permission' => 'meeting_management', 'icon' => 'fas fa-handshake', 'category' => 'Navigation'],
            ['title' => 'Users', 'route' => 'user.index', 'permission' => 'users', 'icon' => 'fas fa-users', 'category' => 'Navigation'],
            ['title' => 'Role Management', 'route' => 'role.index', 'permission' => 'role_management', 'icon' => 'fas fa-user-shield', 'category' => 'Navigation'],
            ['title' => 'Branches List', 'route' => 'sister-concerns.index', 'permission' => 'branches', 'icon' => 'fas fa-store', 'category' => 'Navigation'],
            
            ['title' => 'CRM Dashboard', 'route' => 'crm.dashboard', 'permission' => 'crm', 'icon' => 'fas fa-chart-pie', 'category' => 'CRM'],
            ['title' => 'CRM Leads', 'route' => 'leads.index', 'permission' => 'lead_entry', 'icon' => 'fas fa-funnel-dollar', 'category' => 'CRM'],
            ['title' => 'Customer Types', 'route' => 'customer-types.index', 'permission' => 'customer_type', 'icon' => 'fas fa-users-cog', 'category' => 'CRM'],
            
            ['title' => 'HRM Dashboard', 'route' => 'hrm.dashboard', 'permission' => 'hrm', 'icon' => 'fas fa-chart-pie', 'category' => 'HRM'],
            ['title' => 'Employee Directory (HRM)', 'route' => 'employees.index', 'permission' => 'employee', 'icon' => 'fas fa-users-cog', 'category' => 'HRM'],
            ['title' => 'Departments', 'route' => 'departments.index', 'permission' => 'department', 'icon' => 'fas fa-sitemap', 'category' => 'HRM'],
            ['title' => 'Designations', 'route' => 'designations.index', 'permission' => 'designation', 'icon' => 'fas fa-id-badge', 'category' => 'HRM'],
            ['title' => 'Holidays', 'route' => 'holidays.index', 'permission' => 'holiday', 'icon' => 'fas fa-umbrella-beach', 'category' => 'HRM'],
            ['title' => 'Shifts & Attendance', 'route' => 'shifts.index', 'permission' => 'shift', 'icon' => 'fas fa-clock', 'category' => 'HRM'],
            ['title' => 'Leave Applications', 'route' => 'leave.index', 'permission' => 'leave', 'icon' => 'fas fa-calendar-times', 'category' => 'HRM'],
            ['title' => 'Leave Types', 'route' => 'leave-types.index', 'permission' => 'leave_type', 'icon' => 'fas fa-calendar-alt', 'category' => 'HRM'],
            ['title' => 'Employee Movements', 'route' => 'employee-movements.index', 'permission' => 'employee_movement', 'icon' => 'fas fa-route', 'category' => 'HRM'],
            ['title' => 'Live Attendance', 'route' => 'employees.attendance', 'permission' => 'employee_attendance', 'icon' => 'fas fa-fingerprint', 'category' => 'HRM'],
            ['title' => 'Employee Statuses', 'route' => 'employee-statuses.index', 'permission' => 'employee_status', 'icon' => 'fas fa-toggle-on', 'category' => 'HRM'],
            
            ['title' => 'Payroll Dashboard', 'route' => 'payroll.dashboard', 'permission' => 'payroll', 'icon' => 'fas fa-chart-pie', 'category' => 'Payroll'],
            ['title' => 'Employee Advance Requests', 'route' => 'employee-advances.requests', 'permission' => 'employee_advance', 'icon' => 'fas fa-paper-plane', 'category' => 'Payroll'],
            ['title' => 'Salary Structures', 'route' => 'salary-structures.index', 'permission' => 'salary_process', 'icon' => 'fas fa-sitemap', 'category' => 'Payroll'],
            ['title' => 'Create Salary Process', 'route' => 'salary-processes.create', 'permission' => 'salary_process', 'icon' => 'fas fa-spinner', 'category' => 'Payroll'],
            ['title' => 'Salary Processes', 'route' => 'salary-processes.index', 'permission' => 'salary_process', 'icon' => 'fas fa-wallet', 'category' => 'Payroll'],
            
            ['title' => 'Chart of Accounts', 'route' => 'account-head.index', 'permission' => 'account_head', 'icon' => 'fas fa-file-invoice-dollar', 'category' => 'Accounts'],
            ['title' => 'Settings Dashboard', 'route' => 'settings.dashboard', 'permission' => 'settings', 'icon' => 'fas fa-cogs', 'category' => 'Settings'],
            ['title' => 'Warehouses', 'route' => 'warehouses.index', 'permission' => 'warehouse', 'icon' => 'fas fa-warehouse', 'category' => 'Settings'],
            ['title' => 'Units', 'route' => 'unit.index', 'permission' => 'product_unit', 'icon' => 'fas fa-balance-scale', 'category' => 'Settings'],
            ['title' => 'Product Categories', 'route' => 'product-categories.index', 'permission' => 'product_category', 'icon' => 'fas fa-tags', 'category' => 'Settings'],
            ['title' => 'Sub Categories', 'route' => 'sub-category.index', 'permission' => 'product_category', 'icon' => 'fas fa-tags', 'category' => 'Settings'],
            ['title' => 'Brands', 'route' => 'brand.index', 'permission' => 'brand', 'icon' => 'fas fa-tag', 'category' => 'Settings'],
            ['title' => 'Products List', 'route' => 'product.index', 'permission' => 'product', 'icon' => 'fas fa-box-open', 'category' => 'Settings'],
            ['title' => 'Price Books', 'route' => 'price_book.index', 'permission' => 'price_book', 'icon' => 'fas fa-book-open', 'category' => 'Settings'],
            ['title' => 'Locations (Division/Zila)', 'route' => 'locations.index', 'params' => 'divisions', 'permission' => 'location_setup', 'icon' => 'fas fa-map', 'category' => 'Settings'],
            
            ['title' => 'Purchase Dashboard', 'route' => 'purchase.dashboard', 'permission' => 'purchase', 'icon' => 'fas fa-tachometer-alt', 'category' => 'Purchase'],
            ['title' => 'Create Purchase Order', 'route' => 'purchase.create', 'permission' => 'purchase_create', 'icon' => 'fas fa-cart-plus', 'category' => 'Purchase'],
            ['title' => 'Purchase Orders List', 'route' => 'purchase.index', 'permission' => 'purchase', 'icon' => 'fas fa-shopping-cart', 'category' => 'Purchase'],
            ['title' => 'Supplier Payments', 'route' => 'purchase.supplier_payments_list', 'permission' => 'supplier_payment', 'icon' => 'fas fa-money-check-alt', 'category' => 'Purchase'],
            ['title' => 'Suppliers List', 'route' => 'supplier.index', 'permission' => 'supplier', 'icon' => 'fas fa-truck-loading', 'category' => 'Purchase'],
            ['title' => 'Supplier Ledger', 'route' => 'supplier.ledger', 'permission' => 'supplier_report', 'icon' => 'fas fa-book', 'category' => 'Purchase'],
            ['title' => 'Supplier Report', 'route' => 'supplier.report', 'permission' => 'supplier_report', 'icon' => 'fas fa-truck', 'category' => 'Purchase'],
            ['title' => 'Purchase Report', 'route' => 'purchase.report', 'permission' => 'purchase_report', 'icon' => 'fas fa-chart-line', 'category' => 'Purchase'],
            ['title' => 'Purchase Details Report', 'route' => 'purchase_details.report', 'permission' => 'purchase_details_report', 'icon' => 'fas fa-file-invoice', 'category' => 'Purchase'],
            
            ['title' => 'Inventory Dashboard', 'route' => 'inventory.dashboard', 'permission' => 'inventory', 'icon' => 'fas fa-tachometer-alt', 'category' => 'Inventory'],
            ['title' => 'Inventory Stocks', 'route' => 'inventory.index', 'permission' => 'inventory', 'icon' => 'fas fa-boxes', 'category' => 'Inventory'],
            ['title' => 'Stock Transfers / Dispatch', 'route' => 'stock-transfers.index', 'permission' => 'stock_transfer', 'icon' => 'fas fa-shipping-fast', 'category' => 'Inventory'],
            ['title' => 'Stock Receive List', 'route' => 'stock-transfers.receive_list', 'permission' => 'stock_transfer_receive', 'icon' => 'fas fa-truck-loading', 'category' => 'Inventory'],
            ['title' => 'Stock Conversions', 'route' => 'stock-conversions.index', 'permission' => 'stock_transfer', 'icon' => 'fas fa-recycle', 'category' => 'Inventory'],
            
            ['title' => 'Sales Dashboard', 'route' => 'sales.dashboard', 'permission' => 'sales', 'icon' => 'fas fa-tachometer-alt', 'category' => 'Sales'],
            ['title' => 'Customers List', 'route' => 'customer.index', 'permission' => 'customer', 'icon' => 'fas fa-users', 'category' => 'Sales'],
            
            // Forms Hub
            ['title' => 'Forms Hub Dashboard', 'route' => 'forms_hub.dashboard', 'permission' => 'forms_hub', 'icon' => 'fas fa-file-pdf', 'category' => 'Forms Hub'],
            ['title' => 'Customer Data Collection Form', 'route' => 'forms_hub.customer', 'permission' => 'forms_hub', 'icon' => 'fas fa-file-pdf', 'category' => 'Forms Hub'],
            ['title' => 'Supplier Data Collection Form', 'route' => 'forms_hub.supplier', 'permission' => 'forms_hub', 'icon' => 'fas fa-file-pdf', 'category' => 'Forms Hub'],
            ['title' => 'Dealer Data Collection Form', 'route' => 'forms_hub.dealer', 'permission' => 'forms_hub', 'icon' => 'fas fa-file-pdf', 'category' => 'Forms Hub'],
            ['title' => 'Sales Orders List', 'route' => 'sales.index', 'permission' => 'sales', 'icon' => 'fas fa-shopping-bag', 'category' => 'Sales'],
            ['title' => 'Pre Orders', 'route' => 'pre-orders.index', 'permission' => 'pre_orders', 'icon' => 'fas fa-forward', 'category' => 'Sales'],
            ['title' => 'EMI Sales', 'route' => 'emi-sales.index', 'permission' => 'emi_sales', 'icon' => 'fas fa-hand-holding-usd', 'category' => 'Sales'],
            ['title' => 'Customer Ledger', 'route' => 'report.customer_ledger', 'permission' => 'customer_report', 'icon' => 'fas fa-book', 'category' => 'Sales'],
            ['title' => 'Customer Report', 'route' => 'report.customer', 'permission' => 'customer_report', 'icon' => 'fas fa-users-cog', 'category' => 'Sales'],
            ['title' => 'Customer Due Report', 'route' => 'report.customer_due', 'permission' => 'customer_due_report', 'icon' => 'fas fa-user-clock', 'category' => 'Sales'],
            
            ['title' => 'Corporate Customers', 'route' => 'corporate-customer.index', 'permission' => 'corporate_customer', 'icon' => 'fas fa-user-tie', 'category' => 'Sales'],
            ['title' => 'Corporate Sales', 'route' => 'corporate-sales.index', 'permission' => 'corporate_sales', 'icon' => 'fas fa-building', 'category' => 'Sales'],
            
            ['title' => 'Dealers', 'route' => 'dealer.index', 'permission' => 'dealer', 'icon' => 'fas fa-handshake', 'category' => 'Sales'],
            ['title' => 'Dealer Sales', 'route' => 'dealer-sales.index', 'permission' => 'dealer_sales', 'icon' => 'fas fa-store-alt', 'category' => 'Sales'],
            
            ['title' => 'Service Requests', 'route' => 'service.request.index', 'permission' => 'service_request', 'icon' => 'fas fa-tools', 'category' => 'Service'],
            ['title' => 'Service Technicians', 'route' => 'service.technician.index', 'permission' => 'technician', 'icon' => 'fas fa-user-cog', 'category' => 'Service'],
            ['title' => 'Service Panel', 'route' => 'service.panel.index', 'permission' => 'service_panel', 'icon' => 'fas fa-cogs', 'category' => 'Service'],
            ['title' => 'Customer Payments', 'route' => 'customer-payment.index', 'permission' => 'sales_payment_receive', 'icon' => 'fas fa-file-invoice-dollar', 'category' => 'Sales'],
            
            ['title' => 'Ecommerce Dashboard', 'route' => 'ecom_dashboard', 'permission' => 'ecommerce_dashboard', 'icon' => 'fas fa-tachometer-alt', 'category' => 'Ecommerce'],
            ['title' => 'Ecommerce Settings', 'route' => 'ecom_settings.update', 'params' => ['setting' => 1], 'permission' => 'ecommerce_settings', 'icon' => 'fas fa-cogs', 'category' => 'Ecommerce'],
            ['title' => 'Ecommerce Banners', 'route' => 'ecom_banner', 'permission' => 'ecommerce_banner', 'icon' => 'fas fa-images', 'category' => 'Ecommerce'],
            ['title' => 'Ecommerce Gallery', 'route' => 'ecom_gallery', 'permission' => 'ecommerce_gallery', 'icon' => 'fas fa-photo-video', 'category' => 'Ecommerce'],
            ['title' => 'Ecommerce Coupons', 'route' => 'ecom_coupon', 'permission' => 'ecommerce_coupon', 'icon' => 'fas fa-ticket-alt', 'category' => 'Ecommerce'],
            ['title' => 'Ecommerce Orders', 'route' => 'ecom_order', 'permission' => 'ecommerce_order', 'icon' => 'fas fa-shopping-cart', 'category' => 'Ecommerce'],
            ['title' => 'Ecommerce Offer Products', 'route' => 'ecom_product', 'params' => ['offer' => 1], 'permission' => 'ecommerce_product', 'icon' => 'fas fa-tags', 'category' => 'Ecommerce'],
            ['title' => 'Ecommerce Filters', 'route' => 'filters.index', 'permission' => 'ecommerce_filter', 'icon' => 'fas fa-filter', 'category' => 'Ecommerce'],
            ['title' => 'Ecommerce Products', 'route' => 'ecom_product', 'permission' => 'ecommerce_product', 'icon' => 'fas fa-box', 'category' => 'Ecommerce'],
            ['title' => 'Ecommerce Pages', 'route' => 'ecom_page', 'permission' => 'ecommerce_page', 'icon' => 'fas fa-file-alt', 'category' => 'Ecommerce'],
            ['title' => 'Ecommerce Blogs', 'route' => 'ecom_blog', 'permission' => 'ecommerce_blog', 'icon' => 'fas fa-blog', 'category' => 'Ecommerce'],
            ['title' => 'Ecommerce Contacts', 'route' => 'ecom_contact', 'permission' => 'ecommerce_contact', 'icon' => 'fas fa-address-book', 'category' => 'Ecommerce'],
            
            ['title' => 'Manager Management Dashboard', 'route' => 'manager_management.dashboard', 'permission' => 'manager_management', 'icon' => 'fas fa-chart-pie', 'category' => 'Managers'],
            ['title' => 'Area Managers List', 'route' => 'area-manager.index', 'permission' => 'area_manager', 'icon' => 'fas fa-user-tie', 'category' => 'Managers'],
            ['title' => 'Branch Managers List', 'route' => 'branch-manager.index', 'permission' => 'branch_manager', 'icon' => 'fas fa-user-tie', 'category' => 'Managers'],
            ['title' => 'Area Manager Assignments', 'route' => 'area-manager-assignments.index', 'permission' => 'area_manager_assignments', 'icon' => 'fas fa-link', 'category' => 'Managers'],
            ['title' => 'Branch Manager Assignments', 'route' => 'branch-manager-assignments.index', 'permission' => 'branch_manager_assignments', 'icon' => 'fas fa-link', 'category' => 'Managers'],
            ['title' => 'AM Targets', 'route' => 'tasks.index', 'params' => 'area_manager', 'permission' => 'area_manager_targets', 'icon' => 'fas fa-bullseye', 'category' => 'Managers'],
            ['title' => 'BM Targets', 'route' => 'tasks.index', 'params' => 'branch_manager', 'permission' => 'branch_manager_targets', 'icon' => 'fas fa-bullseye', 'category' => 'Managers'],
            
            ['title' => 'SR Management Dashboard', 'route' => 'sr_management.dashboard', 'permission' => 'sr_management', 'icon' => 'fas fa-tachometer-alt', 'category' => 'Navigation'],
            ['title' => 'SR Management', 'route' => 'sr.index', 'permission' => 'sr_management', 'icon' => 'fas fa-user-friends', 'category' => 'Navigation'],
            ['title' => 'SR Tasks & Targets', 'route' => 'tasks.index', 'params' => 'sr', 'permission' => 'sr_management', 'icon' => 'fas fa-bullseye', 'category' => 'Navigation'],
            ['title' => 'SR Visits Tracking', 'route' => 'sr-visits.index', 'permission' => 'sr_management', 'icon' => 'fas fa-walking', 'category' => 'Navigation'],
            ['title' => 'Route Plan (List)', 'route' => 'markets.index', 'permission' => 'manage_markets', 'icon' => 'fas fa-map-marker-alt', 'category' => 'Navigation'],
            ['title' => 'Route Plans (PJP)', 'route' => 'route-plans.index', 'permission' => 'route_plans', 'icon' => 'fas fa-map-signs', 'category' => 'Navigation'],
            ['title' => 'Trainee Tasks', 'route' => 'trainee.tasks.index', 'permission' => 'trainee_tasks', 'icon' => 'fas fa-graduation-cap', 'category' => 'Navigation'],
            ['title' => 'Microfinance Members', 'route' => 'microfinance.member.index', 'permission' => 'microfinance', 'icon' => 'fas fa-donate', 'category' => 'Navigation'],
            ['title' => 'Company Loan Account', 'route' => 'company_loan.index', 'permission' => 'company_loan', 'icon' => 'fas fa-hand-holding-usd', 'category' => 'Navigation'],
            ['title' => 'Live Tracking Dashboard', 'route' => 'tracking.dashboard', 'permission' => 'live_location', 'icon' => 'fas fa-tachometer-alt', 'category' => 'Navigation'],
            ['title' => 'Live Location Tracking', 'route' => 'tracking.live_location', 'permission' => 'live_location', 'icon' => 'fas fa-map-marker-alt', 'category' => 'Navigation'],
            ['title' => 'Location History', 'route' => 'tracking.location_history', 'permission' => 'location_history', 'icon' => 'fas fa-history', 'category' => 'Navigation'],
            ['title' => 'Product Utilizes', 'route' => 'product-utilizes.index', 'permission' => 'product_utilize', 'icon' => 'fas fa-tasks', 'category' => 'Navigation'],
            
            // Card Management
            ['title' => 'Membership Cash-out Counter', 'route' => 'card-management.cashout.index', 'permission' => 'shomriddhi_advance_transaction_approve', 'icon' => 'fas fa-money-bill-wave', 'category' => 'Card Management'],
            ['title' => 'Card Management Dashboard', 'route' => 'card-management.dashboard', 'permission' => 'card_management', 'icon' => 'fas fa-tachometer-alt', 'category' => 'Card Management'],
            ['title' => 'Member Card Management', 'route' => 'card-management.member.index', 'permission' => 'dashboard', 'icon' => 'fas fa-id-card', 'category' => 'Card Management'],
            ['title' => 'Member Card Print Logs', 'route' => 'card-management.member.logs', 'permission' => 'dashboard', 'icon' => 'fas fa-history', 'category' => 'Card Management'],
            ['title' => 'Employee Card Management', 'route' => 'card-management.employee.index', 'permission' => 'dashboard', 'icon' => 'fas fa-id-badge', 'category' => 'Card Management'],
            ['title' => 'Employee Card Print Logs', 'route' => 'card-management.employee.logs', 'permission' => 'dashboard', 'icon' => 'fas fa-history', 'category' => 'Card Management'],
            ['title' => 'Card Pending Payments', 'route' => 'card-management.collection.index', 'permission' => 'dashboard', 'icon' => 'fas fa-money-check-alt', 'category' => 'Card Management'],

            // Fixed Asset
            ['title' => 'Fixed Asset Dashboard', 'route' => 'fixed-assets.dashboard', 'permission' => 'fixed_asset', 'icon' => 'fas fa-tachometer-alt', 'category' => 'Fixed Asset'],
            ['title' => 'Fixed Asset Categories', 'route' => 'fixed-assets.categories.index', 'permission' => 'fixed_asset_category_view', 'icon' => 'fas fa-tags', 'category' => 'Fixed Asset'],
            ['title' => 'Add Fixed Asset', 'route' => 'fixed-assets.create', 'permission' => 'fixed_asset_create', 'icon' => 'fas fa-plus-circle', 'category' => 'Fixed Asset'],
            ['title' => 'Fixed Asset Register', 'route' => 'fixed-assets.index', 'permission' => 'fixed_asset_view', 'icon' => 'fas fa-list', 'category' => 'Fixed Asset'],
            ['title' => 'Run Asset Depreciation', 'route' => 'fixed-assets.depreciation.index', 'permission' => 'fixed_asset_depreciation_run', 'icon' => 'fas fa-calculator', 'category' => 'Fixed Asset'],
            // Reports
            ['title' => 'Area Manager List Report', 'route' => 'report.area_manager', 'permission' => 'area_manager_list_report', 'icon' => 'fas fa-users', 'category' => 'Reports'],
            ['title' => 'Branch Manager List Report', 'route' => 'report.branch_manager', 'permission' => 'branch_manager_list_report', 'icon' => 'fas fa-users', 'category' => 'Reports'],
            ['title' => 'Area Manager History Report', 'route' => 'report.area_manager_history', 'permission' => 'area_manager_history_report', 'icon' => 'fas fa-history', 'category' => 'Reports'],
            ['title' => 'Branch Manager History Report', 'route' => 'report.branch_manager_history', 'permission' => 'branch_manager_history_report', 'icon' => 'fas fa-history', 'category' => 'Reports'],
            ['title' => 'CRM Outlet Type Report', 'route' => 'report.outlet_types', 'permission' => 'leads_report', 'icon' => 'fas fa-tags', 'category' => 'Reports'],
            ['title' => 'CRM Market Report', 'route' => 'report.markets', 'permission' => 'leads_report', 'icon' => 'fas fa-store', 'category' => 'Reports'],
            ['title' => 'Route-wise Leads Report', 'route' => 'report.route_leads', 'permission' => 'route_plan_reports', 'icon' => 'fas fa-list-ul', 'category' => 'Reports'],
            ['title' => 'SR Route Coverage Report', 'route' => 'report.route_coverage', 'permission' => 'route_plan_reports', 'icon' => 'fas fa-chart-line', 'category' => 'Reports'],
            ['title' => 'Weekly Route Plans Report', 'route' => 'report.route_plans', 'permission' => 'route_plan_reports', 'icon' => 'fas fa-calendar-alt', 'category' => 'Reports'],
            ['title' => 'SR Tasks & Targets Report', 'route' => 'report.sr_tasks', 'permission' => 'sr_tasks_report', 'icon' => 'fas fa-bullseye', 'category' => 'Reports'],
            ['title' => 'SR Performance Report', 'route' => 'report.sr_performance', 'permission' => 'sr_performance_report', 'icon' => 'fas fa-chart-bar', 'category' => 'Reports'],
            ['title' => 'SR Performance Summary', 'route' => 'report.sr_performance_summary', 'permission' => 'sr_performance_summary_report', 'icon' => 'fas fa-poll-h', 'category' => 'Reports'],
            ['title' => 'SR Visits Report', 'route' => 'report.sr_visits', 'permission' => 'sr_visits_report', 'icon' => 'fas fa-walking', 'category' => 'Reports'],
            ['title' => 'SR Social Engagements Report', 'route' => 'report.sr_social_engagements', 'permission' => 'sr_visits_report', 'icon' => 'fas fa-thumbs-up', 'category' => 'Reports'],
            ['title' => 'SR Leads Report', 'route' => 'report.sr_leads', 'permission' => 'sr_visits_report', 'icon' => 'fas fa-bullhorn', 'category' => 'Reports'],
            ['title' => 'Trainee Visits Report', 'route' => 'report.trainee_visits', 'permission' => 'trainee_reports', 'icon' => 'fas fa-clipboard-list', 'category' => 'Reports'],
            ['title' => 'Trainee Performance Report', 'route' => 'report.trainee_performance', 'permission' => 'trainee_reports', 'icon' => 'fas fa-graduation-cap', 'category' => 'Reports'],
            ['title' => 'Trainee Performance Summary', 'route' => 'report.trainee_performance_summary', 'permission' => 'trainee_reports', 'icon' => 'fas fa-user-check', 'category' => 'Reports'],
            ['title' => 'Sales Report', 'route' => 'report.sale', 'permission' => 'sale_report', 'icon' => 'fas fa-file-invoice-dollar', 'category' => 'Reports'],
            ['title' => 'Lead Report', 'route' => 'report.leads', 'permission' => 'leads_report', 'icon' => 'fas fa-file-invoice-dollar', 'category' => 'Reports'],
            ['title' => 'HRM Department Report', 'route' => 'report.department', 'permission' => 'department_report', 'icon' => 'fas fa-building', 'category' => 'Reports'],
            ['title' => 'HRM Designation Report', 'route' => 'report.designation', 'permission' => 'designation_report', 'icon' => 'fas fa-id-badge', 'category' => 'Reports'],
            ['title' => 'HRM Holiday Report', 'route' => 'report.holiday', 'permission' => 'holiday_report', 'icon' => 'fas fa-calendar-day', 'category' => 'Reports'],
            ['title' => 'HRM Employee Report', 'route' => 'report.employee', 'permission' => 'employee_report', 'icon' => 'fas fa-users', 'category' => 'Reports'],
            ['title' => 'Salary Sheet Report', 'route' => 'report.salary-sheet', 'permission' => 'salary_sheet', 'icon' => 'fas fa-file-invoice-dollar', 'category' => 'Reports'],
            ['title' => 'Employee Advance Report', 'route' => 'report.employee-advance', 'permission' => 'payroll_reports', 'icon' => 'fas fa-list-alt', 'category' => 'Reports'],
            ['title' => 'Live Attendance Report', 'route' => 'report.live_attendance', 'permission' => 'live_attendance', 'icon' => 'fas fa-file-invoice-dollar', 'category' => 'Reports'],
            ['title' => 'Attendance Sheet Report', 'route' => 'report.attendance-sheet', 'permission' => 'attendance_sheet', 'icon' => 'fas fa-file-invoice-dollar', 'category' => 'Reports'],
            ['title' => 'Daily Attendance Sheet', 'route' => 'report.daily-attendance-sheet', 'permission' => 'attendance_sheet', 'icon' => 'fas fa-calendar-day', 'category' => 'Reports'],
            ['title' => 'Attendance Summary Report', 'route' => 'report.attendance-summary', 'permission' => 'attendance_summary', 'icon' => 'fas fa-file-invoice-dollar', 'category' => 'Reports'],
            ['title' => 'Branch Dashboard', 'route' => 'branch_management.dashboard', 'permission' => 'branch_view', 'icon' => 'fas fa-tachometer-alt', 'category' => 'Branches'],
            ['title' => 'Branch Report', 'route' => 'sister-concerns.report', 'permission' => 'branch_report', 'icon' => 'fas fa-file-invoice-dollar', 'category' => 'Reports'],
            ['title' => 'Branch Employee Report', 'route' => 'branch.employee_report', 'permission' => 'branch_report', 'icon' => 'fas fa-users', 'category' => 'Reports'],
            ['title' => 'Branch Daily Income Statement', 'route' => 'branch_report.daily_income_statement', 'permission' => 'branch_report', 'icon' => 'fas fa-calendar-day', 'category' => 'Reports'],
            ['title' => 'Branch Cash/Bank Book', 'route' => 'branch_report.cash_or_bank_book', 'permission' => 'branch_report', 'icon' => 'fas fa-book', 'category' => 'Reports'],
            ['title' => 'Branch Receipt & Payment', 'route' => 'branch_report.receipt_and_payment', 'permission' => 'branch_report', 'icon' => 'fas fa-cash-register', 'category' => 'Reports'],
            ['title' => 'Branch Ledger', 'route' => 'branch_report.ledger', 'permission' => 'branch_report', 'icon' => 'fas fa-journal-whills', 'category' => 'Reports'],
            ['title' => 'Branch Trial Balance', 'route' => 'branch_report.trial_balance', 'permission' => 'branch_report', 'icon' => 'fas fa-balance-scale', 'category' => 'Reports'],
            ['title' => 'Branch Income Statement', 'route' => 'branch_report.income_statement', 'permission' => 'branch_report', 'icon' => 'fas fa-file-signature', 'category' => 'Reports'],
            ['title' => 'Branch Balance Sheet', 'route' => 'branch_report.balance_sheet', 'permission' => 'branch_report', 'icon' => 'fas fa-file-invoice-dollar', 'category' => 'Reports'],
            ['title' => 'Users Report', 'route' => 'user.report', 'permission' => 'users_report', 'icon' => 'fas fa-file-invoice-dollar', 'category' => 'Reports'],
            
            // Settings Reports
            ['title' => 'Brand Report', 'route' => 'report.brand', 'permission' => 'report_brand', 'icon' => 'fas fa-copyright', 'category' => 'Reports'],
            ['title' => 'Category Report', 'route' => 'report.category', 'permission' => 'report_category', 'icon' => 'fas fa-folder', 'category' => 'Reports'],
            ['title' => 'Sub-Category Report', 'route' => 'report.sub_category', 'permission' => 'report_sub_category', 'icon' => 'fas fa-folder-open', 'category' => 'Reports'],
            ['title' => 'Product Report', 'route' => 'report.product', 'permission' => 'report_product', 'icon' => 'fas fa-box', 'category' => 'Reports'],
            ['title' => 'Price Book History Report', 'route' => 'report.price_book_history', 'permission' => 'report_price_book_history', 'icon' => 'fas fa-history', 'category' => 'Reports'],
            
            // Inventory & Stock Transfer Reports
            ['title' => 'Stock Report', 'route' => 'report.stock', 'permission' => 'stock_report', 'icon' => 'fas fa-chart-line', 'category' => 'Reports'],
            ['title' => 'Batch Stock Report', 'route' => 'report.batch_stock', 'permission' => 'batch_stock_report', 'icon' => 'fas fa-layer-group', 'category' => 'Reports'],
            ['title' => 'Stock Valuation Report (Cash/Pur)', 'route' => 'report.stock_valuation', 'permission' => 'stock_valuation_report', 'icon' => 'fas fa-calculator', 'category' => 'Reports'],
            ['title' => 'Batch Stock Valuation Report', 'route' => 'report.batch_stock_valuation', 'permission' => 'batch_stock_valuation_report', 'icon' => 'fas fa-list-ol', 'category' => 'Reports'],
            ['title' => 'Stock Conversion Report', 'route' => 'stock-conversions.report', 'permission' => 'stock_transfer_report', 'icon' => 'fas fa-recycle', 'category' => 'Reports'],
            ['title' => 'Stock Transfer Report', 'route' => 'stock-transfers.report', 'permission' => 'stock_transfer_report', 'icon' => 'fas fa-file-invoice', 'category' => 'Reports'],
            ['title' => 'Stock Return Report', 'route' => 'stock-transfers.return_report', 'permission' => 'stock_transfer_report', 'icon' => 'fas fa-file-invoice', 'category' => 'Reports'],
            ['title' => 'Cash/Bank Book Report', 'route' => 'report.cash_or_bank_book', 'permission' => 'account_reports', 'icon' => 'fas fa-file-invoice-dollar', 'category' => 'Reports'],
            ['title' => 'Cash Transaction Details', 'route' => 'report.cash_or_bank_transaction_details', 'permission' => 'account_reports', 'icon' => 'fas fa-list-alt', 'category' => 'Reports'],
            ['title' => 'Receipt & Payment Report', 'route' => 'report.receipt_and_payment', 'permission' => 'account_reports', 'icon' => 'fas fa-file-invoice-dollar', 'category' => 'Reports'],
            ['title' => 'Ledger Report', 'route' => 'report.ledger', 'permission' => 'account_reports', 'icon' => 'fas fa-file-invoice-dollar', 'category' => 'Reports'],
            ['title' => 'Inter-Company Ledger', 'route' => 'report.inter_company_ledger', 'permission' => 'account_reports', 'icon' => 'fas fa-journal-whills', 'category' => 'Reports'],
            ['title' => 'Trial Balance Report', 'route' => 'report.trial_balance', 'permission' => 'account_reports', 'icon' => 'fas fa-file-invoice-dollar', 'category' => 'Reports'],
            ['title' => 'Income Statement Report', 'route' => 'report.income_statement', 'permission' => 'account_reports', 'icon' => 'fas fa-file-invoice-dollar', 'category' => 'Reports'],
            ['title' => 'Balance Sheet Report', 'route' => 'report.balance_sheet', 'permission' => 'account_reports', 'icon' => 'fas fa-file-invoice-dollar', 'category' => 'Reports'],
            ['title' => 'Product Utilize Report', 'route' => 'product-utilizes.report', 'permission' => 'product_utilize_report', 'icon' => 'fas fa-file-invoice', 'category' => 'Reports'],

            // Card Reports
            ['title' => 'Card Collection Report', 'route' => 'card-management.reports.collection', 'permission' => 'dashboard', 'icon' => 'fas fa-file-invoice-dollar', 'category' => 'Reports'],
            ['title' => 'Member Status Summary', 'route' => 'card-management.reports.member.status_summary', 'permission' => 'dashboard', 'icon' => 'fas fa-chart-bar', 'category' => 'Reports'],
            ['title' => 'Member Print History', 'route' => 'card-management.reports.member.print_history', 'permission' => 'dashboard', 'icon' => 'fas fa-history', 'category' => 'Reports'],
            ['title' => 'Member Lost / Damaged', 'route' => 'card-management.reports.member.lost_damaged', 'permission' => 'dashboard', 'icon' => 'fas fa-exclamation-triangle', 'category' => 'Reports'],
            ['title' => 'Employee Status Summary', 'route' => 'card-management.reports.employee.status_summary', 'permission' => 'dashboard', 'icon' => 'fas fa-chart-line', 'category' => 'Reports'],
            ['title' => 'Employee Print History', 'route' => 'card-management.reports.employee.print_history', 'permission' => 'dashboard', 'icon' => 'fas fa-history', 'category' => 'Reports'],
            ['title' => 'Employee Lost / Damaged', 'route' => 'card-management.reports.employee.lost_damaged', 'permission' => 'dashboard', 'icon' => 'fas fa-exclamation-triangle', 'category' => 'Reports'],
            
            // Fixed Asset Reports
            ['title' => 'Fixed Asset Register (FAR)', 'route' => 'fixed-assets.reports.register', 'permission' => 'fixed_asset_view', 'icon' => 'fas fa-file-invoice', 'category' => 'Reports'],
            ['title' => 'Depreciation Schedule', 'route' => 'fixed-assets.reports.depreciation', 'permission' => 'fixed_asset_view', 'icon' => 'fas fa-chart-line', 'category' => 'Reports'],
            ['title' => 'Asset Addition & Disposal', 'route' => 'fixed-assets.reports.addition_disposal', 'permission' => 'fixed_asset_view', 'icon' => 'fas fa-exchange-alt', 'category' => 'Reports'],
            ['title' => 'Asset Category Summary', 'route' => 'fixed-assets.reports.category_summary', 'permission' => 'fixed_asset_view', 'icon' => 'fas fa-chart-pie', 'category' => 'Reports'],

            // Shomriddhi FDR
            ['title' => 'FDR Dashboard', 'route' => 'shomriddhi-fdr.dashboard', 'permission' => 'shomriddhi_fdr_dashboard', 'icon' => 'fas fa-tachometer-alt', 'category' => 'Shomriddhi FDR'],
            ['title' => 'FDR Accounts', 'route' => 'shomriddhi-fdr.accounts.index', 'permission' => 'shomriddhi_fdr_accounts', 'icon' => 'fas fa-file-invoice-dollar', 'category' => 'Shomriddhi FDR'],
            ['title' => 'FDR Transactions', 'route' => 'shomriddhi-fdr.transactions.index', 'permission' => 'shomriddhi_fdr', 'icon' => 'fas fa-exchange-alt', 'category' => 'Shomriddhi FDR'],
            ['title' => 'FDR Schemes', 'route' => 'shomriddhi-fdr.schemes.index', 'permission' => 'shomriddhi_fdr', 'icon' => 'fas fa-layer-group', 'category' => 'Shomriddhi FDR'],
            ['title' => 'FDR Maturity Report', 'route' => 'shomriddhi-fdr.reports.maturity', 'permission' => 'shomriddhi_fdr_maturity_report', 'icon' => 'fas fa-calendar-alt', 'category' => 'Reports'],
            ['title' => 'FDR Transaction Report', 'route' => 'shomriddhi-fdr.reports.transaction', 'permission' => 'shomriddhi_fdr_transaction_report', 'icon' => 'fas fa-list-alt', 'category' => 'Reports'],
            ['title' => 'FDR Account Ledger', 'route' => 'shomriddhi-fdr.reports.ledger', 'permission' => 'shomriddhi_fdr_ledger_report', 'icon' => 'fas fa-file-invoice', 'category' => 'Reports'],
            ['title' => 'FDR Active Accounts', 'route' => 'shomriddhi-fdr.reports.active-accounts', 'permission' => 'shomriddhi_fdr_active_accounts_report', 'icon' => 'fas fa-check-circle', 'category' => 'Reports'],
            ['title' => 'FDR Closed Accounts', 'route' => 'shomriddhi-fdr.reports.closed-accounts', 'permission' => 'shomriddhi_fdr_closed_accounts_report', 'icon' => 'fas fa-times-circle', 'category' => 'Reports'],
            ['title' => 'FDR Scheme Summary', 'route' => 'shomriddhi-fdr.reports.scheme-summary', 'permission' => 'shomriddhi_fdr_scheme_summary_report', 'icon' => 'fas fa-chart-pie', 'category' => 'Reports'],

            // Shomriddhi DPS
            ['title' => 'DPS Dashboard', 'route' => 'shomriddhi-dps.dashboard', 'permission' => 'shomriddhi_dps_dashboard', 'icon' => 'fas fa-tachometer-alt', 'category' => 'Shomriddhi DPS'],
            ['title' => 'DPS Accounts', 'route' => 'shomriddhi-dps.accounts.index', 'permission' => 'shomriddhi_dps_accounts', 'icon' => 'fas fa-piggy-bank', 'category' => 'Shomriddhi DPS'],
            ['title' => 'DPS Collect Installment', 'route' => 'shomriddhi-dps.transactions.create', 'permission' => 'shomriddhi_dps_accounts', 'icon' => 'fas fa-hand-holding-usd', 'category' => 'Shomriddhi DPS'],
            ['title' => 'DPS Collection History', 'route' => 'shomriddhi-dps.transactions.index', 'permission' => 'shomriddhi_dps_accounts', 'icon' => 'fas fa-exchange-alt', 'category' => 'Shomriddhi DPS'],
            ['title' => 'DPS Schemes', 'route' => 'shomriddhi-dps.schemes.index', 'permission' => 'shomriddhi_dps_schemes', 'icon' => 'fas fa-layer-group', 'category' => 'Shomriddhi DPS'],
            ['title' => 'DPS Active Accounts', 'route' => 'shomriddhi-dps.reports.active-accounts', 'permission' => 'shomriddhi_dps_reports', 'icon' => 'fas fa-check-circle', 'category' => 'Reports'],
            ['title' => 'DPS Matured Accounts', 'route' => 'shomriddhi-dps.reports.matured-accounts', 'permission' => 'shomriddhi_dps_reports', 'icon' => 'fas fa-calendar-check', 'category' => 'Reports'],
            ['title' => 'DPS Defaulted Accounts', 'route' => 'shomriddhi-dps.reports.defaulted-accounts', 'permission' => 'shomriddhi_dps_reports', 'icon' => 'fas fa-exclamation-triangle', 'category' => 'Reports'],
            ['title' => 'DPS Transaction Report', 'route' => 'shomriddhi-dps.reports.transaction', 'permission' => 'shomriddhi_dps_reports', 'icon' => 'fas fa-list-alt', 'category' => 'Reports'],
            ['title' => 'DPS Account Ledger', 'route' => 'shomriddhi-dps.reports.ledger', 'permission' => 'shomriddhi_dps_reports', 'icon' => 'fas fa-file-invoice', 'category' => 'Reports'],
            ['title' => 'DPS Scheme Summary', 'route' => 'shomriddhi-dps.reports.scheme-summary', 'permission' => 'shomriddhi_dps_reports', 'icon' => 'fas fa-chart-pie', 'category' => 'Reports'],

            ['title' => 'Fixed Asset Register', 'route' => 'fixed-assets.index', 'permission' => 'fixed_asset_view', 'icon' => 'fas fa-list', 'category' => 'Fixed Assets'],
            ['title' => 'Fixed Asset Categories', 'route' => 'fixed-assets.categories.index', 'permission' => 'fixed_asset_category_view', 'icon' => 'fas fa-tags', 'category' => 'Fixed Assets'],
            
            ['title' => 'Shomriddhi Dashboard', 'route' => 'shomriddhi-advance.dashboard', 'permission' => 'shomriddhi_advance_dashboard', 'icon' => 'fas fa-piggy-bank', 'category' => 'Shomriddhi Advance'],
            ['title' => 'Deposit / Withdraw Entry', 'route' => 'shomriddhi-advance.create', 'permission' => 'shomriddhi_advance_transaction_create', 'icon' => 'fas fa-plus-circle', 'category' => 'Shomriddhi Advance'],
            ['title' => 'Shomriddhi Accounts List', 'route' => 'shomriddhi-advance.accounts.index', 'permission' => 'shomriddhi_advance_transaction_create', 'icon' => 'fas fa-users', 'category' => 'Shomriddhi Advance'],
            ['title' => 'Pending Transactions', 'route' => 'shomriddhi-advance.index', 'permission' => 'shomriddhi_advance_transaction_approve', 'icon' => 'fas fa-list', 'category' => 'Shomriddhi Advance'],
            ['title' => 'Interest Slabs', 'route' => 'shomriddhi-advance.slabs.index', 'permission' => 'shomriddhi_advance_settings', 'icon' => 'fas fa-percent', 'category' => 'Shomriddhi Advance'], 
            
            // Company Loan
            ['title' => 'Company Loan Dashboard', 'route' => 'company_loan.dashboard', 'permission' => 'manage_company_loan', 'icon' => 'fas fa-tachometer-alt', 'category' => 'Company Loan'],
            ['title' => 'Company Loan List', 'route' => 'company_loan.index', 'permission' => 'manage_company_loan', 'icon' => 'fas fa-hand-holding-usd', 'category' => 'Company Loan'],
            ['title' => 'Loan Statement', 'route' => 'company_loan.reports.statement', 'permission' => 'company_loan_report_statement', 'icon' => 'fas fa-file-invoice', 'category' => 'Company Loan'],
            ['title' => 'Loan Receivable Summary', 'route' => 'company_loan.reports.receivable_summary', 'permission' => 'company_loan_report_receivable', 'icon' => 'fas fa-file-download', 'category' => 'Company Loan'],
            ['title' => 'Loan Payable Summary', 'route' => 'company_loan.reports.payable_summary', 'permission' => 'company_loan_report_payable', 'icon' => 'fas fa-file-upload', 'category' => 'Company Loan'],
            ['title' => 'Loan Due Installments', 'route' => 'company_loan.reports.due_installments', 'permission' => 'company_loan_report_due_installments', 'icon' => 'fas fa-calendar-times', 'category' => 'Company Loan'],

            // Microfinance
            ['title' => 'Microfinance Dashboard', 'route' => 'microfinance.dashboard', 'permission' => 'microfinance', 'icon' => 'fas fa-chart-line', 'category' => 'Microfinance'],
            ['title' => 'MF Field Officers', 'route' => 'microfinance.field-officer.index', 'permission' => 'mf_field_officer', 'icon' => 'fas fa-user-tie', 'category' => 'Microfinance'],
            ['title' => 'MF Somitis (Groups)', 'route' => 'microfinance.somiti.index', 'permission' => 'mf_somiti', 'icon' => 'fas fa-users', 'category' => 'Microfinance'],
            ['title' => 'MF Members', 'route' => 'microfinance.member.index', 'permission' => 'mf_member', 'icon' => 'fas fa-user-friends', 'category' => 'Microfinance'],
            ['title' => 'MF Savings Products', 'route' => 'microfinance.savings-product.index', 'permission' => 'mf_savings_product', 'icon' => 'fas fa-piggy-bank', 'category' => 'Microfinance'],
            ['title' => 'MF Member Accounts', 'route' => 'microfinance.savings-account.index', 'permission' => 'mf_savings_account', 'icon' => 'fas fa-wallet', 'category' => 'Microfinance'],
            ['title' => 'MF Loan Products', 'route' => 'microfinance.loan-product.index', 'permission' => 'mf_loan_product', 'icon' => 'fas fa-box', 'category' => 'Microfinance'],
            ['title' => 'MF Loans', 'route' => 'microfinance.loan.index', 'permission' => 'mf_loan', 'icon' => 'fas fa-hand-holding-usd', 'category' => 'Microfinance'],
            ['title' => 'MF Collection Sheet', 'route' => 'microfinance.collection.sheet', 'permission' => 'mf_collection_sheet', 'icon' => 'fas fa-clipboard-list', 'category' => 'Microfinance'],
            ['title' => 'MF Loan Collections', 'route' => 'microfinance.collection.index', 'permission' => 'mf_collection', 'icon' => 'fas fa-money-bill-wave', 'category' => 'Microfinance'],
            ['title' => 'MF Day Closing (EOD)', 'route' => 'microfinance.day-closing.index', 'permission' => 'mf_day_closing', 'icon' => 'fas fa-calendar-check', 'category' => 'Microfinance'],

            // Product Utilize
            ['title' => 'Product Utilize Dashboard', 'route' => 'product-utilizes.dashboard', 'permission' => 'product_utilize', 'icon' => 'fas fa-tachometer-alt', 'category' => 'Product Utilize'],
            ['title' => 'Create Product Utilize', 'route' => 'product-utilizes.create', 'permission' => 'product_utilize', 'icon' => 'fas fa-plus-circle', 'category' => 'Product Utilize'],
            ['title' => 'Product Utilize List', 'route' => 'product-utilizes.index', 'permission' => 'product_utilize', 'icon' => 'fas fa-tasks', 'category' => 'Product Utilize'],

            // Incentive Management
            ['title' => 'Incentive Settings', 'route' => 'incentive-settings.index', 'permission' => 'incentive_management', 'icon' => 'fas fa-cogs', 'category' => 'Incentive Management'],
            ['title' => 'Incentive Payments & Ledger', 'route' => 'incentive-payments.index', 'permission' => 'incentive_management', 'icon' => 'fas fa-money-check-alt', 'category' => 'Incentive Management'],

            // SMS Management
            ['title' => 'SMS Dashboard', 'route' => 'sms_management.dashboard', 'permission' => 'sms_dashboard', 'icon' => 'fas fa-chart-bar', 'category' => 'SMS Management'],
            ['title' => 'SMS Campaign / Bulk SMS', 'route' => 'sms_management.campaign.index', 'permission' => 'sms_dashboard', 'icon' => 'fas fa-paper-plane', 'category' => 'SMS Management'],
            ['title' => 'SMS Delivery Logs', 'route' => 'sms_management.logs.index', 'permission' => 'sms_dashboard', 'icon' => 'fas fa-history', 'category' => 'SMS Management'],
            ['title' => 'SMS Templates', 'route' => 'sms_management.templates.index', 'permission' => 'sms_templates', 'icon' => 'fas fa-envelope-open-text', 'category' => 'SMS Management'],
            ['title' => 'SMS Gateways', 'route' => 'sms_management.gateways.index', 'permission' => 'sms_gateways', 'icon' => 'fas fa-server', 'category' => 'SMS Management'],
            
            // Missing bits
            ['title' => 'HRM Settings (Late/Weekend)', 'route' => 'hrm-settings.index', 'permission' => 'hrm_settings', 'icon' => 'fas fa-cog', 'category' => 'HRM'],
            ['title' => 'Employee Advances', 'route' => 'employee-advances.index', 'permission' => 'employee_advance', 'icon' => 'fas fa-money-bill', 'category' => 'Payroll'],
            ['title' => 'Inter Company Settlements', 'route' => 'inter_company_settlements.index', 'permission' => 'inter_company_settlement', 'icon' => 'fas fa-exchange-alt', 'category' => 'Accounts'],
            
            // Employee Profile (Self Service)
            ['title' => 'My Dashboard', 'route' => 'employee_profile.dashboard', 'permission' => 'dashboard', 'icon' => 'fas fa-id-badge', 'category' => 'My Profile'],
            ['title' => 'My Profile', 'route' => 'employee_profile.profile.index', 'permission' => 'dashboard', 'icon' => 'fas fa-user-circle', 'category' => 'My Profile'],
            ['title' => 'My Financials', 'route' => 'employee_profile.payroll.index', 'permission' => 'dashboard', 'icon' => 'fas fa-wallet', 'category' => 'My Profile'],
            ['title' => 'My Attendance & Leave', 'route' => 'employee_profile.leave.index', 'permission' => 'dashboard', 'icon' => 'fas fa-calendar-alt', 'category' => 'My Profile'],

            // Trainee Management
            ['title' => 'Trainee Dashboard', 'route' => 'trainee_management.dashboard', 'permission' => 'trainee_management', 'icon' => 'fas fa-tachometer-alt', 'category' => 'Trainee Management'],
            ['title' => 'Trainee List', 'route' => 'trainee.index', 'permission' => 'trainee_list', 'icon' => 'fas fa-users', 'category' => 'Trainee Management'],
            ['title' => 'Trainee Tasks', 'route' => 'trainee.tasks.index', 'permission' => 'trainee_tasks', 'icon' => 'fas fa-tasks', 'category' => 'Trainee Management'],
            ['title' => 'Trainee Visit Types', 'route' => 'trainee.visit_type.index', 'permission' => 'trainee_management', 'icon' => 'fas fa-tags', 'category' => 'Trainee Management'],
            ['title' => 'Trainee Visits', 'route' => 'trainee.visits.index', 'permission' => 'trainee_management', 'icon' => 'fas fa-walking', 'category' => 'Trainee Management'],

            // Stock Conversion & Transfer Missing Pages
            ['title' => 'Stock Conversion Dashboard', 'route' => 'stock-conversions.dashboard', 'permission' => 'stock_transfer', 'icon' => 'fas fa-tachometer-alt', 'category' => 'Inventory'],
            ['title' => 'Create Stock Conversion', 'route' => 'stock-conversions.create', 'permission' => 'stock_transfer', 'icon' => 'fas fa-plus-circle', 'category' => 'Inventory'],
            ['title' => 'Stock Transfer Dashboard', 'route' => 'stock-transfers.dashboard', 'permission' => 'stock_transfer', 'icon' => 'fas fa-tachometer-alt', 'category' => 'Inventory'],
            ['title' => 'Pending Dispatch Returns', 'route' => 'stock-transfers.pending_dispatch_returns', 'permission' => 'stock_transfer', 'icon' => 'fas fa-undo', 'category' => 'Inventory'],
            ['title' => 'Pending Returns', 'route' => 'stock-transfers.pending_returns', 'permission' => 'stock_transfer', 'icon' => 'fas fa-undo-alt', 'category' => 'Inventory'],
            ['title' => 'Incoming Stock History', 'route' => 'stock-transfers.incoming_history', 'permission' => 'stock_transfer', 'icon' => 'fas fa-history', 'category' => 'Inventory'],

            // Additional SR Pages
            ['title' => 'SR Lead Collection', 'route' => 'sr_lead_collection.index', 'permission' => 'sr_management', 'icon' => 'fas fa-funnel-dollar', 'category' => 'SR Management'],
            ['title' => 'SR Route Plans', 'route' => 'sr_route_plans.index', 'permission' => 'sr_management', 'icon' => 'fas fa-route', 'category' => 'SR Management'],
            ['title' => 'SR Social Activities', 'route' => 'sr_social.index', 'permission' => 'sr_management', 'icon' => 'fas fa-thumbs-up', 'category' => 'SR Management'],

            // Shomriddhi Reports
            ['title' => 'Shomriddhi Ledger Report', 'route' => 'shomriddhi-advance.reports.ledger', 'permission' => 'shomriddhi_advance_report', 'icon' => 'fas fa-book', 'category' => 'Shomriddhi Advance'],
            ['title' => 'Shomriddhi Summary Report', 'route' => 'shomriddhi-advance.reports.summary', 'permission' => 'shomriddhi_advance_report', 'icon' => 'fas fa-chart-pie', 'category' => 'Shomriddhi Advance'],
            ['title' => 'Shomriddhi Daily Interest Logs', 'route' => 'shomriddhi-advance.reports.daily_interest', 'permission' => 'shomriddhi_advance_report', 'icon' => 'fas fa-history', 'category' => 'Shomriddhi Advance']
        ];

        foreach ($pages as $page) {
            if ($q === '' || stripos($page['title'], $q) !== false) {
                // Check permissions dynamically
                if (auth()->user()->can($page['permission'])) {
                    $results[] = [
                        'title' => $page['title'],
                        'url' => route($page['route'], $page['params'] ?? []),
                        'category' => $page['category'],
                        'icon' => $page['icon']
                    ];
                }
            }
        }

        // 2. Database Entities (only if query is 2+ characters long)
        if (strlen($q) >= 2) {
            $activeConcernId = auth()->user()->activeConcernId();

            // Search Vouchers
            $vouchers = Voucher::where('sister_concern_id', $activeConcernId)
                ->where('voucher_no', 'LIKE', "%{$q}%")
                ->take(5)
                ->get();
            foreach ($vouchers as $voucher) {
                $results[] = [
                    'title' => "Voucher: {$voucher->voucher_no} (" . number_format($voucher->amount, 2) . ")",
                    'url' => route('voucher.details', ['voucher' => $voucher->id, 'voucher_type' => $voucher->voucher_type]),
                    'category' => 'Vouchers',
                    'icon' => 'fas fa-file-invoice-dollar'
                ];
            }

            // Search Employees
            $employees = \App\Models\Hrm\Employee::where('sister_concern_id', $activeConcernId)
                ->where(function($query) use ($q) {
                    $query->where('name', 'LIKE', "%{$q}%")
                          ->orWhere('mobile_no', 'LIKE', "%{$q}%");
                })
                ->take(5)
                ->get();
            foreach ($employees as $employee) {
                $results[] = [
                    'title' => "Employee: {$employee->name} (" . ($employee->mobile_no ?? 'N/A') . ")",
                    'url' => route('employees.index') . "?search=" . urlencode($employee->name),
                    'category' => 'HRM',
                    'icon' => 'fas fa-user'
                ];
            }

            // Search Suppliers & Customers
            $suppliers = Supplier::where(function($query) use ($q) {
                    $query->where('name', 'LIKE', "%{$q}%")
                          ->orWhere('mobile_no', 'LIKE', "%{$q}%");
                })
                ->take(5)
                ->get();
            foreach ($suppliers as $supplier) {
                $label = $supplier->type == 2 ? 'Customer' : 'Supplier';
                $results[] = [
                    'title' => "{$label}: {$supplier->name} (" . ($supplier->mobile_no ?? 'N/A') . ")",
                    'url' => ($supplier->type == 2 ? route('sales.index') : route('purchase.index')) . "?search=" . urlencode($supplier->name),
                    'category' => $supplier->type == 2 ? 'Sales' : 'Purchase',
                    'icon' => $supplier->type == 2 ? 'fas fa-shopping-bag' : 'fas fa-shopping-cart'
                ];
            }

            // Search Account Heads
            $heads = AccountHead::where('name', 'LIKE', "%{$q}%")
                ->take(5)
                ->get();
            foreach ($heads as $head) {
                $results[] = [
                    'title' => "Account Head: {$head->name}",
                    'url' => route('account-head.index') . "?search=" . urlencode($head->name),
                    'category' => 'Accounts',
                    'icon' => 'fas fa-calculator'
                ];
            }
        }

        return response()->json($results);
    }
}
