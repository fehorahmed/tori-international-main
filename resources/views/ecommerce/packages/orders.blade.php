@extends('layouts.app')
@section('title', 'Package Orders')
@section('content')
<div class="container-fluid"><table class="table table-bordered"><thead><tr><th>Order</th><th>Customer</th><th>Total</th><th>Status</th><th></th></tr></thead><tbody>
@forelse($orders as $checkout)<tr><td>{{ $checkout->saleOrder->order_no }}</td><td>{{ $checkout->saleOrder->customer?->name }}</td><td>{{ $checkout->saleOrder->total }}</td><td>{{ $checkout->status }}</td><td><a href="{{ route('package-orders.show',$checkout) }}">View order</a></td></tr>@empty<tr><td colspan="5">No package orders.</td></tr>@endforelse
</tbody></table>{{ $orders->links() }}</div>
@endsection
