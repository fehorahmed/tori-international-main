<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\Settings\Product;
use App\Services\Membership\CashoutService;
use App\Traits\UploadTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class OfferCampaignController extends Controller
{
    use UploadTrait;

    private function authorizeEdit(): void
    {
        abort_unless(auth()->user()->role === 'Admin' || auth()->user()->can('ecommerce_product_edit'), 403);
    }

    public function index()
    {
        $this->authorizeEdit();

        return view('ecommerce.offers.index');
    }

    public function dataTable()
    {
        $this->authorizeEdit();

        return DataTables::query(DB::table('offer_campaigns')->select('offer_campaigns.*'))
            ->addIndexColumn()
            ->addColumn('campaign', fn ($c) => view('ecommerce.offers.table-campaign', ['c' => $c])->render())
            ->editColumn('starts_at', fn ($c) => Carbon::parse($c->starts_at)->setTimezone('Asia/Dhaka')->format('Y-m-d H:i'))
            ->editColumn('ends_at', fn ($c) => Carbon::parse($c->ends_at)->setTimezone('Asia/Dhaka')->format('Y-m-d H:i'))
            ->addColumn('campaign_status', fn ($c) => ! $c->is_active ? 'Inactive' : (now()->lt($c->starts_at) ? 'Scheduled' : (now()->gte($c->ends_at) ? 'Expired' : 'Live')))
            ->addColumn('action', fn ($c) => '<a class="btn btn-primary btn-sm" href="'.e(route('offer-campaigns.edit', $c->id)).'">Edit</a>')
            ->rawColumns(['campaign', 'action'])->toJson();
    }

    public function edit(?int $campaign = null)
    {
        $this->authorizeEdit();
        $offer = $campaign ? DB::table('offer_campaigns')->find($campaign) : null;
        abort_if($campaign && ! $offer, 404);
        $items = $campaign ? DB::table('offer_campaign_products')->where('campaign_id', $campaign)->get()->map(fn ($r) => (array) $r)->all() : [];
        $products = Product::where('status', 1)->where('is_show', 1)->orderBy('name')->get(['id', 'name', 'catalog_type', 'selling_price', 'old_selling_price']);

        return view('ecommerce.offers.edit', compact('offer', 'items', 'products'));
    }

    public function save(Request $r, ?int $campaign = null)
    {
        $this->authorizeEdit();
        $data = $r->validate(['name' => 'required|string|max:255', 'slug' => 'nullable|string|max:200|regex:/^[a-z0-9-]+$/',
            'starts_at' => 'required|date', 'ends_at' => 'required|date|after:starts_at', 'is_active' => 'required|boolean',
            'banner_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120|dimensions:max_width=4096,max_height=4096',
            'remove_banner' => 'sometimes|boolean',
            'items' => 'required|array|min:1|max:200', 'items.*.product_id' => 'required|integer|distinct',
            'items.*.discount_type' => 'required|in:percentage,fixed', 'items.*.discount_value' => 'required|numeric|decimal:0,2|min:0.01|max:10000000', 'items.*.is_active' => 'required|boolean']);
        $slug = ($data['slug'] ?? null) ?: Str::slug($data['name']).'-'.Str::lower(Str::random(6));
        if (DB::table('offer_campaigns')->where('slug', $slug)->when($campaign, fn ($q) => $q->where('id', '!=', $campaign))->exists()) {
            throw ValidationException::withMessages(['slug' => 'This URL is already used.']);
        }
        $newBanner = null;
        $previousBanner = null;
        try {
            if ($r->hasFile('banner_image')) {
                $newBanner = $this->uploadImage($r->file('banner_image'), 'offer-campaigns', 1600);
            }
            DB::transaction(function () use ($data, $campaign, $slug, $r, $newBanner, &$previousBanner) {
                if ($campaign) {
                    $existing = DB::table('offer_campaigns')->where('id', $campaign)->lockForUpdate()->first();
                    abort_unless($existing, 404);
                    $previousBanner = $existing->banner_image;
                }
                $products = Product::whereIn('id', array_column($data['items'], 'product_id'))->orderBy('id')->lockForUpdate()->get()->keyBy('id');
                foreach ($data['items'] as $i => $item) {
                    $p = $products->get($item['product_id']);
                    if (! $p || $p->status != 1 || ! $p->is_show) {
                        throw ValidationException::withMessages(["items.$i.product_id" => 'Select an active visible product or package.']);
                    }
                    $regular = max(CashoutService::minor($p->selling_price), CashoutService::minor($p->old_selling_price ?? 0));
                    $value = CashoutService::minor($item['discount_value']);
                    $discount = $item['discount_type'] === 'percentage' ? intdiv($regular * $value + 5000, 10000) : $value;
                    if ($discount < 1 || $discount >= $regular) {
                        throw ValidationException::withMessages(["items.$i.discount_value" => 'Discount must leave a positive price and save at least Tk 0.01.']);
                    }
                }
                $values = ['name' => $data['name'], 'slug' => $slug, 'starts_at' => Carbon::parse($data['starts_at'], 'Asia/Dhaka')->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'), 'ends_at' => Carbon::parse($data['ends_at'], 'Asia/Dhaka')->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'), 'is_active' => (bool) $data['is_active'], 'updated_at' => now()];
                if ($newBanner !== null || $r->boolean('remove_banner')) {
                    $values['banner_image'] = $newBanner;
                }
                if ($campaign) {
                    DB::table('offer_campaigns')->where('id', $campaign)->update($values);
                } else {
                    $campaign = DB::table('offer_campaigns')->insertGetId($values + ['created_by' => auth()->id(), 'created_at' => now()]);
                }
                $keep = [];
                foreach ($data['items'] as $item) {
                    DB::table('offer_campaign_products')->upsert([['campaign_id' => $campaign, 'product_id' => $item['product_id'],
                        'discount_type' => $item['discount_type'], 'discount_value' => CashoutService::money(CashoutService::minor($item['discount_value'])), 'is_active' => (bool) $item['is_active'], 'updated_at' => now(), 'created_at' => now()]], ['campaign_id', 'product_id'], ['discount_type', 'discount_value', 'is_active', 'updated_at']);
                    $keep[] = $item['product_id'];
                }
                // Preserve historical entry IDs; removed products are disabled, not deleted.
                DB::table('offer_campaign_products')->where('campaign_id', $campaign)->whereNotIn('product_id', $keep)->update(['is_active' => false, 'updated_at' => now()]);
            }, 3);
        } catch (\Throwable $e) {
            if ($newBanner) {
                File::delete(public_path($newBanner));
            }
            throw $e;
        }
        if (($newBanner || $r->boolean('remove_banner')) && $previousBanner && str_starts_with($previousBanner, 'uploads/offer-campaigns/')) {
            File::delete(public_path($previousBanner));
        }

        return redirect()->route('offer-campaigns.index')->with('success', 'Special offer campaign saved.');
    }
}
