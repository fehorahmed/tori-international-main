<?php

namespace App\Http\Controllers\Ecommerce\API;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Sales\SaleOrder;
use App\Enumeration\SaleType;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $orders = SaleOrder::where('type',SaleType::$ECOMMERCE)->where('customer_id',auth()->guard('customer')->user()->id)->orderBy('id','desc')->get();
        return response()->json($orders);
    }
    public function store(Request $request)
    {
        return app(PackageCheckoutController::class)->store($request, app(\App\Services\Packages\PackageOrderService::class));
    }
    public function buyNow(Request $request)
    {
        abort_unless(auth()->guard('customer')->check(), 401, 'Sign in to place an order.');
        return $this->store($request);
    }
    public function buyNowShow($order_no,$mobile)
    {
        $packageCheckout = \App\Models\Ecommerce\PackageOrderCheckout::whereHas('saleOrder', fn($q) => $q->where('order_no', $order_no))->first();
        if ($packageCheckout) { abort_unless(auth()->guard('customer')->id() == $packageCheckout->customer_id && $packageCheckout->billing['phone'] === $mobile, 403); return response()->json(app(\App\Services\Packages\PackageOrderService::class)->data($packageCheckout)); }
        $order = SaleOrder::with('products')->where('order_no', $order_no)->where('type',SaleType::$ECOMMERCE)->where('billing_mobile_no',$mobile)->first();
        return response()->json($order);
    }
    public function show($order_no)
    {
        $packageCheckout = \App\Models\Ecommerce\PackageOrderCheckout::whereHas('saleOrder', fn($q) => $q->where('order_no', $order_no))->first();
        if ($packageCheckout) { abort_unless(auth()->guard('customer')->id() == $packageCheckout->customer_id, 403); return response()->json(app(\App\Services\Packages\PackageOrderService::class)->data($packageCheckout)); }
        $order = SaleOrder::with('products')->where('order_no', $order_no)->where('type',SaleType::$ECOMMERCE)->where('customer_id',auth()->guard('customer')->user()->id)->first();
        return response()->json($order);
    }
    public function downloadReceipt($order_no, $mobile = null)
    {
        $packageCheckout = \App\Models\Ecommerce\PackageOrderCheckout::whereHas('saleOrder', fn($q) => $q->where('order_no', $order_no))->first();
        if ($packageCheckout) {
            abort_unless(auth()->guard('customer')->id() == $packageCheckout->customer_id, 403);
            $order = app(\App\Services\Packages\PackageOrderService::class)->data($packageCheckout);
            return response()->view('ecommerce.packages.receipt', compact('order'));
        }

        // Fetch the order (guest or logged-in user)
        $order = SaleOrder::with('products')
            ->where('order_no', $order_no)
            ->when($mobile !== null, fn ($q) => $q->where('billing_mobile_no', $mobile), fn ($q) => $q->where('customer_id', auth()->guard('customer')->id()))
            ->where('type', SaleType::$ECOMMERCE)
            ->firstOrFail();

        // Load a view to generate PDF
        $pdf = PDF::loadView('ecommerce.order.pdf.print', compact('order'));

        // Download PDF
        return $pdf->download("Order-{$order->order_no}.pdf");
    }
}

