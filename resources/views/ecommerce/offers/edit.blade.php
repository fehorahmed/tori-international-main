@extends('layouts.app')
@section('title', $offer ? 'Edit Special Offer' : 'Create Special Offer')
@section('content')
<div class="container-fluid">
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<form method="POST" enctype="multipart/form-data" action="{{ $offer ? route('offer-campaigns.update',$offer->id) : route('offer-campaigns.store') }}">@csrf
<div class="row">
<div class="col-md-6 form-group"><label>Campaign name</label><input class="form-control" name="name" required maxlength="255" value="{{ old('name',$offer?->name) }}"></div>
<div class="col-md-6 form-group"><label>URL slug (optional; generated if blank)</label><input class="form-control" name="slug" maxlength="200" pattern="[a-z0-9-]+" value="{{ old('slug',$offer?->slug) }}"></div>
<div class="col-md-4 form-group"><label>Starts at (Bangladesh time)</label><input class="form-control" type="datetime-local" name="starts_at" required value="{{ old('starts_at',$offer ? \Carbon\Carbon::parse($offer->starts_at)->setTimezone('Asia/Dhaka')->format('Y-m-d\TH:i') : '') }}"></div>
<div class="col-md-4 form-group"><label>Ends at (Bangladesh time)</label><input class="form-control" type="datetime-local" name="ends_at" required value="{{ old('ends_at',$offer ? \Carbon\Carbon::parse($offer->ends_at)->setTimezone('Asia/Dhaka')->format('Y-m-d\TH:i') : '') }}"></div>
<div class="col-md-4 form-group"><label>Status</label><select class="form-control" name="is_active"><option value="1" @selected(old('is_active',$offer?->is_active ?? 1)==1)>Active</option><option value="0" @selected(old('is_active',$offer?->is_active ?? 1)==0)>Inactive</option></select></div>
</div>
<div class="form-group"><label for="campaign-banner">Campaign banner</label>
<input id="campaign-banner" class="form-control" type="file" name="banner_image" accept="image/jpeg,image/png,image/webp">
<small class="text-muted">JPG, PNG or WebP, up to 5 MB and 4096 × 4096 pixels. Recommended: 1600 × 500 pixels. Live campaign banners appear on the home page.</small>
@if($offer?->banner_image)<div class="mt-2"><img src="{{ asset($offer->banner_image) }}" alt="Current campaign banner" style="max-width:360px;width:100%;height:auto;"></div>
<label class="mt-2"><input type="checkbox" name="remove_banner" value="1"> Remove current banner</label>@endif
</div>
<p>Discounts use the original Selling Price. Customers receive the best of general, eligible member and active campaign discounts. Discounts are never stacked. Prices shown below are previews; stock uses existing product inventory.</p>
<div class="table-responsive"><table class="table table-bordered"><thead><tr><th>Product / Package</th><th>MRP (Tk)</th><th>Discount type</th><th>Value</th><th>Offer price preview (Tk)</th><th>Status</th><th></th></tr></thead><tbody id="offer-items"></tbody></table></div>
<button type="button" id="add-offer-item" class="btn btn-outline-primary">Add product / package</button>
<button class="btn btn-primary" type="submit">Save campaign</button>
<a class="btn btn-secondary" href="{{ route('offer-campaigns.index') }}">Back</a>
</form></div>
@endsection
@push('script')
<script>
(() => {
 const products = @json($products);
 const existing = @json(old('items',$items));
 const rows = document.getElementById('offer-items');
 let index = 0;
 function add(item = {}) {
  const i = index++;
  const row = document.createElement('tr');
  row.innerHTML = `<td><select class="form-control product" name="items[${i}][product_id]" required></select></td><td class="mrp">—</td><td><select class="form-control type" name="items[${i}][discount_type]"><option value="percentage">Percentage (%)</option><option value="fixed">Fixed Tk</option></select></td><td><input class="form-control value" name="items[${i}][discount_value]" type="number" min="0.01" max="10000000" step="0.01" required></td><td class="preview">—</td><td><select class="form-control active" name="items[${i}][is_active]"><option value="1">Active</option><option value="0">Inactive</option></select></td><td><button type="button" class="btn btn-outline-danger remove">Remove</button></td>`;
  const select = row.querySelector('.product'); select.add(new Option('Select product / package', ''));
  products.forEach(p => select.add(new Option(`${p.name} (${p.catalog_type === 'package' ? 'Package' : 'Product'})`,p.id)));
  select.value = item.product_id || '';
  row.querySelector('.type').value = item.discount_type || 'percentage';
  row.querySelector('.value').value = item.discount_value || '';
  row.querySelector('.active').value = item.is_active === false || String(item.is_active) === '0' ? '0' : '1';
  function preview() {
   const p = products.find(p => String(p.id) === select.value);
   const regular = p ? Math.max(Number(p.selling_price),Number(p.old_selling_price || 0)) : 0;
   row.querySelector('.mrp').textContent = p ? regular.toFixed(2) : '—';
   const value = Number(row.querySelector('.value').value);
   const discount = row.querySelector('.type').value === 'percentage' ? Math.round(regular*value)/100 : value;
   row.querySelector('.preview').textContent = p && value > 0 && discount < regular ? (regular-discount).toFixed(2) : 'Invalid / select product';
  }
  row.addEventListener('input',preview); row.addEventListener('change',preview);
  row.querySelector('.remove').addEventListener('click',()=>row.remove());
  rows.appendChild(row); preview();
 }
 document.getElementById('add-offer-item').addEventListener('click',()=>add());
 if(existing.length) existing.forEach(add); else add();
})();
</script>
@endpush
