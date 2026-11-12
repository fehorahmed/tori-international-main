@extends('layouts.app')
@section('title', $package->exists ? 'Edit Package' : 'Create Package')
@section('content')
<div class="container-fluid">
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<form method="POST" enctype="multipart/form-data" action="{{ $package->exists ? route('product-packages.update',$package) : route('product-packages.store') }}">@csrf
@if($package->exists)@method('PUT')@endif
<div class="card"><div class="card-body"><div class="row">
<div class="col-md-6 form-group"><label>Package name</label><input class="form-control" name="name" required maxlength="255" value="{{ old('name',$package->name) }}"></div>
<div class="col-md-3 form-group"><label>Package price (Tk)</label><input class="form-control" name="selling_price" type="number" min="0.01" max="10000000" step="0.01" required value="{{ old('selling_price',$package->selling_price) }}"></div>
<div class="col-md-3 form-group"><label>Package unit</label><select class="form-control" name="unit_id" required>@foreach($units as $unit)<option value="{{ $unit->id }}" @selected(old('unit_id',$package->unit_id)==$unit->id)>{{ $unit->name }}</option>@endforeach</select></div>
<div class="col-md-6 form-group"><label>Category</label><select class="form-control" name="category_id" required>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(old('category_id',$package->category_id)==$category->id)>{{ $category->name }}</option>@endforeach</select></div>
<div class="col-md-6 form-group"><label>Fulfilment branch / warehouse</label><select class="form-control" name="package_warehouse_id" required>@foreach($warehouses as $warehouse)<option value="{{ $warehouse->id }}" @selected(old('package_warehouse_id',$package->package_warehouse_id)==$warehouse->id)>{{ $warehouse->sisterConcern?->name }} — {{ $warehouse->name }}</option>@endforeach</select></div>
<div class="col-md-6 form-group"><label>Image</label><input class="form-control" type="file" name="image" accept="image/jpeg,image/png,image/webp"></div>
<div class="col-md-3 form-group"><label>Status</label><select class="form-control" name="status"><option value="1" @selected(old('status',$package->status)==1)>Active</option><option value="0" @selected(old('status',$package->status)==0)>Inactive</option></select></div>
<div class="col-md-3 form-group"><label>Ecommerce</label><select class="form-control" name="is_show"><option value="0" @selected(old('is_show',$package->is_show)==0)>Hidden</option><option value="1" @selected(old('is_show',$package->is_show)==1)>Published</option></select></div>
<div class="col-12 form-group"><label>Description</label><textarea class="form-control" name="description" maxlength="10000">{{ old('description',$package->description) }}</textarea></div>
</div></div></div>
<div class="card"><div class="card-header">Products inside this package</div><div class="card-body">
<p>Select at least two different products. Quantities use each product's existing unit. A package cannot contain another package. Stock comes from the selected warehouse.</p>
<table class="table"><thead><tr><th>Product</th><th>Quantity per package</th><th></th></tr></thead><tbody id="package-items">
@foreach(old('items',$package->packageItems->map(fn($i)=>['product_id'=>$i->product_id,'quantity'=>$i->quantity])->all()) ?: [['product_id'=>'','quantity'=>1],['product_id'=>'','quantity'=>1]] as $index=>$item)
<tr><td><select class="form-control" name="items[{{ $index }}][product_id]" required><option value="">Select product</option>@foreach($products as $product)<option value="{{ $product->id }}" @selected($item['product_id']==$product->id)>{{ $product->name }} ({{ $product->unit?->name }})</option>@endforeach</select></td><td><input class="form-control" type="number" name="items[{{ $index }}][quantity]" min="1" max="10000" step="1" value="{{ $item['quantity'] }}" required></td><td><button type="button" class="btn btn-outline-danger remove-item">Remove</button></td></tr>
@endforeach
</tbody></table><button type="button" class="btn btn-secondary" id="add-item">Add product</button>
</div></div><button class="btn btn-primary" type="submit">Save Package</button>
</form></div>
@endsection
@push('script')
<script>
const rows=document.getElementById('package-items');let rowIndex=Math.max(...Array.from(rows.querySelectorAll('select')).map(select=>Number(select.name.match(/items\[(\d+)\]/)[1])))+1;
document.getElementById('add-item').addEventListener('click',()=>{const row=rows.firstElementChild.cloneNode(true);row.querySelector('select').name=`items[${rowIndex}][product_id]`;row.querySelector('select').value='';const input=row.querySelector('input');input.name=`items[${rowIndex}][quantity]`;input.value=1;rowIndex++;rows.appendChild(row);});
rows.addEventListener('click',event=>{if(event.target.classList.contains('remove-item')&&rows.children.length>2)event.target.closest('tr').remove();});
</script>
@endpush
