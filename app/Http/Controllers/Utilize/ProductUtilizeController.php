<?php

namespace App\Http\Controllers\Utilize;

use App\Enumeration\InventoryLogType;
use App\Enumeration\ReservedAccountHead;
use App\Enumeration\TransactionType;
use App\Enumeration\VoucherType;
use App\Http\Controllers\Controller;
use App\Models\Account\AccountHead;
use App\Models\Inventory\Inventory;
use App\Models\Inventory\InventoryLog;
use App\Models\Settings\Product;
use App\Models\Project\Project;
use App\Models\Project\ProjectSegment;
use App\Models\Project\ProjectSubSegment;
use App\Models\PurchaseRequisition\PurchaseRequisition;
use App\Models\Account\Transaction;
use App\Models\Utilize\ProductUtilize;
use App\Models\Utilize\ProductUtilizeItem;
use App\Models\Account\Voucher;
use App\Models\Settings\Warehouse;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ProductUtilizeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        abort_if(!auth()->user()->hasPermissionTo('product_utilize_list'), 403, 'Unauthorized');
        return view('product_utilize.index');
    }
    public function dataTable()
    {
        abort_if(!auth()->user()->hasPermissionTo('product_utilize_list'), 403, 'Unauthorized');
        
        $query = ProductUtilize::with( 'warehouse')->where('sister_concern_id',auth()->user()->activeConcernId());

        if (request()->has('start_date') && request('end_date') != '') {
            $query->whereBetween('date', [Carbon::parse(request('start_date'))->format('Y-m-d'), Carbon::parse(request('end_date'))->format('Y-m-d')]);
        }

        return DataTables::eloquent($query)
            ->addColumn('action', function (ProductUtilize $product_utilize) {
                $btn = '';

                if (auth()->user()->hasPermissionTo('product_utilize_edit')) {
                    $btn .= ' <a href="' . route('product-utilizes.edit', ['product_utilize' => $product_utilize->id]) . '" class="btn btn-info bg-gradient-info btn-sm"><i class="fa fa-edit"></i></a>';
                }
                if (auth()->user()->hasPermissionTo('product_utilize_list')) {
                    $btn .= ' <a href="' . route('product-utilizes.details', ['product_utilize' => $product_utilize->id]) . '" class="btn btn-info bg-gradient-info btn-sm"><i class="fa fa-info-circle"></i></a>';
                }
                if (auth()->user()->hasPermissionTo('product_utilize_list') && $product_utilize->voucher) {
                    $btn .= ' <a href="' . route('voucher.details', ['voucher' => $product_utilize->voucher->id,'voucher_type'=>$product_utilize->voucher->voucher_type]) . '" class="btn btn-info bg-gradient-info btn-sm">JV <i class="fa fa-info-circle"></i></a>';
                }
                if(auth()->user()->can('product_utilize_delete')) {
                    $btn .= ' <a role="button" data-id="' . $product_utilize->id . '" class="btn btn-danger btn-sm btn-delete"><i class="fa fa-trash"></i></a>';
                }
                return dropdownMenuContainer($btn);
            })
            ->addColumn('warehouse_name', function (ProductUtilize $product_utilize) {
                return $product_utilize->warehouse->name ?? '';
            })
            ->addColumn('total', function (ProductUtilize $product_utilize) {
                $total = $product_utilize->productUtilizeItems->sum(function ($item) {
                    return $item->quantity * $item->unit_price;
                });
                return $total;
            })
            ->rawColumns(['action'])
            ->toJson();
    }
    public function details(ProductUtilize $product_utilize)
    {
        abort_if(!auth()->user()->hasPermissionTo('product_utilize_list'), 403, 'Unauthorized');

        $product_utilize->load('productUtilizeItems');
        return view('product_utilize.details', compact('product_utilize'));
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('product_utilize_create'), 403, 'Unauthorized');
        $warehouses = Warehouse::where('sister_concern_id',auth()->user()->activeConcernId())->where('type', 'main')->where('status',1)->get();
        return view('product_utilize.create',compact('warehouses'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('product_utilize_create'), 403, 'Unauthorized');

        // Validate the request data
        $rules = [
            'warehouse' => 'required',
            'product_qty.*' => 'required|numeric',
            'product_unit_price.*' => 'required|numeric',
            'date' => 'required|date',
            'notes' => 'nullable|string|max:255',
        ];
        $request->validate($rules);

        
        // Start a database transaction
        DB::beginTransaction();

        try {
            //Check Stock
            foreach ($request->inventory_id as $key => $inventoryId) {
                $inventory = Inventory::find($inventoryId);
    
                if (!$inventory) {
                    throw new \Exception("No available stock record !");
                } else {
                    if ($inventory->quantity < $request->product_qty[$key]) {
                        throw new \Exception($inventory->product->name." insufficient stock, due quantity: $inventory->quantity !");
                    }
                }
            }

            $productUtilize = new ProductUtilize();
            $productUtilize->sister_concern_id = auth()->user()->activeConcernId();
            $productUtilize->warehouse_id = $request->warehouse;
            $productUtilize->notes = $request->notes;
            $productUtilize->user_id = auth()->id();
            $productUtilize->date = Carbon::parse($request->date);
            $productUtilize->order_no = generateOrderNo('product_utilizes','order_no','PUO-');
            $productUtilize->save();

            $total = 0;
            foreach ($request->inventory_id as $key => $inventoryId) {
                $inventory = Inventory::find($inventoryId);
                $product = $inventory->product;

                if (!$inventory) {
                    throw new \Exception("No available stock record !");
                } else {
                    if ($inventory->quantity < $request->product_qty[$key]) {
                        throw new \Exception($product->name." insufficient stock, due quantity: $inventory->quantity !");
                    }
                }

                $productCode = $product->code;
                $productUtilizeItem = new ProductUtilizeItem();
                $productUtilizeItem->sister_concern_id = auth()->user()->activeConcernId();
                $productUtilizeItem->product_utilize_id = $productUtilize->id;
                $productUtilizeItem->product_id = $product->id;
                $productUtilizeItem->quantity = $request->product_qty[$key];
                $productUtilizeItem->unit_price = $request->product_unit_price[$key];
                $productUtilizeItem->save();

                $total += ($request->product_qty[$key] * $request->product_unit_price[$key]);

                //Inventory Decrement
                $inventory->decrement('quantity', $request->product_qty[$key]);

                //Inventory Log
                $inventoryLog = new InventoryLog();
                $inventoryLog->sister_concern_id = auth()->user()->activeConcernId();
                $inventoryLog->type = InventoryLogType::$PRODUCT_UTILIZED;
                $inventoryLog->warehouse_id = $request->warehouse;
                $inventoryLog->inventory_id = $inventory->id;
                $inventoryLog->product_utilize_id = $productUtilize->id;
                $inventoryLog->product_id = $product->id;
                $inventoryLog->quantity = $request->product_qty[$key];
                $inventoryLog->unit_price = $request->product_unit_price[$key];
                $inventoryLog->product_code = $productCode;
                $inventoryLog->notes = $request->notes;
                $inventoryLog->user_id = auth()->id();
                $inventoryLog->date = Carbon::parse($request->date);
                $inventoryLog->save();

            }

            $request['notes'] = $request->notes != ''
                ? $request->notes .' - Product utilized'
                : 'Product utilized';

            //Journal Voucher
            $payeeId = null;

            $voucherNo = generateVoucherNo(VoucherType::$JOURNAL_VOUCHER);
            $voucherNoGroupSl = abs((int)filter_var($voucherNo, FILTER_SANITIZE_NUMBER_INT));


            $voucher = new Voucher();
            $voucher->sister_concern_id = auth()->user()->activeConcernId();
            $voucher->voucher_type = VoucherType::$JOURNAL_VOUCHER;
            $voucher->amount = $total;
            $voucher->date = Carbon::parse($request->date)->format('Y-m-d');
            $voucher->voucher_no_group_sl = $voucherNoGroupSl;
            $voucher->voucher_no = $voucherNo;
            $voucher->account_head_payee_depositor_id = $payeeId;
            $voucher->product_utilize_id = $productUtilize->id;
            $voucher->notes = $request->notes;
            $voucher->user_id = auth()->id();
            $voucher->save();

            //Debit
            $transaction = new Transaction();
            $transaction->sister_concern_id = auth()->user()->activeConcernId();
            $transaction->voucher_id = $voucher->id;
            $transaction->account_head_id = reservedAccountHeadId(ReservedAccountHead::COST_OF_GOODS_SOLD);
            $transaction->voucher_type = VoucherType::$JOURNAL_VOUCHER;
            $transaction->transaction_type = TransactionType::$DEBIT;
            $transaction->amount = $total;
            $transaction->date = Carbon::parse($request->date)->format('Y-m-d');
            $transaction->voucher_no_group_sl = $voucherNoGroupSl;
            $transaction->voucher_no = $voucherNo;
            $transaction->account_head_payee_depositor_id = $payeeId;
            $transaction->product_utilize_id = $productUtilize->id;
            $transaction->notes = $request->notes;
            $transaction->user_id = auth()->id();
            $transaction->save();

            //Credit
            $transaction = new Transaction();
            $transaction->sister_concern_id = auth()->user()->activeConcernId();
            $transaction->voucher_id = $voucher->id;
            $transaction->account_head_id = reservedAccountHeadId(ReservedAccountHead::FINISHED_GOODS_INVENTORY);
            $transaction->voucher_type = VoucherType::$JOURNAL_VOUCHER;
            $transaction->transaction_type = TransactionType::$CREDIT;
            $transaction->amount = $total;
            $transaction->date = Carbon::parse($request->date)->format('Y-m-d');
            $transaction->voucher_no_group_sl = $voucherNoGroupSl;
            $transaction->voucher_no = $voucherNo;
            $transaction->account_head_payee_depositor_id = $payeeId;
            $transaction->product_utilize_id = $productUtilize->id;
            $transaction->notes = $request->notes;
            $transaction->user_id = auth()->id();
            $transaction->save();

            // Commit the transaction
            DB::commit();

            // Redirect to the index page with a success message
            return redirect()->route('product-utilizes.details', ['product_utilize' => $productUtilize->id])
                ->with('success', 'Product utilized successfully');
        } catch (\Exception $e) {
            // Roll back the transaction in case of an error
            DB::rollback();
            // Handle the error and redirect with an error message
            return redirect()->back()->withInput()
                ->withInput()->with('error',$e->getMessage().' - '.$e->getLine());
        }
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProductUtilize $product_utilize)
    {
        abort_if(!auth()->user()->hasPermissionTo('product_utilize_edit'), 403, 'Unauthorized');
        
        $product_utilize->load('productUtilizeItems.product', 'productUtilizeItems.inventoryLog.inventory');
        $warehouses = Warehouse::where('sister_concern_id',auth()->user()->activeConcernId())->where('type', 'main')->where('status',1)->get();
         return view('product_utilize.edit',compact('product_utilize','warehouses'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProductUtilize $product_utilize)
    {
        abort_if(!auth()->user()->hasPermissionTo('product_utilize_edit'), 403, 'Unauthorized');

        // Validate the request data
        $rules = [
            'product_qty.*' => 'required|numeric',
            'product_unit_price.*' => 'required|numeric',
            'date' => 'required|date',
            'notes' => 'nullable|string|max:255',
        ];
        $request->validate($rules);
        // Start a database transaction
        DB::beginTransaction();
        // dd($request->all());
        try {
            // Check Stock before any changes
            $newItems = [];
            foreach ($request->inventory_id as $key => $inventoryId) {
                $newItems[$inventoryId] = ($newItems[$inventoryId] ?? 0) + $request->product_qty[$key];
            }

            foreach ($newItems as $inventoryId => $newQty) {
                $inventory = Inventory::find($inventoryId);
                if (!$inventory) {
                    throw new \Exception("No available stock record !");
                }

                // Calculate available stock (Current stock + what is already utilized in THIS record)
                $existingQty = 0;
                foreach ($product_utilize->productUtilizeItems as $existingItem) {
                    if (($existingItem->inventoryLog->inventory_id ?? null) == $inventoryId) {
                        $existingQty += $existingItem->quantity;
                    }
                }

                $availableStock = $inventory->quantity + $existingQty;

                if ($newQty > $availableStock) {
                    throw new \Exception($inventory->product->name . " insufficient stock, available quantity: $availableStock !");
                }
            }

            //Increment Inventory Quantity for existing items
            foreach ($product_utilize->productUtilizeItems as $existingItem) {
                $inventory = Inventory::find($existingItem->inventoryLog->inventory_id ?? null);
                if ($inventory) {
                    $inventory->increment('quantity', $existingItem->quantity);
                }
            }

            //Remove existing items and logs (they will be recreated)
            ProductUtilizeItem::where('product_utilize_id', $product_utilize->id)->delete();
            InventoryLog::where('product_utilize_id', $product_utilize->id)->delete();

            $productUtilize = $product_utilize;
            $productUtilize->notes = $request->notes;
            $productUtilize->user_id = auth()->id();
            $productUtilize->date = Carbon::parse($request->date);
            $productUtilize->save();

            $total = 0;
            foreach ($request->inventory_id as $key => $inventoryId) {
                $inventory = Inventory::find($inventoryId);
                $product = $inventory->product;

                if (!$inventory) {
                    throw new \Exception("No available stock record !");
                } else {
                    if ($inventory->quantity < $request->product_qty[$key]) {
                        throw new \Exception($product->name." insufficient stock, due quantity: $inventory->quantity !");
                    }
                }

                $productCode = $product->code;
                
                $productUtilizeItem = new ProductUtilizeItem();
                $productUtilizeItem->sister_concern_id = auth()->user()->activeConcernId();
                $productUtilizeItem->product_utilize_id = $productUtilize->id;
                $productUtilizeItem->product_id = $product->id;
                $productUtilizeItem->quantity = $request->product_qty[$key];
                $productUtilizeItem->unit_price = $request->product_unit_price[$key];
                $productUtilizeItem->save();

                $total += ($request->product_qty[$key] * $request->product_unit_price[$key]);

                //Inventory Decrement
                $inventory->decrement('quantity', $request->product_qty[$key]);

                //Inventory Log
                $inventoryLog = new InventoryLog();
                $inventoryLog->sister_concern_id = auth()->user()->activeConcernId();
                $inventoryLog->type = InventoryLogType::$PRODUCT_UTILIZED;
                $inventoryLog->warehouse_id = $request->warehouse;
                $inventoryLog->inventory_id = $inventory->id;
                $inventoryLog->product_utilize_id = $productUtilize->id;
                $inventoryLog->product_id = $product->id;
                $inventoryLog->quantity = $request->product_qty[$key];
                $inventoryLog->unit_price = $request->product_unit_price[$key];
                $inventoryLog->product_code = $productCode;
                $inventoryLog->notes = $request->notes;
                $inventoryLog->user_id = auth()->id();
                $inventoryLog->date = Carbon::parse($request->date);
                $inventoryLog->save();
            }

            $request['notes'] = $request->notes != ''
                ? $request->notes . ' - Product utilized'
                : 'Product utilized';

            $payeeId = null;

            //Updating Voucher
            $voucher = Voucher::where('product_utilize_id',$productUtilize->id)
                ->where('sister_concern_id',auth()->user()->activeConcernId())
                ->where('voucher_type',VoucherType::$JOURNAL_VOUCHER)
                ->first();
            $voucherNoGroupSl =  $voucher->voucher_no_group_sl;
            $voucherNo = $voucher->voucher_no;

            $voucher->voucher_type = VoucherType::$JOURNAL_VOUCHER;
            $voucher->amount = $total;
            $voucher->date = Carbon::parse($request->date)->format('Y-m-d');
            $voucher->voucher_no_group_sl = $voucherNoGroupSl;
            $voucher->voucher_no = $voucherNo;
            $voucher->account_head_payee_depositor_id = $payeeId;
            $voucher->product_utilize_id = $productUtilize->id;
            $voucher->notes = $request->notes;
            $voucher->user_id = auth()->id();
            $voucher->save();

            //Debit
            $transaction = Transaction::where('voucher_id',$voucher->id)->where('transaction_type',TransactionType::$DEBIT)->first();
            $transaction->sister_concern_id = auth()->user()->activeConcernId();
            $transaction->voucher_id = $voucher->id;
            $transaction->account_head_id = reservedAccountHeadId(ReservedAccountHead::COST_OF_GOODS_SOLD);;//Expense: Cost of Goods Sold
            $transaction->voucher_type = VoucherType::$JOURNAL_VOUCHER;
            $transaction->transaction_type = TransactionType::$DEBIT;
            $transaction->amount = $total;
            $transaction->date = Carbon::parse($request->date)->format('Y-m-d');
            $transaction->voucher_no_group_sl = $voucherNoGroupSl;
            $transaction->voucher_no = $voucherNo;
            $transaction->account_head_payee_depositor_id = $payeeId;
            $transaction->product_utilize_id = $productUtilize->id;
            $transaction->notes = $request->notes;
            $transaction->user_id = auth()->id();
            $transaction->save();

            //Credit
            $transaction = Transaction::where('voucher_id',$voucher->id)->where('transaction_type',TransactionType::$CREDIT)
                ->orderBy('id','desc')
                ->first();
            $transaction->voucher_id = $voucher->id;
            $transaction->account_head_id = reservedAccountHeadId(ReservedAccountHead::FINISHED_GOODS_INVENTORY);;//Assets: Decrease in Inventory
            $transaction->voucher_type = VoucherType::$JOURNAL_VOUCHER;
            $transaction->transaction_type = TransactionType::$CREDIT;
            $transaction->amount = $total;
            $transaction->date = Carbon::parse($request->date)->format('Y-m-d');
            $transaction->voucher_no_group_sl = $voucherNoGroupSl;
            $transaction->voucher_no = $voucherNo;
            $transaction->account_head_payee_depositor_id = $payeeId;
            $transaction->product_utilize_id = $productUtilize->id;
            $transaction->notes = $request->notes;
            $transaction->user_id = auth()->id();
            $transaction->save();

            // Commit the transaction
            DB::commit();

            // Redirect to the index page with a success message
            return redirect()->route('product-utilizes.details', ['product_utilize' => $productUtilize->id])
                ->with('success', 'Product utilized updated successfully');
        } catch (\Exception $e) {
            // Roll back the transaction in case of an error
            DB::rollback();
            // Handle the error and redirect with an error message
            return redirect()->back()->withInput()
                ->withInput()->with('error',$e->getMessage().' - '.$e->getLine());
        }
    }

    /**
     * Remove the specified resource from storage.
     */

    public function destroy(ProductUtilize $productUtilize)
    {
        abort_if(!auth()->user()->hasPermissionTo('product_utilize_delete'), 403, 'Unauthorized');
        
        DB::beginTransaction();
        try {
            foreach ($productUtilize->productUtilizeItems as $productUtilizeItem){
                $inventory = Inventory::where('project_id',$productUtilize->project_id)
                    ->where('warehouse_id',$productUtilize->warehouse_id)
                    ->where('product_id',$productUtilizeItem->product_id)
                    ->first();
                if ($inventory){
                    $inventory->increment('quantity', $productUtilizeItem->quantity);
                }
            }

            //Deleted
            ProductUtilizeItem::where('product_utilize_id',$productUtilize->id)->delete();
            InventoryLog::where('product_utilize_id',$productUtilize->id)->delete();
            Voucher::where('product_utilize_id',$productUtilize->id)->delete();
            Transaction::where('product_utilize_id',$productUtilize->id)->delete();

            $productUtilize->delete();
            // Return a JSON success response
            DB::commit();
            return response()->json(['success'=>true,'message' => 'Product utilize deleted successfully'], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            // Handle any errors, such as record not found
            return response()->json(['success'=>false,'message' =>$e->getMessage()], Response::HTTP_OK);
        }
    }
}
