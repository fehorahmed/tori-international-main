<div class="card card-default">
    <div class="card-header border-bottom-0 d-flex justify-content-between">
        <h3 class="card-title">Product Wise Profit / Loss Report</h3>
        @include('layouts.partial.__export_buttons', ['printAreaId' => 'printArea', 'fileName' => 'Product_Profit_Loss_' . date('d_m_Y')])
    </div>
    <div class="card-body">
        <div class="table-responsive" id="printArea">
            @include('layouts.partial.__report_header')
            <h4 class="text-center mb-3">Product Wise Profit / Loss ({{ request('start_date') }} to {{ request('end_date') }})</h4>
            <table class="table table-bordered table-striped" id="reportDT">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Product Name</th>
                        <th class="text-center">Sold Qty</th>
                        <th class="text-right">Total Revenue</th>
                        <th class="text-right">Total Cost</th>
                        <th class="text-right">Profit / Loss</th>
                    </tr>
                </thead>
                <tbody>
                    @php 
                        $totalQty = 0; 
                        $totalRev = 0; 
                        $totalCst = 0; 
                        $totalPrf = 0; 
                    @endphp
                    @foreach($productsData as $data)
                        @php
                            $totalQty += $data->sold_qty;
                            $totalRev += $data->total_revenue;
                            $totalCst += $data->total_cost;
                            $totalPrf += $data->profit;
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $data->product_name }}</td>
                            <td class="text-center">{{ $data->sold_qty }}</td>
                            <td class="text-right">{{ number_format($data->total_revenue, 2) }}</td>
                            <td class="text-right">{{ number_format($data->total_cost, 2) }}</td>
                            <td class="text-right font-weight-bold {{ $data->profit >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ number_format($data->profit, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="font-weight-bold bg-light">
                        <td colspan="2" class="text-right">Total:</td>
                        <td class="text-center">{{ $totalQty }}</td>
                        <td class="text-right">{{ number_format($totalRev, 2) }}</td>
                        <td class="text-right">{{ number_format($totalCst, 2) }}</td>
                        <td class="text-right {{ $totalPrf >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($totalPrf, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

@include('layouts.partial.__export_scripts')
<script>
    if (!$.fn.DataTable.isDataTable('#reportDT')) {
        $('#reportDT').DataTable({
            "paging": false,
            "lengthChange": false,
            "searching": true,
            "ordering": true,
            "info": false,
            "autoWidth": false,
            "responsive": true,
        });
    }
</script>
