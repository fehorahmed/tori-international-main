<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;

use App\Enumeration\InventoryLogType;
use App\Enumeration\ReservedAccountHead;
use App\Enumeration\TransactionType;
use App\Enumeration\VoucherType;
use App\Models\Account\AccountHead;
use App\Models\Account\Transaction;
use App\Models\Account\Voucher;
use App\Models\Inventory\Inventory;
use App\Models\Inventory\InventoryLog;
use App\Models\Purchase\PurchaseOrder;
use App\Models\Sales\SaleOrder;
use App\Models\Settings\Product;
use App\Models\Settings\Category;
use App\Models\Settings\Warehouse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('inventory')) {
            abort(403, 'Unauthorized');
        }
        $pageTitle = 'Inventory';
        $warehouses = Warehouse::where('sister_concern_id', auth()->user()->activeConcernId())->where('type', 'main')->where('status', 1)->get();
        $categories = Category::where('status', 1)->orderBy('name')->get();
        return view('inventory.index',compact('pageTitle', 'warehouses', 'categories'));
    }

    public function details(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('inventory_log')) {
            abort(403, 'Unauthorized');
        }
        
        $inventory = Inventory::with('product.unit','warehouse')
                ->where('sister_concern_id', auth()->user()->activeConcernId())
                ->findOrFail($request->inventory);
        $product = $inventory->product;
        
        $inTypes = InventoryLogType::inTypes();

        $startDate = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : Carbon::parse(auth()->user()->business_date)->startOfDay();
        $endDate = $request->end_date ? Carbon::parse($request->end_date)->endOfDay() : Carbon::parse(auth()->user()->business_date)->endOfDay();

        $openingIn = InventoryLog::where('sister_concern_id', auth()->user()->activeConcernId())
            ->where('inventory_id', $inventory->id)
            ->where('date', '<', $startDate)
            ->whereIn('type', $inTypes)
            ->sum('quantity');

        $openingOut = InventoryLog::where('sister_concern_id', auth()->user()->activeConcernId())
            ->where('inventory_id', $inventory->id)
            ->where('date', '<', $startDate)
            ->whereNotIn('type', $inTypes)
            ->sum('quantity');
            
        $openingBalance = $openingIn - $openingOut;

        $logs = InventoryLog::with(['purchaseOrder', 'saleOrder', 'supplier', 'warehouse', 'stockTransfer'])
            ->where('sister_concern_id', auth()->user()->activeConcernId())
            ->where('inventory_id', $inventory->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();
        
        return view('inventory.details',compact('product','inventory','logs','inTypes','openingBalance','startDate','endDate'));
    }
    public function utilizeProduct(Request $request)
    {
        $rules = [
            'inventory_id' => 'required',
        ];
        if ($request->inventory_id != ''){
            $inventory = Inventory::find($request->inventory_id);
          $rules['utilize_quantity'] = 'required|numeric|min:0.01|max:'.$inventory->quantity;
        }

        $request->validate($rules);

        // Start a database transaction
        DB::beginTransaction();

        try {
            if (!auth()->user()->hasPermissionTo('stock_utilized')) {
                abort(403, 'Unauthorized');
            }
            //Stock Product Utilize
            $inventory = Inventory::find($request->inventory_id);
            if ($inventory){

                $utilizeQuantity = $request->utilize_quantity;
                if ($utilizeQuantity > 0){
                    //if stock available
                    $utilizeAmount = $utilizeQuantity * $inventory->unit_price;

                    //Stock Decrement
                    $inventory->decrement('quantity',$utilizeQuantity);

                    //Inventory Log
                    $inventoryLog = new InventoryLog();
                    $inventoryLog->type = InventoryLogType::$FINISHED_GOODS_MANUFACTURED;
                    $inventoryLog->inventory_id = $inventory->id;
                    $inventoryLog->product_id = $inventory->id;
                    $inventoryLog->quantity = $utilizeQuantity;
                    $inventoryLog->unit_price = $inventory->unit_price;
                    $inventoryLog->product_code = $inventory->product->code ?? '';
                    $inventoryLog->notes = 'Utilize stock product';
                    $inventoryLog->user_id = auth()->id();
                    $inventoryLog->date = Carbon::now()->format('Y-m-d');
                    $inventoryLog->save();


                    //Create JV
                    $voucherNoGroupSl = Transaction::withTrashed()->max('voucher_no_group_sl') + 1;
                    $voucherNo = 'JV-' . $voucherNoGroupSl;

                    $voucher = new Voucher();
                    $voucher->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                    $voucher->amount = $utilizeAmount;
                    $voucher->date = Carbon::now()->format('Y-m-d');
                    $voucher->voucher_no_group_sl = $voucherNoGroupSl;
                    $voucher->voucher_no = $voucherNo;
                    $voucher->inventory_log_id = $inventoryLog->id;
                    $voucher->notes = 'Utilize stock product';
                    $voucher->user_id = auth()->id();
                    $voucher->save();

                    //Debit
                    $transaction = new Transaction();
                    $transaction->voucher_id = $voucher->id;
                    $transaction->account_head_id = reservedAccountHeadId(ReservedAccountHead::COST_OF_GOODS_SOLD);
                    $transaction->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                    $transaction->transaction_type = TransactionType::$DEBIT;
                    $transaction->amount = $utilizeAmount;
                    $transaction->date = Carbon::now()->format('Y-m-d');
                    $transaction->voucher_no_group_sl = $voucherNoGroupSl;
                    $transaction->voucher_no = $voucherNo;
                    $transaction->notes = 'Utilize stock product';
                    $transaction->user_id = auth()->id();
                    $transaction->save();

                    //Credit
                    $transaction = new Transaction();
                    $transaction->voucher_id = $voucher->id;
                    $transaction->account_head_id = reservedAccountHeadId(ReservedAccountHead::FINISHED_GOODS_INVENTORY);
                    $transaction->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                    $transaction->transaction_type = TransactionType::$CREDIT;
                    $transaction->amount = $utilizeAmount;
                    $transaction->date = Carbon::now()->format('Y-m-d');
                    $transaction->voucher_no_group_sl = $voucherNoGroupSl;
                    $transaction->voucher_no = $voucherNo;
                    $transaction->notes = 'Utilize stock product';
                    $transaction->user_id = auth()->id();
                    $transaction->save();
                }
            }


            $message = 'Utilized successful';


            // Commit the transaction
            DB::commit();

            // Redirect to the index page with a success message
            return response()->json([
                'status'=>true,
                'message'=>$message,
            ]);
        } catch (\Exception $e) {
            // Roll back the transaction in case of an error
            DB::rollback();

            // Handle the error and redirect with an error message
            return response()->json([
                'status'=>false,
                'message'=>'An error occurred while Creating: '.$e->getMessage(),
            ]);
        }
    }

    public function dataTable()
    {
        $query = Inventory::whereHas('warehouse', function($q) {
            $q->where('type', 'main');
        })->with('product',
            'product.unit','product.brand','product.category','warehouse')
            ->where('sister_concern_id', auth()->user()->activeConcernId());

        $inventoryType = request('inventory_type');
        if (request()->has('warehouse') && request('warehouse') != '') {
            $query->where('warehouse_id', request('warehouse'));
        }
        if (request()->has('category') && request('category') != '') {
            $query->whereHas('product', function($q) {
                $q->where('category_id', request('category'));
            });
        }
        if (request()->has('product') && request('product') != '') {
            $query->where('product_id', request('product'));
        }
        
        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('action', function(Inventory $inventory) use ($inventoryType) {
                $btn = '';

                if ($inventory->quantity > 0){
                    //$btn .= '<button data-id="'.$inventory->id.'" class="btn btn-warning bg-gradient-warning btn-sm btn-utilize">Utilize</button>';
                }
                if(auth()->user()->can('inventory_log')) {
                    $btn .= ' <a href="' . route('inventory.details', ['inventory' => $inventory->id]) . '" class="btn btn-info bg-gradient-info btn-sm"><i class="fa fa-info-circle"></i></a>';
                }
                return $btn;
            })
            ->addColumn('warehouse_name', function(Inventory $inventory) {
                return $inventory->warehouse->name ?? '';
            })
            ->addColumn('product_name', function(Inventory $inventory) {
                return $inventory->product->name ?? '';
            })
            ->editColumn('product_image', function (Inventory $inventory) {
                $preview_html = '';
                $preview_html .= '<div class="zoom-gallery">';
                $preview_html .= '<a class="image_preview" title="'. $inventory->product->name .'" data-source="'. asset(file_exists($inventory->product->thumbs) ? $inventory->product->thumbs : 'img/no-image.webp') .'" href="'. asset(file_exists($inventory->product->thumbs) ? $inventory->product->thumbs : 'img/no-image.webp') .'"><img height="50px" src="'.asset(file_exists($inventory->product->thumbs) ? $inventory->product->thumbs : 'img/no-image.webp').'"></a>';
                // if($product->images){
                //     foreach($product->images as $image){
                //         $preview_html .= '<a class="image_preview" title="'. $product->name .'" data-source="'. asset(file_exists($image->path) ? $image->path : 'img/no-image.webp') .'" href="'. asset(file_exists($image->path) ? $image->path : 'img/no-image.webp') .'"><img height="0px" src="'.asset(file_exists($image->path) ? $image->path : 'img/no-image.webp').'"></a>';
                //     }
                // }
                $preview_html .= '</div>';
                return $preview_html;
            })
            ->addColumn('unit_name', function(Inventory $inventory) {
                return $inventory->product->unit->name ?? '';
            })
            ->rawColumns(['product_image','action'])
            ->toJson();
    }
    public function inventoryLogDataTable()
    {
        $query = InventoryLog::with('supplier','purchaseOrder','product','warehouse', 'saleOrder', 'stockConversion')
            ->where('sister_concern_id', auth()->user()->activeConcernId());
        
        if (request()->has('inventory_id') && request('inventory_id') != ''){
            $query->where('inventory_id',request('inventory_id'));
        }
        return DataTables::eloquent($query)
            ->addColumn('purchase_or_sales_order', function(InventoryLog $inventoryLog) {
                if ($inventoryLog->purchaseOrder){
                    return '<a href="'.route('purchase.details',['purchase'=>$inventoryLog->purchase_order_id]).'" class="btn btn-info bg-gradient-info btn-sm"><i class="fa fa-info-circle"></i> '.($inventoryLog->purchaseOrder->order_no ?? '').'</a>';
                }elseif ($inventoryLog->saleOrder){
                    $route = route('sales.details', ['sale' => $inventoryLog->sale_order_id]);
                    if ($inventoryLog->saleOrder->type == \App\Enumeration\SaleType::$PRE_ORDER) {
                        $route = route('advance-sales.show', ['advance_sale' => $inventoryLog->sale_order_id]);
                    } elseif ($inventoryLog->saleOrder->type == \App\Enumeration\SaleType::$EMI) {
                        $route = route('emi-sales.show', ['emi_sale' => $inventoryLog->sale_order_id]);
                    } elseif ($inventoryLog->saleOrder->type == \App\Enumeration\SaleType::$CORPORATE) {
                        $route = route('corporate-sales.show', ['corporate_sale' => $inventoryLog->sale_order_id]);
                    } elseif ($inventoryLog->saleOrder->type == \App\Enumeration\SaleType::$DEALER) {
                        $route = route('dealer-sales.show', ['dealer_sale' => $inventoryLog->sale_order_id]);
                    }
                    $orderNo = !empty($inventoryLog->saleOrder->memo_no) ? $inventoryLog->saleOrder->memo_no : ($inventoryLog->saleOrder->order_no ?? '');
                    return '<a href="'.$route.'" class="btn btn-info bg-gradient-info btn-sm"><i class="fa fa-info-circle"></i> '.$orderNo.'</a>';
                }elseif ($inventoryLog->productUtilize){
                    return '<a href="'.route('product-utilizes.details',['product_utilize'=>$inventoryLog->product_utilize_id]).'" class="btn btn-info bg-gradient-info btn-sm"><i class="fa fa-info-circle"></i> '.($inventoryLog->productUtilize->order_no ?? '').'</a>';
                }elseif ($inventoryLog->stockTransfer){
                    return '<a href="'.route('stock-transfers.details',['stock_transfer'=>$inventoryLog->stock_transfer_id]).'" class="btn btn-info bg-gradient-info btn-sm"><i class="fa fa-info-circle"></i> '.($inventoryLog->stockTransfer->order_no ?? '').'</a>';
                }elseif ($inventoryLog->stockConversion){
                    return '<a href="'.route('stock-conversions.show',['stock_conversion'=>$inventoryLog->stock_conversion_id]).'" class="btn btn-info bg-gradient-info btn-sm"><i class="fa fa-info-circle"></i> '.($inventoryLog->stockConversion->order_no ?? '').'</a>';
                }
            })
            ->addColumn('warehouse_name', function(InventoryLog $inventoryLog) {
                return $inventoryLog->warehouse->name ?? '';
            })
            ->addColumn('type', function(InventoryLog $inventoryLog) {
                if ($inventoryLog->type == InventoryLogType::$PURCHASE_IN){
                    return '<span class="badge badge-primary">Purchase In</span>';
                }elseif ($inventoryLog->type == InventoryLogType::$SALE_OUT) {
                    return '<span class="badge badge-warning">Sale Out</span>';
                }elseif ($inventoryLog->type == InventoryLogType::$FINISHED_GOODS_MANUFACTURED) {
                    return '<span class="badge badge-success">Finished Goods Manufactured</span>';
                }elseif ($inventoryLog->type == InventoryLogType::$PRODUCT_UTILIZED) {
                    return '<span class="badge badge-danger">Product Utilized</span>';
                }elseif ($inventoryLog->type == InventoryLogType::$STOCK_TRANSFER_OUT) {
                    return '<span class="badge badge-danger">Stock Transferred Out</span>';
                }elseif ($inventoryLog->type == InventoryLogType::$STOCK_TRANSFER_IN) {
                    return '<span class="badge badge-success">Stock Transferred In</span>';
                }elseif ($inventoryLog->type == InventoryLogType::$SALES_RETURN_IN) {
                    return '<span class="badge badge-success">Sales Return In</span>';
                }elseif ($inventoryLog->type == InventoryLogType::$STOCK_CONVERSION_OUT) {
                    return '<span class="badge badge-warning">Stock Conversion Out</span>';
                }elseif ($inventoryLog->type == InventoryLogType::$STOCK_CONVERSION_IN) {
                    return '<span class="badge badge-success">Stock Conversion In</span>';
                }elseif ($inventoryLog->type == InventoryLogType::$OPENING_STOCK) {
                    return '<span class="badge badge-dark">Opening Stock</span>';
                }
            })
            ->addColumn('stock_in', function(InventoryLog $inventoryLog) {
                if (in_array($inventoryLog->type, InventoryLogType::inTypes())) {
                    return $inventoryLog->quantity ?? '';
                }
                return '';
            })
            ->addColumn('stock_out', function(InventoryLog $inventoryLog) {
                if (in_array($inventoryLog->type, InventoryLogType::outTypes())) {
                    return $inventoryLog->quantity ?? '';
                }
                return '';
            })
            ->addColumn('balance_quantity', function(InventoryLog $inventoryLog) {
                return $inventoryLog->balance_quantity ?? '';
            })
            ->addColumn('supplier_name', function(InventoryLog $inventoryLog) {
                return $inventoryLog->supplier->name ?? '';
            })
            ->addColumn('product_name', function(InventoryLog $inventoryLog) {
                return $inventoryLog->product->name ?? '';
            })
            ->rawColumns(['purchase_or_sales_order','type'])
            ->toJson();
    }

    public function getProductsByCategoryGroup(Request $request)
    {
        // keep existing method if any
    }
    
    public function openingStockCreate()
    {
        if (!auth()->user()->hasPermissionTo('inventory')) {
            abort(403, 'Unauthorized');
        }

        // Block if any purchase or sale already exists for this branch
        if (PurchaseOrder::where('sister_concern_id', auth()->user()->activeConcernId())->exists() || 
            SaleOrder::where('sister_concern_id', auth()->user()->activeConcernId())->exists()) {
            return redirect()->route('inventory.index')
                ->with('error', 'Opening Stock entry is not allowed. This branch already has purchase or sale records. Please use the Purchase module to add stock.');
        }

        $warehouses = Warehouse::where('sister_concern_id', auth()->user()->activeConcernId())
            ->where('type', 'main')->where('status', 1)->get();
        
        $existingOpeningStockProductIds = InventoryLog::where('sister_concern_id', auth()->user()->activeConcernId())
            ->where('type', InventoryLogType::$OPENING_STOCK)
            ->pluck('product_id')->toArray();

        $categories = Category::where('status', 1)->orderBy('name')->get();
        $products   = Product::where('status', 1)
            ->whereNotIn('id', $existingOpeningStockProductIds)
            ->orderBy('name')->get();
            
        return view('inventory.opening_stock', compact('warehouses', 'categories', 'products'));
    }

    public function openingStockStore(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('inventory')) {
            abort(403, 'Unauthorized');
        }

        // Block if any purchase or sale already exists for this branch
        if (PurchaseOrder::where('sister_concern_id', auth()->user()->activeConcernId())->exists() || 
            SaleOrder::where('sister_concern_id', auth()->user()->activeConcernId())->exists()) {
            return redirect()->route('inventory.index')
                ->with('error', 'Opening Stock entry is not allowed. This branch already has purchase or sale records.');
        }

        $request->validate([
            'warehouse_id'      => 'required|exists:warehouses,id',
            'date'              => 'required|date',
            'product_id.*'      => 'required|exists:products,id',
            'quantity.*'        => 'required|numeric|min:0.01',
            'unit_price.*'      => 'required|numeric|min:0',
        ]);

        $sisterConcernId = auth()->user()->activeConcernId();

        // Check for duplicates
        foreach ($request->product_id as $productId) {
            $exists = InventoryLog::where('sister_concern_id', $sisterConcernId)
                ->where('product_id', $productId)
                ->where('type', InventoryLogType::$OPENING_STOCK)
                ->exists();
            if ($exists) {
                $product = Product::find($productId);
                return back()->withInput()->with('error', "Opening Stock for '{$product->name}' already exists. Please delete the previous entry if you want to re-enter it.");
            }
        }

        DB::beginTransaction();
        try {
            $openingEquityHeadId = reservedAccountHeadId(ReservedAccountHead::OPENING_BALANCE_EQUITY);
            $finishedGoodsHeadId = reservedAccountHeadId(ReservedAccountHead::FINISHED_GOODS_INVENTORY);
            $sisterConcernId     = auth()->user()->activeConcernId();
            $date                = Carbon::parse($request->date)->format('Y-m-d');

            $voucherNo        = generateVoucherNo(VoucherType::$JOURNAL_VOUCHER);
            $voucherNoGroupSl = abs((int) filter_var($voucherNo, FILTER_SANITIZE_NUMBER_INT));

            $totalAmount = 0;

            foreach ($request->product_id as $key => $productId) {
                $product  = Product::find($productId);
                $qty      = $request->quantity[$key];
                $price    = $request->unit_price[$key];
                $amount   = $qty * $price;
                $totalAmount += $amount;

                // Inventory record
                $inventory = Inventory::where('sister_concern_id', $sisterConcernId)
                    ->where('product_id', $product->id)
                    ->where('warehouse_id', $request->warehouse_id)
                    ->where('unit_price', $price)
                    ->first();

                if (!$inventory) {
                    $latestInventory = Inventory::where('sister_concern_id', $sisterConcernId)
                        ->where('product_id', $product->id)
                        ->where('warehouse_id', $request->warehouse_id)
                        ->latest('id')->first();
                    $batchNo = $latestInventory ? intval($latestInventory->batch_no) + 1 : 1;

                    $inventory = Inventory::create([
                        'sister_concern_id' => $sisterConcernId,
                        'inventory_type'    => 1,
                        'warehouse_id'      => $request->warehouse_id,
                        'product_id'        => $product->id,
                        'batch_no'          => $batchNo,
                        'product_code'      => $product->code,
                        'quantity'          => $qty,
                        'unit_price'        => $price,
                    ]);
                } else {
                    $batchNo = $inventory->batch_no;
                    $inventory->unit_price = $price; // keep consistent with purchase
                    $inventory->save();
                    $inventory->increment('quantity', $qty);
                }

                // Inventory Log
                InventoryLog::create([
                    'sister_concern_id' => $sisterConcernId,
                    'type'              => InventoryLogType::$OPENING_STOCK,
                    'warehouse_id'      => $request->warehouse_id,
                    'inventory_id'      => $inventory->id,
                    'product_id'        => $product->id,
                    'batch_no'          => $batchNo,
                    'quantity'          => $qty,
                    'unit_price'        => $price,
                    'product_code'      => $product->code,
                    'notes'             => $request->notes ?? 'Opening Stock Entry',
                    'user_id'           => auth()->id(),
                    'date'              => Carbon::parse($request->date), // consistent with purchase
                ]);
            }

            // Journal Voucher
            $voucher = Voucher::create([
                'sister_concern_id'               => $sisterConcernId,
                'voucher_type'                    => VoucherType::$JOURNAL_VOUCHER,
                'amount'                          => $totalAmount,
                'date'                            => $date,
                'voucher_no'                      => $voucherNo,
                'voucher_no_group_sl'             => $voucherNoGroupSl,
                'account_head_payee_depositor_id' => $openingEquityHeadId,
                'notes'                           => $request->notes ?? 'Opening Stock Entry',
                'user_id'                         => auth()->id(),
            ]);

            // Dr. Finished Goods Inventory
            Transaction::create([
                'sister_concern_id'               => $sisterConcernId,
                'voucher_id'                      => $voucher->id,
                'account_head_id'                 => $finishedGoodsHeadId,
                'voucher_type'                    => VoucherType::$JOURNAL_VOUCHER,
                'transaction_type'                => TransactionType::$DEBIT,
                'amount'                          => $totalAmount,
                'date'                            => $date,
                'voucher_no'                      => $voucherNo,
                'voucher_no_group_sl'             => $voucherNoGroupSl,
                'account_head_payee_depositor_id' => $openingEquityHeadId,
                'notes'                           => 'Opening Stock — Inventory Asset',
                'user_id'                         => auth()->id(),
            ]);

            // Cr. Opening Balance Equity
            Transaction::create([
                'sister_concern_id'               => $sisterConcernId,
                'voucher_id'                      => $voucher->id,
                'account_head_id'                 => $openingEquityHeadId,
                'voucher_type'                    => VoucherType::$JOURNAL_VOUCHER,
                'transaction_type'                => TransactionType::$CREDIT,
                'amount'                          => $totalAmount,
                'date'                            => $date,
                'voucher_no'                      => $voucherNo,
                'voucher_no_group_sl'             => $voucherNoGroupSl,
                'account_head_payee_depositor_id' => $openingEquityHeadId,
                'notes'                           => 'Opening Stock — Equity Offset',
                'user_id'                         => auth()->id(),
            ]);

            DB::commit();
            return redirect()->route('inventory.opening_stock.create')
                ->with('success', "Opening Stock saved successfully! Voucher: {$voucherNo}");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function openingStockDataTable()
    {
        $query = InventoryLog::with('product', 'warehouse')
            ->leftJoin('users', 'inventory_logs.user_id', '=', 'users.id')
            ->select('inventory_logs.*', 'users.name as user_name')
            ->where('inventory_logs.sister_concern_id', auth()->user()->activeConcernId())
            ->where('inventory_logs.type', InventoryLogType::$OPENING_STOCK);

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('product_name', fn($log) => $log->product->name ?? '-')
            ->addColumn('warehouse_name', fn($log) => $log->warehouse->name ?? '-')
            ->addColumn('total_value', fn($log) => number_format($log->quantity * $log->unit_price, 2))
            ->addColumn('entered_by', fn($log) => $log->user_name ?? '-')
            ->addColumn('action', function($log) {
                $hasPurchaseOrSale = PurchaseOrder::where('sister_concern_id', $log->sister_concern_id)->exists() || 
                                     SaleOrder::where('sister_concern_id', $log->sister_concern_id)->exists();
                if ($hasPurchaseOrSale) {
                    return '<button class="btn btn-secondary btn-sm" disabled title="Locked: Operations exist"><i class="fa fa-trash"></i></button>';
                }
                return '<button class="btn btn-danger btn-sm btn-delete-log" data-id="'.$log->id.'" title="Delete Entry"><i class="fa fa-trash"></i></button>';
            })
            ->rawColumns(['action'])
            ->toJson();
    }

    public function openingStockDestroy($id)
    {
        if (!auth()->user()->hasPermissionTo('inventory')) {
            return response()->json(['status' => false, 'message' => 'Unauthorized']);
        }

        $sisterConcernId = auth()->user()->activeConcernId();

        // Block if purchase or sale exists
        if (PurchaseOrder::where('sister_concern_id', $sisterConcernId)->exists() || 
            SaleOrder::where('sister_concern_id', $sisterConcernId)->exists()) {
            return response()->json(['status' => false, 'message' => 'Cannot delete: This branch already has purchase or sale records.']);
        }

        $log = InventoryLog::where('sister_concern_id', $sisterConcernId)->find($id);
        if (!$log) {
            return response()->json(['status' => false, 'message' => 'Record not found.']);
        }

        DB::beginTransaction();
        try {
            // Decrease inventory
            $inventory = Inventory::find($log->inventory_id);
            if ($inventory) {
                $inventory->decrement('quantity', $log->quantity);
                if ($inventory->quantity <= 0) {
                    $inventory->delete();
                }
            }

            // Create Reversal Journal Voucher
            $amount = $log->quantity * $log->unit_price;
            $openingEquityHeadId = reservedAccountHeadId(ReservedAccountHead::OPENING_BALANCE_EQUITY);
            $finishedGoodsHeadId = reservedAccountHeadId(ReservedAccountHead::FINISHED_GOODS_INVENTORY);
            $date = date('Y-m-d');
            $voucherNo = generateVoucherNo(VoucherType::$JOURNAL_VOUCHER);
            $voucherNoGroupSl = abs((int) filter_var($voucherNo, FILTER_SANITIZE_NUMBER_INT));

            $voucher = Voucher::create([
                'sister_concern_id'               => $sisterConcernId,
                'voucher_type'                    => VoucherType::$JOURNAL_VOUCHER,
                'amount'                          => $amount,
                'date'                            => $date,
                'voucher_no'                      => $voucherNo,
                'voucher_no_group_sl'             => $voucherNoGroupSl,
                'account_head_payee_depositor_id' => $openingEquityHeadId,
                'notes'                           => 'Opening Stock Reversal',
                'user_id'                         => auth()->id(),
            ]);

            // Dr. Opening Balance Equity
            Transaction::create([
                'sister_concern_id'               => $sisterConcernId,
                'voucher_id'                      => $voucher->id,
                'account_head_id'                 => $openingEquityHeadId,
                'voucher_type'                    => VoucherType::$JOURNAL_VOUCHER,
                'transaction_type'                => TransactionType::$DEBIT,
                'amount'                          => $amount,
                'date'                            => $date,
                'voucher_no'                      => $voucherNo,
                'voucher_no_group_sl'             => $voucherNoGroupSl,
                'account_head_payee_depositor_id' => $openingEquityHeadId,
                'notes'                           => 'Opening Stock Reversal — Equity Debit',
                'user_id'                         => auth()->id(),
            ]);

            // Cr. Finished Goods Inventory
            Transaction::create([
                'sister_concern_id'               => $sisterConcernId,
                'voucher_id'                      => $voucher->id,
                'account_head_id'                 => $finishedGoodsHeadId,
                'voucher_type'                    => VoucherType::$JOURNAL_VOUCHER,
                'transaction_type'                => TransactionType::$CREDIT,
                'amount'                          => $amount,
                'date'                            => $date,
                'voucher_no'                      => $voucherNo,
                'voucher_no_group_sl'             => $voucherNoGroupSl,
                'account_head_payee_depositor_id' => $openingEquityHeadId,
                'notes'                           => 'Opening Stock Reversal — Asset Credit',
                'user_id'                         => auth()->id(),
            ]);

            $log->delete();
            DB::commit();

            return response()->json(['status' => true, 'message' => 'Opening stock entry deleted and reversed successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }
    }
}
