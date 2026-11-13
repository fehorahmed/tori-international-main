@extends('layouts.app')
@section('title', 'Ecommerce Order')
@section('content')
<div class="container-fluid">
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<h4>{{ $order['order_no'] }} — {{ $checkout->status }} @if($order['status'] == 2) / Dispatched @elseif($order['status'] == 3) / Delivered @endif</h4>
<p>{{ $order['billing_name'] }} / {{ $order['billing_mobile_no'] }}<br>{{ $order['billing_address'] }}</p>
<p>Payment: Cash on Delivery. Stock is checked again at confirmation; payment is recorded separately after collection.</p>
<table class="table table-bordered"><thead><tr><th>Item</th><th>Contents per item</th><th>Qty</th><th>Unit price</th><th>Total</th></tr></thead><tbody>
@foreach($order['products'] as $line)<tr><td>{{ $line['name'] }}</td><td>@foreach($line['components'] as $part){{ $part['name'] }} × {{ $part['quantity'] }}<br>@endforeach</td><td>{{ $line['quantity'] }}</td><td>{{ $line['unit_price'] }}</td><td>{{ $line['line_total'] }}</td></tr>@endforeach
</tbody></table><p>Products: Tk {{ $order['sub_total'] }} | Delivery: Tk {{ $order['shipping_charge'] }} | Total: Tk {{ $order['total'] }} | Due: Tk {{ $order['due'] }}</p>
@if($checkout->status === 'Pending')
@can('ecommerce_order_confirm')<form method="POST" action="{{ route('package-orders.confirm',$checkout) }}" onsubmit="return confirm('Confirm this order and deduct stock?')">@csrf<button class="btn btn-success" type="submit">Confirm order &amp; deduct stock</button></form>@endcan
@can('ecommerce_order_confirm')<form class="mt-2" method="POST" action="{{ route('package-orders.cancel',$checkout) }}" onsubmit="return confirm('Cancel this pending order?')">@csrf<button class="btn btn-danger" type="submit">Cancel order</button></form>@endcan
@endif

@if($checkout->status === 'Confirmed' && $order['status'] < 3)
@can('ecommerce_order_confirm')<form method="POST" class="mt-3" action="{{ route('package-orders.status',$checkout) }}">@csrf<select name="status"><option value="3">Delivered</option>@if($order['status'] < 2)<option value="2">Dispatched</option>@endif</select><button class="btn btn-primary" type="submit">Update delivery status</button></form>@endcan
@endif
</div>
@endsection
