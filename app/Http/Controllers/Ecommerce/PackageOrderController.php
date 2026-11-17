<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\Ecommerce\PackageOrderCheckout;
use App\Models\Sales\SaleOrder;
use App\Services\Packages\PackageOrderService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class PackageOrderController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()->can('ecommerce_order'), 403);
        return redirect()->route('ecom_order');
    }

    public function dataTable()
    {
        abort_unless(auth()->user()->can('ecommerce_order'), 403);
        $query = DB::table('package_order_checkouts as checkouts')
            ->join('sale_orders as orders', 'orders.id', '=', 'checkouts.sale_order_id')
            ->leftJoin('suppliers as customers', 'customers.id', '=', 'orders.customer_id')
            ->whereNull('orders.deleted_at')->where('orders.sister_concern_id', auth()->user()->activeConcernId())
            ->select('checkouts.id', 'orders.order_no', 'orders.total', 'checkouts.status', 'customers.name as customer_name');
        return \Yajra\DataTables\Facades\DataTables::query($query)->addIndexColumn()
            ->addColumn('action', fn ($c) => '<a class="btn btn-primary btn-sm" href="'.e(route('package-orders.show', $c->id)).'">View order</a>')
            ->rawColumns(['action'])->toJson();
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
        app(PackageOrderService::class)->cancel($checkout, auth()->user());

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
