@if(\App\Support\ReportBranch::allowed())
<form method="GET" action="{{ url()->current() }}" class="extra-remove card mb-3">
    @foreach(request()->except(['report_branch_id', 'page']) as $filter => $value)
        @if(is_scalar($value))<input type="hidden" name="{{ $filter }}" value="{{ $value }}">@endif
    @endforeach
    @include('layouts.partial.__report_branch_filter')
</form>
@endif
