<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\Ecommerce\PackageOrderCheckout;
use App\Models\Sales\SaleOrder;
use App\Services\Packages\PackageOrderService;
use Illuminate\Support\Facades\DB;

class PackageOrderController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()->can('ecommerce_order'), 403);
        $orders = PackageOrderCheckout::whereHas('saleOrder', fn ($q) => $q->where('sister_concern_id', auth()->user()->activeConcernId()))->with('saleOrder.customer')->latest('id')->paginate(20);

        return view('ecommerce.packages.orders', compact('orders'));
    }

    public function show(PackageOrderCheckout $checkout, PackageOrderService $service)
    {
        abort_unless(auth()->user()->can('ecommerce_order') && $checkout->saleOrder->sister_concern_id == auth()->user()->activeConcernId(), 403);
        $order = $service->data($checkout);

        return view('ecommerce.packages.order', compact('checkout', 'order'));
    }

    public function confirm(PackageOrderCheckout $checkout, PackageOrderService $service)
    {
        $service->confirm($checkout, auth()->user());

        return redirect()->route('package-orders.show', $checkout)->with('success', 'Order confirmed. Component stock and accounting posted.');
    }

    public function cancel(PackageOrderCheckout $checkout)
    {
        abort_unless(auth()->user()->can('ecommerce_order_confirm'), 403);
        DB::transaction(function () use ($checkout) {
            $checkout = PackageOrderCheckout::whereKey($checkout->id)->lockForUpdate()->firstOrFail();
            $order = SaleOrder::whereKey($checkout->sale_order_id)->lockForUpdate()->firstOrFail();
            abort_unless($order->sister_concern_id == auth()->user()->activeConcernId(), 403);
            abort_unless($checkout->status === 'Pending', 422, 'Only pending package orders can be cancelled.');
            $checkout->update(['status' => 'Cancelled']);
            $order->update(['status' => 4]);
        });

        return back()->with('success', 'Order cancelled.');
    }

    public function status(Request $request, PackageOrderCheckout $checkout)
    {
        abort_unless(auth()->user()->can('ecommerce_order_confirm'), 403);
        $request->validate(['status' => 'required|integer|in:2,3']);
        DB::transaction(function () use ($request, $checkout) {
            $checkout = PackageOrderCheckout::whereKey($checkout->id)->lockForUpdate()->firstOrFail();
            $order = SaleOrder::whereKey($checkout->sale_order_id)->lockForUpdate()->firstOrFail();
            abort_unless($order->sister_concern_id == auth()->user()->activeConcernId(), 403);
            abort_unless($checkout->status === 'Confirmed' && $request->integer('status') > $order->status, 422);
            $order->update(['status' => $request->integer('status')]);
        });

        return back()->with('success', 'Order status updated.');
    }
}
