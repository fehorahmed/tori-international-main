@extends('layouts.app')
@section('title', 'Product Packages')
@section('content')
<div class="container-fluid">
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@can('ecommerce_product_create')<a class="btn btn-primary mb-3" href="{{ route('product-packages.create') }}">Create Package</a>@endcan
<div class="card"><div class="card-body"><div class="table-responsive"><table id="table" class="table table-bordered table-striped"><thead><tr><th>#</th><th>Package</th><th>Code</th><th>Products</th><th>Price</th><th>Source</th><th>Available packages</th><th>Status</th><th>Action</th></tr></thead><tbody></tbody></table></div></div></div></div>
@endsection
@section('script')
<script>
$(function () {
 $('#table').DataTable({
  processing: true, serverSide: true,
  ajax: "{{ route('product-packages.datatable') }}",
  pagingType: 'full_numbers', lengthMenu: [[10,25,50,-1],[10,25,50,"All"]],
  order: [[1, "desc"]],
  columns: [
   {data: "DT_RowIndex", name: "DT_RowIndex", orderable: false, searchable: false},
   {data: "package", name: "name", orderable: true, searchable: true, defaultContent: "—"},
   {data: "code", name: "code", orderable: true, searchable: true, defaultContent: "—"},
   {data: "components", name: "components", orderable: false, searchable: false, defaultContent: "—"},
   {data: "selling_price", name: "selling_price", orderable: true, searchable: true, defaultContent: "—"},
   {data: "source", name: "source", orderable: false, searchable: false, defaultContent: "—"},
   {data: "available", name: "available", orderable: false, searchable: false, defaultContent: "—"},
   {data: "package_status", name: "package_status", orderable: false, searchable: false, defaultContent: "—"},
   {data: "action", name: "action", orderable: false, searchable: false, defaultContent: "—"},
  ],
  dom: 'lBfrtip', buttons: datatableButtons(), responsive: true, autoWidth: false, colReorder: true
 });
});
</script>
@endsection
