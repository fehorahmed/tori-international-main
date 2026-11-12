@extends('layouts.app')
@section('title', 'Product Packages')
@section('content')
<div class="container-fluid">
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@can('ecommerce_product_create')<a class="btn btn-primary mb-3" href="{{ route('product-packages.create') }}">Create Package</a>@endcan
<div class="table-responsive"><table class="table table-bordered"><thead><tr><th>Package</th><th>Products</th><th>Price</th><th>Source</th><th>Available packages</th><th>Status</th><th></th></tr></thead><tbody>
@forelse($packages as $package)<tr><td>{{ $package->name }}<br>{{ $package->code }}</td><td>@foreach($package->packageItems as $item){{ $item->product?->name }} × {{ $item->quantity }}<br>@endforeach</td><td>{{ $package->selling_price }}</td><td>{{ $package->packageWarehouse?->sisterConcern?->name }} / {{ $package->packageWarehouse?->name }}</td><td>{{ app(\App\Services\Packages\ProductPackageService::class)->availability($package) }}</td><td>{{ $package->status ? 'Active' : 'Inactive' }} / {{ $package->is_show ? 'Published' : 'Hidden' }}</td><td>@can('ecommerce_product_edit')<a class="btn btn-sm btn-primary" href="{{ route('product-packages.edit', $package) }}">Edit</a>@endcan</td></tr>
@empty<tr><td colspan="7">No packages created yet.</td></tr>@endforelse
</tbody></table></div>{{ $packages->links() }}
</div>
@endsection
