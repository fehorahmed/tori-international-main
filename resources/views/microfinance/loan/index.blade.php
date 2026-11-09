@extends('layouts.app')
@section('title', $pageTitle)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary" style="border-radius: 15px 15px 0 0;">
                    <h3 class="card-title mb-0"><i class="fa fa-hand-holding-usd mr-2"></i> {{ $pageTitle }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('microfinance.loan.create') }}" class="btn btn-light btn-sm shadow-sm" style="border-radius: 10px;">
                            <i class="fa fa-plus-circle mr-1"></i> New Loan Application
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover data-table" style="width: 100%;">
                            <thead class="bg-light">
                                <tr>
                                    <th>SL</th>
                                    <th>Loan No</th>
                                    <th>Member Info</th>
                                    <th>Somiti</th>
                                    <th>Product</th>
                                    <th>Applied Amt.</th>
                                    <th>Status</th>
                                    <th width="120px">Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(function () {
        var table = $('.data-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('microfinance.loan.datatable') }}",
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                {data: 'loan_no', name: 'loan_no'},
                {data: 'member_info', name: 'member_info'},
                {data: 'somiti.name', name: 'somiti.name', defaultContent: 'N/A'},
                {data: 'product.name', name: 'product.name'},
                {data: 'applied_amount', name: 'applied_amount'},
                {data: 'status', name: 'status'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });

        // Handle Reject Button Click
        $(document).on('click', '.btn-reject', function() {
            var loanId = $(this).data('id');
            var rejectUrl = "{{ route('microfinance.loan.reject', ':id') }}".replace(':id', loanId);
            
            if (confirm('আপনি কি নিশ্চিত যে এই ঋণের আবেদনটি বাতিল (Reject) করতে চান?')) {
                $.ajax({
                    url: rejectUrl,
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('সাফল্যের সাথে ঋণ আবেদনটি বাতিল করা হয়েছে।');
                            table.ajax.reload();
                        } else {
                            alert('ভুল: ' + response.message);
                        }
                    },
                    error: function(xhr) {
                        var err = xhr.responseJSON ? xhr.responseJSON.message : 'আবেদন বাতিল করতে সমস্যা হয়েছে।';
                        alert('ভুল: ' + err);
                    }
                });
            }
        });
    });
</script>
@endsection
