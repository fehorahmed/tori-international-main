<?php

namespace App\Services\Offers;

use App\Services\Membership\CashoutService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OfferService
{
    public static function active()
    {
        return DB::table('offer_campaign_products as op')->join('offer_campaigns as oc', 'oc.id', '=', 'op.campaign_id')
            ->where('oc.is_active', 1)->where('op.is_active', 1)->where('oc.starts_at', '<=', now())->where('oc.ends_at', '>', now());
    }

    public static function banners()
    {
        $regular = 'GREATEST(p.selling_price, COALESCE(p.old_selling_price, 0))';
        $discount = "CASE WHEN op.discount_type = 'percentage' THEN ROUND($regular * op.discount_value / 100, 2) ELSE op.discount_value END";

        return self::active()->join('products as p', 'p.id', '=', 'op.product_id')
            ->whereNull('p.deleted_at')->where('p.status', 1)->where('p.is_show', 1)
            ->whereRaw("($discount) > 0 AND ($discount) < $regular")
            ->whereNotNull('oc.banner_image')->where('oc.banner_image', '!=', '')
            ->select('oc.id', 'oc.name', 'oc.slug', 'oc.banner_image', 'oc.ends_at')->distinct()->orderBy('oc.ends_at')->orderBy('oc.id')->get()
            ->map(fn ($c) => ['id' => $c->id, 'name' => $c->name, 'slug' => $c->slug,
                'image_url' => asset($c->banner_image), 'ends_at' => Carbon::parse($c->ends_at)->toIso8601String()]);
    }

    public static function best(int $product, int $regular): ?array
    {
        $best = null;
        foreach (self::active()->where('op.product_id', $product)->orderBy('op.id')->get(['op.*', 'oc.name as campaign_name', 'oc.slug', 'oc.ends_at']) as $row) {
            $value = CashoutService::minor($row->discount_value);
            $discount = $row->discount_type === 'percentage' ? intdiv($regular * $value + 5000, 10000) : $value;
            if ($discount <= 0 || $discount >= $regular || ($best && $best['discount'] >= $discount)) {
                continue;
            }
            $best = ['id' => $row->id, 'campaign_id' => $row->campaign_id, 'campaign_name' => $row->campaign_name, 'slug' => $row->slug,
                'discount_type' => $row->discount_type, 'discount_value' => $row->discount_value, 'ends_at' => Carbon::parse($row->ends_at)->toIso8601String(), 'discount' => $discount];
        }

        return $best;
    }

    // Catalog filtering and sorting must use the same final prices as cart and checkout.
    public static function sqlPrice(string $base): string
    {
        $at = DB::connection()->getPdo()->quote(now()->format('Y-m-d H:i:s'));
        $regular = 'GREATEST(products.selling_price, COALESCE(products.old_selling_price, 0))';
        $discount = "CASE WHEN op.discount_type = 'percentage' THEN ROUND($regular * op.discount_value / 100, 2) ELSE op.discount_value END";

        return "LEAST(($base), COALESCE((SELECT MIN($regular - ($discount)) FROM offer_campaign_products op INNER JOIN offer_campaigns oc ON oc.id = op.campaign_id WHERE op.product_id = products.id AND op.is_active = 1 AND oc.is_active = 1 AND oc.starts_at <= $at AND oc.ends_at > $at AND ($discount) > 0 AND ($discount) < $regular), ($base)))";
    }
}
