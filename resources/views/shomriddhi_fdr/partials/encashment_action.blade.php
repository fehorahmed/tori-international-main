@if($r->status === 'Pending' && auth()->user()->can('shomriddhi_fdr_account_approve'))
<form class="encashment-review-form" method="post" action="{{ route('shomriddhi-fdr.encashment-requests.review', $r->id) }}">
    @csrf
    <input name="review_note" class="form-control mb-2" maxlength="500" placeholder="Review note" aria-label="Review note">
    <button name="decision" value="approve" class="btn btn-success mb-1" @disabled($r->preview_error)>Approve to Savings</button>
    <button name="decision" value="reject" class="btn btn-danger mb-1">Reject</button>
</form>
@endif
