@extends('layouts.app')
@section('title','Stock Transfer — Manual Return')
@section('style')
    <style>
        .table-bordered td, .table-bordered th {
            border: 1px solid #000 !important;
            padding: 4px 6px;
        }
        .table thead th {
            vertical-align: bottom;
            border-bottom: 2px solid #000 !important;
        }
        .return-input {
            width: 90px;
            text-align: center;
            border: 1px solid #ffc107;
            border-radius: 4px;
            padding: 2px 4px;
        }
        .table-bordered td, .table-bordered th { font-size: 14px !important; }
    </style>
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">Initiate Manual Return</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        {{-- Transfer Info --}}
                        <div class="row pt-3 pb-2">
                            <div class="col-4">
                                <h5 class="m-0">Returning To: <strong>{{ $stockTransfer->fromSisterConcern->name ?? ($stockTransfer->fromWarehouse->name ?? '') }}</strong></h5>
                            </div>
                            <div class="col-4">
                                <h5 class="m-0">Original Order: <strong>{{ $stockTransfer->order_no }}</strong></h5>
                            </div>
                            <div class="col-4">
                                <h5 class="m-0">Date: <strong>{{ \Carbon\Carbon::parse($stockTransfer->date)->format('d-m-Y') }}</strong></h5>
                            </div>
                        </div>

                        <div class="alert alert-warning py-2">
                            <i class="fa fa-info-circle"></i>
                            <strong>Return Mode:</strong> Enter the quantity you wish to return for each item. 
                            You can only return up to the maximum returnable quantity.
                        </div>

                        <form action="{{ route('stock-transfers.manual_return_store', ['stock_transfer' => $stockTransfer->id]) }}" method="post" id="returnForm">
                            @csrf
                            
                            <div class="row mb-3 mt-4">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Return Reason <span class="text-danger">*</span></label>
                                        <select class="form-control select2" name="return_reason" required>
                                            <option value="">-- Select Reason --</option>
                                            <option value="Damaged Product">Damaged Product</option>
                                            <option value="Defective/Faulty">Defective / Faulty</option>
                                            <option value="Unsold/Dead Stock">Unsold / Dead Stock</option>
                                            <option value="Replacement Request">Replacement Request</option>
                                            <option value="Expired">Expired</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <table class="table table-bordered mt-3">
                                <thead>
                                <tr>
                                    <th class="text-center">#</th>
                                    <th>Product</th>
                                    <th class="text-center">Code</th>
                                    <th class="text-center">Total Received</th>
                                    <th class="text-center">Already Returned</th>
                                    <th class="text-center text-success">Max Returnable</th>
                                    <th class="text-center text-warning" style="width: 150px;">Return Quantity</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($stockTransfer->stockTransferItems as $item)
                                    @php
                                        $maxReturnable = $item->received_quantity - $item->return_quantity;
                                    @endphp
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td>{{ $item->product->name ?? '' }}</td>
                                        <td class="text-center">{{ $item->product_code }}</td>
                                        <td class="text-center"><strong>{{ number_format($item->received_quantity, 0) }}</strong></td>
                                        <td class="text-center text-danger">{{ number_format($item->return_quantity, 0) }}</td>
                                        <td class="text-center text-success font-weight-bold">{{ number_format($maxReturnable, 0) }}</td>
                                        
                                        <td class="text-center">
                                            @if($maxReturnable > 0)
                                                <input
                                                    type="number"
                                                    class="return-input"
                                                    name="return_quantity[{{ $item->id }}]"
                                                    value="0"
                                                    min="0"
                                                    max="{{ $maxReturnable }}"
                                                    step="1"
                                                >
                                            @else
                                                <span class="badge badge-secondary">Fully Returned</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                            
                            <div class="mt-4">
                                <button type="submit" class="btn btn-warning bg-gradient-warning font-weight-bold" id="submitBtn">
                                    <i class="fa fa-undo"></i> Create Return Transfer
                                </button>
                                <a href="{{ route('stock-transfers.incoming_history') }}" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
<script>
    $('#returnForm').on('submit', function(e) {
        let hasValue = false;
        $('.return-input').each(function() {
            if (parseFloat($(this).val()) > 0) {
                hasValue = true;
            }
        });
        
        if (!hasValue) {
            e.preventDefault();
            Swal.fire('Error', 'Please enter a return quantity for at least one item.', 'error');
            return;
        }

        // Prevent default submission to show confirmation
        e.preventDefault();
        var form = this;

        Swal.fire({
            title: 'Are you sure?',
            text: "You are about to create a return transfer for the selected items.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f39c12',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, create return!'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
</script>
@endsection
