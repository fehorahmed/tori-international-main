<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    @include('layouts.partial.__styles')
    <style>
        body { background: #f4f6f9; }
        .report-container { background: white; padding: 1.5rem; min-height: 100vh; }
        .report-container td, .report-container th { font-size: 12px; vertical-align: middle; border: 1px solid #333 !important; }
        .report-total { border: 1px solid #ddd; padding: .7rem; margin-bottom: .7rem; border-radius: 4px; }
        @page { size: landscape; margin: .3in .5in; }
        @media print {
            .extra-remove { display: none !important; }
            .report-container { padding: 0; }
            .table-responsive { overflow: visible !important; }
            tr { break-inside: avoid; }
        }
    </style>
</head>
<body>
<div class="container-fluid report-container">
    <div class="mb-3 extra-remove"><a href="{{ route('shomriddhi-advance.reports.'.$kind, $filters) }}" class="btn btn-secondary btn-sm">Change Filters</a></div>
    @include('layouts.partial.__export_buttons', ['printAreaId' => 'printArea', 'fileName' => str_replace(' ', '_', $title).'_'.now()->format('Y_m_d')])
    <div id="printArea">
        @include('layouts.partial.__report_header')
        <h3 class="text-center mt-3">{{ $title }}</h3>
        <p class="text-center">Branch: {{ $branch->code }} — {{ $branch->name }}<br>{{ $dateLabel }}: {{ $filters['start_date'] ?? 'All earlier dates' }} to {{ $filters['end_date'] ?? 'All later dates' }}<br>Generated: {{ now()->format('d-m-Y h:i A') }}</p>
        @if(count($filterLabels))<p>@foreach($filterLabels as $label => $value)<strong>{{ $label }}:</strong> {{ $value }}@if(!$loop->last) · @endif @endforeach</p>@endif
        <div class="row">
            @foreach($report['totals'] as $label => $value)
            <div class="col-6 col-lg"><div class="report-total">{{ $label }}<br><strong>{{ $value }}</strong></div></div>
            @endforeach
        </div>
        <p class="text-muted small">{{ $report['note'] }}</p>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead><tr><th>S/L</th>@foreach($report['columns'] as $column)<th>{{ $column }}</th>@endforeach</tr></thead>
                <tbody>
                    @forelse($report['rows'] as $row)
                    @php($rowIndex = $loop->index)
                    <tr><td>{{ $loop->iteration }}</td>
                        @foreach($row as $columnIndex => $cell)
                        <td>@if(isset($report['detail_links'][$rowIndex][$columnIndex]))
                            <a href="{{ $report['detail_links'][$rowIndex][$columnIndex] }}" target="_blank" rel="noopener">{{ $cell ?? '—' }}</a>
                        @elseif(isset($report['voucher_links'][$rowIndex][$columnIndex]))
                            <a href="{{ route('voucher.details', ['voucher' => $report['voucher_links'][$rowIndex][$columnIndex]]) }}" target="_blank" rel="noopener">{{ $cell ?? '—' }}</a>
                        @else{{ $cell ?? '—' }}@endif</td>
                        @endforeach
                    </tr>
                    @empty
                    <tr><td colspan="{{ count($report['columns']) + 1 }}" class="text-center">No records found for this selection.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@include('layouts.partial.__export_scripts')
</body></html>
