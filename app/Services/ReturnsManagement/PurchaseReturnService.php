<?php

namespace App\Services\ReturnsManagement;

use App\Models\ReturnsManagement\PurchaseReturn;
use App\Models\ReturnsManagement\PurchaseReturnItem;
use App\Models\Account\Transaction;
use App\Models\Account\Voucher;
use App\Models\Account\AccountHead;
use App\Models\Inventory\Inventory;
use App\Models\Inventory\InventoryLog;
use App\Enumeration\VoucherType;
use App\Enumeration\TransactionType;
use App\Enumeration\InventoryLogType;
use App\Enumeration\ReservedAccountHead;
use App\Models\Purchase\PurchaseOrderItem;
use Illuminate\Support\Facades\DB;

class PurchaseReturnService
{
    /**
     * Store a new Purchase Return (Draft mode)
     */
    public function storeAndApprove(array $data, $userId, $sisterConcernId)
    {
        DB::beginTransaction();
        try {
            // Generate Return No
            $lastReturn = PurchaseReturn::withTrashed()->latest('id')->first();
            $nextId = 1;
            if ($lastReturn && preg_match('/PR-(\d+)/', $lastReturn->return_no, $matches)) {
                $nextId = (int)$matches[1] + 1;
            }
            $returnNo = 'PR-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

            $totalReturnValue = 0;
            $itemsToReturn = [];

            if (isset($data['return_items']) && is_array($data['return_items'])) {
                foreach ($data['return_items'] as $item) {
                    if (!empty($item['quantity']) && $item['quantity'] > 0) {
                        $totalReturnValue += ($item['quantity'] * $item['unit_price']);
                        $itemsToReturn[] = $item;
                    }
                }
            }

            if (empty($itemsToReturn)) {
                throw new \Exception("Please provide at least one product to return.");
            }

            $formattedDate = \Carbon\Carbon::parse($data['date'])->format('Y-m-d');

            $purchaseReturn = PurchaseReturn::create([
                'sister_concern_id' => $sisterConcernId,
                'supplier_id' => $data['supplier_id'],
                'purchase_order_id' => $data['purchase_order_id'],
                'return_warehouse_id' => $data['return_warehouse_id'],
                'return_no' => $returnNo,
                'date' => $formattedDate,
                'total_return_value' => $totalReturnValue,
                'status' => 2, // 2 = Sent/Approved
                'create_user_id' => $userId
            ]);

            foreach ($itemsToReturn as $item) {
                PurchaseReturnItem::create([
                    'purchase_return_id' => $purchaseReturn->id,
                    'product_id' => $item['product_id'],
                    'returned_qty' => $item['quantity'],
                    'replaced_qty' => 0, 
                    'unit_price' => $item['unit_price'],
                ]);

                // Update Purchase Order Item return_quantity
                $orderItem = PurchaseOrderItem::where('purchase_order_id', $purchaseReturn->purchase_order_id)
                                ->where('product_id', $item['product_id'])
                                ->first();
                if ($orderItem) {
                    $orderItem->increment('return_quantity', $item['quantity']);
                }
            }

            // Financial Voucher (Journal)
            $supplierAccountHead = AccountHead::where('head_type', 'primary')->where('supplier_id', $purchaseReturn->supplier_id)->first();
            if (!$supplierAccountHead) throw new \Exception('Supplier Account Head not found.');

            $voucherNo = generateVoucherNo(VoucherType::$JOURNAL_VOUCHER, $purchaseReturn->sister_concern_id);
            $voucherNoGroupSl = abs((int) filter_var($voucherNo, FILTER_SANITIZE_NUMBER_INT));
            
            $voucher = new Voucher();
            $voucher->sister_concern_id = $purchaseReturn->sister_concern_id;
            $voucher->voucher_type = VoucherType::$JOURNAL_VOUCHER;
            $voucher->amount = $purchaseReturn->total_return_value;
            $voucher->date = $purchaseReturn->date;
            $voucher->voucher_no = $voucherNo;
            $voucher->voucher_no_group_sl = $voucherNoGroupSl;
            $voucher->account_head_payee_depositor_id = $supplierAccountHead->id;
            $voucher->notes = 'Supplier Return No: ' . $purchaseReturn->return_no;
            $voucher->user_id = $userId;
            $voucher->save();

            // Accounting Logic: Supplier owes us money for returned items -> Debit Supplier
            $debitTx = new Transaction();
            $debitTx->sister_concern_id = $purchaseReturn->sister_concern_id;
            $debitTx->voucher_id = $voucher->id;
            $debitTx->account_head_id = $supplierAccountHead->id;
            $debitTx->voucher_type = VoucherType::$JOURNAL_VOUCHER;
            $debitTx->transaction_type = TransactionType::$DEBIT;
            $debitTx->amount = $purchaseReturn->total_return_value;
            $debitTx->date = $purchaseReturn->date;
            $debitTx->voucher_no = $voucherNo;
            $debitTx->voucher_no_group_sl = $voucherNoGroupSl;
            $debitTx->notes = 'Supplier Debit for Return No: ' . $purchaseReturn->return_no;
            $debitTx->user_id = $userId;
            $debitTx->save();

            // Credit Finished Goods Inventory (পণ্য warehouse থেকে supplier এর কাছে ফিরে গেছে — Asset কমলো)
            $creditTx = new Transaction();
            $creditTx->sister_concern_id = $purchaseReturn->sister_concern_id;
            $creditTx->voucher_id = $voucher->id;
            $creditTx->account_head_id = reservedAccountHeadId(ReservedAccountHead::FINISHED_GOODS_INVENTORY);
            $creditTx->voucher_type = VoucherType::$JOURNAL_VOUCHER;
            $creditTx->transaction_type = TransactionType::$CREDIT;
            $creditTx->amount = $purchaseReturn->total_return_value;
            $creditTx->date = $purchaseReturn->date;
            $creditTx->voucher_no = $voucherNo;
            $creditTx->voucher_no_group_sl = $voucherNoGroupSl;
            $creditTx->notes = 'Inventory reduced — Purchase Return No: ' . $purchaseReturn->return_no;
            $creditTx->user_id = $userId;
            $creditTx->save();

            // Inventory Updates: Reduce stock from Defect Warehouse
            foreach ($itemsToReturn as $item) {
                $remainingQtyToReturn = $item['quantity'];

                if (empty($item['batch_no'])) {
                    throw new \Exception("Batch Number is missing for Product ID: " . $item['product_id'] . ". Legacy orders without batches cannot be returned until manually updated.");
                }

                $query = Inventory::where('product_id', $item['product_id'])
                                        ->where('warehouse_id', $purchaseReturn->return_warehouse_id)
                                        ->where('sister_concern_id', $purchaseReturn->sister_concern_id)
                                        ->where('batch_no', $item['batch_no'])
                                        ->where('quantity', '>', 0);

                $inventories = $query->orderBy('id', 'asc')->get();
                
                $totalAvailable = $inventories->sum('quantity');
                if ($totalAvailable < $remainingQtyToReturn) {
                    throw new \Exception("Not enough stock in the selected warehouse to return. Product ID: " . $item['product_id']);
                }

                foreach ($inventories as $invOut) {
                    if ($remainingQtyToReturn <= 0) break;

                    $deduct = min($invOut->quantity, $remainingQtyToReturn);
                    $invOut->decrement('quantity', $deduct);
                    $remainingQtyToReturn -= $deduct;

                    InventoryLog::create([
                        'inventory_id' => $invOut->id,
                        'product_id' => $item['product_id'],
                        'warehouse_id' => $purchaseReturn->return_warehouse_id,
                        'purchase_return_id' => $purchaseReturn->id,
                        'type' => InventoryLogType::$PURCHASE_RETURN_OUT,
                        'batch_no' => $invOut->batch_no,
                        'unit_price' => $invOut->unit_price,
                        'quantity' => $deduct,
                        'notes' => 'Supplier Return No: ' . $purchaseReturn->return_no,
                        'date' => $formattedDate,
                        'sister_concern_id' => $purchaseReturn->sister_concern_id,
                        'user_id' => $userId
                    ]);
                }
            }

            $purchaseReturn->update(['return_voucher_id' => $voucher->id]);

            DB::commit();
            return $purchaseReturn;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Reverse and Delete a Purchase Return
     */
    public function deleteAndReverse($id)
    {
        DB::beginTransaction();
        try {
            $purchaseReturn = PurchaseReturn::with('items')->findOrFail($id);

            // Reverse only if it was approved
            if ($purchaseReturn->status == 2) {
                // 1. Reverse Inventory
                foreach ($purchaseReturn->items as $item) {
                    if ($item->returned_qty > 0) {
                        $invOut = Inventory::where('product_id', $item->product_id)
                                           ->where('warehouse_id', $purchaseReturn->return_warehouse_id)
                                           ->where('sister_concern_id', $purchaseReturn->sister_concern_id)
                                           ->first();
                        if ($invOut) {
                            $invOut->increment('quantity', $item->returned_qty);
                        }
                        
                        // Decrease return_quantity from purchase_order_items
                        if ($purchaseReturn->purchase_order_id) {
                            $orderItem = PurchaseOrderItem::where('purchase_order_id', $purchaseReturn->purchase_order_id)
                                            ->where('product_id', $item->product_id)
                                            ->first();
                            if ($orderItem && $orderItem->return_quantity >= $item->returned_qty) {
                                $orderItem->decrement('return_quantity', $item->returned_qty);
                            }
                        }
                    }
                }

                // 2. Delete Inventory Logs
                InventoryLog::where('purchase_return_id', $purchaseReturn->id)->delete();

                // 3. Delete Accounting Vouchers
                if ($purchaseReturn->return_voucher_id) {
                    Transaction::where('voucher_id', $purchaseReturn->return_voucher_id)->delete();
                    Voucher::where('id', $purchaseReturn->return_voucher_id)->delete();
                }
                
                if ($purchaseReturn->replacement_voucher_id) {
                    Transaction::where('voucher_id', $purchaseReturn->replacement_voucher_id)->delete();
                    Voucher::where('id', $purchaseReturn->replacement_voucher_id)->delete();
                }
            }

            // 4. Delete Items and Return
            PurchaseReturnItem::where('purchase_return_id', $purchaseReturn->id)->delete();
            $purchaseReturn->delete();

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
