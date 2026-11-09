<?php


use App\Http\Controllers\StockTransfer\StockTransferController;
use App\Http\Controllers\StockTransfer\DashboardController;
use App\Http\Controllers\StockTransfer\StockTransferReportController;
use Illuminate\Support\Facades\Route;


Route::middleware('permission:stock_transfer_management')->group(function () {

    Route::get('stock-transfers/dashboard', [DashboardController::class, 'index'])->name('stock-transfers.dashboard')->middleware('permission:stock_transfer');

    Route::resource('stock-transfers', StockTransferController::class)->middleware('permission:stock_transfer');
    Route::post('stock-transfers-dispatch/{stock_transfer}', [StockTransferController::class, 'dispatchStock'])->name('stock-transfers.dispatch')->middleware('permission:stock_transfer');
    Route::get('stock-transfers-datatable', [StockTransferController::class, 'dataTable'])->name('stock-transfers.datatable');
    Route::get('stock-transfers-details/{stock_transfer}', [StockTransferController::class, 'details'])->name('stock-transfers.details')->middleware('permission:stock_transfer');
    Route::get('stock-transfers-challan/{stock_transfer}', [StockTransferController::class, 'printChallan'])->name('stock-transfers.print_challan')->middleware('permission:stock_transfer');
    
    // Receive List Routes
    Route::get('stock-transfers-receive-list', [StockTransferController::class, 'receiveList'])->name('stock-transfers.receive_list')->middleware('permission:stock_transfer_receive');
    Route::get('stock-transfers-receive-datatable', [StockTransferController::class, 'receiveDatatable'])->name('stock-transfers.receive_datatable')->middleware('permission:stock_transfer_receive');
    
    Route::get('stock-transfers-receive/{stock_transfer}', [StockTransferController::class, 'receive'])->name('stock-transfers.receive')->middleware('permission:stock_transfer_receive');
    Route::post('stock-transfers-receive-store/{stock_transfer}', [StockTransferController::class, 'receiveStore'])->name('stock-transfers.receive_store')->middleware('permission:stock_transfer_receive');
    Route::post('stock-transfers-reject/{stock_transfer}', [StockTransferController::class, 'rejectTransfer'])->name('stock-transfers.reject')->middleware('permission:stock_transfer_receive');
    
    // Incoming History Routes
    // Incoming History Routes
    Route::get('stock-transfers-incoming-history', [StockTransferController::class, 'incomingHistory'])->name('stock-transfers.incoming_history')->middleware('permission:stock_transfer_receive');
    Route::get('stock-transfers-incoming-datatable', [StockTransferController::class, 'incomingDatatable'])->name('stock-transfers.incoming_datatable')->middleware('permission:stock_transfer_receive');

    // Return Transfer Routes (Sender receives back shortage items)
    Route::get('stock-transfers-pending-returns', [StockTransferController::class, 'pendingReturns'])->name('stock-transfers.pending_returns')->middleware('permission:stock_transfer_receive');
    Route::get('stock-transfers-pending-returns-datatable', [StockTransferController::class, 'pendingReturnsDatatable'])->name('stock-transfers.pending_returns_datatable')->middleware('permission:stock_transfer_receive');
    Route::get('stock-transfers-return-receive/{stock_transfer}', [StockTransferController::class, 'returnReceive'])->name('stock-transfers.return_receive')->middleware('permission:stock_transfer_receive');
    Route::post('stock-transfers-return-receive-store/{stock_transfer}', [StockTransferController::class, 'returnReceiveStore'])->name('stock-transfers.return_receive_store')->middleware('permission:stock_transfer_receive');

    // Return History Routes
    Route::get('stock-transfers-return-history', [StockTransferController::class, 'returnHistory'])->name('stock-transfers.return_history')->middleware('permission:stock_transfer_receive');
    Route::get('stock-transfers-return-history-datatable', [StockTransferController::class, 'returnHistoryDatatable'])->name('stock-transfers.return_history_datatable')->middleware('permission:stock_transfer_receive');

    // Return Dispatch Routes (For the branch who is returning/dispatching the shortage back)
    Route::get('stock-transfers-pending-dispatch-returns', [StockTransferController::class, 'pendingDispatchReturns'])->name('stock-transfers.pending_dispatch_returns')->middleware('permission:stock_transfer');
    Route::get('stock-transfers-pending-dispatch-returns-datatable', [StockTransferController::class, 'pendingDispatchReturnsDatatable'])->name('stock-transfers.pending_dispatch_returns_datatable')->middleware('permission:stock_transfer');

    // Manual Return Route
    Route::get('stock-transfers-manual-return/{stock_transfer}', [StockTransferController::class, 'manualReturnCreate'])->name('stock-transfers.manual_return')->middleware('permission:stock_transfer');
    Route::post('stock-transfers-manual-return-store/{stock_transfer}', [StockTransferController::class, 'manualReturnStore'])->name('stock-transfers.manual_return_store')->middleware('permission:stock_transfer');

    // Report Route
    Route::get('stock-transfers-report', [StockTransferReportController::class, 'transferReport'])->name('stock-transfers.report')->middleware('permission:stock_transfer_report');
    Route::get('stock-transfers-return-report', [StockTransferReportController::class, 'returnReport'])->name('stock-transfers.return_report')->middleware('permission:stock_transfer_report');
});