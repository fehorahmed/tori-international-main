@extends('layouts.app')
@section('title','Balance Sheet')
@section('style')
    <style>

        .table-bordered td, .table-bordered th {
            border: 1px solid #000 !important;
            vertical-align: middle !important;
            text-align: center;
            padding: 0.2rem !important;
            font-size: 19px !important;
        }
        .table-bordered td{
            border: none !important;
        }
        .table-bordered {
            border: 1px solid #ffffff;
        }
        table thead{
            border-collapse: inherit !important;
        }
        td a:hover{
            color: #000 !important;
            text-decoration: underline;
        }
        @media print {

           td a{
                text-decoration: none !important;
                color: #000 !important;
            }
        }

        td.double-single-border {
            border-top: 1px solid #000 !important;
            border-bottom: 4px double #000 !important;
        }
        td.border-left-right{
            border-left: 1px solid #000 !important;
            border-right: 1px solid #000 !important;
        }
        td.border-top{
            border-top: 1px solid #000 !important;
        }
        td.border-bottom{
            border-bottom: 1px solid #000 !important;
        }

        .custom-ml-18{
            margin-left: 18px !important;
        }
    </style>
@endsection
@section('content')
    <div class="row">
        <!-- left column -->
        <div class="col-md-12">
            <!-- jquery validation -->
            <div class="card card-outline card-default">
                <div class="card-header">
                    <h3 class="card-title">Data Filter</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                <form action="{{ route('report.balance_sheet') }}">
                    <div class="card-body">
                        <div class="row">
                            
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="start_date">Start Date <span
                                            class="text-danger">*</span></label>
                                    <input required type="text" id="start_date" autocomplete="off"
                                           name="start_date" class="form-control date-picker"
                                           placeholder="Enter Start Date"
                                           value="{{ request()->get('start_date') ?? date('d-m-Y', strtotime(auth()->user()->business_date)) }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="end_date">End Date <span
                                            class="text-danger">*</span></label>
                                    <input required type="text" id="end_date" autocomplete="off"
                                           name="end_date" class="form-control date-picker"
                                           placeholder="Enter Start Date"
                                           value="{{ request()->get('end_date') ?? date('d-m-Y', strtotime(auth()->user()->business_date)) }}">
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <input type="submit" name="search"
                                           class="btn btn-info bg-gradient-info form-control" value="Search">
                                </div>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
            <!-- /.card -->
        </div>
        <!--/.col (left) -->
    </div>

    @if(request('start_date'))
    <div class="row">
            <div class="col-12">
                <div class="card card-default">
                    <div class="card-header">
                        <a href="#" onclick="getprint('printArea')"
                           class="btn btn-info bg-gradient-info btn-sm"><i class="fa fa-print"></i></a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive-sm" id="printArea">
                            @include('layouts.partial.__report_header')
                            <div class="row header-common-info print-heading">
                                <div class="col-12">
                                    <h3 class="text-center m-0" style="font-size: 20px !important;">
                                        STATEMENT OF FINANCIAL POSITION </h3>
                                    <h3 class="text-center" style="font-size: 20px !important;">FOR THE
                                        AS AT {{ date('d',strtotime(request('end_date'))) }}th {{ date('F',strtotime(request('end_date'))) }},  {{ date('Y',strtotime(request('end_date'))) }} (Provisional)</h3>
                                </div>
                            </div>
                            <table class="table table-bordered">
                                <thead>
                                <tr>
                                    <th  width="50%" class="text-center" rowspan="2">Particulars</th>
                                    <th width="4%" class="text-center" rowspan="2">Notes</th>
                                    <th colspan="4" class="text-center">Amount in Taka</th>
                                </tr>
                                <tr>
                                    <th class="text-center">{{ date('d-m-Y',strtotime(request('end_date'))) }}</th>
                                    <th width="2%"></th>
                                    <th class="text-center">{{ date('d-m-Y',strtotime('-1 day', strtotime(request('start_date'))))}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr><td colspan="4">&nbsp;</td></tr>
                                @php
                                    $assetSlArray = [];
                                    $assetSl = 1;
                                    $totalAssetBalance = 0;
                                    $totalPreviousAssetBalance = 0;
                                @endphp

                                @foreach($assets as $asset)
                                    @php
                                        $assetBalance = accountGroupBalance($asset->id, request('start_date'), request('end_date'), 1,auth()->user()->activeConcernId());
                                        $previousAssetBalance = accountGroupBalance($asset->id, request('start_date'), request('end_date'), 2,auth()->user()->activeConcernId());
                                        
                                        $totalAssetBalance += $assetBalance;
                                        $totalPreviousAssetBalance += $previousAssetBalance;
                                    @endphp
                                    @if(!$loop->first)
                                        <tr><td colspan="4">&nbsp;</td></tr>
                                    @endif
                                    <tr>
                                        <td class="text-left" colspan="2"><b>{{ $assetSl }}.<u>{{ $asset->name }}:</u></b></td>
                                        <td class="text-right"><b>{{ wrapNegative($assetBalance) }}</b></td>
                                        <td></td>
                                        <td class="text-right"><b>{{ wrapNegative($previousAssetBalance) }}</b></td>
                                    </tr>

                                    @foreach($asset->accountGroups as $subGroup)
                                        @php
                                            $subBalance = accountGroupBalance($subGroup->id, request('start_date'), request('end_date'), 1,auth()->user()->activeConcernId());
                                            $prevSubBalance = accountGroupBalance($subGroup->id, request('start_date'), request('end_date'), 2,auth()->user()->activeConcernId());
                                        @endphp
                                        <tr>
                                            <td class="text-left"><a href="{{ route('report.trial_balance',['start_date'=>request('start_date'),'end_date'=>request('end_date'),'account_group_id'=>$subGroup->id,'sister_concern'=>auth()->user()->activeConcernId()]) }}"><span class="custom-ml-18 {{ $subGroup->accountGroups->count() > 0 ? 'font-weight-bold text-uppercase' : '' }}">{{ $subGroup->name }}</span></a></td>
                                            <td><b>{{ $subGroup->note_no }}</b></td>
                                            <td class="text-right border-left-right {{ $loop->first ? 'border-top' : ''}} {{ ($loop->last && $subGroup->accountGroups->count() == 0) ? 'border-bottom' : ''}}">{{ wrapNegative($subBalance) }}</td>
                                            <td></td>
                                            <td class="text-right border-left-right {{ $loop->first ? 'border-top' : ''}} {{ ($loop->last && $subGroup->accountGroups->count() == 0) ? 'border-bottom' : ''}}">{{ wrapNegative($prevSubBalance) }}</td>
                                        </tr>
                                        {{-- If nested sub-groups exist (e.g. under Current Assets) --}}
                                        @foreach($subGroup->accountGroups as $childGroup)
                                            @php
                                                $childBalance = accountGroupBalance($childGroup->id, request('start_date'), request('end_date'), 1,auth()->user()->activeConcernId());
                                                $prevChildBalance = accountGroupBalance($childGroup->id, request('start_date'), request('end_date'), 2,auth()->user()->activeConcernId());
                                            @endphp
                                            <tr>
                                                <td class="text-left"><a href="{{ route('report.trial_balance',['start_date'=>request('start_date'),'end_date'=>request('end_date'),'account_group_id'=>$childGroup->id,'sister_concern'=>auth()->user()->activeConcernId()]) }}"><span class="custom-ml-18" style="margin-left: 45px !important;">{{ $childGroup->name }}</span></a></td>
                                                <td>{{ $childGroup->note_no }}</td>
                                                <td class="text-right border-left-right {{ $loop->last ? 'border-bottom' : ''}}" style="font-size: 16px !important;">{{ wrapNegative($childBalance) }}</td>
                                                <td></td>
                                                <td class="text-right border-left-right {{ $loop->last ? 'border-bottom' : ''}}" style="font-size: 16px !important;">{{ wrapNegative($prevChildBalance) }}</td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                    @php
                                        $assetSlArray[] = $assetSl;
                                        $assetSl++;
                                    @endphp
                                @endforeach

                                <tr><td colspan="4">&nbsp;</td></tr>
                                <tr>
                                    <td class="text-left" colspan="2"><b>Total Assets {{ '(' . implode('+', $assetSlArray) . ')' }}</b></td>
                                    <td class="text-right double-single-border"><b>{{ wrapNegative($totalAssetBalance) }}</b></td>
                                    <td></td>
                                    <td class="text-right double-single-border"><b>{{ wrapNegative($totalPreviousAssetBalance) }}</b></td>
                                </tr>
                                <tr><td colspan="4">&nbsp;</td></tr>

                                @php
                                    $equityLiabilityArray = [];
                                    $equityLiabilitySl = $assetSl;
                                    $totalEquityBalance = 0;
                                    $totalPreviousEquityBalance = 0;
                                @endphp

                                @foreach($equities as $equity)
                                    @php
                                        $equityBal = accountGroupBalance($equity->id, request('start_date'), request('end_date'), 1,auth()->user()->activeConcernId());
                                        $prevEquityBal = accountGroupBalance($equity->id, request('start_date'), request('end_date'), 2,auth()->user()->activeConcernId());
                                        $totalEquityBalance += $equityBal;
                                        $totalPreviousEquityBalance += $prevEquityBal;
                                    @endphp
                                    @if(!$loop->first)
                                        <tr><td colspan="4">&nbsp;</td></tr>
                                    @endif
                                    <tr>
                                        <td class="text-left" colspan="2"><b>{{ $equityLiabilitySl }}.<u>{{ $equity->name }}:</u></b></td>
                                        <td class="text-right"><b>{{ wrapPositive($equityBal) }}</b></td>
                                        <td></td>
                                        <td class="text-right"><b>{{ wrapPositive($prevEquityBal) }}</b></td>
                                    </tr>

                                    @foreach($equity->accountGroups as $subGroup)
                                        @php
                                            $subBalance = accountGroupBalance($subGroup->id, request('start_date'), request('end_date'), 1,auth()->user()->activeConcernId());
                                            $prevSubBalance = accountGroupBalance($subGroup->id, request('start_date'), request('end_date'), 2,auth()->user()->activeConcernId());
                                        @endphp
                                        <tr>
                                            <td class="text-left"><a href="{{ route('report.trial_balance',['start_date'=>request('start_date'),'end_date'=>request('end_date'),'account_group_id'=>$subGroup->id,'sister_concern'=>auth()->user()->activeConcernId()]) }}"><span class="custom-ml-18">{{ $subGroup->name }}</span></a></td>
                                            <td><b>{{ $subGroup->note_no }}</b></td>
                                            <td class="text-right border-left-right {{ $loop->first ? 'border-top' : ''}} {{ $loop->last ? 'border-bottom' : ''}}">{{ wrapPositive($subBalance) }}</td>
                                            <td></td>
                                            <td class="text-right border-left-right {{ $loop->first ? 'border-top' : ''}} {{ $loop->last ? 'border-bottom' : ''}}">{{ wrapPositive($prevSubBalance) }}</td>
                                        </tr>
                                    @endforeach
                                    @php
                                        $equityLiabilityArray[] = $equityLiabilitySl;
                                        $equityLiabilitySl++;
                                    @endphp
                                @endforeach

                                {{-- Calculate and Add Net Profit/(Loss) --}}
                                @php
                                    $netProfitBalance = 0;
                                    $previousNetProfitBalance = 0;

                                    if(isset($operatingRevenue) && $operatingRevenue){
                                        $netProfitBalance += accountGroupBalanceIncomeStatement($operatingRevenue->id,request('start_date'),request('end_date'),1,auth()->user()->activeConcernId());
                                        $previousNetProfitBalance += accountGroupBalanceIncomeStatement($operatingRevenue->id,request('start_date'),request('end_date'),2,auth()->user()->activeConcernId());
                                    }
                                    if(isset($costOfGoodsSold) && $costOfGoodsSold){
                                        $netProfitBalance += accountGroupBalanceIncomeStatement($costOfGoodsSold->id,request('start_date'),request('end_date'),1,auth()->user()->activeConcernId());
                                        $previousNetProfitBalance += accountGroupBalanceIncomeStatement($costOfGoodsSold->id,request('start_date'),request('end_date'),2,auth()->user()->activeConcernId());
                                    }
                                    if(isset($operatingExpenses) && $operatingExpenses){
                                        $netProfitBalance += accountGroupBalanceIncomeStatement($operatingExpenses->id,request('start_date'),request('end_date'),1,auth()->user()->activeConcernId());
                                        $previousNetProfitBalance += accountGroupBalanceIncomeStatement($operatingExpenses->id,request('start_date'),request('end_date'),2,auth()->user()->activeConcernId());
                                    }
                                    if(isset($otherIncome) && $otherIncome){
                                        $netProfitBalance += accountGroupBalanceIncomeStatement($otherIncome->id,request('start_date'),request('end_date'),1,auth()->user()->activeConcernId());
                                        $previousNetProfitBalance += accountGroupBalanceIncomeStatement($otherIncome->id,request('start_date'),request('end_date'),2,auth()->user()->activeConcernId());
                                    }
                                    if(isset($financialExpenses) && $financialExpenses){
                                        $netProfitBalance += accountGroupBalanceIncomeStatement($financialExpenses->id,request('start_date'),request('end_date'),1,auth()->user()->activeConcernId());
                                        $previousNetProfitBalance += accountGroupBalanceIncomeStatement($financialExpenses->id,request('start_date'),request('end_date'),2,auth()->user()->activeConcernId());
                                    }
                                    if(isset($taxExpenses) && $taxExpenses){
                                        $netProfitBalance += accountGroupBalanceIncomeStatement($taxExpenses->id,request('start_date'),request('end_date'),1,auth()->user()->activeConcernId());
                                        $previousNetProfitBalance += accountGroupBalanceIncomeStatement($taxExpenses->id,request('start_date'),request('end_date'),2,auth()->user()->activeConcernId());
                                    }

                                    $totalEquityBalance += $netProfitBalance;
                                    $totalPreviousEquityBalance += $previousNetProfitBalance;
                                @endphp

                                <tr>
                                    <td class="text-left" colspan="2"><b>{{ $equityLiabilitySl }}.<u>Net Profit/(Loss):</u></b></td>
                                    <td class="text-right"><b>{{ wrapPositive($netProfitBalance) }}</b></td>
                                    <td></td>
                                    <td class="text-right"><b>{{ wrapPositive($previousNetProfitBalance) }}</b></td>
                                </tr>
                                @php
                                    $equityLiabilityArray[] = $equityLiabilitySl;
                                    $equityLiabilitySl++;
                                @endphp

                                <tr><td colspan="4">&nbsp;</td></tr>

                                @php
                                    $totalLiabilityBalance = 0;
                                    $totalPreviousLiabilityBalance = 0;
                                @endphp

                                @foreach($liabilities as $liability)
                                    @php
                                        $liabBal = accountGroupBalance($liability->id, request('start_date'), request('end_date'), 1,auth()->user()->activeConcernId());
                                        $prevLiabBal = accountGroupBalance($liability->id, request('start_date'), request('end_date'), 2,auth()->user()->activeConcernId());
                                        $totalLiabilityBalance += $liabBal;
                                        $totalPreviousLiabilityBalance += $prevLiabBal;
                                    @endphp
                                    @if(!$loop->first)
                                        <tr><td colspan="4">&nbsp;</td></tr>
                                    @endif
                                    <tr>
                                        <td class="text-left" colspan="2"><b>{{ $equityLiabilitySl }}.<u>{{ $liability->name }}:</u></b></td>
                                        <td class="text-right"><b>{{ wrapPositive($liabBal) }}</b></td>
                                        <td></td>
                                        <td class="text-right"><b>{{ wrapPositive($prevLiabBal) }}</b></td>
                                    </tr>

                                    @foreach($liability->accountGroups as $subGroup)
                                        @php
                                            $subBalance = accountGroupBalance($subGroup->id, request('start_date'), request('end_date'), 1,auth()->user()->activeConcernId());
                                            $prevSubBalance = accountGroupBalance($subGroup->id, request('start_date'), request('end_date'), 2,auth()->user()->activeConcernId());
                                        @endphp
                                        <tr>
                                            <td class="text-left"><a href="{{ route('report.trial_balance',['start_date'=>request('start_date'),'end_date'=>request('end_date'),'account_group_id'=>$subGroup->id,'sister_concern'=>auth()->user()->activeConcernId()]) }}"><span class="custom-ml-18 {{ $subGroup->accountGroups->count() > 0 ? 'font-weight-bold text-uppercase' : '' }}">{{ $subGroup->name }}</span></a></td>
                                            <td><b>{{ $subGroup->note_no }}</b></td>
                                            <td class="text-right border-left-right {{ $loop->first ? 'border-top' : ''}} {{ ($loop->last && $subGroup->accountGroups->count() == 0) ? 'border-bottom' : ''}}">{{ wrapPositive($subBalance) }}</td>
                                            <td></td>
                                            <td class="text-right border-left-right {{ $loop->first ? 'border-top' : ''}} {{ ($loop->last && $subGroup->accountGroups->count() == 0) ? 'border-bottom' : ''}}">{{ wrapPositive($prevSubBalance) }}</td>
                                        </tr>
                                        @foreach($subGroup->accountGroups as $childGroup)
                                            @php
                                                $childBalance = accountGroupBalance($childGroup->id, request('start_date'), request('end_date'), 1,auth()->user()->activeConcernId());
                                                $prevChildBalance = accountGroupBalance($childGroup->id, request('start_date'), request('end_date'), 2,auth()->user()->activeConcernId());
                                            @endphp
                                            <tr>
                                                <td class="text-left"><a href="{{ route('report.trial_balance',['start_date'=>request('start_date'),'end_date'=>request('end_date'),'account_group_id'=>$childGroup->id,'sister_concern'=>auth()->user()->activeConcernId()]) }}"><span class="custom-ml-18" style="margin-left: 45px !important;">{{ $childGroup->name }}</span></a></td>
                                                <td>{{ $childGroup->note_no }}</td>
                                                <td class="text-right border-left-right {{ $loop->last ? 'border-bottom' : ''}}" style="font-size: 16px !important;">{{ wrapPositive($childBalance) }}</td>
                                                <td></td>
                                                <td class="text-right border-left-right {{ $loop->last ? 'border-bottom' : ''}}" style="font-size: 16px !important;">{{ wrapPositive($prevChildBalance) }}</td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                    @php
                                        $equityLiabilityArray[] = $equityLiabilitySl;
                                        $equityLiabilitySl++;
                                    @endphp
                                @endforeach

                                <tr><td colspan="4">&nbsp;</td></tr>

                                <tr>
                                    <td class="text-left" colspan="2"><b>Total Equity and Liabilities {{ '(' . implode('+', $equityLiabilityArray) . ')' }}</b></td>
                                    <td class="text-right double-single-border"><b>{{ wrapPositive($totalEquityBalance + $totalLiabilityBalance) }}</b></td>
                                    <td></td>
                                    <td class="text-right double-single-border"><b>{{ wrapPositive($totalPreviousEquityBalance + $totalPreviousLiabilityBalance) }}</b></td>
                                </tr>
                                <tr><td colspan="4">&nbsp;</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@section('script')
    <script>
        $(function (){
            var projectSelected = '{{ old('project',request('project')) }}';

            $("#sister_concern").change(function () {
                let sisterConcernId = $(this).val();
                $('#project').html('<option value="">All Project</option>');
                if (sisterConcernId != '') {
                    $.ajax({
                        method: "GET",
                        url: "{{ route('get_sister_concern_wise_project') }}",
                        data: {sisterConcernId: sisterConcernId}
                    }).done(function (data) {
                        $.each(data, function (index, item) {
                            if (projectSelected == item.id)
                                $('#project').append('<option value="' + item.id + '" selected>' + item.name + '</option>');
                            else
                                $('#project').append('<option value="' + item.id + '">' + item.name + '</option>');
                        });
                    });
                }
            })

        })

        var APP_URL = '{!! url()->full()  !!}';

        function getprint(print) {
            $('.print-heading').css('display', 'block');
            $('.extra_column').remove();
            $('body').html($('#' + print).html());
            window.print();
            window.location.replace(APP_URL)
        }
    </script>
@endsection
