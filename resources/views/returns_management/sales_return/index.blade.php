@extends('layouts.app')
@section('title', 'Sales Returns')
@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-default">
            <div class="card-header">
                @if (auth()->user()->can('sales_return'))
                <a href="{{ route('returns-management.sales-returns.create') }}" class="btn btn-info bg-gradient-info btn-sm">Create Sales Return <i class="fa fa-plus"></i></a>
                @endif
            </div>
            
            <div class="card-header">
                <div class="row">
                    <div class="col-12 col-md-4">
                        <div class="form-group">
                            <label for="customer" class="col-form-label">Customer</label>
                            <select name="customer" id="customer" class="form-control select2">
                                <option value="">All Customer</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->member_no ? $customer->member_no . ' - ' : '' }}{{ $customer->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-12 col-md-2">
                        <div class="form-group">
                            <label for="start_date" class="col-form-label">Start Date</label>
                            <input autocomplete="off" type="text" value="{{ request('start_date', auth()->user()->business_date ? \Carbon\Carbon::parse(auth()->user()->business_date)->format('d-m-Y') : '') }}" name="start_date" class="form-control date-picker" id="start_date" placeholder="Enter Start Date">
                        </div>
                    </div>
                    <div class="col-12 col-md-2">
                        <div class="form-group">
                            <label for="end_date" class="col-form-label">End Date</label>
                            <input autocomplete="off" type="text" value="{{ request('end_date', auth()->user()->business_date ? \Carbon\Carbon::parse(auth()->user()->business_date)->format('d-m-Y') : '') }}" name="end_date" class="form-control date-picker" id="end_date" placeholder="Enter End Date">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="status" class="col-form-label">Status</label>
                            <select name="status" id="status" class="form-control form-control-sm select2">
                                <option value="">All</option>
                                <option value="Active">Active</option>
                                <option value="Deleted">Cancelled / Voided</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <input style="margin-top: -4px;" type="button" id="search_btn" name="search" class="btn btn-info bg-gradient-info form-control" value="Search">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card-body">
                <div class="table-responsive">
                    <table id="customer_returns_table" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th class="text-center">Sl</th>
                                <th class="text-center">Date</th>
                                <th class="text-center">Return No</th>
                                <th class="text-center">Customer</th>
                                <th class="text-center">Invoice No</th>
                                <th class="text-center">Net Amount</th>
                                <!-- <th class="text-center">Status</th> -->
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        var table = $('#customer_returns_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('returns-management.sales-returns.datatable') }}",
                data: function (d) {
                    d.customer = $("#customer").val();
                    d.start_date = $("#start_date").val();
                    d.end_date = $("#end_date").val();
                    d.status = $("#status").val();
                }
            },
            "pagingType": "full_numbers",
            "lengthMenu": [[10, 25, 50, -1],[10, 25, 50, "All"]],
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                { data: 'date', name: 'date', className: 'text-center' },
                { data: 'return_no', name: 'return_no', className: 'text-center' },
                { data: 'customer', name: 'customer' },
                { data: 'sale_order', name: 'sale_order', className: 'text-center' },
                { data: 'net_payable_or_receivable', name: 'net_payable_or_receivable', className: 'text-right' },
                // { data: 'status', name: 'status', className: 'text-center' },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
            ],
            order: [[1, 'desc']],
            "responsive": true, 
            "autoWidth": false
        });
        
        $('#search_btn, #customer, #start_date, #end_date, #status').on('change click', function() {
            table.ajax.reload();
        });
        
        $('body').on('click', '.btn-delete', function () {
            let id = $(this).data('id');
            Swal.fire({
                title: 'Are you sure?',
                text: "This will cancel the return, revert inventory, and reverse accounting!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, cancel it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    let deleteUrl = "{{ route('returns-management.sales-returns.destroy', ':id') }}";
                    deleteUrl = deleteUrl.replace(':id', id);
                    
                    $.ajax({
                        url: deleteUrl,
                        type: 'DELETE',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function (response) {
                            if (response.success) {
                                Swal.fire('Deleted!', response.message, 'success');
                                $('#customer_returns_table').DataTable().ajax.reload();
                            } else {
                                Swal.fire('Error!', response.message, 'error');
                            }
                        },
                        error: function (xhr) {
                            Swal.fire('Error!', 'Something went wrong.', 'error');
                        }
                    });
                }
            });
        });
    });
</script>
@endsection
