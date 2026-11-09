@extends('layouts.app')
@section('title','Stock Conversion Report')
@section('style')
    <style>
        .table-bordered thead td, .table-bordered thead th {
            vertical-align: middle;
            border: 1px solid #000!important;
            padding: 4px;
        }
        .table-bordered td, .table-bordered th {
            border: 1px solid #000000!important;
            vertical-align: middle;
            padding: 4px;
        }
        .inner-table td, .inner-table th {
            border: 1px solid #ddd!important;
            font-size: 0.85rem;
        }
        @page{
            margin: .3in .5in .5in .5in;
        }
    </style>
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    <div class="card-title"><i class="fa fa-filter text-info mr-1"></i> Report Filter</div>
                </div>
                <div class="card-body">
                    <form action="{{ route('stock-conversions.report') }}">
                        <div class="row">
                            <div class="col-12 col-md-3">
                                <div class="form-group">
                                    <label>Warehouse</label>
                                    <select class="form-control select2" name="warehouse_id">
                                        <option value="">All Warehouses</option>
                                        @foreach ($warehouses as $warehouse)
                                            <option value="{{ $warehouse->id }}" {{ request('warehouse_id') == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-12 col-md-3">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select class="form-control select2" name="status">
                                        <option value="">All Statuses</option>
                                        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Pending</option>
                                        <option value="2" {{ request('status') === '2' ? 'selected' : '' }}>Approved</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-12 col-md-2">
                                <div class="form-group">
                                    <label for="start_date" class="col-form-label">Start Date <span class="text-danger">*</span></label>
                                    <input required autocomplete="off" type="text" value="{{ request('start_date', \Carbon\Carbon::parse(auth()->user()->activeSisterConcern->business_date ?? now())->format('d-m-Y')) }}"
                                           name="start_date" class="form-control date-picker" id="start_date"
                                           placeholder="Enter Start Date">
                                </div>
                            </div>
                            <div class="col-12 col-md-2">
                                <div class="form-group">
                                    <label for="end_date" class="col-form-label">End Date <span class="text-danger">*</span></label>
                                    <input required autocomplete="off" type="text" value="{{ request('end_date', \Carbon\Carbon::parse(auth()->user()->activeSisterConcern->business_date ?? now())->format('d-m-Y')) }}"
                                           name="end_date" class="form-control date-picker" id="end_date"
                                           placeholder="Enter End Date">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <input style="margin-top: -1px;" type="submit" name="search"
                                           class="btn btn-primary bg-gradient-primary form-control" value="Search">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @if(request()->filled('start_date'))
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    <a role="button" onclick="getprint('printArea')" class="btn btn-primary bg-gradient-primary btn-sm"><i class="fa fa-print"></i></a>
                </div>
                <div class="card-body">
                    <div class="table-responsive-md" id="printArea">
                        @include('layouts.partial.__report_header',['report_title' => 'Stock Conversion Report'])
                        
                        <div class="row header-common-info">
                            <div class="col-12 text-center">
                                @if(request('warehouse_id'))
                                    @php $selectedWh = $warehouses->firstWhere('id', request('warehouse_id')); @endphp
                                    <h5 class="m-0">Warehouse: <b>{{ $selectedWh->name ?? 'All Warehouses' }}</b></h5>
                                @endif
                                <h5 class="m-0">Date: <b>{{ request('start_date') }} to {{ request('end_date') }}</b></h5>
                            </div>
                        </div>
                        
                        @if(count($conversions) > 0)
                        <table class="table table-bordered mt-3">
                            <thead>
                                <tr class="bg-light">
                                    <th class="text-center" width="5%">Sl</th>
                                    <th class="text-center" width="10%">Date</th>
                                    <th class="text-center" width="15%">Conversion No</th>
                                    <th class="text-center" width="15%">Warehouse</th>
                                    <th class="text-center" width="45%">Conversion Details</th>
                                    <th class="text-center" width="10%">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($conversions as $index => $conversion)
                                    <tr>
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td class="text-center">{{ \Carbon\Carbon::parse($conversion->date)->format('d-m-Y') }}</td>
                                        <td class="text-center">{{ $conversion->order_no ?? $conversion->conversion_no }}</td>
                                        <td class="text-center">{{ $conversion->warehouse->name ?? 'N/A' }}</td>
                                        <td class="p-0">
                                            <table class="table table-sm inner-table m-0">
                                                <thead class="bg-white">
                                                    <tr>
                                                        <th>From Product</th>
                                                        <th>To Product</th>
                                                        <th class="text-center">Qty</th>
                                                        <th class="text-right">Conv. Cost</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($conversion->items as $item)
                                                        <tr>
                                                            <td>{{ $item->fromProduct->name ?? 'N/A' }}</td>
                                                            <td>{{ $item->toProduct->name ?? 'N/A' }}</td>
                                                            <td class="text-center">{{ $item->quantity }}</td>
                                                            <td class="text-right">{{ number_format($item->unit_conversion_cost, 2) }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </td>
                                        <td class="text-center">
                                            @if($conversion->status == 1)
                                                Pending
                                            @elseif($conversion->status == 2)
                                                Approved
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @else
                            <div class="alert alert-warning text-center">No stock conversions found for the selected criteria.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
@endsection
@section('script')
    <script>
        var initPrintScript = true;
    </script>
@endsection
