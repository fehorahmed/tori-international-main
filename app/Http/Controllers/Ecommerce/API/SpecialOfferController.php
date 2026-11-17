<?php

namespace App\Http\Controllers\Ecommerce\API;

use App\Http\Controllers\Controller;
use App\Models\Settings\Product;
use App\Services\Membership\DiscountService;
use App\Services\Offers\OfferService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SpecialOfferController extends Controller
{
    public function availability()
    {
        $regular = 'GREATEST(p.selling_price, COALESCE(p.old_selling_price, 0))';
        $discount = "CASE WHEN op.discount_type = 'percentage' THEN ROUND($regular * op.discount_value / 100, 2) ELSE op.discount_value END";
        $available = OfferService::active()->join('products as p', 'p.id', '=', 'op.product_id')
            ->whereNull('p.deleted_at')->where('p.status', 1)->where('p.is_show', 1)
            ->whereRaw("($discount) > 0 AND ($discount) < $regular")->exists();

        return response()->json(['available' => $available]);
    }

    public function banners()
    {
        return response()->json(['banners' => OfferService::banners()]);
    }

    public function index(Request $r)
    {
        $r->validate(['campaign' => 'nullable|string|max:255', 'page' => 'nullable|integer|min:1', 'per_page' => 'nullable|integer|min:1|max:60']);
        $campaigns = DB::table('offer_campaigns')->where('is_active', 1)->where('starts_at', '<=', now())->where('ends_at', '>', now())->orderBy('ends_at')->get(['id', 'name', 'slug', 'ends_at']);
        $regular = 'GREATEST(p.selling_price, COALESCE(p.old_selling_price, 0))';
        $discount = "CASE WHEN op.discount_type = 'percentage' THEN ROUND($regular * op.discount_value / 100, 2) ELSE op.discount_value END";
        $ids = OfferService::active()->join('products as p', 'p.id', '=', 'op.product_id')->whereRaw("($discount) > 0 AND ($discount) < $regular")->when($r->campaign, fn ($q) => $q->where('oc.slug', $r->campaign))->select('op.product_id');
        $products = Product::select('id', 'name', 'slug', 'image', 'youtube_video_link', 'catalog_type', 'package_warehouse_id', 'selling_price', 'old_selling_price', 'discount', 'discount_percent', 'member_discount_percent', 'status', 'is_show', 'rating', 'total_review', 'sort')->where('status', 1)->where('is_show', 1)->whereIn('id', $ids)->orderBy('sort')->orderBy('id')->paginate($r->integer('per_page', 24));
        $eligible = DiscountService::eligible(auth('customer')->user());
        $products->setCollection($products->getCollection()->map(fn ($p) => DiscountService::present($p->toArray(), $eligible)));

        return response()->json(['campaigns' => $campaigns->map(fn ($c) => ['id' => $c->id, 'name' => $c->name, 'slug' => $c->slug, 'ends_at' => Carbon::parse($c->ends_at)->toIso8601String()]), 'products' => $products]);
    }
}
