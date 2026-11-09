@php
    $currentRoute = Route::currentRouteName();
@endphp

<li class="nav-header text-uppercase font-weight-bold text-muted" style="font-size: 0.75rem;">Sales Workspace</li>
<li class="nav-item">
    <a href="{{ route('sales.dashboard') }}" class="nav-link {{ $currentRoute == 'sales.dashboard' ? 'active' : '' }}">
        <i class="nav-icon fas fa-tachometer-alt"></i>
        <p>Dashboard</p>
    </a>
</li>

@if(auth()->user()->can('sales_management'))
    <!-- Category 1: General Sales -->
    <li class="nav-header text-uppercase font-weight-bold text-muted"
        style="font-size: 0.7rem; padding-left: 10px; margin-top: 10px;">
        General Sales
    </li>

    <!-- 1. Customers -->
    @if(auth()->user()->can('customer'))
        <li class="nav-item">
            <a href="{{ route('customer.index') }}"
                class="nav-link {{ in_array($currentRoute, ['customer.index', 'customer.create', 'customer.edit', 'customer.show']) ? 'active' : '' }}">
                <i class="nav-icon fas fa-users"></i>
                <p>Customers</p>
            </a>
        </li>
    @endif

    <!-- 1.1 Customer Payment Receive -->
    @if(auth()->user()->can('sales_payment_receive'))
        <li class="nav-item">
            <a href="{{ route('customer-payment.index') }}"
                class="nav-link {{ in_array($currentRoute, ['customer-payment.index', 'customer-payment.create', 'customer-payment.history']) ? 'active' : '' }}">
                <i class="nav-icon fas fa-money-check-alt"></i>
                <p>Customer Receive</p>
            </a>
        </li>
    @endif

    <!-- 2. Pre-Orders -->
    @if(auth()->user()->can('pre_orders'))
        <li class="nav-item">
            <a href="{{ route('pre-orders.index') }}"
                class="nav-link {{ in_array($currentRoute, ['pre-orders.index', 'pre-orders.create', 'pre-orders.show', 'pre-orders.edit', 'pre-orders.delivery']) ? 'active' : '' }}">
                <i class="nav-icon fas fa-hand-holding-usd"></i>
                <p>Pre-Orders</p>
            </a>
        </li>
    @endif

    <!-- 3. Sales Invoice -->
    @if(auth()->user()->can('sales'))
        <li class="nav-item">
            <a href="{{ route('sales.index') }}"
                class="nav-link {{ in_array($currentRoute, ['sales.index', 'sales.edit', 'sales.details']) ? 'active' : '' }}">
                <i class="nav-icon fas fa-file-invoice"></i>
                <p>Sales Invoice</p>
            </a>
        </li>
    @endif

    <!-- Category 2: Corporate Sales -->
    <li class="nav-header text-uppercase font-weight-bold text-muted"
        style="font-size: 0.7rem; padding-left: 10px; margin-top: 15px;">
        Corporate Sales
    </li>

    <!-- 5. Corporate Clients -->
    @if(auth()->user()->can('corporate_customer'))
        <li class="nav-item">
            <a href="{{ route('corporate-customer.index') }}"
                class="nav-link {{ in_array($currentRoute, ['corporate-customer.index', 'corporate-customer.create', 'corporate-customer.edit', 'corporate-customer.show']) ? 'active' : '' }}">
                <i class="nav-icon fas fa-building"></i>
                <p>Corporate Clients</p>
            </a>
        </li>
    @endif

    <!-- 6. Corporate Invoice -->
    @if(auth()->user()->can('corporate_sales'))
        <li class="nav-item">
            <a href="{{ route('corporate-sales.index') }}"
                class="nav-link {{ in_array($currentRoute, ['corporate-sales.index', 'corporate-sales.create', 'corporate-sales.show', 'corporate-sales.edit']) ? 'active' : '' }}">
                <i class="nav-icon fas fa-file-signature"></i>
                <p>Corporate Invoice</p>
            </a>
        </li>
    @endif

    <!-- Category 3: Dealer Sales -->
    <li class="nav-header text-uppercase font-weight-bold text-muted"
        style="font-size: 0.7rem; padding-left: 10px; margin-top: 15px;">
        Dealer Sales
    </li>

    <!-- 7. Dealer Clients -->
    @if(auth()->user()->can('dealer'))
        <li class="nav-item">
            <a href="{{ route('dealer.index') }}"
                class="nav-link {{ in_array($currentRoute, ['dealer.index', 'dealer.create', 'dealer.edit', 'dealer.show']) ? 'active' : '' }}">
                <i class="nav-icon fas fa-handshake"></i>
                <p>Dealer Clients</p>
            </a>
        </li>
    @endif

    <!-- 8. Dealer Invoice -->
    @if(auth()->user()->can('dealer_sales'))
        <li class="nav-item">
            <a href="{{ route('dealer-sales.index') }}"
                class="nav-link {{ in_array($currentRoute, ['dealer-sales.index', 'dealer-sales.create', 'dealer-sales.show', 'dealer-sales.edit']) ? 'active' : '' }}">
                <i class="nav-icon fas fa-file-contract"></i>
                <p>Dealer Invoice</p>
            </a>
        </li>
    @endif

    <!-- Category 4: Service Management -->
    <li class="nav-header text-uppercase font-weight-bold text-muted"
        style="font-size: 0.7rem; padding-left: 10px; margin-top: 15px;">
        Service Management
    </li>

    <!-- 9. Service Requests -->
    @if(auth()->user()->can('service_request'))
        <li class="nav-item">
            <a href="{{ route('service.request.index') }}"
                class="nav-link {{ request()->is('*service/request*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-tools"></i>
                <p>Service Requests</p>
            </a>
        </li>
    @endif

    <!-- 10. Technicians -->
    @if(auth()->user()->can('technician'))
        <li class="nav-item">
            <a href="{{ route('service.technician.index') }}"
                class="nav-link {{ request()->is('*service/technician*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-user-cog"></i>
                <p>Technicians</p>
            </a>
        </li>
    @endif

    <!-- 11. Technician Panel -->
    @if(auth()->user()->can('service_panel'))
        <li class="nav-item">
            <a href="{{ route('service.panel.index') }}"
                class="nav-link {{ request()->is('*service/panel*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-laptop-house"></i>
                <p>Technician Panel</p>
            </a>
        </li>
    @endif

    <!-- Category 5: Sales Reports -->
    @if(auth()->user()->can('sales_reports'))
        <li class="nav-header text-uppercase font-weight-bold text-muted"
            style="font-size: 0.7rem; padding-left: 10px; margin-top: 15px;">
            Sales Reports
        </li>
        @if(auth()->user()->can('customer_report'))
            <li class="nav-item">
                <a href="{{ route('report.customer_ledger') }}"
                    class="nav-link {{ $currentRoute == 'report.customer_ledger' ? 'active' : '' }}">
                    <i class="nav-icon fas fa-book"></i>
                    <p>Customer Ledger</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('report.customer') }}" class="nav-link {{ $currentRoute == 'report.customer' ? 'active' : '' }}">
                    <i class="nav-icon fas fa-users-cog"></i>
                    <p>Customer Report</p>
                </a>
            </li>
        @endif
        @if(auth()->user()->can('sale_report'))
            <li class="nav-item">
                <a href="{{ route('report.sale') }}" class="nav-link {{ $currentRoute == 'report.sale' ? 'active' : '' }}">
                    <i class="nav-icon fas fa-chart-pie"></i>
                    <p>Sale Report</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('report.invoice_profit_loss') }}"
                    class="nav-link {{ $currentRoute == 'report.invoice_profit_loss' ? 'active' : '' }}">
                    <i class="nav-icon fas fa-file-invoice-dollar"></i>
                    <p>Invoice Profit / Loss</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('report.product_profit_loss') }}"
                    class="nav-link {{ $currentRoute == 'report.product_profit_loss' ? 'active' : '' }}">
                    <i class="nav-icon fas fa-boxes"></i>
                    <p>Product Profit / Loss</p>
                </a>
            </li>
        @endif

        @if(auth()->user()->can('customer_due_report'))
            <li class="nav-item">
                <a href="{{ route('report.customer_due') }}"
                    class="nav-link {{ $currentRoute == 'report.customer_due' ? 'active' : '' }}">
                    <i class="nav-icon fas fa-user-clock"></i>
                    <p>Customer Due Report</p>
                </a>
            </li>
        @endif


    @endif
@endif