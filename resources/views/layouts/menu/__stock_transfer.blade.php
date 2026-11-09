<?php
$subMenu = [
    'stock-transfers.index',
    'stock-transfers.create',
    'stock-transfers.edit',
    'stock-transfers.details',
    'stock-transfers.receive',
    'stock-transfers.receive_list',
    'stock-transfers.incoming_history',
    'stock-transfers.pending_returns',
    'stock-transfers.return_receive',
    'stock-transfers.report',
    'stock-transfers.return_report',
];
?>
@if(auth()->user()->can('stock_transfer'))
    <li class="nav-item {{ in_array(Route::currentRouteName(), $subMenu) ? 'menu-open' : '' }}">
        <a href="#"
           class="nav-link {{ in_array(Route::currentRouteName(), $subMenu) ? 'active' : '' }}">
            <i class="nav-icon fas fa-exchange"></i>
            <p>
                Stock Transfer
                <i class="right fas fa-angle-left"></i>
            </p>
        </a>
        <ul class="nav nav-treeview">
            <?php
            $subSubMenu = ['stock-transfers.create', 'stock-transfers.index', 'stock-transfers.edit'];
            ?>
            @if(auth()->user()->can('stock_transfer_create'))
                <!-- 1. Transfer Stock (Outgoing & Outbound Returns) -->
                <li class="nav-item">
                    <a href="{{ route('stock-transfers.index') }}"
                       class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                        <i class="fas fa-truck nav-icon"></i>
                        <p>Transfer Stock</p>
                    </a>
                </li>
            @endif

            <?php
            $receiveMenu = ['stock-transfers.receive_list', 'stock-transfers.receive', 'stock-transfers.details'];
            ?>
            @if(auth()->user()->can('stock_transfer_receive'))
                <!-- 2. Receive Stock (Standard incoming) -->
                <li class="nav-item">
                    <a href="{{ route('stock-transfers.receive_list') }}"
                       class="nav-link {{ in_array(Route::currentRouteName(), $receiveMenu) ? 'active' : '' }}">
                        <i class="fas fa-box nav-icon"></i>
                        <p>Receive Stock</p>
                    </a>
                </li>
                
                <!-- 3. Return Transfer Stock (Shortage returns waiting to be received) -->
                <?php
                $returnMenu = ['stock-transfers.pending_returns', 'stock-transfers.return_receive'];
                ?>
                <li class="nav-item">
                    <a href="{{ route('stock-transfers.pending_returns') }}"
                       class="nav-link {{ in_array(Route::currentRouteName(), $returnMenu) ? 'active' : '' }}">
                        <i class="fas fa-undo nav-icon"></i>
                        <p>Return Transfer Stock</p>
                    </a>
                </li>

                <!-- 4. Incoming History (Standard Receipts & Return Receipts) -->
                <?php
                $incomingMenu = ['stock-transfers.incoming_history'];
                ?>
                <li class="nav-item">
                    <a href="{{ route('stock-transfers.incoming_history') }}"
                       class="nav-link {{ in_array(Route::currentRouteName(), $incomingMenu) ? 'active' : '' }}">
                        <i class="fas fa-history nav-icon"></i>
                        <p>Incoming History</p>
                    </a>
                </li>
            @endif

            <!-- 5. Transfer Report -->
            <li class="nav-item">
                <a href="{{ route('stock-transfers.report') }}"
                   class="nav-link {{ Route::currentRouteName() == 'stock-transfers.report' ? 'active' : '' }}">
                    <i class="fas fa-file-text nav-icon"></i>
                    <p>Transfer Report</p>
                </a>
            </li>

            <!-- 6. Return Report -->
            <li class="nav-item">
                <a href="{{ route('stock-transfers.return_report') }}"
                   class="nav-link {{ Route::currentRouteName() == 'stock-transfers.return_report' ? 'active' : '' }}">
                    <i class="fas fa-file-invoice nav-icon"></i>
                    <p>Return Report</p>
                </a>
            </li>
        </ul>
    </li>
@endif
