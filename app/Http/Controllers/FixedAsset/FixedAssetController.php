<?php

namespace App\Http\Controllers\FixedAsset;

use App\Enumeration\ReservedAccountHead;
use App\Enumeration\TransactionType;
use App\Enumeration\VoucherType;
use App\Http\Controllers\Controller;
use App\Models\Account\AccountHead;
use App\Models\Account\Transaction;
use App\Models\Account\Voucher;
use App\Models\BranchManagement\SisterConcern;
use App\Models\FixedAsset\FixedAsset;
use App\Models\FixedAsset\FixedAssetCategory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class FixedAssetController extends Controller
{
    // ── Index / List ────────────────────────────────────────────────

    public function index()
    {
        abort_if(!auth()->user()->hasPermissionTo('fixed_asset_view'), 403);
        return view('fixed_asset.asset.index');
    }

    public function dataTable()
    {
        abort_if(!auth()->user()->hasPermissionTo('fixed_asset_view'), 403);

        $query = FixedAsset::with('category', 'sisterConcern')
            ->where('sister_concern_id', auth()->user()->activeConcernId());

        if (request()->filled('status')) {
            $query->where('status', request('status'));
        }
        if (request()->filled('category_id')) {
            $query->where('category_id', request('category_id'));
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('category_name', fn($a) => $a->category->name ?? '-')
            ->addColumn('book_value_formatted', fn($a) => number_format($a->book_value, 2))
            ->addColumn('total_formatted', fn($a) => number_format($a->total, 2))
            ->addColumn('status_badge', fn($a) => $a->status_badge)
            ->addColumn('action', function ($a) {
                $btn = '<a href="' . route('fixed-assets.show', $a->id) . '" class="btn btn-info btn-sm" title="View"><i class="fa fa-eye"></i></a>';
                if ($a->status === 'active') {
                    if (auth()->user()->can('fixed_asset_edit')) {
                        $btn .= ' <a href="' . route('fixed-assets.edit', $a->id) . '" class="btn btn-warning btn-sm" title="Edit"><i class="fa fa-edit"></i></a>';
                    }
                    if (auth()->user()->can('fixed_asset_sell')) {
                        $btn .= ' <a href="' . route('fixed-assets.sell-form', $a->id) . '" class="btn btn-success btn-sm" title="Sell"><i class="fas fa-hand-holding-usd"></i></a>';
                    }
                    if (auth()->user()->can('fixed_asset_scrap')) {
                        $btn .= ' <a href="' . route('fixed-assets.scrap-form', $a->id) . '" class="btn btn-danger btn-sm" title="Scrap"><i class="fas fa-dumpster"></i></a>';
                    }
                }
                return $btn;
            })
            ->rawColumns(['action', 'status_badge'])
            ->toJson();
    }

    // ── Create / Store ───────────────────────────────────────────────

    public function create()
    {
        abort_if(!auth()->user()->hasPermissionTo('fixed_asset_create'), 403);

        $categories = FixedAssetCategory::with('accountHead')->orderBy('name')->get();
        $paymentHeads = AccountHead::where('payment_mode', '>', 0)
            ->where(function ($q) {
                $q->whereNull('sister_concern_id')
                  ->orWhere('sister_concern_id', auth()->user()->activeConcernId());
            })
            ->orderBy('name')
            ->get();

        return view('fixed_asset.asset.create', compact('categories', 'paymentHeads'));
    }

    public function store(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('fixed_asset_create'), 403);

        $request->validate([
            'category_id'             => 'required|exists:fixed_asset_categories,id',
            'name'                    => 'required|string|max:255',
            'date'                    => 'required|date',
            'total'                   => 'required|numeric|min:0.01',
            'quantity'                => 'required|integer|min:1|max:500',
            'payment_account_head_id' => 'required|exists:account_heads,id',
            'location'                => 'nullable|string|max:255',
            'serial_no'               => 'nullable|string|max:255',
            'vendor'                  => 'nullable|string|max:255',
            'warranty_expiry'         => 'nullable|date',
            'salvage_value'           => 'nullable|numeric|min:0',
            'useful_life_years'       => 'nullable|integer|min:1',
            'depreciation_method'     => 'required|in:SLM,WDV,none',
            'depreciation_rate'       => 'nullable|numeric|min:0|max:100',
            'notes'                   => 'nullable|string|max:500',
        ]);

        $category   = FixedAssetCategory::with('accountHead')->findOrFail($request->category_id);
        $user       = auth()->user();
        $scId       = $user->activeConcernId();
        $quantity   = (int) $request->quantity;
        $unitCost   = (float) $request->total;
        $totalCost  = $unitCost * $quantity;
        $today      = Carbon::parse($request->date)->format('Y-m-d');

        $paymentAccount = AccountHead::findOrFail($request->payment_account_head_id);
        // if (!$paymentAccount->isSufficient($totalCost, $scId)) {
        //     return back()->withInput()->with('error', 'Insufficient balance in the selected payment account.');
        // }

        DB::beginTransaction();
        try {
            // ── 1. Journal Voucher (one voucher for entire batch) ────────
            $voucherNo      = generateVoucherNo(VoucherType::$JOURNAL_VOUCHER);
            $voucherNoGroupSl = abs((int) filter_var($voucherNo, FILTER_SANITIZE_NUMBER_INT));

            $voucher = Voucher::create([
                'sister_concern_id'             => $scId,
                'voucher_type'                  => VoucherType::$JOURNAL_VOUCHER,
                'amount'                        => $totalCost,
                'date'                          => $today,
                'voucher_no'                    => $voucherNo,
                'voucher_no_group_sl'           => $voucherNoGroupSl,
                'account_head_payee_depositor_id' => $category->account_head_id,
                'notes'                         => 'Fixed Asset: ' . $request->name . ' (' . $quantity . ' unit' . ($quantity > 1 ? 's' : '') . ')',
                'user_id'                       => $user->id,
            ]);

            // Dr. Fixed Asset Account Head (category's account head)
            Transaction::create([
                'sister_concern_id'             => $scId,
                'voucher_id'                    => $voucher->id,
                'account_head_id'               => $category->account_head_id,
                'voucher_type'                  => VoucherType::$JOURNAL_VOUCHER,
                'transaction_type'              => TransactionType::$DEBIT,
                'amount'                        => $totalCost,
                'date'                          => $today,
                'voucher_no'                    => $voucherNo,
                'voucher_no_group_sl'           => $voucherNoGroupSl,
                'account_head_payee_depositor_id' => $category->account_head_id,
                'notes'                         => 'Purchase: ' . $request->name . ' × ' . $quantity,
                'user_id'                       => $user->id,
            ]);

            // Cr. Cash / Bank account
            Transaction::create([
                'sister_concern_id'             => $scId,
                'voucher_id'                    => $voucher->id,
                'account_head_id'               => $request->payment_account_head_id,
                'voucher_type'                  => VoucherType::$JOURNAL_VOUCHER,
                'transaction_type'              => TransactionType::$CREDIT,
                'amount'                        => $totalCost,
                'date'                          => $today,
                'voucher_no'                    => $voucherNo,
                'voucher_no_group_sl'           => $voucherNoGroupSl,
                'account_head_payee_depositor_id' => $category->account_head_id,
                'notes'                         => 'Payment for: ' . $request->name . ' × ' . $quantity,
                'user_id'                       => $user->id,
            ]);

            // ── 2. Create individual asset records ───────────────────────
            for ($i = 1; $i <= $quantity; $i++) {
                $assetCode = FixedAsset::generateAssetCode();

                FixedAsset::create([
                    'asset_code'              => $assetCode,
                    'name'                    => $quantity > 1 ? $request->name . ' (' . $i . '/' . $quantity . ')' : $request->name,
                    'category_id'             => $category->id,
                    'sister_concern_id'       => $scId,
                    'date'                    => $today,
                    'total'                   => $unitCost,
                    'salvage_value'           => $request->salvage_value ?? 0,
                    'useful_life_years'       => $request->useful_life_years ?? $category->useful_life_years,
                    'depreciation_method'     => $request->depreciation_method,
                    'depreciation_rate'       => $request->depreciation_rate ?? $category->depreciation_rate,
                    'accumulated_depreciation'=> 0,
                    'book_value'              => $unitCost,
                    'account_head_id'         => $category->account_head_id,
                    'payment_account_head_id' => $request->payment_account_head_id,
                    'location'                => $request->location,
                    'serial_no'               => $quantity > 1 ? null : $request->serial_no,
                    'vendor'                  => $request->vendor,
                    'warranty_expiry'         => $request->filled('warranty_expiry') ? Carbon::parse($request->warranty_expiry)->format('Y-m-d') : null,
                    'status'                  => 'active',
                    'purchase_voucher_id'     => $voucher->id,
                    'created_by'              => $user->id,
                    'notes'                   => $request->notes,
                ]);
            }

            DB::commit();
            return redirect()->route('fixed-assets.index')
                ->with('success', $quantity . ' asset(s) created successfully. Journal Voucher: ' . $voucherNo);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    // ── Show (Asset Detail) ──────────────────────────────────────────

    public function show(FixedAsset $fixedAsset)
    {
        abort_if(!auth()->user()->hasPermissionTo('fixed_asset_view'), 403);
        abort_if($fixedAsset->sister_concern_id !== auth()->user()->activeConcernId(), 403);

        $fixedAsset->load('category', 'accountHead', 'paymentAccountHead', 'depreciationLogs.voucher', 'disposal', 'purchaseVoucher');
        return view('fixed_asset.asset.show', compact('fixedAsset'));
    }

    // ── Edit / Update ────────────────────────────────────────────────

    public function edit(FixedAsset $fixedAsset)
    {
        abort_if(!auth()->user()->hasPermissionTo('fixed_asset_edit'), 403);
        abort_if($fixedAsset->status !== 'active', 403, 'Only active assets can be edited.');

        $categories  = FixedAssetCategory::orderBy('name')->get();
        $paymentHeads = AccountHead::where('payment_mode', '>', 0)->orderBy('name')->get();
        return view('fixed_asset.asset.edit', compact('fixedAsset', 'categories', 'paymentHeads'));
    }

    public function update(Request $request, FixedAsset $fixedAsset)
    {
        abort_if(!auth()->user()->hasPermissionTo('fixed_asset_edit'), 403);
        abort_if($fixedAsset->status !== 'active', 403, 'Only active assets can be edited.');

        $request->validate([
            'name'            => 'required|string|max:255',
            'location'        => 'nullable|string|max:255',
            'serial_no'       => 'nullable|string|max:255',
            'vendor'          => 'nullable|string|max:255',
            'warranty_expiry' => 'nullable|date',
            'notes'           => 'nullable|string|max:500',
        ]);

        // Only allow editing of non-financial metadata; financial changes go through disposal/depreciation
        $data = $request->only(['name', 'location', 'serial_no', 'vendor', 'notes']);
        $data['warranty_expiry'] = $request->filled('warranty_expiry') ? Carbon::parse($request->warranty_expiry)->format('Y-m-d') : null;
        
        $fixedAsset->update($data);

        return redirect()->route('fixed-assets.show', $fixedAsset->id)
            ->with('success', 'Asset updated successfully.');
    }
}
