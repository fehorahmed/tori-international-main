<?php

namespace App\Http\Controllers\Ecommerce\API;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Services\Packages\PackageOrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PackageCheckoutController extends Controller
{
    public function store(Request $r, PackageOrderService $service)
    {
        $customer = Auth::guard('customer')->user();
        abort_unless($customer instanceof Supplier, 403);
        $data = $r->validate(['billing.name' => 'required|string|max:255', 'billing.phone' => 'required|string|max:20', 'billing.address' => 'required|string|max:500', 'payment_method' => 'required|in:cod', 'shipping_charge' => 'required|numeric|in:50,100', 'coupon_code' => 'nullable|string|max:64', 'request_key' => 'nullable|uuid', 'items' => 'required|array|min:1|max:50', 'items.*.product_id' => 'required|integer|distinct|exists:products,id', 'items.*.quantity' => 'required|integer|min:1|max:1000']);
        $data['request_key'] ??= (string) \Illuminate\Support\Str::uuid();
        $checkout = $service->checkout($customer, $data);

        return response()->json(['success' => true, 'order_no' => $checkout->saleOrder->order_no, 'order' => $service->data($checkout)], 201);
    }
}
