@extends('layouts.app')
@section('title')
   @if($product)
       @if($product->brand)
           Brand: {{ $product->brand->name ?? '' }} -
        @endif
    {{ $product->name ?? '' }} - Unit: {{ $product->unit->name ?? '' }}
    @endif
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">Inventory Transaction Logs (Batch: {{ $inventory->batch_no ?? '-' }})</h3>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <form action="{{ route('inventory.details') }}" method="GET" class="mb-4">
                        <input type="hidden" name="inventory" value="{{ $inventory->id }}">
                        <div class="row align-items-end">
                            <div class="col-md-3">
                                <div class="form-group mb-0">
                                    <label>Start Date</label>
                                    <input type="text" class="form-control date-picker" name="start_date" value="{{ $startDate->format('d-m-Y') }}" autocomplete="off">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-0">
                                    <label>End Date</label>
                                    <input type="text" class="form-control date-picker" name="end_date" value="{{ $endDate->format('d-m-Y') }}" autocomplete="off">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-info btn-block">Filter</button>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table id="table" class="table table-bordered text-center">
                            <thead>
                            <tr>
                                <th width="15%" class="text-center">Date</th>
                                <th class="text-left">Description</th>
                                <th width="10%" class="text-center">In</th>
                                <th width="10%" class="text-center">Out</th>
                                <th width="15%" class="text-center">Balance</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>{{ $startDate->format('d-m-Y') }}</td>
                                <td class="text-left font-weight-bold">Opening Balance</td>
                                <td></td>
                                <td></td>
                                <td class="font-weight-bold">{{ number_format($openingBalance, 2) }}</td>
                            </tr>
                            @php
                                $balance = $openingBalance;
                            @endphp
                            @forelse($logs as $log)
                                @php
                                    $description = '';
                                    if($log->type == \App\Enumeration\InventoryLogType::$PURCHASE_IN) {
                                        $route = $log->purchase_order_id ? route('purchase.details', ['purchase' => $log->purchase_order_id]) : '#';
                                        $description = 'Purchase In - <a href="'.$route.'" class="text-info font-weight-bold">'.($log->purchaseOrder->order_no ?? '').'</a>';
                                    } else if($log->type == \App\Enumeration\InventoryLogType::$SALE_OUT) {
                                        $orderNo = $log->saleOrder->order_no ?? '';
                                        $route = '#';
                                        if ($log->sale_order_id && $log->saleOrder) {
                                            $route = route('sales.details', ['sale' => $log->sale_order_id]);
                                            if ($log->saleOrder->type == \App\Enumeration\SaleType::$PRE_ORDER) {
                                                $route = route('advance-sales.show', ['advance_sale' => $log->sale_order_id]);
                                            } elseif ($log->saleOrder->type == \App\Enumeration\SaleType::$EMI) {
                                                $route = route('emi-sales.show', ['emi_sale' => $log->sale_order_id]);
                                            } elseif ($log->saleOrder->type == \App\Enumeration\SaleType::$CORPORATE) {
                                                $route = route('corporate-sales.show', ['corporate_sale' => $log->sale_order_id]);
                                            } elseif ($log->saleOrder->type == \App\Enumeration\SaleType::$DEALER) {
                                                $route = route('dealer-sales.show', ['dealer_sale' => $log->sale_order_id]);
                                            }
                                        }
                                        $description = 'Sale Out - <a href="'.$route.'" class="text-info font-weight-bold">' . $orderNo . '</a>';
                                    } else if($log->type == \App\Enumeration\InventoryLogType::$SALES_RETURN_IN) {
                                        $route = $log->sales_return_id ? route('returns-management.sales-returns.show', $log->sales_return_id) : '#';
                                        $description = 'Sales Return In - <a href="'.$route.'" class="text-info font-weight-bold">'.($log->salesReturn->return_no ?? '').'</a>';
                                    } else if($log->type == \App\Enumeration\InventoryLogType::$OPENING_STOCK) {
                                        $description = 'Opening Stock Entry';
                                    } else if($log->type == \App\Enumeration\InventoryLogType::$STOCK_TRANSFER_OUT || $log->type == \App\Enumeration\InventoryLogType::$STOCK_TRANSFER_IN) {
                                        $route = $log->stock_transfer_id ? route('stock-transfers.details', ['stock_transfer' => $log->stock_transfer_id]) : '#';
                                        $typeStr = $log->type == \App\Enumeration\InventoryLogType::$STOCK_TRANSFER_IN ? 'Stock Transfer In' : 'Stock Transfer Out';
                                        $description = $typeStr . ' - <a href="'.$route.'" class="text-info font-weight-bold">'.($log->stockTransfer->transfer_no ?? '').'</a>';
                                    } else if($log->type == \App\Enumeration\InventoryLogType::$FINISHED_GOODS_MANUFACTURED) {
                                        $description = 'Finished Goods Manufactured';
                                    } else if($log->type == \App\Enumeration\InventoryLogType::$SALES_REPLACEMENT_OUT) {
                                        $route = $log->sales_return_id ? route('returns-management.sales-returns.show', $log->sales_return_id) : '#';
                                        $description = 'Sales Replacement Out - <a href="'.$route.'" class="text-info font-weight-bold">'.($log->salesReturn->return_no ?? '').'</a>';
                                    } else if($log->type == \App\Enumeration\InventoryLogType::$PURCHASE_RETURN_OUT) {
                                        $route = $log->purchase_return_id ? route('returns-management.purchase-returns.show', $log->purchase_return_id) : '#';
                                        $description = 'Purchase Return Out - <a href="'.$route.'" class="text-info font-weight-bold">'.($log->purchaseReturn->return_no ?? '').'</a>';
                                    } else if($log->type == \App\Enumeration\InventoryLogType::$PURCHASE_REPLACEMENT_IN) {
                                        $route = $log->purchase_return_id ? route('returns-management.purchase-returns.show', $log->purchase_return_id) : '#';
                                        $description = 'Purchase Replacement In - <a href="'.$route.'" class="text-info font-weight-bold">'.($log->purchaseReturn->return_no ?? '').'</a>';
                                    } else {
                                        $description = 'Other (Type: '.$log->type.')';
                                    }
                                    
                                    $inQty = in_array($log->type, $inTypes) ? $log->quantity : 0;
                                    $outQty = !in_array($log->type, $inTypes) ? $log->quantity : 0;
                                    
                                    $balance += $inQty;
                                    $balance -= $outQty;
                                @endphp
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($log->date)->format('d-m-Y') }}</td>
                                    <td class="text-left">{!! $description !!}</td>
                                    <td class="text-success">{{ $inQty > 0 ? number_format($inQty, 2) : '-' }}</td>
                                    <td class="text-danger">{{ $outQty > 0 ? number_format($outQty, 2) : '-' }}</td>
                                    <td class="font-weight-bold {{ $balance >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $balance > 0 ? '+' : '' }}{{ number_format($balance, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">No transaction logs found for this inventory batch.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- /.card-body -->
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        $(function () {
            $('#table').DataTable({
                "pagingType": "full_numbers",
                "lengthMenu": [[10, 25, 50, -1],[10, 25, 50, "All"]],
                "ordering": false, // Disable sorting to maintain the correct running balance order
                "dom": 'lBfrtip',
                "buttons": datatableButtons(),
                "responsive": true, 
                "autoWidth": false
            });
        });
    </script>
@endsection
