<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Balance Sheet Report</title>
    @include('layouts.partial.__styles')
    <style>
        .table-bordered td, .table-bordered th {
            border: 1px solid #000 !important;
            vertical-align: middle !important;
            padding: 0.2rem !important;
            font-size: 16px !important;
        }
        .table-borderless td, .table-borderless th {
            border: none !important;
            padding: 0.2rem !important;
            font-size: 16px !important;
        }
        td a:hover{
            color: #000 !important;
            text-decoration: underline;
        }
        .double-single-border {
            border-top: 1px solid #000 !important;
            border-bottom: 4px double #000 !important;
        }
        .custom-ml-18{
            margin-left: 18px !important;
        }
        @page {
            margin: .3in .5in .5in .5in;
        }
        body {
            background-color: #f4f6f9;
        }
        .report-container {
            background: #fff;
            padding: 1.5rem;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            min-height: 100vh;
        }
        @media print {
            .report-container {
                margin: 0;
                padding: 0;
                box-shadow: none;
            }
            .extra-remove {
                display: none !important;
            }
            body {
                background-color: #fff;
            }
            .table-responsive-sm {
                overflow-x: visible !important;
            }
            td a{
                text-decoration: none !important;
                color: #000 !important;
            }
            .row.outer-border {
                display: flex !important;
                flex-wrap: nowrap !important;
                align-items: stretch !important;
            }
            .col-6 {
                width: 50% !important;
                max-width: 50% !important;
                flex: 0 0 50% !important;
                float: none !important;
            }
            .border-middle {
                border-right: 2px solid #000 !important;
            }
            .outer-border {
                border: 2px solid #000 !important;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="report-container">
                    @include('layouts.partial.__export_buttons', ['printAreaId' => 'printArea', 'fileName' => 'Balance_Sheet_Report_' . date('d_m_Y')])
                    
                    <div class="table-responsive-sm" id="printArea">
                            @include('layouts.partial.__report_header')
                            <div class="row header-common-info print-heading m-0">
                                <div class="col-12">
                                    <h3 class="text-center m-0" style="font-size: 20px !important;">STATEMENT OF FINANCIAL
                                        POSITION</h3>
                                    @php
                                        $branchName = auth()->user()->sisterConcern->display_name ?? auth()->user()->sisterConcern->name ?? 'Head Office';
                                    @endphp
                                    <h3 class="text-center m-0" style="font-size: 20px !important;">Branch: {{ $branchName }}</h3>
                                    <h3 class="text-center" style="font-size: 20px !important;">
                                        AS AT {{ date('d', strtotime(request('end_date'))) }}th
                                        {{ date('F', strtotime(request('end_date'))) }},
                                        {{ date('Y', strtotime(request('end_date'))) }} (Provisional)
                                    </h3>
                                </div>
                            </div>

                            <div class="row mt-4 outer-border" style="border: 2px solid #000; margin: 0; display: flex;">
                                <!-- ASSETS COLUMN -->
                                <div class="col-6 d-flex flex-column border-middle"
                                    style="padding: 0; border-right: 2px solid #000;">
                                    <table class="table table-borderless" style="margin-bottom:0; width: 100%;">
                                        <thead>
                                            <tr>
                                                <th colspan="2" class="text-center"
                                                    style="border-bottom: 2px solid #000; font-size: 18px !important;">Property
                                                    & Assets</th>
                                            </tr>
                                            <tr style="border-bottom: 2px solid #000;">
                                                <th width="50%">Particulars</th>
                                                <th class="text-right" width="25%">
                                                    {{ date('d-m-Y', strtotime(request('end_date'))) }}</th>
                                                <th class="text-right" width="25%">{{ date('d-m-Y',strtotime('-1 day',
                                                    strtotime(request('start_date'))))}}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $assetSlArray = [];
                                                $assetSl = 1;
                                                $totalAssetBalance = 0;
                                                $totalPreviousAssetBalance = 0;
                                            @endphp

                                            @foreach($assets as $asset)
                                                @php
                                                    $assetBalance = accountGroupBalance($asset->id, request('start_date'), request('end_date'), 1, auth()->user()->activeConcernId());
                                                    $previousAssetBalance = accountGroupBalance($asset->id, request('start_date'), request('end_date'), 2, auth()->user()->activeConcernId());
                                                    $totalAssetBalance += $assetBalance;
                                                    $totalPreviousAssetBalance += $previousAssetBalance;
                                                @endphp
                                                @if(!$loop->first)
                                                    <tr>
                                                        <td colspan="2">&nbsp;</td>
                                                    </tr>
                                                @endif
                                                <tr>
                                                    <td class="text-left"><b>{{ $assetSl }}.<u>{{ $asset->name }}:</u></b></td>
                                                    <td class="text-right"><b>{{ wrapNegative($assetBalance) }}</b></td>
                                                    <td class="text-right"><b>{{ wrapNegative($previousAssetBalance) }}</b>
                                                    </td>
                                                </tr>

                                                @foreach($asset->accountGroups as $subGroup)
                                                    @php
                                                        $subBalance = accountGroupBalance($subGroup->id, request('start_date'), request('end_date'), 1, auth()->user()->activeConcernId());
                                                        $prevSubBalance = accountGroupBalance($subGroup->id, request('start_date'), request('end_date'), 2, auth()->user()->activeConcernId());
                                                    @endphp
                                                    <tr>
                                                        <td class="text-left"><a
                                                                href="{{ route('report.trial_balance', ['start_date' => request('start_date'), 'end_date' => request('end_date'), 'account_group_id' => $subGroup->id, 'sister_concern' => auth()->user()->activeConcernId(), 'search' => 'Search']) }}"><span
                                                                    class="custom-ml-18 {{ $subGroup->accountGroups->count() > 0 ? 'font-weight-bold text-uppercase' : '' }}">{{ $subGroup->name }}</span></a>
                                                        </td>
                                                        <td class="text-right">{{ wrapNegative($subBalance) }}</td>
                                                        <td class="text-right">{{ wrapNegative($prevSubBalance) }}</td>
                                                    </tr>
                                                    @foreach($subGroup->accountGroups as $childGroup)
                                                        @php
                                                            $childBalance = accountGroupBalance($childGroup->id, request('start_date'), request('end_date'), 1, auth()->user()->activeConcernId());
                                                            $prevChildBalance = accountGroupBalance($childGroup->id, request('start_date'), request('end_date'), 2, auth()->user()->activeConcernId());
                                                        @endphp
                                                        <tr>
                                                            <td class="text-left"><a
                                                                    href="{{ route('report.trial_balance', ['start_date' => request('start_date'), 'end_date' => request('end_date'), 'account_group_id' => $childGroup->id, 'sister_concern' => auth()->user()->activeConcernId(), 'search' => 'Search']) }}"><span
                                                                        class="custom-ml-18"
                                                                        style="margin-left: 45px !important;">{{ $childGroup->name }}</span></a>
                                                            </td>
                                                            <td class="text-right">{{ wrapNegative($childBalance) }}</td>
                                                            <td class="text-right">{{ wrapNegative($prevChildBalance) }}</td>
                                                        </tr>
                                                    @endforeach
                                                @endforeach
                                                @php
                                                    $assetSlArray[] = $assetSl;
                                                    $assetSl++;
                                                @endphp
                                            @endforeach
                                        </tbody>
                                    </table>
                                    <table class="table table-borderless mt-auto" style="margin-bottom:0; width: 100%;">
                                        <tfoot>
                                            <tr style="border-top: 2px solid #000;">
                                                <th width="50%" class="text-left">Total Assets</th>
                                                <th width="25%" class="text-right double-single-border">
                                                    {{ wrapNegative($totalAssetBalance) }}</th>
                                                <th width="25%" class="text-right double-single-border">{{
                                                    wrapNegative($totalPreviousAssetBalance) }}</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>

                                <!-- EQUITY & LIABILITIES COLUMN -->
                                <div class="col-6 d-flex flex-column" style="padding: 0;">
                                    <table class="table table-borderless" style="margin-bottom:0; width: 100%;">
                                        <thead>
                                            <tr>
                                                <th colspan="2" class="text-center"
                                                    style="border-bottom: 2px solid #000; font-size: 18px !important;">Capital &
                                                    Liabilities</th>
                                            </tr>
                                            <tr style="border-bottom: 2px solid #000;">
                                                <th width="50%">Particulars</th>
                                                <th class="text-right" width="25%">
                                                    {{ date('d-m-Y', strtotime(request('end_date'))) }}</th>
                                                <th class="text-right" width="25%">{{ date('d-m-Y',strtotime('-1 day',
                                                    strtotime(request('start_date'))))}}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $equityLiabilityArray = [];
                                                $equityLiabilitySl = 1;
                                                $totalEquityBalance = 0;
                                                $totalPreviousEquityBalance = 0;
                                            @endphp

                                            @foreach($equities as $equity)
                                                @php
                                                    $equityBal = accountGroupBalance($equity->id, request('start_date'), request('end_date'), 1, auth()->user()->activeConcernId());
                                                    $prevEquityBal = accountGroupBalance($equity->id, request('start_date'), request('end_date'), 2, auth()->user()->activeConcernId());
                                                    $totalEquityBalance += $equityBal;
                                                    $totalPreviousEquityBalance += $prevEquityBal;
                                                @endphp
                                                @if(!$loop->first)
                                                    <tr>
                                                        <td colspan="2">&nbsp;</td>
                                                    </tr>
                                                @endif
                                                <tr>
                                                    <td class="text-left">
                                                        <b>{{ $equityLiabilitySl }}.<u>{{ $equity->name }}:</u></b></td>
                                                    <td class="text-right"><b>{{ wrapPositive($equityBal) }}</b></td>
                                                    <td class="text-right"><b>{{ wrapPositive($prevEquityBal) }}</b></td>
                                                </tr>

                                                @foreach($equity->accountGroups as $subGroup)
                                                    @php
                                                        $subBalance = accountGroupBalance($subGroup->id, request('start_date'), request('end_date'), 1, auth()->user()->activeConcernId());
                                                        $prevSubBalance = accountGroupBalance($subGroup->id, request('start_date'), request('end_date'), 2, auth()->user()->activeConcernId());
                                                    @endphp
                                                    <tr>
                                                        <td class="text-left"><a
                                                                href="{{ route('report.trial_balance', ['start_date' => request('start_date'), 'end_date' => request('end_date'), 'account_group_id' => $subGroup->id, 'sister_concern' => auth()->user()->activeConcernId(), 'search' => 'Search']) }}"><span
                                                                    class="custom-ml-18">{{ $subGroup->name }}</span></a></td>
                                                        <td class="text-right">{{ wrapPositive($subBalance) }}</td>
                                                        <td class="text-right">{{ wrapPositive($prevSubBalance) }}</td>
                                                    </tr>
                                                @endforeach
                                                @php
                                                    $equityLiabilityArray[] = $equityLiabilitySl;
                                                    $equityLiabilitySl++;
                                                @endphp
                                            @endforeach

                                            {{-- Net Profit --}}
                                            @php
                                                $netProfitBalance = 0;
                                                $previousNetProfitBalance = 0;

                                                /* 
                                                // OLD LOGIC: Commented out per user request because it only calculates profit for the selected date range.
                                                if (isset($operatingRevenue) && $operatingRevenue) {
                                                    $netProfitBalance += accountGroupBalanceIncomeStatement($operatingRevenue->id, request('start_date'), request('end_date'), 1, auth()->user()->activeConcernId());
                                                    $previousNetProfitBalance += accountGroupBalanceIncomeStatement($operatingRevenue->id, request('start_date'), request('end_date'), 2, auth()->user()->activeConcernId());
                                                }
                                                if (isset($costOfGoodsSold) && $costOfGoodsSold) {
                                                    $netProfitBalance += accountGroupBalanceIncomeStatement($costOfGoodsSold->id, request('start_date'), request('end_date'), 1, auth()->user()->activeConcernId());
                                                    $previousNetProfitBalance += accountGroupBalanceIncomeStatement($costOfGoodsSold->id, request('start_date'), request('end_date'), 2, auth()->user()->activeConcernId());
                                                }
                                                if (isset($operatingExpenses) && $operatingExpenses) {
                                                    $netProfitBalance += accountGroupBalanceIncomeStatement($operatingExpenses->id, request('start_date'), request('end_date'), 1, auth()->user()->activeConcernId());
                                                    $previousNetProfitBalance += accountGroupBalanceIncomeStatement($operatingExpenses->id, request('start_date'), request('end_date'), 2, auth()->user()->activeConcernId());
                                                }
                                                if (isset($otherIncome) && $otherIncome) {
                                                    $netProfitBalance += accountGroupBalanceIncomeStatement($otherIncome->id, request('start_date'), request('end_date'), 1, auth()->user()->activeConcernId());
                                                    $previousNetProfitBalance += accountGroupBalanceIncomeStatement($otherIncome->id, request('start_date'), request('end_date'), 2, auth()->user()->activeConcernId());
                                                }
                                                if (isset($financialExpenses) && $financialExpenses) {
                                                    $netProfitBalance += accountGroupBalanceIncomeStatement($financialExpenses->id, request('start_date'), request('end_date'), 1, auth()->user()->activeConcernId());
                                                    $previousNetProfitBalance += accountGroupBalanceIncomeStatement($financialExpenses->id, request('start_date'), request('end_date'), 2, auth()->user()->activeConcernId());
                                                }
                                                if (isset($taxExpenses) && $taxExpenses) {
                                                    $netProfitBalance += accountGroupBalanceIncomeStatement($taxExpenses->id, request('start_date'), request('end_date'), 1, auth()->user()->activeConcernId());
                                                    $previousNetProfitBalance += accountGroupBalanceIncomeStatement($taxExpenses->id, request('start_date'), request('end_date'), 2, auth()->user()->activeConcernId());
                                                }
                                                */

                                                // NEW LOGIC: Calculates cumulative Retained Earnings up to the end_date.
                                                if (isset($operatingRevenue) && $operatingRevenue) {
                                                    $netProfitBalance += accountGroupBalance($operatingRevenue->id, request('start_date'), request('end_date'), 1, auth()->user()->activeConcernId());
                                                    $previousNetProfitBalance += accountGroupBalance($operatingRevenue->id, request('start_date'), request('end_date'), 2, auth()->user()->activeConcernId());
                                                }
                                                if (isset($costOfGoodsSold) && $costOfGoodsSold) {
                                                    $netProfitBalance += accountGroupBalance($costOfGoodsSold->id, request('start_date'), request('end_date'), 1, auth()->user()->activeConcernId());
                                                    $previousNetProfitBalance += accountGroupBalance($costOfGoodsSold->id, request('start_date'), request('end_date'), 2, auth()->user()->activeConcernId());
                                                }
                                                if (isset($operatingExpenses) && $operatingExpenses) {
                                                    $netProfitBalance += accountGroupBalance($operatingExpenses->id, request('start_date'), request('end_date'), 1, auth()->user()->activeConcernId());
                                                    $previousNetProfitBalance += accountGroupBalance($operatingExpenses->id, request('start_date'), request('end_date'), 2, auth()->user()->activeConcernId());
                                                }
                                                if (isset($otherIncome) && $otherIncome) {
                                                    $netProfitBalance += accountGroupBalance($otherIncome->id, request('start_date'), request('end_date'), 1, auth()->user()->activeConcernId());
                                                    $previousNetProfitBalance += accountGroupBalance($otherIncome->id, request('start_date'), request('end_date'), 2, auth()->user()->activeConcernId());
                                                }
                                                if (isset($financialExpenses) && $financialExpenses) {
                                                    $netProfitBalance += accountGroupBalance($financialExpenses->id, request('start_date'), request('end_date'), 1, auth()->user()->activeConcernId());
                                                    $previousNetProfitBalance += accountGroupBalance($financialExpenses->id, request('start_date'), request('end_date'), 2, auth()->user()->activeConcernId());
                                                }
                                                if (isset($taxExpenses) && $taxExpenses) {
                                                    $netProfitBalance += accountGroupBalance($taxExpenses->id, request('start_date'), request('end_date'), 1, auth()->user()->activeConcernId());
                                                    $previousNetProfitBalance += accountGroupBalance($taxExpenses->id, request('start_date'), request('end_date'), 2, auth()->user()->activeConcernId());
                                                }

                                                $totalEquityBalance += $netProfitBalance;
                                                $totalPreviousEquityBalance += $previousNetProfitBalance;
                                            @endphp

                                            <tr>
                                                <td class="text-left"><b>{{ $equityLiabilitySl }}.<u>Net Profit/(Loss):</u></b>
                                                </td>
                                                <td class="text-right"><b>{{ wrapPositive($netProfitBalance) }}</b></td>
                                                <td class="text-right"><b>{{ wrapPositive($previousNetProfitBalance) }}</b>
                                                </td>
                                            </tr>
                                            @php
                                                $equityLiabilityArray[] = $equityLiabilitySl;
                                                $equityLiabilitySl++;
                                            @endphp
                                            <tr>
                                                <td colspan="2">&nbsp;</td>
                                            </tr>

                                            @php
                                                $totalLiabilityBalance = 0;
                                                $totalPreviousLiabilityBalance = 0;
                                            @endphp

                                            @foreach($liabilities as $liability)
                                                @php
                                                    $liabBal = accountGroupBalance($liability->id, request('start_date'), request('end_date'), 1, auth()->user()->activeConcernId());
                                                    $prevLiabBal = accountGroupBalance($liability->id, request('start_date'), request('end_date'), 2, auth()->user()->activeConcernId());
                                                    $totalLiabilityBalance += $liabBal;
                                                    $totalPreviousLiabilityBalance += $prevLiabBal;
                                                @endphp
                                                @if(!$loop->first)
                                                    <tr>
                                                        <td colspan="2">&nbsp;</td>
                                                    </tr>
                                                @endif
                                                <tr>
                                                    <td class="text-left">
                                                        <b>{{ $equityLiabilitySl }}.<u>{{ $liability->name }}:</u></b></td>
                                                    <td class="text-right"><b>{{ wrapPositive($liabBal) }}</b></td>
                                                    <td class="text-right"><b>{{ wrapPositive($prevLiabBal) }}</b></td>
                                                </tr>

                                                @foreach($liability->accountGroups as $subGroup)
                                                    @php
                                                        $subBalance = accountGroupBalance($subGroup->id, request('start_date'), request('end_date'), 1, auth()->user()->activeConcernId());
                                                        $prevSubBalance = accountGroupBalance($subGroup->id, request('start_date'), request('end_date'), 2, auth()->user()->activeConcernId());
                                                    @endphp
                                                    <tr>
                                                        <td class="text-left"><a
                                                                href="{{ route('report.trial_balance', ['start_date' => request('start_date'), 'end_date' => request('end_date'), 'account_group_id' => $subGroup->id, 'sister_concern' => auth()->user()->activeConcernId(), 'search' => 'Search']) }}"><span
                                                                    class="custom-ml-18 {{ $subGroup->accountGroups->count() > 0 ? 'font-weight-bold text-uppercase' : '' }}">{{ $subGroup->name }}</span></a>
                                                        </td>
                                                        <td class="text-right">{{ wrapPositive($subBalance) }}</td>
                                                        <td class="text-right">{{ wrapPositive($prevSubBalance) }}</td>
                                                    </tr>
                                                    @foreach($subGroup->accountGroups as $childGroup)
                                                        @php
                                                            $childBalance = accountGroupBalance($childGroup->id, request('start_date'), request('end_date'), 1, auth()->user()->activeConcernId());
                                                            $prevChildBalance = accountGroupBalance($childGroup->id, request('start_date'), request('end_date'), 2, auth()->user()->activeConcernId());
                                                        @endphp
                                                        <tr>
                                                            <td class="text-left"><a
                                                                    href="{{ route('report.trial_balance', ['start_date' => request('start_date'), 'end_date' => request('end_date'), 'account_group_id' => $childGroup->id, 'sister_concern' => auth()->user()->activeConcernId(), 'search' => 'Search']) }}"><span
                                                                        class="custom-ml-18"
                                                                        style="margin-left: 45px !important;">{{ $childGroup->name }}</span></a>
                                                            </td>
                                                            <td class="text-right">{{ wrapPositive($childBalance) }}</td>
                                                            <td class="text-right">{{ wrapPositive($prevChildBalance) }}</td>
                                                        </tr>
                                                    @endforeach
                                                @endforeach
                                                @php
                                                    $equityLiabilityArray[] = $equityLiabilitySl;
                                                    $equityLiabilitySl++;
                                                @endphp
                                            @endforeach
                                        </tbody>
                                    </table>
                                    <table class="table table-borderless mt-auto" style="margin-bottom:0; width: 100%;">
                                        <tfoot>
                                            <tr style="border-top: 2px solid #000;">
                                                <th width="50%" class="text-left">Total Capital & Liabilities</th>
                                                <th width="25%" class="text-right double-single-border">
                                                    {{ wrapPositive($totalEquityBalance + $totalLiabilityBalance) }}</th>
                                                <th width="25%" class="text-right double-single-border">{{
                                                    wrapPositive($totalPreviousEquityBalance + $totalPreviousLiabilityBalance)
                                                    }}</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('layouts.partial.__export_scripts')
</body>
</html>