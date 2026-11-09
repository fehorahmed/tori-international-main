<?php

namespace App\Http\Controllers\FixedAsset;

use App\Enumeration\ReservedAccountGroup;
use App\Enumeration\TransactionType;
use App\Enumeration\VoucherType;
use App\Http\Controllers\Controller;
use App\Models\Account\AccountGroup;
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

class FixedAssetCategoryController extends Controller
{
    public function index()
    {
        return view('fixed_asset.category.index');
    }

    public function dataTable()
    {
        $query = FixedAssetCategory::with('accountHead');

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('account_head_name', fn($c) => $c->accountHead->name ?? '-')
            ->addColumn('dep_info', function ($c) {
                if ($c->depreciation_method === 'none') return '<span class="badge badge-secondary">No Depreciation</span>';
                if ($c->depreciation_method === 'WDV') return '<span class="badge badge-info">WDV — ' . $c->depreciation_rate . '%/yr</span>';
                return '<span class="badge badge-primary">SLM — ' . $c->useful_life_years . ' yrs</span>';
            })
            ->addColumn('assets_count', fn($c) => $c->activeAssetsCount())
            ->addColumn('action', function ($c) {
                if ($c->isLocked()) {
                    return '<button class="btn btn-secondary btn-sm" title="Locked: Active assets or financial entries exist" disabled><i class="fa fa-lock"></i></button>';
                }

                $btn = '';
                if (auth()->user()->can('fixed_asset_category_edit')) {
                    $btn .= '<a href="' . route('fixed-assets.categories.edit', $c->id) . '" class="btn btn-info bg-gradient-info btn-sm"><i class="fa fa-edit"></i></a>';
                }
                
                if (auth()->user()->can('fixed_asset_category_delete')) {
                    $btn .= ' <a role="button" data-id="' . $c->id . '" class="btn btn-danger btn-sm btn-delete"><i class="fa fa-trash"></i></a>';
                }
                return $btn;
            })
            ->rawColumns(['action', 'dep_info'])
            ->toJson();
    }

    public function create()
    {
        abort_if(!auth()->user()->hasPermissionTo('fixed_asset_category_create'), 403);
        return view('fixed_asset.category.create');
    }

    public function store(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('fixed_asset_category_create'), 403);

        $request->validate([
            'name'               => 'required|string|max:255|unique:fixed_asset_categories,name',
            'depreciation_method'=> 'required|in:SLM,WDV,none',
            'depreciation_rate'  => 'required_if:depreciation_method,WDV|nullable|numeric|min:0|max:100',
            'useful_life_years'  => 'required_if:depreciation_method,SLM|nullable|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            // ── Auto-create Account Head for this category ──────────────
            $fixedAssetsGroup = AccountGroup::where('reserved_id', ReservedAccountGroup::FIXED_ASSETS)->first();

            $maxCode = AccountHead::where('code', 'like', '1' . '%')->max('code');
            $newCode = $maxCode ? $maxCode + 1 : '10801';

            $accountHead = AccountHead::create([
                'name'             => $request->name,
                'code'             => $newCode,
                'account_group_id' => $fixedAssetsGroup->id,
                'nature'           => 'Debit',
                'payment_mode'     => 0,
                'is_editable'      => 1,
            ]);

            // ── Create Category ─────────────────────────────────────────
            FixedAssetCategory::create([
                'name'                  => $request->name,
                'depreciation_method'   => $request->depreciation_method,
                'depreciation_rate'     => $request->depreciation_rate,
                'useful_life_years'     => $request->useful_life_years,
                'account_head_id'       => $accountHead->id,
                'note'                  => $request->note,
            ]);

            DB::commit();
            return redirect()->route('fixed-assets.categories.index')->with('success', 'Category created and account head auto-generated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function edit(FixedAssetCategory $category)
    {
        abort_if(!auth()->user()->hasPermissionTo('fixed_asset_category_edit'), 403);
        return view('fixed_asset.category.edit', compact('category'));
    }

    public function update(Request $request, FixedAssetCategory $category)
    {
        abort_if(!auth()->user()->hasPermissionTo('fixed_asset_category_edit'), 403);

        $request->validate([
            'name'               => 'required|string|max:255|unique:fixed_asset_categories,name,' . $category->id,
            'depreciation_method'=> 'required|in:SLM,WDV,none',
            'depreciation_rate'  => 'required_if:depreciation_method,WDV|nullable|numeric|min:0|max:100',
            'useful_life_years'  => 'required_if:depreciation_method,SLM|nullable|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            // Update linked Account Head name
            if ($category->accountHead) {
                $category->accountHead->update(['name' => $request->name]);
            }

            $category->update([
                'name'               => $request->name,
                'depreciation_method'=> $request->depreciation_method,
                'depreciation_rate'  => $request->depreciation_rate,
                'useful_life_years'  => $request->useful_life_years,
                'note'               => $request->note,
            ]);

            DB::commit();
            return redirect()->route('fixed-assets.categories.index')->with('success', 'Category updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function destroy(FixedAssetCategory $category)
    {
        abort_if(!auth()->user()->hasPermissionTo('fixed_asset_category_delete'), 403);

        if ($category->isLocked()) {
            return response()->json(['success' => false, 'message' => 'This category has active assets or financial entries. Cannot delete.'], Response::HTTP_OK);
        }

        DB::beginTransaction();
        try {
            $accountHead = $category->accountHead;
            $category->delete();
            if ($accountHead) {
                $accountHead->delete();
            }
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Category and its Account Ledger deleted successfully.'], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], Response::HTTP_OK);
        }
    }
}
