@php($q = $r->current_preview ?? $r->preview)
Principal: {{ $q['principal'] ?? '—' }}<br>
Earned: {{ $q['earned_interest'] ?? '—' }}<br>
Previously paid: {{ $q['paid_interest'] ?? '—' }}<br>
Penalty: {{ $q['penalty_amount'] ?? '—' }}<br>
Net: {{ $q['net_payable'] ?? '—' }}<br>
<small>Current date: {{ $q['date'] ?? '—' }}. Recalculated on approval.</small>
@if($r->preview_error)<p class="text-danger">{{ $r->preview_error }}</p>@endif
