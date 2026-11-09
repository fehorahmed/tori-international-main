<div class="card card-default">
    <div class="card-header border-bottom-0 d-flex justify-content-between">
        <h3 class="card-title">Invoice Wise Profit / Loss Report</h3>
        @include('layouts.partial.__export_buttons', ['printAreaId' => 'printArea', 'fileName' => 'Invoice_Profit_Loss_' . date('d_m_Y')])
    </div>
    <div class="card-body">
        <div class="table-responsive" id="printArea">
            @include('layouts.partial.__report_header')
            <h4 class="text-center mb-3">Invoice Wise Profit / Loss ({{ request('start_date') }} to {{ request('end_date') }})</h4>
            <table class="table table-bordered table-striped" id="reportDT">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Invoice Date</th>
                        <th>Invoice No</th>
                        <th>Customer</th>
                        <th class="text-right">Total Revenue</th>
                        <th class="text-right">Total Cost</th>
                        <th class="text-right">Profit / Loss</th>
                    </tr>
                </thead>
                <tbody>
                    @php 
                        $totalRev = 0; 
                        $totalCst = 0; 
                        $totalPrf = 0; 
                    @endphp
                    @foreach($orders as $order)
                        @php
                            $totalRev += $order->total;
                            $totalCst += $order->total_cost;
                            $totalPrf += $order->profit;
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ \Carbon\Carbon::parse($order->date)->format('d-m-Y') }}</td>
                            <td><a href="{{ route('sales.details', $order->id) }}" target="_blank">{{ $order->order_no }}</a></td>
                            <td>{{ $order->customer->name ?? '-' }}</td>
                            <td class="text-right">{{ number_format($order->total, 2) }}</td>
                            <td class="text-right">{{ number_format($order->total_cost, 2) }}</td>
                            <td class="text-right font-weight-bold {{ $order->profit >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ number_format($order->profit, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="font-weight-bold bg-light">
                        <td colspan="4" class="text-right">Total:</td>
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
