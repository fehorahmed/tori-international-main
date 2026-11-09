<?php

namespace App\Http\Controllers\StockConversion;

use App\Http\Controllers\Controller;
use App\Models\Account\AccountGroup;
use Illuminate\Http\Request;
use App\Models\StockConversion\StockConversion;
use App\Models\StockConversion\StockConversionItem;
use App\Models\Settings\Warehouse;
use App\Models\Settings\Product;
use App\Models\Inventory\Inventory;
use App\Models\Inventory\InventoryLog;
use App\Models\Account\Voucher;
use App\Models\Account\Transaction;
use App\Enumeration\InventoryLogType;
use App\Enumeration\VoucherType;
use App\Enumeration\TransactionType;
use App\Enumeration\ReservedAccountHead;
use App\Enumeration\ReservedAccountGroup;
use App\Models\Account\AccountHead;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class StockConversionController extends Controller
{
    public function index()
    {
        if (!auth()->user()->hasPermissionTo('stock_conversion')) {
            abort(403, 'Unauthorized');
        }
        return view('stock_conversion.index');
    }

    public function dataTable()
    {
        if (!auth()->user()->hasPermissionTo('stock_conversion')) {
            abort(403, 'Unauthorized');
        }

        $user = auth()->user();
        $query = StockConversion::with('warehouse', 'sisterConcern', 'items', 'vouchers')
            ->where('sister_concern_id', $user->sister_concern_id);

        if ($user->role === 'Branch_Manager' && $user->sister_concern_id) {
            $branchWarehouse = Warehouse::where('sister_concern_id', $user->sister_concern_id)
                ->where('type', 'main')
                ->first();
            if ($branchWarehouse) {
                $query->where('warehouse_id', $branchWarehouse->id);
            }
        }

        if (request()->has('start_date') && request('end_date') != '') {
            $query->whereBetween('date', [Carbon::parse(request('start_date'))->format('Y-m-d'), Carbon::parse(request('end_date'))->format('Y-m-d')]);
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('warehouse_name', function (StockConversion $conversion) {
                return $conversion->warehouse->name ?? 'N/A';
            })
            ->addColumn('status', function (StockConversion $conversion) {
                if ($conversion->status == 1) {
                    return '<span class="badge badge-warning">Pending</span>';
                } elseif ($conversion->status == 2) {
                    return '<span class="badge badge-success">Approved</span>';
                } else {
                    return '<span class="badge badge-danger">Cancelled</span>';
                }
            })
            ->addColumn('action', function (StockConversion $conversion) {
                $btn = '';
                if ($conversion->status == 1) {
                    if (auth()->user()->hasPermissionTo('stock_transfer_create')) {
                        $btn .= '<a href="' . route('stock-conversions.edit', $conversion->id) . '" class="btn btn-info btn-sm bg-gradient-info"><i class="fa fa-edit"></i></a> ';
                    }
                }
                if (auth()->user()->hasPermissionTo('stock_conversion')) {
                    $btn .= '<a href="' . route('stock-conversions.show', $conversion->id) . '" class="btn btn-info btn-sm bg-gradient-info"><i class="fa fa-eye"></i></a> ';
                }
                if ($conversion->status == 2 && auth()->user()->hasPermissionTo('stock_conversion') && $conversion->vouchers) {
                    foreach ($conversion->vouchers as $voucher) {
                        $btn .= ' <a href="' . route('voucher.details', ['voucher' => $voucher->id, 'voucher_type' => $voucher->voucher_type]) . '" class="btn btn-info bg-gradient-info btn-sm" target="_blank"><i class="fa fa-file-invoice"></i> ' . $voucher->voucher_no . '</a>';
                    }
                }
                if ($conversion->status == 1 && auth()->user()->hasPermissionTo('stock_conversion_delete')) {
                    $btn .= '<a role="button" data-id="' . $conversion->id . '" class="btn btn-danger btn-sm btn-delete"><i class="fa fa-trash"></i></a>';
                }
                return $btn;
            })
            ->rawColumns(['status', 'action'])
            ->toJson();
    }

    public function create()
    {
        if (!auth()->user()->hasPermissionTo('stock_conversion_create')) {
            abort(403, 'Unauthorized');
        }
        $user = auth()->user();
        $products = Product::with('brand', 'category')->get();
        $warehouses = Warehouse::with('sisterConcern')->where('sister_concern_id', $user->sister_concern_id)
            ->where('type', 'main')
            ->where('status', 1)
            ->get();

        return view('stock_conversion.create', compact('products', 'warehouses'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('stock_conversion_create')) {
            abort(403, 'Unauthorized');
        }

        $rules = [
            'warehouse_id' => 'required',
            'date' => 'required|date',
            'from_product_id.*' => 'required',
            'from_inventory_id.*' => 'required',
            'to_product_id.*' => 'required',
            'quantity.*' => 'required|numeric|min:0.01',
            'unit_conversion_cost.*' => 'nullable|numeric|min:0',
        ];
        $request->validate($rules);

        if (!$request->from_product_id) {
            return redirect()->back()->with('error', 'Please add at least one product to convert')->withInput();
        }

        foreach ($request->from_product_id as $key => $fromProductId) {
            if ($fromProductId == $request->to_product_id[$key]) {
                return redirect()->back()->with('error', 'From Product and To Product cannot be the same.')->withInput();
            }
            $inventory = Inventory::where('id', $request->from_inventory_id[$key])->first();
            if (!$inventory) {
                return redirect()->back()->with('error', 'Inventory not found for product id: ' . $fromProductId)->withInput();
            }
            if ($inventory->quantity < $request->quantity[$key]) {
                return redirect()->back()->with('error', 'Insufficient stock for batch: ' . $inventory->batch_no)->withInput();
            }
        }

        DB::beginTransaction();
        try {
            $warehouse = Warehouse::findOrFail($request->warehouse_id);

            $conversion = new StockConversion();
            $conversion->sister_concern_id = $warehouse->sister_concern_id;
            $conversion->warehouse_id = $warehouse->id;

            $prefix = 'CONV-';
            $max = DB::table('stock_conversions')
                ->where('sister_concern_id', auth()->user()->activeConcernId())
                ->where('order_no', 'LIKE', $prefix . '%')
                ->selectRaw("MAX(CAST(SUBSTRING(order_no, LENGTH('$prefix') + 1) AS UNSIGNED)) as max_number")
                ->value('max_number');
            $next = $max ? $max + 1 : 1;
            $conversion->order_no = $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
            
            $conversion->date = Carbon::parse($request->date);
            $conversion->notes = $request->notes;
            $conversion->status = 1; // Pending
            $conversion->create_user_id = auth()->id();
            $conversion->save();

            if (!$request->from_product_id) {
                throw new \Exception('Please add at least one product to convert');
            }

            foreach ($request->from_product_id as $key => $fromProductId) {
                $inventory = Inventory::where('id', $request->from_inventory_id[$key])->first();
                if (!$inventory) {
                    throw new \Exception('Inventory not found for product id: ' . $fromProductId);
                }

                if ($fromProductId == $request->to_product_id[$key]) {
                    throw new \Exception('From Product and To Product cannot be the same.');
                }

                if ($inventory->quantity < $request->quantity[$key]) {
                    throw new \Exception('Insufficient stock for batch: ' . $inventory->batch_no);
                }

                $fromUnitPrice = $inventory->unit_price;
                $conversionCost = $request->unit_conversion_cost[$key] ?? 0;
                $toUnitPrice = $fromUnitPrice + $conversionCost;

                $item = new StockConversionItem();
                $item->stock_conversion_id = $conversion->id;
                $item->from_product_id = $fromProductId;
                $item->from_inventory_id = $inventory->id;
                $item->quantity = $request->quantity[$key];
                $item->from_unit_price = $fromUnitPrice;
                $item->to_product_id = $request->to_product_id[$key];
                $item->unit_conversion_cost = $conversionCost;
                $item->to_unit_price = $toUnitPrice;
                $item->save();
            }

            DB::commit();
            return redirect()->route('stock-conversions.index')->with('success', 'Stock Conversion saved and pending for approval.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function show($id)
    {
        $conversion = StockConversion::with('warehouse', 'sisterConcern', 'items.fromProduct', 'items.toProduct', 'items.fromInventory')->findOrFail($id);
        return view('stock_conversion.details', compact('conversion'));
    }

    public function edit($id)
    {
        abort_if(!auth()->user()->hasPermissionTo('stock_conversion_edit'), 403, 'Unauthorized');
        $conversion = StockConversion::with('items')->findOrFail($id);
        if ($conversion->status != 1) {
            return redirect()->back()->with('error', 'Only pending conversions can be edited.');
        }

        $user = auth()->user();
        $products = Product::with('brand', 'category')->get();
        $warehouses = Warehouse::with('sisterConcern')->where('sister_concern_id', $user->sister_concern_id)
            ->where('type', 'main')
            ->where('status', 1)
            ->get();

        return view('stock_conversion.edit', compact('conversion', 'products', 'warehouses'));
    }

    public function update(Request $request, $id)
    {
        abort_if(!auth()->user()->hasPermissionTo('stock_conversion_edit'), 403, 'Unauthorized');
        $conversion = StockConversion::findOrFail($id);
        if ($conversion->status != 1) {
            return redirect()->back()->with('error', 'Only pending conversions can be updated.');
        }

        $rules = [
            'warehouse_id' => 'required',
            'date' => 'required|date',
            'from_product_id.*' => 'required',
            'from_inventory_id.*' => 'required',
            'to_product_id.*' => 'required',
            'quantity.*' => 'required|numeric|min:0.01',
            'unit_conversion_cost.*' => 'nullable|numeric|min:0',
        ];
        $request->validate($rules);

        if (!$request->from_product_id) {
            return redirect()->back()->with('error', 'Please add at least one product to convert')->withInput();
        }

        foreach ($request->from_product_id as $key => $fromProductId) {
            if ($fromProductId == $request->to_product_id[$key]) {
                return redirect()->back()->with('error', 'From Product and To Product cannot be the same.')->withInput();
            }
            $inventory = Inventory::where('id', $request->from_inventory_id[$key])->first();
            if (!$inventory) {
                return redirect()->back()->with('error', 'Inventory not found for product id: ' . $fromProductId)->withInput();
            }
            if ($inventory->quantity < $request->quantity[$key]) {
                return redirect()->back()->with('error', 'Insufficient stock for batch: ' . $inventory->batch_no)->withInput();
            }
        }

        DB::beginTransaction();
        try {
            $warehouse = Warehouse::findOrFail($request->warehouse_id);
            $conversion->warehouse_id = $warehouse->id;
            $conversion->date = Carbon::parse($request->date);
            $conversion->notes = $request->notes;
            $conversion->save();

            // Clear old items and insert new
            StockConversionItem::where('stock_conversion_id', $conversion->id)->delete();

            if (!$request->from_product_id) {
                throw new \Exception('Please add at least one product to convert');
            }

            foreach ($request->from_product_id as $key => $fromProductId) {
                $inventory = Inventory::where('id', $request->from_inventory_id[$key])->first();
                if (!$inventory) {
                    throw new \Exception('Inventory not found for product id: ' . $fromProductId);
                }

                if ($fromProductId == $request->to_product_id[$key]) {
                    throw new \Exception('From Product and To Product cannot be the same.');
                }

                if ($inventory->quantity < $request->quantity[$key]) {
                    throw new \Exception('Insufficient stock for batch: ' . $inventory->batch_no);
                }

                $fromUnitPrice = $inventory->unit_price;
                $conversionCost = $request->unit_conversion_cost[$key] ?? 0;
                $toUnitPrice = $fromUnitPrice + $conversionCost;

                $item = new StockConversionItem();
                $item->stock_conversion_id = $conversion->id;
                $item->from_product_id = $fromProductId;
                $item->from_inventory_id = $inventory->id;
                $item->quantity = $request->quantity[$key];
                $item->from_unit_price = $fromUnitPrice;
                $item->to_product_id = $request->to_product_id[$key];
                $item->unit_conversion_cost = $conversionCost;
                $item->to_unit_price = $toUnitPrice;
                $item->save();
            }

            DB::commit();
            return redirect()->route('stock-conversions.index')->with('success', 'Stock Conversion updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function approve($id)
    {
        abort_if(!auth()->user()->hasPermissionTo('stock_conversion_approve'), 403, 'Unauthorized');
        $conversion = StockConversion::with('items')->findOrFail($id);
        if ($conversion->status != 1) {
            return redirect()->back()->with('error', 'Only pending conversions can be approved.');
        }

        foreach ($conversion->items as $item) {
            $checkInventory = Inventory::where('id', $item->from_inventory_id)->first();
            if (!$checkInventory) {
                return redirect()->back()->with('error', 'Inventory record not found for product: ' . ($item->fromProduct->name ?? ''));
            }
            if ($checkInventory->quantity < $item->quantity) {
                return redirect()->back()->with('error', 'Insufficient stock for product: ' . ($item->fromProduct->name ?? '') . ' (Batch: ' . $checkInventory->batch_no . '). Available: ' . $checkInventory->quantity);
            }
        }

        DB::beginTransaction();
        try {
            $sisterConcernId = $conversion->sister_concern_id;
            $totalCostOld = 0;
            $totalCostNew = 0;
            $totalConversionCost = 0;

            foreach ($conversion->items as $item) {
                $fromInventory = Inventory::where('id', $item->from_inventory_id)->lockForUpdate()->first();
                if (!$fromInventory || $fromInventory->quantity < $item->quantity) {
                    throw new \Exception('Insufficient stock for one or more products during approval.');
                }

                // Inventory Out (From Product)
                $fromInventory->quantity -= $item->quantity;
                $fromInventory->save();

                $logOut = new InventoryLog();
                $logOut->inventory_id = $fromInventory->id;
                $logOut->product_id = $fromInventory->product_id;
                $logOut->product_code = $fromInventory->product_code;
                $logOut->warehouse_id = $conversion->warehouse_id;
                $logOut->sister_concern_id = $sisterConcernId;
                $logOut->stock_conversion_id = $conversion->id;
                $logOut->quantity = $item->quantity;
                $logOut->unit_price = $fromInventory->unit_price;
                $logOut->batch_no = $fromInventory->batch_no;
                $logOut->date = $conversion->date;
                $logOut->type = InventoryLogType::$STOCK_CONVERSION_OUT; // Out
                $logOut->notes = 'Conversion Out (Order No: ' . $conversion->order_no . ')';
                $logOut->user_id = auth()->id();
                $logOut->save();

                // Inventory In (To Product)
                $toInventory = Inventory::where('sister_concern_id', $sisterConcernId)
                    ->where('product_id', $item->to_product_id)
                    ->where('warehouse_id', $conversion->warehouse_id)
                    ->where('unit_price', $item->to_unit_price)
                    ->first();

                $batch_no = null;
                if (!$toInventory) {
                    $latest_inventory = Inventory::where('sister_concern_id', $sisterConcernId)
                        ->where('product_id', $item->to_product_id)
                        ->where('warehouse_id', $conversion->warehouse_id)
                        ->latest('id')
                        ->first();

                    $batch_no = $latest_inventory ? intval($latest_inventory->batch_no) + 1 : 1;
                    $toProductCode = $item->toProduct->code ?? '';

                    $toInventory = new Inventory();
                    $toInventory->product_id = $item->to_product_id;
                    $toInventory->product_code = $toProductCode;
                    $toInventory->warehouse_id = $conversion->warehouse_id;
                    $toInventory->sister_concern_id = $sisterConcernId;
                    $toInventory->inventory_type = 1; // 1 means typical inventory entry, aligning with Purchase
                    $toInventory->quantity = $item->quantity;
                    $toInventory->unit_price = $item->to_unit_price;
                    $toInventory->batch_no = $batch_no;
                    $toInventory->save();
                } else {
                    $batch_no = $toInventory->batch_no;
                    $toProductCode = $toInventory->product_code;
                    $toInventory->save();
                    $toInventory->increment('quantity', $item->quantity);
                }

                $logIn = new InventoryLog();
                $logIn->inventory_id = $toInventory->id;
                $logIn->product_id = $toInventory->product_id;
                $logIn->product_code = $toProductCode;
                $logIn->warehouse_id = $conversion->warehouse_id;
                $logIn->sister_concern_id = $sisterConcernId;
                $logIn->stock_conversion_id = $conversion->id;
                $logIn->quantity = $item->quantity;
                $logIn->unit_price = $item->to_unit_price;
                $logIn->batch_no = $batch_no;
                $logIn->date = $conversion->date;
                $logIn->type = InventoryLogType::$STOCK_CONVERSION_IN; // In
                $logIn->notes = 'Conversion In (Order No: ' . $conversion->order_no . ')';
                $logIn->user_id = auth()->id();
                $logIn->save();

                $totalCostOld += ($item->from_unit_price * $item->quantity);
                $totalCostNew += ($item->to_unit_price * $item->quantity);
                $totalConversionCost += ($item->unit_conversion_cost * $item->quantity);
            }

            // Accounting Entries
            $inventoryHead = AccountHead::where('reserved_id', ReservedAccountHead::FINISHED_GOODS_INVENTORY)
                ->first();

            // Need an expense head for Conversion Cost. 
            // Often, it's better to find or create one if it doesn't exist, but we can assume we'll just search for one,
            // or create it on the fly if needed under Direct Expense. 
            // For now, if $totalConversionCost > 0, we create/find "Conversion Expense".
            
            $voucher = new Voucher();
            $voucher->sister_concern_id = $sisterConcernId;
            $voucher->voucher_type = VoucherType::$JOURNAL_VOUCHER;
            $voucher->amount = $totalCostNew; // Total debit amount
            $voucher->date = $conversion->date;
            $voucher->stock_conversion_id = $conversion->id;
            
            $voucherNo = generateVoucherNo(VoucherType::$JOURNAL_VOUCHER, null, $sisterConcernId);
            $voucherNoGroupSl = abs((int)filter_var($voucherNo, FILTER_SANITIZE_NUMBER_INT));
            
            $voucher->voucher_no = $voucherNo;
            $voucher->voucher_no_group_sl = $voucherNoGroupSl;
            $voucher->account_head_payee_depositor_id = $inventoryHead->id; // Set Party Name to Inventory Head
            $voucher->notes = 'Stock Conversion: ' . $conversion->order_no;
            $voucher->user_id = auth()->id();
            $voucher->save();

            // Debit: Inventory (New Value)
            $txDebit = new Transaction();
            $txDebit->sister_concern_id = $sisterConcernId;
            $txDebit->account_head_id = $inventoryHead->id;
            $txDebit->voucher_id = $voucher->id;
            $txDebit->voucher_type = VoucherType::$JOURNAL_VOUCHER;
            $txDebit->transaction_type = TransactionType::$DEBIT;
            $txDebit->amount = $totalCostNew;
            $txDebit->date = $conversion->date;
            $txDebit->stock_conversion_id = $conversion->id;
            $txDebit->voucher_no = $voucher->voucher_no;
            $txDebit->voucher_no_group_sl = $voucher->voucher_no_group_sl;
            $txDebit->notes = $voucher->notes;
            $txDebit->user_id = auth()->id();
            $txDebit->save();

            // Credit: Inventory (Old Value)
            $txCreditInv = new Transaction();
            $txCreditInv->sister_concern_id = $sisterConcernId;
            $txCreditInv->account_head_id = $inventoryHead->id;
            $txCreditInv->voucher_id = $voucher->id;
            $txCreditInv->voucher_type = VoucherType::$JOURNAL_VOUCHER;
            $txCreditInv->transaction_type = TransactionType::$CREDIT;
            $txCreditInv->amount = $totalCostOld;
            $txCreditInv->date = $conversion->date;
            $txCreditInv->stock_conversion_id = $conversion->id;
            $txCreditInv->voucher_no = $voucher->voucher_no;
            $txCreditInv->voucher_no_group_sl = $voucher->voucher_no_group_sl;
            $txCreditInv->notes = $voucher->notes;
            $txCreditInv->user_id = auth()->id();
            $txCreditInv->save();

            if ($totalConversionCost > 0) {
                // Find or create "Conversion Expense" head under Operating Expenses
                $directExpGroup = AccountGroup::where('reserved_id', ReservedAccountGroup::OPERATING_EXPENSES)->first();
                    
                if (!$directExpGroup) {
                    throw new \Exception('Operating Expenses account group not found.');
                }
                    
                $conversionExpHead = AccountHead::where('name', 'Conversion Expense')
                    ->first();
                    
                if (!$conversionExpHead && $directExpGroup) {
                    $conversionExpHead = new AccountHead();
                    $conversionExpHead->sister_concern_id = null;
                    $conversionExpHead->account_group_id = $directExpGroup->id;
                    $conversionExpHead->name = 'Conversion Expense';
                    $conversionExpHead->code = generateAccountCode($directExpGroup->top_reserved_id);
                    $conversionExpHead->nature = 'Debit';
                    $conversionExpHead->is_system = 1;
                    $conversionExpHead->save();
                }

                // If it's a cost we are allocating INTO inventory, we credit the expense account (or a payable).
                // Usually, you debit Inventory, credit Expense/Cost of Goods Manufactured to capitalize it.
                // Or credit Cash/Bank/Payable if paid directly. For simplicity, we credit Conversion Expense to offset the capitalization.
                $txCreditExp = new Transaction();
                $txCreditExp->sister_concern_id = $sisterConcernId;
                $txCreditExp->account_head_id = $conversionExpHead->id;
                $txCreditExp->voucher_id = $voucher->id;
                $txCreditExp->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                $txCreditExp->transaction_type = TransactionType::$CREDIT;
                $txCreditExp->amount = $totalConversionCost;
                $txCreditExp->date = $conversion->date;
                $txCreditExp->stock_conversion_id = $conversion->id;
                $txCreditExp->voucher_no = $voucher->voucher_no;
                $txCreditExp->voucher_no_group_sl = $voucher->voucher_no_group_sl;
                $txCreditExp->notes = 'Capitalized Conversion Cost for ' . $conversion->order_no;
                $txCreditExp->user_id = auth()->id();
                $txCreditExp->save();
            }

            $conversion->status = 2; // Approved
            $conversion->approved_user_id = auth()->id();
            $conversion->save();

            DB::commit();
            return redirect()->route('stock-conversions.index')->with('success', 'Stock Conversion Approved Successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function destroy($id)
    {
        abort_if(!auth()->user()->hasPermissionTo('stock_conversion_delete'), 403, 'Unauthorized');
        // For deleting pending only. We don't implement full reverse yet via destroy for safety.
        $conversion = StockConversion::findOrFail($id);
        if ($conversion->status != 1) {
            return response()->json(['success' => false, 'message' => 'Only pending conversions can be deleted.']);
        }

        try {
            StockConversionItem::where('stock_conversion_id', $conversion->id)->delete();
            $conversion->delete();
            return response()->json(['success' => true, 'message' => 'Conversion deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
