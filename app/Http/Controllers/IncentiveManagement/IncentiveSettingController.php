<?php

namespace App\Http\Controllers\IncentiveManagement;

use App\Http\Controllers\Controller;
use App\Models\Settings\Category;
use App\Models\IncentiveManagement\IncentiveSetting;
use App\Models\BranchManagement\SisterConcern;
use App\Models\Settings\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class IncentiveSettingController extends Controller
{
    // ─── Settings Index (List Page) ────────────────────────
    public function index()
    {
        abort_if(!auth()->user()->hasPermissionTo('incentive_settings'), 403);
        $pageTitle = 'Incentive Settings';
        
        $activeConcern = SisterConcern::find(auth()->user()->activeConcernId());
        if ($activeConcern && $activeConcern->is_head_office) {
            $branches = SisterConcern::where('status', 1)->orderBy('name')->get();
        } else {
            $branches = SisterConcern::where('id', auth()->user()->activeConcernId())->get();
        }

        return view('incentive_management.settings.index', compact('pageTitle', 'branches'));
    }

    // ─── Settings Create/Edit (Bulk Entry Page) ────────────────────────
    public function create(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('incentive_settings'), 403);

        $activeConcern = SisterConcern::find(auth()->user()->activeConcernId());
        if ($activeConcern && $activeConcern->is_head_office) {
            $branches = SisterConcern::where('status', 1)->orderBy('name')->get();
        } else {
            $branches = SisterConcern::where('id', auth()->user()->activeConcernId())->get();
        }

        $categories = Category::where('status', 1)->orderBy('name')->get();

        $sisterConcernId = $request->get('sister_concern_id');
        $categoryId = $request->get('category_id');
        $products   = collect();
        $existingSettings = collect();

        if ($sisterConcernId && $categoryId) {
            $products = Product::where('status',1)
                ->where('category_id', $categoryId)
                ->orderBy('name')
                ->get();

            $existingSettings = IncentiveSetting::forConcern($sisterConcernId)
                ->whereIn('product_id', $products->pluck('id'))
                ->get()
                ->keyBy('product_id');
        }

        $pageTitle = 'Configure Incentive Settings';
        return view('incentive_management.settings.create', compact(
            'pageTitle', 'branches', 'categories', 'products', 'existingSettings', 'categoryId', 'sisterConcernId'
        ));
    }
    
    // ─── Global Settings (Apply to All Products) ──────────────────

    public function global(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('incentive_settings'), 403);

        $branches = [];
        $activeConcern = SisterConcern::find(auth()->user()->activeConcernId());
        
        if ($activeConcern && $activeConcern->is_head_office) {
            $branches = SisterConcern::where('status', 1)->get();
        } else {
            $branches = SisterConcern::where('id', auth()->user()->activeConcernId())->get();
        }

        $pageTitle = 'Global Incentive Settings';
        return view('incentive_management.settings.global', compact('pageTitle', 'branches'));
    }

    public function globalStore(Request $request): RedirectResponse
    {
        abort_if(!auth()->user()->hasPermissionTo('incentive_settings'), 403);

        $request->validate([
            'sister_concern_id' => 'required|exists:sister_concerns,id',
            'sr_percent'        => 'required|numeric|min:0|max:100',
            'bm_percent'        => 'required|numeric|min:0|max:100',
            'am_percent'        => 'required|numeric|min:0|max:100',
            'sr_active'         => 'nullable|boolean',
            'bm_active'         => 'nullable|boolean',
            'am_active'         => 'nullable|boolean',
        ]);

        $scId = $request->sister_concern_id;
        $now  = now();
        $authId = auth()->id();

        $products = Product::where('status', 1)->get();
        if($products->isEmpty()) {
            return back()->with('error', 'কোনো অ্যাক্টিভ প্রোডাক্ট পাওয়া যায়নি।');
        }

        foreach ($products as $product) {
            IncentiveSetting::updateOrCreate(
                [
                    'sister_concern_id' => $scId,
                    'product_id'        => $product->id,
                ],
                [
                    'sr_percent' => $request->sr_percent,
                    'bm_percent' => $request->bm_percent,
                    'am_percent' => $request->am_percent,
                    'sr_active'  => $request->has('sr_active'),
                    'bm_active'  => $request->has('bm_active'),
                    'am_active'  => $request->has('am_active'),
                    'updated_by' => $authId,
                ]
            );
        }

        return redirect()->route('incentive-settings.index')->with('success', 'সকল প্রোডাক্টের জন্য গ্লোবাল ইনসেনটিভ সফলভাবে সেভ হয়েছে!');
    }

    // ─── Bulk Upsert (Save All) ───────────────────────────────────

    public function bulkStore(Request $request): JsonResponse
    {
        abort_if(!auth()->user()->hasPermissionTo('incentive_settings'), 403);

        $request->validate([
            'sister_concern_id'         => 'required|exists:sister_concerns,id',
            'settings'                  => 'required|array|min:1',
            'settings.*.product_id'     => 'required|exists:products,id',
            'settings.*.sr_percent'     => 'required|numeric|min:0|max:100',
            'settings.*.bm_percent'     => 'required|numeric|min:0|max:100',
            'settings.*.am_percent'     => 'required|numeric|min:0|max:100',
            'settings.*.sr_active'      => 'required|boolean',
            'settings.*.bm_active'      => 'required|boolean',
            'settings.*.am_active'      => 'required|boolean',
        ]);

        $scId = $request->sister_concern_id;
        $now  = now();

        $rows = collect($request->settings)->map(fn($row) => [
            'sister_concern_id' => $scId,
            'product_id'        => $row['product_id'],
            'sr_percent'        => $row['sr_percent'],
            'bm_percent'        => $row['bm_percent'],
            'am_percent'        => $row['am_percent'],
            'sr_active'         => (bool) $row['sr_active'],
            'bm_active'         => (bool) $row['bm_active'],
            'am_active'         => (bool) $row['am_active'],
            'updated_by'        => auth()->id(),
            'updated_at'        => $now,
            'created_by'        => auth()->id(),
            'created_at'        => $now,
        ])->toArray();

        foreach ($rows as $row) {
            IncentiveSetting::updateOrCreate(
                [
                    'sister_concern_id' => $row['sister_concern_id'],
                    'product_id'        => $row['product_id'],
                ],
                [
                    'sr_percent' => $row['sr_percent'],
                    'bm_percent' => $row['bm_percent'],
                    'am_percent' => $row['am_percent'],
                    'sr_active'  => $row['sr_active'],
                    'bm_active'  => $row['bm_active'],
                    'am_active'  => $row['am_active'],
                    'updated_by' => $row['updated_by'],
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => count($rows) . 'টি প্রোডাক্টের ইনসেনটিভ সেটিং সফলভাবে সেভ হয়েছে!',
        ]);
    }

    // ─── Toggle Single Role Active/Inactive (Quick Toggle) ───────

    public function toggleRole(Request $request, IncentiveSetting $incentiveSetting): JsonResponse
    {
        abort_if(!auth()->user()->hasPermissionTo('incentive_settings'), 403);
        abort_if($incentiveSetting->sister_concern_id !== auth()->user()->activeConcernId(), 403);

        $request->validate(['role' => 'required|in:sr,bm,am']);

        $column  = $request->role . '_active';
        $newVal  = !$incentiveSetting->$column;

        $incentiveSetting->update([
            $column      => $newVal,
            'updated_by' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'active'  => $newVal,
            'message' => strtoupper($request->role) . ' ইনসেনটিভ ' . ($newVal ? 'চালু' : 'বন্ধ') . ' করা হয়েছে।',
        ]);
    }

    // ─── Toggle Status ───────────────────────────────────
    public function toggleStatus(Request $request): JsonResponse
    {
        abort_if(!auth()->user()->hasPermissionTo('incentive_settings'), 403);

        $request->validate([
            'id' => 'required|exists:incentive_settings,id',
            'field' => 'required|in:sr_active,bm_active,am_active',
            'status' => 'required|boolean'
        ]);

        $setting = IncentiveSetting::findOrFail($request->id);
        $setting->{$request->field} = $request->status;
        $setting->save();

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully!'
        ]);
    }

    // ─── DataTable (Settings List) ────────────────────────────────

    public function dataTable(Request $request): JsonResponse
    {
        abort_if(!auth()->user()->hasPermissionTo('incentive_settings'), 403);

        $scId = $request->get('sister_concern_id');
        $activeConcern = SisterConcern::find(auth()->user()->activeConcernId());

        $query = IncentiveSetting::with('product', 'product.category');
        
        if ($activeConcern && $activeConcern->is_head_office) {
            if ($scId) {
                $query->forConcern($scId);
            }
        } else {
            $query->forConcern(auth()->user()->activeConcernId());
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('branch_name', fn($s) => $s->sisterConcern->name ?? '-')
            ->addColumn('product_name', fn($s) =>
                $s->product->name . '<br><small class="text-muted">' . ($s->product->category->name ?? '-') . '</small>'
            )
            ->addColumn('sr_status', fn($s) => '
                <div class="d-flex align-items-center justify-content-center">
                    <span class="mr-2">' . $s->sr_percent . '%</span>
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input status-toggle" 
                               id="sr_active_'.$s->id.'" 
                               data-id="'.$s->id.'" 
                               data-field="sr_active" 
                               '.($s->sr_active ? 'checked' : '').'>
                        <label class="custom-control-label" for="sr_active_'.$s->id.'"></label>
                    </div>
                </div>
            ')
            ->addColumn('bm_status', fn($s) => '
                <div class="d-flex align-items-center justify-content-center">
                    <span class="mr-2">' . $s->bm_percent . '%</span>
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input status-toggle" 
                               id="bm_active_'.$s->id.'" 
                               data-id="'.$s->id.'" 
                               data-field="bm_active" 
                               '.($s->bm_active ? 'checked' : '').'>
                        <label class="custom-control-label" for="bm_active_'.$s->id.'"></label>
                    </div>
                </div>
            ')
            ->addColumn('am_status', fn($s) => '
                <div class="d-flex align-items-center justify-content-center">
                    <span class="mr-2">' . $s->am_percent . '%</span>
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input status-toggle" 
                               id="am_active_'.$s->id.'" 
                               data-id="'.$s->id.'" 
                               data-field="am_active" 
                               '.($s->am_active ? 'checked' : '').'>
                        <label class="custom-control-label" for="am_active_'.$s->id.'"></label>
                    </div>
                </div>
            ')
            ->rawColumns(['product_name', 'sr_status', 'bm_status', 'am_status'])
            ->toJson();
    }
}
