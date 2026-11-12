<?php

namespace App\Services\ReturnsManagement;

use App\Models\ReturnsManagement\SalesReturn;
use App\Models\ReturnsManagement\SalesReturnItem;
use App\Models\Account\Transaction;
use App\Models\Account\Voucher;
use App\Models\Account\AccountHead;
use App\Enumeration\VoucherType;
use App\Enumeration\TransactionType;
use App\Enumeration\ReservedAccountHead;
use App\Models\Inventory\Inventory;
use App\Models\Inventory\InventoryLog;
use App\Enumeration\InventoryLogType;
use App\Models\Sales\SaleOrderItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SalesReturnService
{
    public function createAndApprove($data)
    {
        if (!empty($data['sale_order_id']) && \App\Models\Ecommerce\PackageOrderCheckout::where('sale_order_id', $data['sale_order_id'])->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages(['sale_order_id' => 'Package returns require a dedicated full-package return workflow.']);
        }

        DB::beginTransaction();

        try {
            $sisterConcernId = auth()->user()->sister_concern_id;
            $userId = auth()->id();
            
            // Format the date for MySQL (from DD-MM-YYYY to YYYY-MM-DD)
            $formattedDate = Carbon::parse($data['date'])->format('Y-m-d');

            // 1. Generate Return No
            $lastReturn = SalesReturn::withTrashed()->latest('id')->first();
            $nextId = 1;
            if ($lastReturn && preg_match('/SR-(\d+)/', $lastReturn->return_no, $matches)) {
                $nextId = (int)$matches[1] + 1;
            }
            $returnNo = 'SR-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

            // 2. Calculate Totals (Securely overriding frontend prices with DB prices)
            $totalReturnAmount = 0;
            $totalReplacementAmount = 0;
            $totalReturnCost = 0;
            $totalReplacementCost = 0;
            $returnDeduction = $data['return_deduction'] ?? 0;

            foreach ($data['return_items'] as $key => $item) {
                if ($item['quantity'] > 0) {
                    $orderItem = SaleOrderItem::where('sale_order_id', $data['sale_order_id'])
                                              ->where('product_id', $item['product_id'])
                                              ->first();
                    if ($orderItem) {
                        $data['return_items'][$key]['unit_price'] = $orderItem->selling_price;
                        $item['unit_price'] = $orderItem->selling_price;
                        $totalReturnCost += ($item['quantity'] * $orderItem->unit_price);
                    } else {
                        $inv = Inventory::find($item['inventory_id'] ?? 0);
                        if ($inv) $totalReturnCost += ($item['quantity'] * $inv->unit_price);
                    }
                    $totalReturnAmount += ($item['quantity'] * $item['unit_price']);
                }
            }

            if (isset($data['replacement_items'])) {
                foreach ($data['replacement_items'] as $key => $item) {
                    if ($item['quantity'] > 0) {
                        if (!empty($item['inventory_id'])) {
                            $inv = Inventory::find($item['inventory_id']);
                            if ($inv) {
                                $totalReplacementCost += ($item['quantity'] * $inv->unit_price);
                            }
                        }
                        $totalReplacementAmount += ($item['quantity'] * $item['unit_price']);
                    }
                }
            }

            $netReturnAmount = $totalReturnAmount - $returnDeduction;
            $netPayableOrReceivable = $totalReplacementAmount - $netReturnAmount;

            // 3. Create Sales Return (Status 2 = Approved)
            $salesReturn = SalesReturn::create([
                'sister_concern_id' => $sisterConcernId,
                'customer_id' => $data['customer_id'],
                'sale_order_id' => $data['sale_order_id'],
                'return_warehouse_id' => $data['return_warehouse_id'],
                'return_no' => $returnNo,
                'date' => $formattedDate,
                'total_return_amount' => $totalReturnAmount,
                'return_deduction' => $returnDeduction,
                'total_replacement_amount' => $totalReplacementAmount,
                'net_payable_or_receivable' => $netPayableOrReceivable,
                'status' => 2,
                'create_user_id' => $userId
            ]);

            // 4. Create Items & Handle Inventory
            // Returned Items (IN)
            foreach ($data['return_items'] as $item) {
                if ($item['quantity'] > 0) {
                    SalesReturnItem::create([
                        'sales_return_id' => $salesReturn->id,
                        'product_id' => $item['product_id'],
                        'inventory_id' => $item['inventory_id'] ?? null,
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'is_replacement' => false,
                    ]);

                    $originalInv = null;
                    $batchNo = null;
                    if (!empty($item['inventory_id'])) {
                        $originalInv = Inventory::find($item['inventory_id']);
                        $batchNo = $originalInv ? $originalInv->batch_no : null;
                    }

                    $invIn = Inventory::firstOrCreate(
                        [
                            'product_id' => $item['product_id'], 
                            'warehouse_id' => $salesReturn->return_warehouse_id,
                            'batch_no' => $batchNo,
                            'sister_concern_id' => $sisterConcernId
                        ],
                        [
                            'quantity' => 0, 
                            'unit_price' => $originalInv ? $originalInv->unit_price : $item['unit_price']
                        ]
                    );
                    $invIn->increment('quantity', $item['quantity']);

                    InventoryLog::create([
                        'inventory_id' => $invIn->id,
                        'product_id' => $item['product_id'],
                        'warehouse_id' => $salesReturn->return_warehouse_id,
                        'sales_return_id' => $salesReturn->id,
                        'type' => InventoryLogType::$SALES_RETURN_IN,
                        'batch_no' => $batchNo,
                        'quantity' => $item['quantity'],
                        'unit_price' => $originalInv ? $originalInv->unit_price : $item['unit_price'],
                        'notes' => 'Sales Return No: ' . $salesReturn->return_no,
                        'date' => $formattedDate,
                        'sister_concern_id' => $sisterConcernId,
                        'user_id' => $userId
                    ]);

                    if ($salesReturn->sale_order_id) {
                        $orderItem = SaleOrderItem::where('sale_order_id', $salesReturn->sale_order_id)
                                                  ->where('product_id', $item['product_id'])
                                                  ->first();
                        if ($orderItem) {
                            $orderItem->increment('return_quantity', $item['quantity']);
                        }
                    }
                }
            }

            // Replacement Items (OUT)
            if (isset($data['replacement_items'])) {
                foreach ($data['replacement_items'] as $item) {
                    if ($item['quantity'] > 0) {
                        SalesReturnItem::create([
                            'sales_return_id' => $salesReturn->id,
                            'product_id' => $item['product_id'],
                            'inventory_id' => $item['inventory_id'] ?? null,
                            'quantity' => $item['quantity'],
                            'unit_price' => $item['unit_price'],
                            'is_replacement' => true,
                        ]);

                        if (empty($item['inventory_id'])) {
                            throw new \Exception("Batch (Inventory) is missing for replacement item.");
                        }
                        
                        $invOut = Inventory::find($item['inventory_id']);
                        if (!$invOut || $invOut->quantity < $item['quantity']) {
                            throw new \Exception("Not enough stock for replacement item in selected Batch.");
                        }

                        $invOut->decrement('quantity', $item['quantity']);

                        InventoryLog::create([
                        'inventory_id' => $invOut->id,
                        'product_id' => $item['product_id'],
                        'warehouse_id' => $invOut->warehouse_id,
                        'sales_return_id' => $salesReturn->id,
                        'type' => InventoryLogType::$SALES_REPLACEMENT_OUT,
                        'batch_no' => $invOut->batch_no,
                        'quantity' => $item['quantity'],
                        'unit_price' => $invOut->unit_price,
                        'notes' => 'Sales Replacement No: ' . $salesReturn->return_no,
                        'date' => $formattedDate,
                        'sister_concern_id' => $sisterConcernId,
                        'user_id' => $userId
                    ]);
                    }
                }
            }

            // 5. Accounting Logic
            $customerAccountHead = AccountHead::where('head_type', 'primary')->where('supplier_id', $salesReturn->customer_id)->first();
            if (!$customerAccountHead) throw new \Exception('Customer Account Head not found.');
            
            $customerAccountHeadId = $customerAccountHead->id;
            $salesReturnAccountHeadId = reservedAccountHeadId(ReservedAccountHead::SALES_RETURN);
            $salesRevenueAccountHeadId = reservedAccountHeadId(ReservedAccountHead::SALES_REVENUE);

            // Voucher 1: For Sales Return (If there are returned items)
            if ($netReturnAmount > 0 || $totalReturnCost > 0) {
                $returnVoucherNo = generateVoucherNo(VoucherType::$JOURNAL_VOUCHER);
                $returnVoucherNoGroupSl = abs((int) filter_var($returnVoucherNo, FILTER_SANITIZE_NUMBER_INT));
                
                $returnVoucher = new Voucher();
                $returnVoucher->sister_concern_id = $sisterConcernId;
                $returnVoucher->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                $returnVoucher->amount = $netReturnAmount + $totalReturnCost;
                $returnVoucher->date = $formattedDate;
                $returnVoucher->voucher_no = $returnVoucherNo;
                $returnVoucher->voucher_no_group_sl = $returnVoucherNoGroupSl;
                $returnVoucher->account_head_payee_depositor_id = $customerAccountHeadId;
                $returnVoucher->notes = 'Sales Return: ' . $salesReturn->return_no;
                $returnVoucher->user_id = $userId;
                $returnVoucher->save();

                if ($netReturnAmount > 0) {
                    // Debit: Sales Return
                    $debitTx1 = new Transaction();
                    $debitTx1->sister_concern_id = $sisterConcernId;
                    $debitTx1->voucher_id = $returnVoucher->id;
                    $debitTx1->account_head_id = $salesReturnAccountHeadId;
                    $debitTx1->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                    $debitTx1->transaction_type = TransactionType::$DEBIT;
                    $debitTx1->amount = $netReturnAmount;
                    $debitTx1->date = $formattedDate;
                    $debitTx1->voucher_no = $returnVoucherNo;
                    $debitTx1->voucher_no_group_sl = $returnVoucherNoGroupSl;
                    $debitTx1->notes = 'Sales Return Expense';
                    $debitTx1->user_id = $userId;
                    $debitTx1->save();

                    // Credit: Customer
                    $creditTx1 = new Transaction();
                    $creditTx1->sister_concern_id = $sisterConcernId;
                    $creditTx1->voucher_id = $returnVoucher->id;
                    $creditTx1->account_head_id = $customerAccountHeadId;
                    $creditTx1->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                    $creditTx1->transaction_type = TransactionType::$CREDIT;
                    $creditTx1->amount = $netReturnAmount;
                    $creditTx1->date = $formattedDate;
                    $creditTx1->voucher_no = $returnVoucherNo;
                    $creditTx1->voucher_no_group_sl = $returnVoucherNoGroupSl;
                    $creditTx1->notes = 'Customer Credit from Sales Return';
                    $creditTx1->user_id = $userId;
                    $creditTx1->save();
                }

                if ($totalReturnCost > 0) {
                    // Debit: Finished Goods Inventory
                    $invDebit = new Transaction();
                    $invDebit->sister_concern_id = $sisterConcernId;
                    $invDebit->voucher_id = $returnVoucher->id;
                    $invDebit->account_head_id = reservedAccountHeadId(ReservedAccountHead::FINISHED_GOODS_INVENTORY);
                    $invDebit->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                    $invDebit->transaction_type = TransactionType::$DEBIT;
                    $invDebit->amount = $totalReturnCost;
                    $invDebit->date = $formattedDate;
                    $invDebit->voucher_no = $returnVoucherNo;
                    $invDebit->voucher_no_group_sl = $returnVoucherNoGroupSl;
                    $invDebit->notes = 'Inventory in from Sales Return';
                    $invDebit->user_id = $userId;
                    $invDebit->save();

                    // Credit: COGS
                    $cogsCredit = new Transaction();
                    $cogsCredit->sister_concern_id = $sisterConcernId;
                    $cogsCredit->voucher_id = $returnVoucher->id;
                    $cogsCredit->account_head_id = reservedAccountHeadId(ReservedAccountHead::COST_OF_GOODS_SOLD);
                    $cogsCredit->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                    $cogsCredit->transaction_type = TransactionType::$CREDIT;
                    $cogsCredit->amount = $totalReturnCost;
                    $cogsCredit->date = $formattedDate;
                    $cogsCredit->voucher_no = $returnVoucherNo;
                    $cogsCredit->voucher_no_group_sl = $returnVoucherNoGroupSl;
                    $cogsCredit->notes = 'COGS reversed from Sales Return';
                    $cogsCredit->user_id = $userId;
                    $cogsCredit->save();
                }
            }

            // Voucher 2: For Replacement Sales (If there are replacement items)
            if ($totalReplacementAmount > 0 || $totalReplacementCost > 0) {
                $salesVoucherNo = generateVoucherNo(VoucherType::$JOURNAL_VOUCHER);
                $salesVoucherNoGroupSl = abs((int) filter_var($salesVoucherNo, FILTER_SANITIZE_NUMBER_INT));
                
                $salesVoucher = new Voucher();
                $salesVoucher->sister_concern_id = $sisterConcernId;
                $salesVoucher->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                $salesVoucher->amount = $totalReplacementAmount + $totalReplacementCost;
                $salesVoucher->date = $formattedDate;
                $salesVoucher->voucher_no = $salesVoucherNo;
                $salesVoucher->voucher_no_group_sl = $salesVoucherNoGroupSl;
                $salesVoucher->account_head_payee_depositor_id = $customerAccountHeadId;
                $salesVoucher->notes = 'Sales Replacement: ' . $salesReturn->return_no;
                $salesVoucher->user_id = $userId;
                $salesVoucher->save();

                if ($totalReplacementAmount > 0) {
                    // Debit: Customer
                    $debitTx2 = new Transaction();
                    $debitTx2->sister_concern_id = $sisterConcernId;
                    $debitTx2->voucher_id = $salesVoucher->id;
                    $debitTx2->account_head_id = $customerAccountHeadId;
                    $debitTx2->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                    $debitTx2->transaction_type = TransactionType::$DEBIT;
                    $debitTx2->amount = $totalReplacementAmount;
                    $debitTx2->date = $formattedDate;
                    $debitTx2->voucher_no = $salesVoucherNo;
                    $debitTx2->voucher_no_group_sl = $salesVoucherNoGroupSl;
                    $debitTx2->notes = 'Customer Debit for Replacement Sale';
                    $debitTx2->user_id = $userId;
                    $debitTx2->save();

                    // Credit: Sales Revenue
                    $creditTx2 = new Transaction();
                    $creditTx2->sister_concern_id = $sisterConcernId;
                    $creditTx2->voucher_id = $salesVoucher->id;
                    $creditTx2->account_head_id = $salesRevenueAccountHeadId;
                    $creditTx2->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                    $creditTx2->transaction_type = TransactionType::$CREDIT;
                    $creditTx2->amount = $totalReplacementAmount;
                    $creditTx2->date = $formattedDate;
                    $creditTx2->voucher_no = $salesVoucherNo;
                    $creditTx2->voucher_no_group_sl = $salesVoucherNoGroupSl;
                    $creditTx2->notes = 'Sales Revenue from Replacement';
                    $creditTx2->user_id = $userId;
                    $creditTx2->save();
                }

                if ($totalReplacementCost > 0) {
                    // Debit: COGS
                    $cogsDebit = new Transaction();
                    $cogsDebit->sister_concern_id = $sisterConcernId;
                    $cogsDebit->voucher_id = $salesVoucher->id;
                    $cogsDebit->account_head_id = reservedAccountHeadId(ReservedAccountHead::COST_OF_GOODS_SOLD);
                    $cogsDebit->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                    $cogsDebit->transaction_type = TransactionType::$DEBIT;
                    $cogsDebit->amount = $totalReplacementCost;
                    $cogsDebit->date = $formattedDate;
                    $cogsDebit->voucher_no = $salesVoucherNo;
                    $cogsDebit->voucher_no_group_sl = $salesVoucherNoGroupSl;
                    $cogsDebit->notes = 'COGS for Replacement Sale';
                    $cogsDebit->user_id = $userId;
                    $cogsDebit->save();

                    // Credit: Finished Goods Inventory
                    $invCredit = new Transaction();
                    $invCredit->sister_concern_id = $sisterConcernId;
                    $invCredit->voucher_id = $salesVoucher->id;
                    $invCredit->account_head_id = reservedAccountHeadId(ReservedAccountHead::FINISHED_GOODS_INVENTORY);
                    $invCredit->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                    $invCredit->transaction_type = TransactionType::$CREDIT;
                    $invCredit->amount = $totalReplacementCost;
                    $invCredit->date = $formattedDate;
                    $invCredit->voucher_no = $salesVoucherNo;
                    $invCredit->voucher_no_group_sl = $salesVoucherNoGroupSl;
                    $invCredit->notes = 'Inventory out for Replacement Sale';
                    $invCredit->user_id = $userId;
                    $invCredit->save();
                }
            }

            // Update Sales Return with Voucher IDs
            $salesReturn->update([
                'return_voucher_id' => isset($returnVoucher) ? $returnVoucher->id : null,
                'replacement_voucher_id' => isset($salesVoucher) ? $salesVoucher->id : null,
            ]);

            DB::commit();
            return $salesReturn;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteAndReverse($id)
    {
        DB::beginTransaction();
        try {
            $salesReturn = SalesReturn::with('items')->findOrFail($id);

            // 1. Reverse Inventory
            foreach ($salesReturn->items as $item) {
                if (!$item->is_replacement) {
                    // It was Return IN, so we must decrement it from the return warehouse
                    $batchNo = null;
                    if ($item->inventory_id) {
                        $originalInv = Inventory::find($item->inventory_id);
                        $batchNo = $originalInv ? $originalInv->batch_no : null;
                    }

                    $invIn = Inventory::where('product_id', $item->product_id)
                                      ->where('warehouse_id', $salesReturn->return_warehouse_id)
                                      ->where('batch_no', $batchNo)
                                      ->where('sister_concern_id', $salesReturn->sister_concern_id)
                                      ->first();

                    if ($invIn) {
                        $invIn->decrement('quantity', $item->quantity);
                    }

                    // Reverse SaleOrderItem return_quantity
                    if ($salesReturn->sale_order_id) {
                        $orderItem = SaleOrderItem::where('sale_order_id', $salesReturn->sale_order_id)
                                                  ->where('product_id', $item->product_id)
                                                  ->first();
                        if ($orderItem && $orderItem->return_quantity >= $item->quantity) {
                            $orderItem->decrement('return_quantity', $item->quantity);
                        }
                    }
                } else {
                    // It was Replacement OUT, so we must increment it back
                    if ($item->inventory_id) {
                        $inv = Inventory::find($item->inventory_id);
                        if ($inv) {
                            $inv->increment('quantity', $item->quantity);
                        }
                    }
                }
            }

            // 2. Delete Inventory Logs
            InventoryLog::where('sales_return_id', $salesReturn->id)->delete();

            // 3. Delete Accounting (Transactions & Vouchers)
            if ($salesReturn->return_voucher_id) {
                Transaction::where('voucher_id', $salesReturn->return_voucher_id)->delete();
                Voucher::where('id', $salesReturn->return_voucher_id)->delete();
            }
            if ($salesReturn->replacement_voucher_id) {
                Transaction::where('voucher_id', $salesReturn->replacement_voucher_id)->delete();
                Voucher::where('id', $salesReturn->replacement_voucher_id)->delete();
            }

            // 4. Delete Items and Return
            SalesReturnItem::where('sales_return_id', $salesReturn->id)->delete();
            $salesReturn->delete();

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
