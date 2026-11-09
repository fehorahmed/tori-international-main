<?php

namespace App\Http\Controllers\StockTransfer;

use App\Enumeration\InventoryLogType;
use App\Enumeration\ReservedAccountGroup;
use App\Enumeration\ReservedAccountHead;
use App\Enumeration\TransactionType;
use App\Enumeration\VoucherType;
use App\Http\Controllers\Controller;
use App\Models\Account\AccountGroup;
use App\Models\Account\AccountHead;
use App\Models\Inventory\Inventory;
use App\Models\Inventory\InventoryLog;
use App\Models\Settings\Product;
use App\Models\BranchManagement\SisterConcern;
use App\Models\StockTransfer\StockTransfer;
use App\Models\StockTransfer\StockTransferItem;
use App\Models\Account\Transaction;
use App\Models\Account\Voucher;
use App\Models\Settings\Warehouse;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class StockTransferController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!auth()->user()->hasPermissionTo('stock_transfer')) {
            abort(403, 'Unauthorized');
        }

        return view('stock_transfer.index');
    }
    public function details(StockTransfer $stockTransfer)
    {
        if (!auth()->user()->hasPermissionTo('stock_transfer')) {
            abort(403, 'Unauthorized');
        }
        $stockTransfer->load('stockTransferItems');
        return view('stock_transfer.details', compact('stockTransfer'));
    }
    public function receiveList()
    {
        if (!auth()->user()->hasPermissionTo('stock_transfer_receive')) {
            abort(403, 'Unauthorized');
        }
        return view('stock_transfer.receive_list');
    }

    public function receiveDatatable()
    {
        if (!auth()->user()->hasPermissionTo('stock_transfer_receive')) {
            abort(403, 'Unauthorized');
        }

        $user = auth()->user();
        $query = StockTransfer::with('fromWarehouse', 'toWarehouse', 'fromSisterConcern', 'toSisterConcern', 'stockTransferItems')
            ->where('to_sister_concern_id', $user->activeConcernId())
            ->where('is_return', 0) // Hide Return transfers from standard receive list
            ->where('status', 2); // Show only Dispatched (status = 2) transfers waiting to be received

        // Branch Manager: show transfers where to_warehouse is their branch's warehouse
        if ($user->role === 'Branch_Manager' && $user->sister_concern_id) {
            $branchWarehouse = Warehouse::where('sister_concern_id', $user->sister_concern_id)
                ->where('type', 'main')
                ->first();
            if ($branchWarehouse) {
                $query->where('to_warehouse_id', $branchWarehouse->id);
            }
        }

        // Date filter support for receive_list page
        if (request()->filled('start_date') && request()->filled('end_date')) {
            $query->whereBetween('date', [
                Carbon::parse(request('start_date'))->format('Y-m-d'),
                Carbon::parse(request('end_date'))->format('Y-m-d'),
            ]);
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('from_warehouse', function (StockTransfer $transfer) {
                return $transfer->fromSisterConcern->name ?? ($transfer->fromWarehouse->name ?? 'N/A');
            })
            ->addColumn('to_warehouse', function (StockTransfer $transfer) {
                return $transfer->toSisterConcern->name ?? ($transfer->toWarehouse->name ?? 'N/A');
            })
            ->addColumn('status', function (StockTransfer $transfer) {
                if ($transfer->status == 2) {
                    return '<span class="badge badge-warning"><i class="fa fa-truck"></i> Dispatched / In Transit</span>';
                } elseif ($transfer->status == 3) {
                    return '<span class="badge badge-info"><i class="fa fa-hourglass-half"></i> Partially Received</span>';
                } elseif ($transfer->status == 4) {
                    return '<span class="badge badge-success"><i class="fa fa-check-circle"></i> Fully Received</span>';
                }
                return '<span class="badge badge-secondary">Pending</span>';
            })
            ->addColumn('action', function (StockTransfer $transfer) {
                $btn = '';
                $pendingCount = $transfer->stockTransferItems->where('status', 'pending')->count();
                if ($pendingCount > 0) {
                    $btn .= '<a href="' . route('stock-transfers.receive', ['stock_transfer' => $transfer->id]) . '" class="btn btn-success btn-sm"><i class="fa fa-check-circle"></i> Receive</a>';
                }
                $btn .= ' <a href="' . route('stock-transfers.details', ['stock_transfer' => $transfer->id]) . '" class="btn btn-info btn-sm"><i class="fa fa-eye"></i></a>';
                return $btn;
            })
            ->rawColumns(['action', 'status'])
            ->toJson();
    }

    public function incomingHistory()
    {
        if (!auth()->user()->hasPermissionTo('stock_transfer_receive')) {
            abort(403, 'Unauthorized');
        }
        return view('stock_transfer.incoming_history');
    }

    public function incomingDatatable()
    {
        if (!auth()->user()->hasPermissionTo('stock_transfer_receive')) {
            abort(403, 'Unauthorized');
        }

        $user = auth()->user();
        $query = StockTransfer::with('fromWarehouse', 'toWarehouse', 'fromSisterConcern', 'toSisterConcern', 'stockTransferItems')
            ->where('to_sister_concern_id', $user->activeConcernId())
            ->where('is_return', 0) // Hide Return transfers from incoming history list
            ->where('status', '>=', 2); // Show only dispatched, received or partially received transfers

        // Branch Manager: show transfers where to_warehouse is their branch's warehouse
        if ($user->role === 'Branch_Manager' && $user->sister_concern_id) {
            $branchWarehouse = Warehouse::where('sister_concern_id', $user->sister_concern_id)
                ->where('type', 'main')
                ->first();
            if ($branchWarehouse) {
                $query->where('to_warehouse_id', $branchWarehouse->id);
            }
        }

        // Date filter support
        if (request()->filled('start_date') && request()->filled('end_date')) {
            $query->whereBetween('date', [
                Carbon::parse(request('start_date'))->format('Y-m-d'),
                Carbon::parse(request('end_date'))->format('Y-m-d'),
            ]);
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('from_warehouse', function (StockTransfer $transfer) {
                return $transfer->fromSisterConcern->name ?? ($transfer->fromWarehouse->name ?? 'N/A');
            })
            ->addColumn('to_warehouse', function (StockTransfer $transfer) {
                return $transfer->toSisterConcern->name ?? ($transfer->toWarehouse->name ?? 'N/A');
            })
            ->addColumn('status', function (StockTransfer $transfer) {
                $pendingCount = $transfer->stockTransferItems->where('status', 'pending')->count();
                $processedCount = $transfer->stockTransferItems->whereIn('status', ['received', 'partially_received'])->count();
                if ($processedCount === 0) {
                    return '<span class="badge badge-warning">Pending</span>';
                } elseif ($pendingCount > 0) {
                    return '<span class="badge badge-info">Partially Received</span>';
                } else {
                    return '<span class="badge badge-success">Received</span>';
                }
            })
            ->addColumn('action', function (StockTransfer $transfer) {
                $btn = '';
                $pendingCount = $transfer->stockTransferItems->where('status', 'pending')->count();
                if ($transfer->status >= 2 && $pendingCount > 0) {
                    $btn .= '<a href="' . route('stock-transfers.receive', ['stock_transfer' => $transfer->id]) . '" class="btn btn-success btn-sm"><i class="fa fa-check-circle"></i> Receive</a>';
                }
                $btn .= ' <a href="' . route('stock-transfers.details', ['stock_transfer' => $transfer->id]) . '" class="btn btn-info btn-sm"><i class="fa fa-eye"></i></a>';
                
                // Add Return Stock button if fully received
                if ($transfer->status == 4) {
                    $btn .= ' <a href="' . route('stock-transfers.manual_return', ['stock_transfer' => $transfer->id]) . '" class="btn btn-warning btn-sm"><i class="fa fa-undo"></i> Return Stock</a>';
                }
                
                return $btn;
            })
            ->rawColumns(['action', 'status'])
            ->toJson();
    }

    // â”€â”€â”€ RETURN TRANSFER (Pending Returns for Sender) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function pendingReturns()
    {
        if (!auth()->user()->hasPermissionTo('stock_transfer_receive')) {
            abort(403, 'Unauthorized');
        }
        return view('stock_transfer.pending_returns');
    }

    public function pendingReturnsDatatable()
    {
        if (!auth()->user()->hasPermissionTo('stock_transfer_receive')) {
            abort(403, 'Unauthorized');
        }
        $user = auth()->user();
        // Show returns where THIS user's SC is the TO (i.e., the original sender getting back)
        $query = StockTransfer::with('fromWarehouse', 'toWarehouse', 'fromSisterConcern', 'toSisterConcern', 'stockTransferItems', 'parentTransfer')
            ->where('to_sister_concern_id', $user->activeConcernId())
            ->whereIn('is_return', [1, 2])
            ->where('status', 2); // Show only Dispatched/In Transit (status = 2) return transfers waiting to be received back

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('from_branch', fn(StockTransfer $t) => $t->fromSisterConcern->name ?? ($t->fromWarehouse->name ?? ''))
            ->addColumn('to_branch',   fn(StockTransfer $t) => $t->toSisterConcern->name   ?? ($t->toWarehouse->name   ?? ''))
            ->addColumn('parent_order', fn(StockTransfer $t) => $t->parentTransfer->order_no ?? 'â€”')
            ->addColumn('items_count', fn(StockTransfer $t) => $t->stockTransferItems->count() . ' item(s)')
            ->addColumn('action', function (StockTransfer $t) {
                $btn = '<a href="' . route('stock-transfers.return_receive', ['stock_transfer' => $t->id]) . '" class="btn btn-warning btn-sm"><i class="fa fa-undo"></i> Receive Return</a>';
                $btn .= ' <a href="' . route('stock-transfers.details', ['stock_transfer' => $t->id]) . '" class="btn btn-info btn-sm"><i class="fa fa-eye"></i></a>';
                return $btn;
            })
            ->rawColumns(['action'])
            ->toJson();
    }

    // â”€â”€â”€ RETURN HISTORY â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function returnHistory()
    {
        if (!auth()->user()->hasPermissionTo('stock_transfer_receive')) {
            abort(403, 'Unauthorized');
        }
        return view('stock_transfer.return_history');
    }

    public function returnHistoryDatatable()
    {
        if (!auth()->user()->hasPermissionTo('stock_transfer_receive')) {
            abort(403, 'Unauthorized');
        }
        $user = auth()->user();
        $query = StockTransfer::with('fromWarehouse', 'toWarehouse', 'fromSisterConcern', 'toSisterConcern', 'stockTransferItems', 'parentTransfer')
            ->where('to_sister_concern_id', $user->activeConcernId())
            ->whereIn('is_return', [1, 2])
            ->where('status', 3);

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('from_branch', fn(StockTransfer $t) => $t->fromSisterConcern->name ?? ($t->fromWarehouse->name ?? ''))
            ->addColumn('to_branch',   fn(StockTransfer $t) => $t->toSisterConcern->name   ?? ($t->toWarehouse->name   ?? ''))
            ->addColumn('parent_order', fn(StockTransfer $t) => $t->parentTransfer->order_no ?? 'â€”')
            ->addColumn('items_count', fn(StockTransfer $t) => $t->stockTransferItems->count() . ' item(s)')
            ->addColumn('action', function (StockTransfer $t) {
                return '<a href="' . route('stock-transfers.details', ['stock_transfer' => $t->id]) . '" class="btn btn-info btn-sm"><i class="fa fa-eye"></i> Details</a>';
            })
            ->rawColumns(['action'])
            ->toJson();
    }

    // â”€â”€â”€ RETURN DISPATCH (For Returning Branch) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function pendingDispatchReturns()
    {
        if (!auth()->user()->hasPermissionTo('stock_transfer')) {
            abort(403, 'Unauthorized');
        }
        return view('stock_transfer.pending_dispatch_returns');
    }

    public function pendingDispatchReturnsDatatable()
    {
        if (!auth()->user()->hasPermissionTo('stock_transfer')) {
            abort(403, 'Unauthorized');
        }
        $user = auth()->user();
        $query = StockTransfer::with('fromWarehouse', 'toWarehouse', 'fromSisterConcern', 'toSisterConcern', 'stockTransferItems', 'parentTransfer')
            ->where('from_sister_concern_id', $user->activeConcernId())
            ->whereIn('is_return', [1, 2]);

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('from_branch', fn(StockTransfer $t) => $t->fromSisterConcern->name ?? ($t->fromWarehouse->name ?? ''))
            ->addColumn('to_branch',   fn(StockTransfer $t) => $t->toSisterConcern->name   ?? ($t->toWarehouse->name   ?? ''))
            ->addColumn('parent_order', fn(StockTransfer $t) => $t->parentTransfer->order_no ?? 'â€”')
            ->addColumn('items_count', fn(StockTransfer $t) => $t->stockTransferItems->count() . ' item(s)')
            ->addColumn('status_label', function (StockTransfer $t) {
                if ($t->status == 1) {
                    return '<span class="badge badge-warning">Pending Dispatch</span>';
                } elseif ($t->status == 2) {
                    return '<span class="badge badge-primary">Dispatched / In Transit</span>';
                } elseif ($t->status == 3) {
                    return '<span class="badge badge-success">Received back</span>';
                }
                return '<span class="badge badge-secondary">Pending</span>';
            })
            ->addColumn('action', function (StockTransfer $t) {
                $btn = '';
                if ($t->status == 1) {
                    $btn .= '<form action="' . route('stock-transfers.dispatch', ['stock_transfer' => $t->id]) . '" method="post" class="d-inline dispatch-return-form">';
                    $btn .= csrf_field();
                    $btn .= '<button type="submit" class="btn btn-primary btn-sm btn-dispatch-return mr-1"><i class="fa fa-truck"></i> Dispatch</button>';
                    $btn .= '</form>';
                }
                $btn .= '<a href="' . route('stock-transfers.details', ['stock_transfer' => $t->id]) . '" class="btn btn-info btn-sm"><i class="fa fa-eye"></i> Details</a>';
                return $btn;
            })
            ->rawColumns(['action', 'status_label'])
            ->toJson();
    }

    public function returnReceive(StockTransfer $stock_transfer)
    {
        if (!auth()->user()->hasPermissionTo('stock_transfer_receive')) {
            abort(403, 'Unauthorized');
        }
        if ($stock_transfer->is_return == 0) {
            return redirect()->route('stock-transfers.pending_returns')->with('error', 'Normal transfers must be received via the standard Receive interface.');
        }
        if ($stock_transfer->status < 2) {
            return redirect()->route('stock-transfers.pending_returns')->with('error', 'Return transfer has not been dispatched yet.');
        }
        $stock_transfer->load('stockTransferItems', 'parentTransfer');
        return view('stock_transfer.return_receive', compact('stock_transfer'));
    }

    public function returnReceiveStore(StockTransfer $stock_transfer, Request $request)
    {
        if (!auth()->user()->hasPermissionTo('stock_transfer_receive')) {
            abort(403, 'Unauthorized');
        }
        if ($stock_transfer->is_return == 0) {
            return redirect()->route('stock-transfers.pending_returns')->with('error', 'Normal transfers must be received via the standard Receive interface.');
        }
        if ($stock_transfer->status < 2) {
            return redirect()->route('stock-transfers.pending_returns')->with('error', 'Return transfer has not been dispatched yet.');
        }

        DB::beginTransaction();
        try {
            if (!$request->stock_transfer_item) {
                throw new \Exception('No items selected to receive.');
            }

            $toWarehouse   = Warehouse::find($stock_transfer->to_warehouse_id);   // Original Sender's main WH (receives back)
            $fromWarehouse = Warehouse::find($stock_transfer->from_warehouse_id); // Original receiver (sending return)
            $toScId   = $toWarehouse->sister_concern_id;
            $fromScId = $fromWarehouse->sister_concern_id;

            // If Shortage Return (1), stock is in Original Sender's (toScId) Transit.
            // If Manual Return (2), stock is in Returning Branch's (fromScId) Transit.
            $transitScId = ($stock_transfer->is_return == 2) ? $fromScId : $toScId;
            $transitWarehouseToClear = Warehouse::where('sister_concern_id', $transitScId)
                ->where('type', 'transit')->first();

            $transferTotal = 0;
            $costTotal = 0;
            $markupTotal = 0;

            foreach ($request->stock_transfer_item as $reqItemId) {
                $item = StockTransferItem::find($reqItemId);
                if (!$item || $item->status !== 'pending') continue;

                // Find original batch number from Chittagong's item inventory
                $chittagongInventory = Inventory::find($item->inventory_id);
                $batchNo = $chittagongInventory->batch_no ?? null;

                // Find or create Dhaka's MAIN warehouse Inventory record
                $originalInventory = Inventory::where('sister_concern_id', $toScId)
                    ->where('warehouse_id', $stock_transfer->to_warehouse_id) // Dhaka's main warehouse
                    ->where('product_id', $item->product_id)
                    ->where('batch_no', $batchNo)
                    ->first();

                if ($originalInventory) {
                    $originalInventory->increment('quantity', $item->quantity);
                } else {
                    // Create new inventory record in Dhaka if not exists
                    $originalInventory = new Inventory();
                    $originalInventory->sister_concern_id = $toScId;
                    $originalInventory->inventory_type = 1;
                    $originalInventory->warehouse_id = $stock_transfer->to_warehouse_id;
                    $originalInventory->product_id = $item->product_id;
                    $originalInventory->product_code = $item->product_code;
                    $originalInventory->quantity = $item->quantity;
                    $originalInventory->batch_no = $batchNo;
                    $originalInventory->unit_price = $item->unit_price;
                    $originalInventory->save();
                }

                // Remove from the correct Transit Warehouse
                if ($transitWarehouseToClear) {
                    $transitInv = Inventory::where('sister_concern_id', $transitScId)
                        ->where('warehouse_id', $transitWarehouseToClear->id)
                        ->where('product_id', $item->product_id)
                        ->where('batch_no', $batchNo)
                        ->first();
                    if ($transitInv) {
                        $transitInv->decrement('quantity', $item->quantity);
                    }
                }
                
                $transferPrice = $item->transfer_price ?? $item->unit_price;
                $unitPrice = $item->unit_price;
                
                $transferTotal += ($item->quantity * $transferPrice);
                $costTotal += ($item->quantity * $unitPrice);
                $markupTotal += ($item->quantity * ($transferPrice - $unitPrice));

                // Inventory Log: Return received
                $log = new InventoryLog();
                $log->sister_concern_id = $toScId;
                $log->type = InventoryLogType::$STOCK_TRANSFER_RETURN_IN;
                $log->warehouse_id = $toWarehouse->id;
                $log->inventory_id = $originalInventory->id;
                $log->stock_transfer_id = $stock_transfer->id;
                $log->stock_transfer_item_id = $item->id;
                $log->product_id = $item->product_id;
                $log->quantity = $item->quantity;
                $log->unit_price = $item->unit_price;
                $log->product_code = $item->product_code;
                $log->batch_no = $batchNo;
                $log->notes = 'Return received from: ' . ($fromWarehouse->name ?? '') . ' | Ref: ' . ($stock_transfer->parentTransfer->order_no ?? $stock_transfer->order_no);
                $log->user_id = auth()->id();
                $log->date = Carbon::now();
                $log->save();

                $item->status = 'received';
                $item->received_quantity = $item->quantity;
                $item->alter_user_id = auth()->id();
                $item->save();
            }

            // Update return transfer status
            $allReceived = StockTransferItem::where('stock_transfer_id', $stock_transfer->id)
                ->where('status', 'pending')->count() === 0;
            if ($allReceived) {
                $stock_transfer->status = 3; // Fully Received
                $stock_transfer->received_user_id = auth()->id();
                $stock_transfer->save();
            }

            // DUAL-SIDED REVERSE ACCOUNTING: Only if transferred between different Sister Concerns
            if ($fromScId != $toScId && $transferTotal > 0) {
                $fromSc = SisterConcern::find($fromScId);
                $toSc = SisterConcern::find($toScId);

                // ==========================================
                // 1. SENDER SIDE (Original Sender, Now Receiver)
                // ==========================================
                $senderVoucherNo = generateVoucherNo(VoucherType::$JOURNAL_VOUCHER, null, $toScId);
                $senderVoucherNoGroupSl = abs((int)filter_var($senderVoucherNo, FILTER_SANITIZE_NUMBER_INT));

                // Find Heads
                $toScCode = $toSc->code ?? sprintf('%03d', $toScId);
                $expectedSenderHeadName = 'Inter-company Receivable - ' . ($fromSc->code ?? sprintf('%03d', $fromScId)) . ' (' . ($fromWarehouse->name ?? '') . ')';
                
                // Let's use the standard search logic for heads
                $senderReceivableHead = AccountHead::where('reserved_id', ReservedAccountHead::INTER_COMPANY_RECEIVABLE)
                    ->where('sister_concern_id', null)
                    ->where('name', 'like', 'Inter-company Receivable - ' . ($fromSc->code ?? sprintf('%03d', $fromScId)) . '%')
                    ->first();
                
                $senderInventoryHead = AccountHead::where('reserved_id', ReservedAccountHead::FINISHED_GOODS_INVENTORY)->first();
                $transferRevenueHead = AccountHead::where('reserved_id', ReservedAccountHead::INTERNAL_TRANSFER_REVENUE)->first();

                if ($senderReceivableHead && $senderInventoryHead) {
                    $senderVoucher = new Voucher();
                    $senderVoucher->sister_concern_id = $toScId;
                    $senderVoucher->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                    $senderVoucher->amount = $transferTotal;
                    $senderVoucher->date = Carbon::parse($stock_transfer->date)->format('Y-m-d');
                    $senderVoucher->voucher_no = $senderVoucherNo;
                    $senderVoucher->voucher_no_group_sl = $senderVoucherNoGroupSl;
                    $senderVoucher->account_head_payee_depositor_id = $senderReceivableHead->id;
                    $senderVoucher->notes = 'Stock Return Received from ' . ($fromSc->name ?? '') . ': Return Order No. ' . $stock_transfer->order_no;
                    $senderVoucher->stock_transfer_id = $stock_transfer->id;
                    $senderVoucher->user_id = auth()->id();
                    $senderVoucher->save();

                    // Credit: Inter-company Receivable (Decrease Receivable)
                    $tx1 = new Transaction();
                    $tx1->sister_concern_id = $toScId;
                    $tx1->account_head_id = $senderReceivableHead->id;
                    $tx1->voucher_id = $senderVoucher->id;
                    $tx1->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                    $tx1->transaction_type = TransactionType::$CREDIT;
                    $tx1->amount = $transferTotal;
                    $tx1->date = Carbon::parse($stock_transfer->date)->format('Y-m-d');
                    $tx1->voucher_no = $senderVoucherNo;
                    $tx1->voucher_no_group_sl = $senderVoucherNoGroupSl;
                    $tx1->stock_transfer_id = $stock_transfer->id;
                    $tx1->notes = $senderVoucher->notes;
                    $tx1->user_id = auth()->id();
                    $tx1->save();

                    // Debit: Inventory (Increase Inventory at COST)
                    $tx2 = new Transaction();
                    $tx2->sister_concern_id = $toScId;
                    $tx2->account_head_id = $senderInventoryHead->id;
                    $tx2->voucher_id = $senderVoucher->id;
                    $tx2->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                    $tx2->transaction_type = TransactionType::$DEBIT;
                    $tx2->amount = $costTotal;
                    $tx2->date = Carbon::parse($stock_transfer->date)->format('Y-m-d');
                    $tx2->voucher_no = $senderVoucherNo;
                    $tx2->voucher_no_group_sl = $senderVoucherNoGroupSl;
                    $tx2->stock_transfer_id = $stock_transfer->id;
                    $tx2->notes = $senderVoucher->notes;
                    $tx2->user_id = auth()->id();
                    $tx2->save();

                    // Debit: Transfer Revenue (Decrease Markup)
                    if ($markupTotal > 0 && $transferRevenueHead) {
                        $tx3 = new Transaction();
                        $tx3->sister_concern_id = $toScId;
                        $tx3->account_head_id = $transferRevenueHead->id;
                        $tx3->voucher_id = $senderVoucher->id;
                        $tx3->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                        $tx3->transaction_type = TransactionType::$DEBIT;
                        $tx3->amount = $markupTotal;
                        $tx3->date = Carbon::parse($stock_transfer->date)->format('Y-m-d');
                        $tx3->voucher_no = $senderVoucherNo;
                        $tx3->voucher_no_group_sl = $senderVoucherNoGroupSl;
                        $tx3->stock_transfer_id = $stock_transfer->id;
                        $tx3->notes = 'Reversing markup profit for return: ' . $stock_transfer->order_no;
                        $tx3->user_id = auth()->id();
                        $tx3->save();
                    }
                }

                // ==========================================
                // 2. RECEIVER SIDE (Original Receiver, Now returning)
                // ==========================================
                $receiverVoucherNo = generateVoucherNo(VoucherType::$JOURNAL_VOUCHER, null, $fromScId);
                $receiverVoucherNoGroupSl = abs((int)filter_var($receiverVoucherNo, FILTER_SANITIZE_NUMBER_INT));

                $receiverPayableHead = AccountHead::where('reserved_id', ReservedAccountHead::INTER_COMPANY_PAYABLE)
                    ->where('sister_concern_id', null)
                    ->where('name', 'like', 'Inter-company Payable - ' . ($toSc->code ?? sprintf('%03d', $toScId)) . '%')
                    ->first();
                $receiverInventoryHead = AccountHead::where('reserved_id', ReservedAccountHead::FINISHED_GOODS_INVENTORY)->first();

                if ($receiverPayableHead && $receiverInventoryHead) {
                    $receiverVoucher = new Voucher();
                    $receiverVoucher->sister_concern_id = $fromScId;
                    $receiverVoucher->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                    $receiverVoucher->amount = $transferTotal;
                    $receiverVoucher->date = Carbon::parse($stock_transfer->date)->format('Y-m-d');
                    $receiverVoucher->voucher_no = $receiverVoucherNo;
                    $receiverVoucher->voucher_no_group_sl = $receiverVoucherNoGroupSl;
                    $receiverVoucher->account_head_payee_depositor_id = $receiverPayableHead->id;
                    $receiverVoucher->notes = 'Stock Return Sent to ' . ($toSc->name ?? '') . ': Return Order No. ' . $stock_transfer->order_no;
                    $receiverVoucher->stock_transfer_id = $stock_transfer->id;
                    $receiverVoucher->user_id = auth()->id();
                    $receiverVoucher->save();

                    // Debit: Inter-company Payable (Decrease Payable)
                    $tx4 = new Transaction();
                    $tx4->sister_concern_id = $fromScId;
                    $tx4->account_head_id = $receiverPayableHead->id;
                    $tx4->voucher_id = $receiverVoucher->id;
                    $tx4->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                    $tx4->transaction_type = TransactionType::$DEBIT;
                    $tx4->amount = $transferTotal;
                    $tx4->date = Carbon::parse($stock_transfer->date)->format('Y-m-d');
                    $tx4->voucher_no = $receiverVoucherNo;
                    $tx4->voucher_no_group_sl = $receiverVoucherNoGroupSl;
                    $tx4->stock_transfer_id = $stock_transfer->id;
                    $tx4->notes = $receiverVoucher->notes;
                    $tx4->user_id = auth()->id();
                    $tx4->save();

                    // Credit: Inventory (Decrease Inventory at TRANSFER PRICE)
                    $tx5 = new Transaction();
                    $tx5->sister_concern_id = $fromScId;
                    $tx5->account_head_id = $receiverInventoryHead->id;
                    $tx5->voucher_id = $receiverVoucher->id;
                    $tx5->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                    $tx5->transaction_type = TransactionType::$CREDIT;
                    $tx5->amount = $transferTotal;
                    $tx5->date = Carbon::parse($stock_transfer->date)->format('Y-m-d');
                    $tx5->voucher_no = $receiverVoucherNo;
                    $tx5->voucher_no_group_sl = $receiverVoucherNoGroupSl;
                    $tx5->stock_transfer_id = $stock_transfer->id;
                    $tx5->notes = $receiverVoucher->notes;
                    $tx5->user_id = auth()->id();
                    $tx5->save();
                }
            }

            DB::commit();
            return redirect()->back()->with('success', 'Return received. Stock added back to main warehouse.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    // ─── CANCEL / REJECT TRANSFER ──────────────────────────────────────────

    public function rejectTransfer(StockTransfer $stock_transfer)
    {
        if (!auth()->user()->hasPermissionTo('stock_transfer_receive')) {
            abort(403, 'Unauthorized');
        }
        if ($stock_transfer->status != 2) {
            return redirect()->back()->with('error', 'Only dispatched transfers can be rejected.');
        }

        DB::beginTransaction();
        try {
            // Find sender's transit warehouse
            $fromWarehouse = Warehouse::find($stock_transfer->from_warehouse_id);
            $fromScId = $fromWarehouse->sister_concern_id;
            $senderTransitWarehouse = Warehouse::where('sister_concern_id', $fromScId)
                ->where('type', 'transit')->first();

            // Return items from transit back to sender's main warehouse
            foreach ($stock_transfer->stockTransferItems as $item) {
                if ($senderTransitWarehouse) {
                    $transitInventory = Inventory::where('sister_concern_id', $fromScId)
                        ->where('warehouse_id', $senderTransitWarehouse->id)
                        ->where('product_id', $item->product_id)
                        ->first();
                    if ($transitInventory) {
                        $transitInventory->decrement('quantity', $item->quantity);
                    }
                }

                $mainInventory = Inventory::where('sister_concern_id', $fromScId)
                    ->where('warehouse_id', $fromWarehouse->id)
                    ->where('product_id', $item->product_id)
                    ->first();
                if ($mainInventory) {
                    $mainInventory->increment('quantity', $item->quantity);
                }

                $item->status = 'rejected';
                $item->save();
            }

            $stock_transfer->status = 5; // Rejected status
            $stock_transfer->save();

            DB::commit();
            
            if ($stock_transfer->is_return == 1) {
                return redirect()->route('stock-transfers.pending_returns')->with('success', 'Return transfer has been rejected and stock returned to sender.');
            }
            return redirect()->route('stock-transfers.receive_list')->with('success', 'Transfer has been rejected and stock returned to sender.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────────────────

    public function manualReturnCreate(StockTransfer $stock_transfer)
    {
        if (!auth()->user()->hasPermissionTo('stock_transfer')) {
            abort(403, 'Unauthorized');
        }
        if ($stock_transfer->is_return == 1 || $stock_transfer->status != 4) {
            return redirect()->back()->with('error', 'Only fully received standard transfers can be returned.');
        }
        $stock_transfer->load('stockTransferItems.product');
        $stockTransfer = $stock_transfer;
        return view('stock_transfer.manual_return', compact('stockTransfer'));
    }

    public function manualReturnStore(StockTransfer $stock_transfer, Request $request)
    {
        if (!auth()->user()->hasPermissionTo('stock_transfer')) {
            abort(403, 'Unauthorized');
        }
        
        DB::beginTransaction();
        try {
            $hasReturns = false;
            $returnItemsData = [];
            
            $toScId = $stock_transfer->from_sister_concern_id; // Original sender receives back
            $fromScId = $stock_transfer->to_sister_concern_id; // Current user sending
            
            foreach ($request->return_quantity as $itemId => $qty) {
                $qty = (float)$qty;
                if ($qty <= 0) continue;
                
                $item = StockTransferItem::find($itemId);
                if (!$item || $item->stock_transfer_id != $stock_transfer->id) continue;
                
                $maxReturnable = $item->received_quantity - $item->return_quantity;
                if ($qty > $maxReturnable) {
                    throw new \Exception("Cannot return more than maximum returnable quantity for " . ($item->product->name ?? 'item'));
                }
                
                $hasReturns = true;
                
                // Track returned amount on the original item
                $item->return_quantity += $qty;
                $item->save();
                
                $returnItemsData[] = [
                    'original_item' => $item,
                    'return_qty' => $qty,
                ];
            }
            
            if (!$hasReturns) {
                throw new \Exception("No valid return quantities provided.");
            }
            
            $retPrefix = 'RET-';
            $retMax = DB::table('stock_transfers')
                ->where('from_sister_concern_id', $fromScId)
                ->where('order_no', 'LIKE', $retPrefix . '%')
                ->selectRaw("MAX(CAST(SUBSTRING(order_no, LENGTH('$retPrefix') + 1) AS UNSIGNED)) as max_number")
                ->value('max_number');
            $retNext = $retMax ? $retMax + 1 : 1;

            $returnTransfer = new StockTransfer();
            $returnTransfer->from_sister_concern_id = $fromScId;
            $returnTransfer->to_sister_concern_id = $toScId;
            $returnTransfer->from_warehouse_id = $stock_transfer->to_warehouse_id;
            $returnTransfer->to_warehouse_id = $stock_transfer->from_warehouse_id;
            $returnTransfer->order_no = $retPrefix . str_pad($retNext, 4, '0', STR_PAD_LEFT);
            $returnTransfer->challan_no = 'CHN-RET-' . str_pad($retNext, 4, '0', STR_PAD_LEFT);
            $returnTransfer->date = Carbon::now();
            $returnTransfer->notes = 'Manual return from transfer: ' . $stock_transfer->order_no;
            $returnTransfer->status = 1; // Pending Dispatch
            $returnTransfer->is_return = 2; // 2 indicates Manual Return
            $returnTransfer->return_reason = $request->return_reason;
            $returnTransfer->parent_transfer_id = $stock_transfer->id;
            $returnTransfer->create_user_id = auth()->id();
            $returnTransfer->save();

            foreach ($returnItemsData as $rd) {
                $origItem = $rd['original_item'];
                
                $senderInventory = Inventory::where('sister_concern_id', $fromScId)
                    ->where('warehouse_id', $stock_transfer->to_warehouse_id)
                    ->where('product_id', $origItem->product_id)
                    ->first();

                if (!$senderInventory || $senderInventory->quantity < $rd['return_qty']) {
                    throw new \Exception("Insufficient stock in warehouse to return " . ($origItem->product->name ?? 'item'));
                }
                
                $returnItem = new StockTransferItem();
                $returnItem->sister_concern_id = $fromScId;
                $returnItem->stock_transfer_id = $returnTransfer->id;
                $returnItem->inventory_id = $senderInventory->id;
                $returnItem->product_id = $origItem->product_id;
                $returnItem->product_code = $origItem->product_code;
                $returnItem->quantity = $rd['return_qty'];
                $returnItem->received_quantity = 0;
                $returnItem->return_quantity = 0;
                $returnItem->unit_price = $origItem->unit_price;
                $returnItem->transfer_price = $origItem->transfer_price;
                $returnItem->status = 'pending';
                $returnItem->create_user_id = auth()->id();
                $returnItem->save();
            }

            DB::commit();
            return redirect()->route('stock-transfers.pending_dispatch_returns')->with('success', 'Manual Return transfer created successfully. Please dispatch it.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────────────────

    public function receive(StockTransfer $stockTransfer)
    {
        if (!auth()->user()->hasPermissionTo('stock_transfer_receive')) {
            abort(403, 'Unauthorized');
        }
        if ($stockTransfer->is_return == 1) {
            return redirect()->route('stock-transfers.receive_list')->with('error', 'Return transfers must be received via the Return Receive interface.');
        }
        if ($stockTransfer->status < 2) {
            return redirect()->route('stock-transfers.receive_list')->with('error', 'Stock transfer has not been dispatched yet.');
        }
        $stockTransfer->load('stockTransferItems');
        return view('stock_transfer.receive', compact('stockTransfer'));
    }


    public function receiveStore(StockTransfer $stock_transfer,Request $request)
    {
        if (!auth()->user()->hasPermissionTo('stock_transfer_receive')) {
            abort(403, 'Unauthorized');
        }
        if ($stock_transfer->is_return == 1) {
            return response()->json(['status' => false, 'message' => 'Return transfers must be received via the Return Receive interface.']);
        }
        if ($stock_transfer->status < 2) {
            return response()->json(['status' => false, 'message' => 'Stock transfer has not been dispatched yet.']);
        }

        DB::beginTransaction();
        try {
            if (!$request->stock_transfer_item){
                throw new \Exception('No pending items found to receive or no items selected.');
            }

            // Load warehouses to get their sister_concern_id
            $fromWarehouse = Warehouse::find($stock_transfer->from_warehouse_id);
            $toWarehouse = Warehouse::find($stock_transfer->to_warehouse_id);

            if (!$fromWarehouse) {
                throw new \Exception('Sender warehouse not found. Please check the transfer record.');
            }
            if (!$toWarehouse) {
                throw new \Exception('Receiver warehouse not found. Please check the transfer record.');
            }

            $fromScId = $fromWarehouse->sister_concern_id;
            $toScId = $toWarehouse->sister_concern_id;

            // Find sender's transit warehouse
            $senderTransitWarehouse = Warehouse::where('sister_concern_id', $fromScId)
                ->where('type', 'transit')->first();

            $hasReturnItems = false;
            $returnItemsData = []; // collect items that need a return transfer

            foreach ($request->stock_transfer_item as $reqItemId) {
                $stockTransferItem = StockTransferItem::where('id', $reqItemId)->first();

                if ($stockTransferItem->status != 'pending') {
                    throw new \Exception('Already received: ' . ($stockTransferItem->product->name ?? ''));
                }

                $receivedQty = $stockTransferItem->quantity;

                // Update item: save received qty and status
                $stockTransferItem->received_quantity = $receivedQty;
                $stockTransferItem->return_quantity = 0; // Handled manually later
                $stockTransferItem->status = 'received';
                $stockTransferItem->alter_user_id = auth()->id();
                $stockTransferItem->save();

                $product = $stockTransferItem->product;

                // --- SENDER TRANSIT WAREHOUSE: decrement transit stock ---
                $transitInventory = $senderTransitWarehouse
                    ? Inventory::where('sister_concern_id', $fromScId)
                        ->where('warehouse_id', $senderTransitWarehouse->id)
                        ->where('product_id', $stockTransferItem->product_id)
                        ->first()
                    : null;
                if ($transitInventory) {
                    $transitInventory->decrement('quantity', $receivedQty);
                }

                // --- RECEIVER MAIN WAREHOUSE: increment received_qty ---
                if ($receivedQty > 0) {
                    $fromInventory = Inventory::find($stockTransferItem->inventory_id);
                    $inventory = Inventory::where('sister_concern_id', $toScId)
                        ->where('warehouse_id', $stock_transfer->to_warehouse_id)
                        ->where('product_id', $stockTransferItem->product_id)
                        ->where('batch_no', $fromInventory->batch_no ?? null)
                        ->first();

                    if (!$inventory) {
                        $inventory = new Inventory();
                        $inventory->sister_concern_id = $toScId;
                        $inventory->inventory_type = 1;
                        $inventory->warehouse_id = $stock_transfer->to_warehouse_id;
                        $inventory->product_id = $product->id;
                        $inventory->product_code = $stockTransferItem->product_code;
                        $inventory->quantity = $receivedQty;
                        $inventory->batch_no = $fromInventory->batch_no ?? null;
                        $inventory->unit_price = $stockTransferItem->transfer_price ?? $stockTransferItem->unit_price;
                        $inventory->save();
                    } else {
                        $inventory->unit_price = $stockTransferItem->transfer_price ?? $stockTransferItem->unit_price;
                        $inventory->save();
                        $inventory->increment('quantity', $receivedQty);
                    }

                    // Inventory Log: Transfer In to Receiver
                    $inventoryLog = new InventoryLog();
                    $inventoryLog->sister_concern_id = $toScId;
                    $inventoryLog->type = InventoryLogType::$STOCK_TRANSFER_IN;
                    $inventoryLog->warehouse_id = $stock_transfer->to_warehouse_id;
                    $inventoryLog->inventory_id = $inventory->id;
                    $inventoryLog->stock_transfer_id = $stock_transfer->id;
                    $inventoryLog->stock_transfer_item_id = $stockTransferItem->id;
                    $inventoryLog->product_id = $product->id;
                    $inventoryLog->quantity = $receivedQty;
                    $inventoryLog->unit_price = $stockTransferItem->unit_price;
                    $inventoryLog->product_code = $stockTransferItem->product_code;
                    $inventoryLog->batch_no = $inventory->batch_no;
                    $inventoryLog->notes = 'Stock Transfer In from ' . ($fromWarehouse->name ?? '');
                    $inventoryLog->user_id = auth()->id();
                    $inventoryLog->date = Carbon::now();
                    $inventoryLog->save();
                }
            }

            // Mark as Fully Received
            $stock_transfer->status = 4;
            $stock_transfer->received_user_id = auth()->id();
            $stock_transfer->save();


            if ($request->notes == '') {
                $request['notes'] = 'Stock Transfer Received: Order No. ' . $stock_transfer->order_no;
            }

            // Calculate total ONLY based on actually received_quantity (not full quantity)
            $transferTotal = 0;
            foreach ($request->stock_transfer_item as $reqItemId) {
                $item = StockTransferItem::find($reqItemId);
                if ($item) {
                    $itemPrice = $item->transfer_price ?? $item->unit_price;
                    $transferTotal += $itemPrice * $item->received_quantity;
                }
            }

            // Calculate cost total (at unit_price) for inventory credit entry
            $costTotal = 0;
            foreach ($request->stock_transfer_item as $reqItemId) {
                $ci = StockTransferItem::find($reqItemId);
                if ($ci) {
                    $costTotal += $ci->unit_price * $ci->received_quantity;
                }
            }
            $markupTotal = $transferTotal - $costTotal;

            // DUAL-SIDED ACCOUNTING: Only if transferred between different Sister Concerns
            if ($fromScId != $toScId) {
                $fromSc = SisterConcern::find($fromScId);
                $toSc = SisterConcern::find($toScId);

                // --- 1. SENDER SIDE (From SC) ACCOUNTING ---
                $senderVoucherNo = generateVoucherNo(VoucherType::$JOURNAL_VOUCHER, null, $fromScId);
                $senderVoucherNoGroupSl = abs((int)filter_var($senderVoucherNo, FILTER_SANITIZE_NUMBER_INT));

                // Find the Accounts Receivable parent group for the Sender
                $senderReceivableGroup = AccountGroup::query()
                    ->where('reserved_id', ReservedAccountGroup::ACCOUNTS_RECEIVABLE)
                    ->first();

                if (!$senderReceivableGroup) {
                    throw new \Exception('Accounts Receivable group not found for sister concern: ' . ($fromSc->name ?? $fromScId) . '. Please run the AccountGroupSeeder.');
                }

                // Generate a stable, unique company code based on the target sister concern's dynamic code (falling back to padded ID)
                $toScCode = $toSc->code ?? sprintf('%03d', $toScId);
                $expectedSenderHeadName = 'Inter-company Receivable - ' . $toScCode . ' (' . $toSc->name . ')';

                // Look for an existing head for this target company using its stable code prefix
                $senderHead = AccountHead::where('reserved_id', ReservedAccountHead::INTER_COMPANY_RECEIVABLE)
                    ->where('name', 'like', 'Inter-company Receivable - ' . $toScCode . '%')
                    ->first();

                if ($senderHead) {
                    if ($senderHead->name !== $expectedSenderHeadName) {
                        $senderHead->name = $expectedSenderHeadName;
                        $senderHead->save();
                    }
                } else {
                    $senderHead = new AccountHead();
                    $senderHead->sister_concern_id = null;
                    $senderHead->reserved_id = ReservedAccountHead::INTER_COMPANY_RECEIVABLE;
                    $senderHead->account_group_id = $senderReceivableGroup->id;
                    $senderHead->name = $expectedSenderHeadName;
                    $senderHead->code = generateAccountCode($senderReceivableGroup->top_reserved_id);
                    $senderHead->nature = 'Debit';
                    $senderHead->is_system = 1;
                    $senderHead->is_control_account = 0;
                    $senderHead->is_editable = 0;
                    $senderHead->save();
                }

                // Credit: Inventory (Sender's account) â€” null check
                $senderInventoryHead = AccountHead::where('reserved_id', ReservedAccountHead::FINISHED_GOODS_INVENTORY)
                    ->first();

                if (!$senderInventoryHead) {
                    throw new \Exception('Finished Goods Inventory account head not found for sender: ' . ($fromSc->name ?? $fromScId) . '.');
                }

                // Create a NEW Voucher for each receive action (avoid overwriting amount on partial receives)
                $senderVoucher = new Voucher();
                $senderVoucher->sister_concern_id = $fromScId;
                $senderVoucher->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                $senderVoucher->amount = $transferTotal;
                $senderVoucher->date = Carbon::parse($stock_transfer->date)->format('Y-m-d');
                $senderVoucher->voucher_no = $senderVoucherNo;
                $senderVoucher->voucher_no_group_sl = $senderVoucherNoGroupSl;
                $senderVoucher->account_head_payee_depositor_id = $senderHead->id; // Set Party Name to the Receiver Branch's Inter-company account
                $senderVoucher->notes = 'Stock Transfer Sent to ' . $toSc->name . ': Order No. ' . $stock_transfer->order_no;
                $senderVoucher->stock_transfer_id = $stock_transfer->id;
                $senderVoucher->user_id = auth()->id();
                $senderVoucher->save();

                // Debit: Inter-company Receivable (Sender's account)
                $senderDebitTx = new Transaction();
                $senderDebitTx->sister_concern_id = $fromScId;
                $senderDebitTx->account_head_id = $senderHead->id;
                $senderDebitTx->voucher_id = $senderVoucher->id;
                $senderDebitTx->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                $senderDebitTx->transaction_type = TransactionType::$DEBIT;
                $senderDebitTx->amount = $transferTotal;
                $senderDebitTx->date = Carbon::parse($stock_transfer->date)->format('Y-m-d');
                $senderDebitTx->voucher_no = $senderVoucherNo;
                $senderDebitTx->voucher_no_group_sl = $senderVoucherNoGroupSl;
                $senderDebitTx->stock_transfer_id = $stock_transfer->id;
                $senderDebitTx->notes = $senderVoucher->notes;
                $senderDebitTx->user_id = auth()->id();
                $senderDebitTx->save();

                // Credit: Inventory (Sender's account) at COST price
                $senderCreditTx = new Transaction();
                $senderCreditTx->sister_concern_id = $fromScId;
                $senderCreditTx->account_head_id = $senderInventoryHead->id;
                $senderCreditTx->voucher_id = $senderVoucher->id;
                $senderCreditTx->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                $senderCreditTx->transaction_type = TransactionType::$CREDIT;
                $senderCreditTx->amount = $costTotal; // Inventory goes out at COST
                $senderCreditTx->date = Carbon::parse($stock_transfer->date)->format('Y-m-d');
                $senderCreditTx->voucher_no = $senderVoucherNo;
                $senderCreditTx->voucher_no_group_sl = $senderVoucherNoGroupSl;
                $senderCreditTx->stock_transfer_id = $stock_transfer->id;
                $senderCreditTx->notes = $senderVoucher->notes;
                $senderCreditTx->user_id = auth()->id();
                $senderCreditTx->save();

                // Credit: Internal Transfer Revenue (markup profit) â€” only if markup exists
                if ($markupTotal > 0) {
                    $otherIncomeGroup = AccountGroup::where('reserved_id', ReservedAccountGroup::OTHER_INCOME)
                        ->first();
                    if (!$otherIncomeGroup) {
                        throw new \Exception('Other Income account group not found for: ' . ($fromSc->name ?? $fromScId));
                    }
                    $transferRevenueHead = AccountHead::where('reserved_id', ReservedAccountHead::INTERNAL_TRANSFER_REVENUE)
                        ->first();
                    if (!$transferRevenueHead) {
                        $transferRevenueHead = new AccountHead();
                        $transferRevenueHead->sister_concern_id = null;
                        $transferRevenueHead->reserved_id = ReservedAccountHead::INTERNAL_TRANSFER_REVENUE;
                        $transferRevenueHead->account_group_id = $otherIncomeGroup->id;
                        $transferRevenueHead->name = 'Internal Transfer Revenue';
                        $transferRevenueHead->code = generateAccountCode($otherIncomeGroup->top_reserved_id);
                        $transferRevenueHead->nature = 'Credit';
                        $transferRevenueHead->is_system = 1;
                        $transferRevenueHead->is_control_account = 0;
                        $transferRevenueHead->is_editable = 0;
                        $transferRevenueHead->save();
                    }
                    $revenueTx = new Transaction();
                    $revenueTx->sister_concern_id = $fromScId;
                    $revenueTx->account_head_id = $transferRevenueHead->id;
                    $revenueTx->voucher_id = $senderVoucher->id;
                    $revenueTx->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                    $revenueTx->transaction_type = TransactionType::$CREDIT;
                    $revenueTx->amount = $markupTotal;
                    $revenueTx->date = Carbon::parse($stock_transfer->date)->format('Y-m-d');
                    $revenueTx->voucher_no = $senderVoucherNo;
                    $revenueTx->voucher_no_group_sl = $senderVoucherNoGroupSl;
                    $revenueTx->stock_transfer_id = $stock_transfer->id;
                    $revenueTx->notes = 'Transfer markup profit to ' . $toSc->name . ': Order No. ' . $stock_transfer->order_no;
                    $revenueTx->user_id = auth()->id();
                    $revenueTx->save();
                }

                // --- 2. RECEIVER SIDE (To SC) ACCOUNTING ---
                $receiverVoucherNo = generateVoucherNo(VoucherType::$JOURNAL_VOUCHER, null, $toScId);
                $receiverVoucherNoGroupSl = abs((int)filter_var($receiverVoucherNo, FILTER_SANITIZE_NUMBER_INT));

                // Find the Accounts Payable parent group for the Receiver
                $receiverPayableGroup = AccountGroup::where('reserved_id', ReservedAccountGroup::ACCOUNTS_PAYABLE)
                    ->first();

                if (!$receiverPayableGroup) {
                    throw new \Exception('Accounts Payable group not found for sister concern: ' . ($toSc->name ?? $toScId) . '. Please run the AccountGroupSeeder.');
                }

                // Generate a stable, unique company code based on the source sister concern's dynamic code (falling back to padded ID)
                $fromScCode = $fromSc->code ?? sprintf('%03d', $fromScId);
                $expectedReceiverHeadName = 'Inter-company Payable - ' . $fromScCode . ' (' . $fromSc->name . ')';

                // Look for an existing head for this source company using its stable code prefix
                $receiverHead = AccountHead::where('reserved_id', ReservedAccountHead::INTER_COMPANY_PAYABLE)
                    ->where('name', 'like', 'Inter-company Payable - ' . $fromScCode . '%')
                    ->first();

                if ($receiverHead) {
                    if ($receiverHead->name !== $expectedReceiverHeadName) {
                        $receiverHead->name = $expectedReceiverHeadName;
                        $receiverHead->save();
                    }
                } else {
                    $receiverHead = new AccountHead();
                    $receiverHead->sister_concern_id = null;
                    $receiverHead->reserved_id = ReservedAccountHead::INTER_COMPANY_PAYABLE;
                    $receiverHead->account_group_id = $receiverPayableGroup->id;
                    $receiverHead->name = $expectedReceiverHeadName;
                    $receiverHead->code = generateAccountCode($receiverPayableGroup->top_reserved_id);
                    $receiverHead->nature = 'Credit';
                    $receiverHead->is_system = 1;
                    $receiverHead->is_control_account = 0;
                    $receiverHead->is_editable = 0;
                    $receiverHead->save();
                }

                // Debit: Inventory (Receiver's account) â€” null check
                $receiverInventoryHead = AccountHead::where('reserved_id', ReservedAccountHead::FINISHED_GOODS_INVENTORY)
                    ->first();

                if (!$receiverInventoryHead) {
                    throw new \Exception('Finished Goods Inventory account head not found for receiver: ' . ($toSc->name ?? $toScId) . '.');
                }

                // Create a NEW Voucher for each receive action (avoid overwriting amount on partial receives)
                $receiverVoucher = new Voucher();
                $receiverVoucher->sister_concern_id = $toScId;
                $receiverVoucher->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                $receiverVoucher->amount = $transferTotal;
                $receiverVoucher->date = Carbon::parse($stock_transfer->date)->format('Y-m-d');
                $receiverVoucher->voucher_no = $receiverVoucherNo;
                $receiverVoucher->voucher_no_group_sl = $receiverVoucherNoGroupSl;
                $receiverVoucher->account_head_payee_depositor_id = $receiverHead->id; // Set Party Name to the Sender Branch's Inter-company account
                $receiverVoucher->notes = 'Stock Transfer Received from ' . $fromSc->name . ': Order No. ' . $stock_transfer->order_no;
                $receiverVoucher->stock_transfer_id = $stock_transfer->id;
                $receiverVoucher->user_id = auth()->id();
                $receiverVoucher->save();

                // Debit: Inventory (Receiver's account)
                $receiverDebitTx = new Transaction();
                $receiverDebitTx->sister_concern_id = $toScId;
                $receiverDebitTx->account_head_id = $receiverInventoryHead->id;
                $receiverDebitTx->voucher_id = $receiverVoucher->id;
                $receiverDebitTx->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                $receiverDebitTx->transaction_type = TransactionType::$DEBIT;
                $receiverDebitTx->amount = $transferTotal;
                $receiverDebitTx->date = Carbon::parse($stock_transfer->date)->format('Y-m-d');
                $receiverDebitTx->voucher_no = $receiverVoucherNo;
                $receiverDebitTx->voucher_no_group_sl = $receiverVoucherNoGroupSl;
                $receiverDebitTx->stock_transfer_id = $stock_transfer->id;
                $receiverDebitTx->notes = $receiverVoucher->notes;
                $receiverDebitTx->user_id = auth()->id();
                $receiverDebitTx->save();

                // Credit: Inter-company Payable (Receiver's account)
                $receiverCreditTx = new Transaction();
                $receiverCreditTx->sister_concern_id = $toScId;
                $receiverCreditTx->account_head_id = $receiverHead->id;
                $receiverCreditTx->voucher_id = $receiverVoucher->id;
                $receiverCreditTx->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                $receiverCreditTx->transaction_type = TransactionType::$CREDIT;
                $receiverCreditTx->amount = $transferTotal;
                $receiverCreditTx->date = Carbon::parse($stock_transfer->date)->format('Y-m-d');
                $receiverCreditTx->voucher_no = $receiverVoucherNo;
                $receiverCreditTx->voucher_no_group_sl = $receiverVoucherNoGroupSl;
                $receiverCreditTx->stock_transfer_id = $stock_transfer->id;
                $receiverCreditTx->notes = $receiverVoucher->notes;
                $receiverCreditTx->user_id = auth()->id();
                $receiverCreditTx->save();
            }

            // Save transfer status (already set to 2=Partially Received or 3=Fully Received above)
            $stock_transfer->received_user_id = auth()->id();
            $stock_transfer->save();

            DB::commit();
            return redirect()->back()
                ->with('success','Stock transfer product received.');
        }catch (\Exception $exception){
            DB::rollBack();
            return redirect()->back()
                ->with('error',$exception->getMessage().' '.$exception->getLine());
        }
    }

    public function dataTable()
    {
        $user = auth()->user();
        $query = StockTransfer::with('fromWarehouse','toWarehouse','fromSisterConcern','toSisterConcern','stockTransferItems','vouchers')
            ->where('from_sister_concern_id', $user->activeConcernId())
            ->where('is_return', 0);

        // Branch Manager: only see transfers they sent (from their branch warehouse)
        if ($user->role === 'Branch_Manager' && $user->sister_concern_id) {
            $branchWarehouse = Warehouse::where('sister_concern_id', $user->sister_concern_id)
                ->where('type', 'main')
                ->first();
            if ($branchWarehouse) {
                $query->where('from_warehouse_id', $branchWarehouse->id);
            }
        }

        if (request()->has('start_date') && request('end_date') != '') {
            $query->whereBetween('date', [Carbon::parse(request('start_date'))->format('Y-m-d'), Carbon::parse(request('end_date'))->format('Y-m-d')]);
        }

        return DataTables::eloquent($query)
            ->addColumn('action', function (StockTransfer $stock_transfer) {
                $btn = '';
                
                // Allow edit ONLY if status is Draft/Pending Dispatch (status = 1), and NOT a Return Transfer
                if (auth()->user()->hasPermissionTo('stock_transfer_edit') && $stock_transfer->status == 1 && $stock_transfer->is_return == 0) {
                    $btn .= ' <a href="' . route('stock-transfers.edit', ['stock_transfer' => $stock_transfer->id]) . '" class="btn btn-info bg-gradient-info btn-sm" title="Edit Draft"><i class="fa fa-edit"></i></a>';
                }
                
                if (auth()->user()->hasPermissionTo('stock_transfer')) {
                    $btn .= ' <a href="' . route('stock-transfers.details', ['stock_transfer' => $stock_transfer->id]) . '" class="btn btn-info bg-gradient-info btn-sm" title="View Details"><i class="fa fa-info-circle"></i></a>';
                }
                
                if (auth()->user()->hasPermissionTo('stock_transfer') && $stock_transfer->vouchers) {
                    foreach ($stock_transfer->vouchers as $voucher){
                        if ($voucher->sister_concern_id == auth()->user()->activeConcernId()) {
                            $btn .= ' <a href="' . route('voucher.details', ['voucher' => $voucher->id,'voucher_type'=>$voucher->voucher_type]) . '" class="btn btn-info bg-gradient-info btn-sm"><i class="fa fa-receipt"></i> JV-'.$voucher->voucher_no.'</a>';
                        }
                    }
                }
                
                // Allow delete ONLY if Draft (status = 1) or Dispatched (status = 2), and NOT a Return Transfer
                if(auth()->user()->can('stock_transfer_delete') && $stock_transfer->status <= 2 && $stock_transfer->is_return == 0) {
                    $btn .= ' <a role="button" data-id="' . $stock_transfer->id . '" class="btn btn-danger btn-sm btn-delete" title="Cancel/Delete"><i class="fa fa-trash"></i></a>';
                }
                return dropdownMenuContainer($btn);
            })
            ->addColumn('from_warehouse', function (StockTransfer $stock_transfer) {
                return $stock_transfer->fromSisterConcern->name ?? ($stock_transfer->fromWarehouse->name ?? '');
            })
            ->addColumn('to_warehouse', function (StockTransfer $stock_transfer) {
                return $stock_transfer->toSisterConcern->name ?? ($stock_transfer->toWarehouse->name ?? '');
            })
            ->addColumn('status', function (StockTransfer $stock_transfer) {
                if ($stock_transfer->status == 1) {
                    return '<span class="badge badge-secondary py-1 px-2">Pending Dispatch</span>';
                } elseif ($stock_transfer->status == 2) {
                    return '<span class="badge badge-warning py-1 px-2">Dispatched / In Transit</span>';
                } elseif ($stock_transfer->status == 3) {
                    return '<span class="badge badge-info py-1 px-2">Partially Received</span>';
                } elseif ($stock_transfer->status == 4) {
                    return '<span class="badge badge-success py-1 px-2">Fully Received</span>';
                } elseif ($stock_transfer->status == 5) {
                    return '<span class="badge badge-danger py-1 px-2">Cancelled</span>';
                }
                return '<span class="badge badge-dark py-1 px-2">Unknown</span>';
            })
            ->rawColumns(['action','status'])
            ->toJson();
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('stock_transfer_create')) {
            abort(403, 'Unauthorized');
        }
        $user = auth()->user();
        $products = Product::all();

        // From branch: all active warehouses under the user's active concern
        $fromBranches = Warehouse::with('sisterConcern')->where('sister_concern_id', $user->activeConcernId())
            ->where('type', 'main')
            ->where('status', 1)
            ->get();

        // To branch: all active warehouses across all sister concerns for branch-to-branch transfer
        $fromBranchId = $request->get('from_branch', '');
        $toBranchesQuery = Warehouse::with('sisterConcern')->where('type', 'main')->where('status', 1);

        if ($fromBranchId) {
            $toBranchesQuery->where('id', '!=', $fromBranchId);
        }

        $toBranches = $toBranchesQuery->get();
        return view('stock_transfer.create', compact('toBranches', 'fromBranches', 'products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('stock_transfer_create')) {
            abort(403, 'Unauthorized');
        }

        $rules = [
            'product_id.*' => 'required',
            'inventory_id.*' => 'required',
            'notes' => 'nullable|max:255',
            'from_branch' => 'required',
            'to_branch' => 'required',
            'date' => 'required|date',
        ];
        $request->validate($rules);

        DB::beginTransaction();
        try {
            $fromWarehouse = Warehouse::findOrFail($request->from_branch);
            $toWarehouse = Warehouse::findOrFail($request->to_branch);

            $stockTransfer = new StockTransfer();
            $stockTransfer->from_sister_concern_id = $fromWarehouse->sister_concern_id;
            $stockTransfer->to_sister_concern_id = $toWarehouse->sister_concern_id;
            $stockTransfer->from_warehouse_id = $fromWarehouse->id;
            $stockTransfer->to_warehouse_id = $toWarehouse->id;
            
            $stoPrefix = 'STO-';
            $stoMax = DB::table('stock_transfers')
                ->where('from_sister_concern_id', auth()->user()->activeConcernId())
                ->where('order_no', 'LIKE', $stoPrefix . '%')
                ->selectRaw("MAX(CAST(SUBSTRING(order_no, LENGTH('$stoPrefix') + 1) AS UNSIGNED)) as max_number")
                ->value('max_number');
            $stoNext = $stoMax ? $stoMax + 1 : 1;
            $stockTransfer->order_no = $stoPrefix . str_pad($stoNext, 4, '0', STR_PAD_LEFT);
            $stockTransfer->challan_no = 'CHN-' . str_pad($stoNext, 4, '0', STR_PAD_LEFT);
            $stockTransfer->date = Carbon::parse($request->date);
            $stockTransfer->notes = $request->notes;
            $stockTransfer->vehicle_no = $request->vehicle_no;
            $stockTransfer->driver_name = $request->driver_name;
            $stockTransfer->driver_phone = $request->driver_phone;
            $stockTransfer->status = 1; // 1 = Draft / Pending Dispatch
            $stockTransfer->is_return = 0;
            $stockTransfer->create_user_id = auth()->id();
            $stockTransfer->save();

            if (!$request->product_id) {
                throw new \Exception('Please add to product');
            } else {
                $counter = 0;

                foreach ($request->product_id as $reqProductId) {
                    $product = Product::find($request->product_id[$counter]);
                    $inventory = Inventory::where('sister_concern_id', auth()->user()->activeConcernId())
                                ->where('id', $request->inventory_id[$counter])
                                ->first();

                    if (!$inventory) {
                        throw new \Exception("$product->name stock batch not found !");
                    } else {
                        if ($inventory->quantity < $request->product_qty[$counter]) {
                            throw new \Exception("$product->name insufficient stock in selected batch, available: $inventory->quantity !");
                        }
                    }

                    $qty = $request->product_qty[$counter];

                    // NOTE: Stock is NOT decremented yet! It is just recorded in the transfer items table in draft status.
                    $stockTransferItem = new StockTransferItem();
                    $stockTransferItem->sister_concern_id = auth()->user()->activeConcernId();
                    $stockTransferItem->stock_transfer_id = $stockTransfer->id;
                    $stockTransferItem->inventory_id = $inventory->id;
                    $stockTransferItem->product_id = $product->id;
                    $stockTransferItem->product_code = $product->code;
                    $stockTransferItem->quantity = $qty;
                    $stockTransferItem->received_quantity = $qty; // default
                    $stockTransferItem->return_quantity = 0;
                    $stockTransferItem->unit_price = $inventory->unit_price;
                    
                    $reqTp = isset($request->transfer_price[$counter]) ? (float)$request->transfer_price[$counter] : 0;
                    $stockTransferItem->transfer_price = $reqTp > 0 ? $reqTp : $inventory->unit_price;
                    $stockTransferItem->create_user_id = auth()->id();
                    $stockTransferItem->save();

                    $counter++;
                }
            }

            if ($request->notes == '') {
                $request['notes'] = 'Stock Transfer: Order No. ' . $stockTransfer->order_no;
            }

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Stock transfer draft created successfully.',
                'redirect_url' => route('stock-transfers.details', ['stock_transfer' => $stockTransfer->id]),
            ]);
        } catch (\Exception $exception) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => $exception->getMessage(),
            ]);
        }
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(StockTransfer $stock_transfer,Request $request)
    {
        if (!auth()->user()->hasPermissionTo('stock_transfer_edit')) {
            abort(403, 'Unauthorized');
        }
        if ($stock_transfer->is_return == 1) {
            abort(404, 'Return transfers cannot be edited manually.');
        }

        $stock_transfer->load('stockTransferItems');

        $checkReceivedCount = count($stock_transfer->stockTransferItems->where('status','received'));
        if ($checkReceivedCount != 0) {
            abort(404, 'Not Fund');
        }
        $user = auth()->user();
        $products = Product::all();

        // From branch: all active warehouses under the user's active concern
        $fromBranches = \App\Models\Settings\Warehouse::with('sisterConcern')->where('sister_concern_id', $user->activeConcernId())
            ->where('type', 'main')
            ->where('status', 1)
            ->get();

        // To branch: all active warehouses across all sister concerns for branch-to-branch transfer
        $fromBranchId = $request->get('from_branch', $stock_transfer->from_warehouse_id);
        $toBranchesQuery = \App\Models\Settings\Warehouse::with('sisterConcern')->where('type', 'main')->where('status', 1);

        if ($fromBranchId) {
            $toBranchesQuery->where('id', '!=', $fromBranchId);
        }

        $toBranches = $toBranchesQuery->get();

        return view('stock_transfer.edit',compact('stock_transfer', 'products','fromBranches','toBranches'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, StockTransfer $stock_transfer)
    {
        if (!auth()->user()->hasPermissionTo('stock_transfer_edit')) {
            abort(403, 'Unauthorized');
        }
        if ($stock_transfer->is_return == 1) {
            return response()->json([
                'status' => false,
                'message' => 'Return transfers cannot be edited manually.'
            ]);
        }

        $checkReceivedCount = count($stock_transfer->stockTransferItems->where('status','received'));
        if ($checkReceivedCount != 0) {
            abort(404, 'Cannot edit a transfer that has already been received.');
        }

        $rules = [
            'product_id.*' => 'required',
            'inventory_id.*' => 'required',
            'notes' => 'nullable|max:255',
            'from_branch' => 'required',
            'to_branch' => 'required',
            'date' => 'required|date',
        ];
        $request->validate($rules);

        // Guard: Block editing if already dispatched
        if ($stock_transfer->status >= 2) {
            return response()->json([
                'status' => false,
                'message' => 'Cannot edit a transfer that has already been Dispatched / Gate Pass Generated.'
            ]);
        }

        DB::beginTransaction();
        try {
            $fromWarehouse = Warehouse::findOrFail($request->from_branch);
            $toWarehouse = Warehouse::findOrFail($request->to_branch);

            $stockTransfer = $stock_transfer;
            $stockTransfer->from_sister_concern_id = $fromWarehouse->sister_concern_id;
            $stockTransfer->to_sister_concern_id = $toWarehouse->sister_concern_id;
            $stockTransfer->from_warehouse_id = $fromWarehouse->id;
            $stockTransfer->to_warehouse_id = $toWarehouse->id;
            $stockTransfer->date = Carbon::parse($request->date);
            $stockTransfer->notes = $request->notes;
            $stockTransfer->vehicle_no = $request->vehicle_no;
            $stockTransfer->driver_name = $request->driver_name;
            $stockTransfer->driver_phone = $request->driver_phone;
            $stockTransfer->alter_user_id = auth()->id();
            $stockTransfer->save();

            if (!$request->product_id) {
                throw new \Exception('Please add to product');
            } else {
                $counter = 0;
                
                // Delete old items (no stock rollback needed because it is in status=1, Draft)
                StockTransferItem::where('stock_transfer_id', $stockTransfer->id)->delete();

                foreach ($request->product_id as $reqProductId) {
                    $product = Product::find($request->product_id[$counter]);
                    $inventory = Inventory::where('sister_concern_id', auth()->user()->activeConcernId())
                                ->where('id', $request->inventory_id[$counter])
                                ->first();

                    if (!$inventory) {
                        throw new \Exception("$product->name stock batch not found !");
                    } else {
                        if ($inventory->quantity < $request->product_qty[$counter]) {
                            throw new \Exception("$product->name insufficient stock in selected batch, available: $inventory->quantity !");
                        }
                    }

                    $stockTransferItem = new StockTransferItem();
                    $stockTransferItem->sister_concern_id = auth()->user()->activeConcernId();
                    $stockTransferItem->stock_transfer_id = $stockTransfer->id;
                    $stockTransferItem->inventory_id = $inventory->id;
                    $stockTransferItem->product_id = $product->id;
                    $stockTransferItem->product_code = $product->code;
                    $stockTransferItem->quantity = $request->product_qty[$counter];
                    $stockTransferItem->received_quantity = $request->product_qty[$counter];
                    $stockTransferItem->return_quantity = 0;
                    $stockTransferItem->unit_price = $inventory->unit_price;
                    
                    $reqTp = isset($request->transfer_price[$counter]) ? (float)$request->transfer_price[$counter] : 0;
                    $stockTransferItem->transfer_price = $reqTp > 0 ? $reqTp : $inventory->unit_price;
                    $stockTransferItem->create_user_id = auth()->id();
                    $stockTransferItem->save();

                    $counter++;
                }
            }

            if ($request->notes == '') {
                $request['notes'] = 'Stock Transfer: Order No. ' . $stockTransfer->order_no;
            }

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Stock transfer order updated successfully.',
                'redirect_url' => route('stock-transfers.details', ['stock_transfer' => $stockTransfer->id]),
            ]);
        } catch (\Exception $exception) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */

    public function destroy(StockTransfer $stock_transfer)
    {
        if (!auth()->user()->hasPermissionTo('stock_transfer_delete')) {
            abort(403, 'Unauthorized');
        }
        if ($stock_transfer->is_return == 1) {
            return response()->json(['success' => false, 'message' => 'Return transfers cannot be deleted manually.'], Response::HTTP_OK);
        }

        DB::beginTransaction();
        try {
            $stockTransferItems = StockTransferItem::where('stock_transfer_id', $stock_transfer->id)->get();
            
            // Guard: Cannot delete if already partially or fully received
            if ($stock_transfer->status >= 3) {
                return response()->json(['success' => false, 'message' => 'Cannot cancel a transfer that has already been partially or fully received.'], Response::HTTP_OK);
            }

            // Only rollback if status was Dispatched / Gate Pass Generated (status = 2)
            if ($stock_transfer->status == 2) {
                $fromWarehouse = Warehouse::find($stock_transfer->from_warehouse_id);
                $transitWarehouse = $fromWarehouse ? Warehouse::where('sister_concern_id', $fromWarehouse->sister_concern_id)
                    ->where('type', 'transit')->first() : null;
                $mainWarehouse = $fromWarehouse;

                foreach ($stockTransferItems as $item) {
                    $originalInventory = Inventory::find($item->inventory_id);
                    if (!$originalInventory) continue;

                    // Rollback: Transit â†’ Main
                    if ($transitWarehouse) {
                        $transitInv = Inventory::where('sister_concern_id', $fromWarehouse->sister_concern_id)
                            ->where('warehouse_id', $transitWarehouse->id)
                            ->where('product_id', $item->product_id)
                            ->where('batch_no', $originalInventory->batch_no)
                            ->first();
                        if ($transitInv) {
                            $transitInv->decrement('quantity', $item->quantity);
                        }
                    }
                    
                    // Restore to Main warehouse
                    $originalInventory->increment('quantity', $item->quantity);

                    // Log rollback
                    $log = new InventoryLog();
                    $log->sister_concern_id = $fromWarehouse->sister_concern_id;
                    $log->type = InventoryLogType::$STOCK_TRANSFER_RETURN_IN;
                    $log->warehouse_id = $mainWarehouse->id;
                    $log->inventory_id = $originalInventory->id;
                    $log->stock_transfer_id = $stock_transfer->id;
                    $log->product_id = $item->product_id;
                    $log->quantity = $item->quantity;
                    $log->unit_price = $item->unit_price;
                    $log->product_code = $item->product_code;
                    $log->batch_no = $originalInventory->batch_no;
                    $log->notes = 'Transfer cancelled - stock returned to main: ' . $stock_transfer->order_no;
                    $log->user_id = auth()->id();
                    $log->date = Carbon::now();
                    $log->save();
                }
            }

            StockTransferItem::where('stock_transfer_id', $stock_transfer->id)->delete();
            $stock_transfer->status = 5; // 5 = Cancelled
            $stock_transfer->save();
            $stock_transfer->delete();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Transfer cancelled.'], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], Response::HTTP_OK);
        }
    }

    public function dispatchStock(Request $request, StockTransfer $stock_transfer)
    {
        if (!auth()->user()->hasPermissionTo('stock_transfer')) {
            abort(403, 'Unauthorized');
        }

        if ($stock_transfer->status != 1) {
            return redirect()->back()->with('error', 'Only Pending Draft transfers can be Dispatched.');
        }

        DB::beginTransaction();
        try {
            // Update vehicle & driver details if submitted during Dispatch
            if ($request->filled('vehicle_no')) {
                $stock_transfer->vehicle_no = $request->vehicle_no;
            }
            if ($request->filled('driver_name')) {
                $stock_transfer->driver_name = $request->driver_name;
            }
            if ($request->filled('driver_phone')) {
                $stock_transfer->driver_phone = $request->driver_phone;
            }
            $fromWarehouse = Warehouse::findOrFail($stock_transfer->from_warehouse_id);
            $transitWarehouse = Warehouse::where('sister_concern_id', $fromWarehouse->sister_concern_id)
                ->where('type', 'transit')
                ->first();
            
            // Auto-create Transit Warehouse if not exists to avoid errors
            if (!$transitWarehouse) {
                $transitWarehouse = new Warehouse();
                $transitWarehouse->sister_concern_id = $fromWarehouse->sister_concern_id;
                $transitWarehouse->name = 'Transit Warehouse';
                $transitWarehouse->type = 'transit';
                $transitWarehouse->status = 1;
                $transitWarehouse->save();
            }

            $items = $stock_transfer->stockTransferItems;
            if ($items->isEmpty()) {
                throw new \Exception('No items found in this transfer order.');
            }

            foreach ($items as $item) {
                $inventory = Inventory::where('sister_concern_id', $stock_transfer->from_sister_concern_id)
                            ->where('id', $item->inventory_id)
                            ->first();

                if (!$inventory) {
                    throw new \Exception("Product " . ($item->product->name ?? '') . " stock batch not found!");
                }

                // If it is a Return Transfer, we DO NOT deduct stock from the receiver's (Chittagong) main warehouse
                // because they never received these shortage items physically in the first place.
                // The shortage stock is already waiting in the original sender's (Dhaka) transit warehouse.
                if ($stock_transfer->is_return == 1) {
                    // Record only dispatch log without mutating main stock
                    $logOut = new InventoryLog();
                    $logOut->sister_concern_id = $fromWarehouse->sister_concern_id;
                    $logOut->type = InventoryLogType::$STOCK_TRANSFER_OUT;
                    $logOut->warehouse_id = $fromWarehouse->id;
                    $logOut->inventory_id = $inventory->id;
                    $logOut->stock_transfer_id = $stock_transfer->id;
                    $logOut->product_id = $item->product_id;
                    $logOut->quantity = $item->quantity;
                    $logOut->unit_price = $inventory->unit_price;
                    $logOut->product_code = $inventory->product_code;
                    $logOut->batch_no = $inventory->batch_no;
                    $logOut->notes = 'Shortage return stock dispatched to original sender: ' . $stock_transfer->order_no;
                    $logOut->user_id = auth()->id();
                    $logOut->date = Carbon::now();
                    $logOut->save();
                    continue;
                }

                if ($inventory->quantity < $item->quantity) {
                    throw new \Exception("Insufficient stock for product: " . ($item->product->name ?? '') . ". Available: " . $inventory->quantity . ", requested: " . $item->quantity);
                }

                // Deduct from sender's Main Warehouse
                $inventory->decrement('quantity', $item->quantity);

                // Add to sender's Transit Warehouse
                $transitInventory = Inventory::where('sister_concern_id', $fromWarehouse->sister_concern_id)
                    ->where('warehouse_id', $transitWarehouse->id)
                    ->where('product_id', $item->product_id)
                    ->where('batch_no', $inventory->batch_no)
                    ->first();
                
                if (!$transitInventory) {
                    $transitInventory = new Inventory();
                    $transitInventory->sister_concern_id = $fromWarehouse->sister_concern_id;
                    $transitInventory->warehouse_id = $transitWarehouse->id;
                    $transitInventory->product_id = $item->product_id;
                    $transitInventory->product_code = $inventory->product_code;
                    $transitInventory->quantity = $item->quantity;
                    $transitInventory->batch_no = $inventory->batch_no;
                    $transitInventory->unit_price = $inventory->unit_price;
                    $transitInventory->save();
                } else {
                    $transitInventory->increment('quantity', $item->quantity);
                }

                // Inventory Log: Stock Transfer Out (Main -> Transit)
                $logOut = new InventoryLog();
                $logOut->sister_concern_id = $fromWarehouse->sister_concern_id;
                $logOut->type = InventoryLogType::$STOCK_TRANSFER_OUT;
                $logOut->warehouse_id = $fromWarehouse->id;
                $logOut->inventory_id = $inventory->id;
                $logOut->stock_transfer_id = $stock_transfer->id;
                $logOut->product_id = $item->product_id;
                $logOut->quantity = $item->quantity;
                $logOut->unit_price = $inventory->unit_price;
                $logOut->product_code = $inventory->product_code;
                $logOut->batch_no = $inventory->batch_no;
                $logOut->notes = 'Stock dispatched to transit: ' . $stock_transfer->order_no;
                $logOut->user_id = auth()->id();
                $logOut->date = Carbon::now();
                $logOut->save();
            }

            // Update status to 2 (Dispatched / In Transit)
            $stock_transfer->status = 2; // Dispatched
            $stock_transfer->save();

            DB::commit();
            return redirect()->back()->with('success', 'Stock Dispatched & Gate Pass Generated successfully. Items are now in Transit.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function printChallan(StockTransfer $stock_transfer)
    {
        if (!auth()->user()->hasPermissionTo('stock_transfer')) {
            abort(403, 'Unauthorized');
        }
        $stockTransfer = $stock_transfer->load('stockTransferItems.product.unit', 'fromWarehouse', 'toWarehouse', 'user');
        return view('stock_transfer.challan', compact('stockTransfer'));
    }
}
