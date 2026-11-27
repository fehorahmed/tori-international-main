@if(\App\Support\ReportBranch::allowed())
<div class="row mx-0 report-branch-filter"><div class="col-md-4 form-group mt-3">
    <label for="report_branch_id">Branch</label>
    <select id="report_branch_id" name="report_branch_id" class="form-control select2">
        <option value="all" @selected(request('report_branch_id') === 'all')>All Branches</option>
        @foreach(\App\Models\BranchManagement\SisterConcern::orderBy('code')->get() as $reportBranch)
        <option value="{{ $reportBranch->id }}" @selected((string) \App\Support\ReportBranch::id() === (string) $reportBranch->id)>{{ $reportBranch->code }} — {{ $reportBranch->name }}</option>
        @endforeach
    </select>
    @error('report_branch_id')<div class="text-danger">{{ $message }}</div>@enderror
    <small class="text-muted">Applies to this report only. Changing branch refreshes the available filters.</small>
</div></div>
@endif

@once
<script>
function changeReportBranch(event) {
    if (event.target.name !== 'report_branch_id') return;
    const url = new URL(location.pathname, location.origin);
    url.searchParams.set('report_branch_id', event.target.value);
    const form = event.target.closest('form');
    if (form) ['start_date', 'end_date', 'from_date', 'to_date', 'date'].forEach(function (name) {
        const input = form.elements.namedItem(name);
        if (input && input.value) url.searchParams.set(name, input.value);
    });
    location.assign(url.href);
}
function bindReportBranch() {
    if (window.jQuery) window.jQuery(document).on('change.reportBranch', '[name=report_branch_id]', changeReportBranch);
    else document.addEventListener('change', changeReportBranch);
}
if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', bindReportBranch);
else bindReportBranch();
</script>
@endonce
