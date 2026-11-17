@extends('layouts.app')
@section('title', 'Special Offers')
@section('content')
<div class="container-fluid">
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
<a class="btn btn-primary mb-3" href="{{ route('offer-campaigns.create') }}">Create campaign</a>
<div class="card"><div class="card-body"><div class="table-responsive"><table id="table" class="table table-bordered table-striped"><thead><tr><th>#</th><th>Campaign</th><th>Starts (Bangladesh)</th><th>Ends (Bangladesh)</th><th>Status</th><th>Action</th></tr></thead><tbody></tbody></table></div></div></div></div>
@endsection
@section('script')
<script>
$(function () {
 $('#table').DataTable({
  processing: true, serverSide: true,
  ajax: "{{ route('offer-campaigns.datatable') }}",
  pagingType: 'full_numbers', lengthMenu: [[10,25,50,-1],[10,25,50,"All"]],
  order: [[1, "desc"]],
  columns: [
   {data: "DT_RowIndex", name: "DT_RowIndex", orderable: false, searchable: false},
   {data: "campaign", name: "name", orderable: true, searchable: true, defaultContent: "—"},
   {data: "starts_at", name: "starts_at", orderable: true, searchable: true, defaultContent: "—"},
   {data: "ends_at", name: "ends_at", orderable: true, searchable: true, defaultContent: "—"},
   {data: "campaign_status", name: "campaign_status", orderable: false, searchable: false, defaultContent: "—"},
   {data: "action", name: "action", orderable: false, searchable: false, defaultContent: "—"},
  ],
  dom: 'lBfrtip', buttons: datatableButtons(), responsive: true, autoWidth: false, colReorder: true
 });
});
</script>
@endsection
