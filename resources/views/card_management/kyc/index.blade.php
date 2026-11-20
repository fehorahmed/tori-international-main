@extends('layouts.app')
@section('title', 'Membership KYC Review')
@section('content')
<div class="card">
<div class="card-header"><h3 class="card-title">Membership KYC — Active Branch</h3></div>
<div class="card-body">
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<form id="kyc-filters" class="row mb-3">
<div class="col-md-3"><label for="from_date">From date</label><input class="form-control" type="date" id="from_date"></div>
<div class="col-md-3"><label for="to_date">To date</label><input class="form-control" type="date" id="to_date"></div>
<div class="col-md-3"><label for="status">Status</label><select id="status" class="form-control"><option value="">All statuses</option><option value="Pending">Pending</option><option value="Verified">Approved</option><option value="Rejected">Rejected</option></select></div>
<div class="col-md-3 align-self-end"><button class="btn btn-primary" type="submit">Search</button> <button class="btn btn-secondary" type="reset">Reset</button></div>
</form>
<div class="table-responsive"><table id="table" class="table table-bordered"><thead><tr><th>ID</th><th>Member ID</th><th>Name</th><th>Submitted</th><th>Status</th><th>Review</th></tr></thead></table></div>
</div></div>
@endsection
@section('script')
<script>
$(function () {
 let filters = {};
 const table = $('#table').DataTable({processing:true, serverSide:true, order:[[0,'desc']],
 ajax:{url:window.location.pathname, data:function(d){Object.assign(d,filters);}},
 columns:[{data:'id'},{data:'customer.member_no',name:'customer.member_no',defaultContent:''},{data:'submitted_name'},{data:'created_at'},{data:'status'},{data:'review',orderable:false,searchable:false}]});
 $('#kyc-filters').on('submit', function(e){e.preventDefault();filters={from_date:$('#from_date').val(),to_date:$('#to_date').val(),status:$('#status').val()};table.ajax.reload();});
 $('#kyc-filters').on('reset',function(){filters={};table.search('').columns().search('');table.ajax.reload();});
 $('#table').on('click','.kyc-review',function(){const row=table.row($(this).closest('tr'));if(row.child.isShown()){row.child.hide();}else{row.child(row.data().details).show();}});
});
</script>
@endsection
