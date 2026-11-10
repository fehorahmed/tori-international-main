@extends('layouts.app')
@section('title', 'Membership Cash-out Counter')
@section('content')
<div class="container-fluid">
    <h3>Membership Cash-out — {{ auth()->user()->active_sister_concern?->name }}</h3>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    <div class="mb-3">
        <a class="btn btn-outline-primary" href="{{ route('card-management.dashboard') }}">Card Management</a>
        <a class="btn btn-outline-primary" href="{{ route('card-management.cashout.index') }}">Cash-out Counter</a>
        @if(auth()->user()->role === 'Admin')
        <a class="btn btn-outline-secondary" href="{{ route('card-management.cashout.settings') }}">Cash-out Settings</a>
        @endif
    </div>
    <div class="card"><div class="card-header">This branch counter</div><div class="card-body">
        <form method="POST" action="{{ route('card-management.cashout.booth') }}">@csrf
            <label>Cash ledger for payouts</label><select name="cash_head_id" class="form-control" required>@foreach($cashHeads as $head)<option value="{{ $head->id }}" @selected($head->id == $booth?->cash_head_id)>{{ $head->name }}</option>@endforeach</select>
            <label class="mt-2"><input type="checkbox" name="enabled" value="1" @checked($booth?->enabled ?? true)> Counter enabled</label>
            <label class="ml-3"><input type="checkbox" name="rotate" value="1"> Replace QR (old printed QR stops working)</label>
            <button class="btn btn-primary ml-3">Save counter</button>
        </form>
        @if($booth)
            <div class="mt-3"><img src="{{ route('card-management.cashout.qr') }}" width="260" alt="Branch cash-out QR"><p>Members scan this QR in the Cash Out screen.</p>
                <a href="{{ route('card-management.cashout.qr') }}" target="_blank" rel="noopener">Open QR to print</a>
            </div>
        @endif
    </div></div>
    <h4>Cash-out requests <a class="btn btn-secondary btn-sm" href="{{ route('card-management.cashout.index') }}">Refresh</a></h4>
    <p>Verify the member and the request reference shown in their app before handing over cash. Accept reserves the request for the counter. Accepted requests do not expire automatically. Confirm cash payment at handover; reject with a reason if payment cannot proceed.</p>
    @forelse($requests as $cashout)
    <div class="card"><div class="card-body">
        <strong>{{ $cashout->reference }} — {{ $cashout->effectiveStatus() }}</strong>
        <p>{{ $cashout->customer?->name }} / {{ $cashout->customer?->member_no }} / {{ $cashout->customer?->mobile_no }}</p>
        <p>Pay cash: Tk {{ $cashout->amount }} | Charge: Tk {{ $cashout->fee }} | Savings debit: Tk {{ $cashout->total }}</p>
        @if($cashout->reason)<p>{{ $cashout->reason }}</p>@endif
        @if($cashout->effectiveStatus() === 'Pending')
        <form method="POST" action="{{ route('card-management.cashout.change', [$cashout, 'accept']) }}" data-cashout-confirm data-confirm-title="Accept cash-out request?" data-confirm-message="{{ $cashout->reference }} | Cash: Tk {{ $cashout->amount }} | Charge: Tk {{ $cashout->fee }} | Savings debit: Tk {{ $cashout->total }}. Accept this request and reserve it for this counter." data-confirm-button="Yes, accept request">@csrf<button class="btn btn-primary">Accept request</button></form>
        @endif
        @if($cashout->status === 'Accepted')
        <form method="POST" action="{{ route('card-management.cashout.change', [$cashout, 'complete']) }}" data-cashout-confirm data-confirm-title="Confirm cash payment?" data-confirm-message="{{ $cashout->reference }} | Cash: Tk {{ $cashout->amount }} | Charge: Tk {{ $cashout->fee }} | Savings debit: Tk {{ $cashout->total }}. Confirm only after handing the cash to this member. This posts the savings debit and cash payment." data-confirm-button="Yes, cash paid">@csrf
            <label><input type="checkbox" required name="cash_payment_confirmed" value="1"> I confirm payment of Tk {{ $cashout->amount }} cash to this member.</label>
            <button class="btn btn-success">Confirm cash payment</button>
        </form>
        @endif
        @if(in_array($cashout->effectiveStatus(), ['Pending', 'Accepted']))
        <form class="mt-2" method="POST" action="{{ route('card-management.cashout.change', [$cashout, 'reject']) }}" data-cashout-confirm data-confirm-title="Reject cash-out request?" data-confirm-message="{{ $cashout->reference }} | Cash: Tk {{ $cashout->amount }} | Charge: Tk {{ $cashout->fee }} | Savings debit: Tk {{ $cashout->total }}. Reject this request and release its held savings." data-confirm-button="Yes, reject request">@csrf
            <label>Reason for rejection</label><input name="reason" class="form-control" required maxlength="1000"><button class="btn btn-danger mt-1">Reject / release funds</button>
        </form>
        @endif
    </div></div>
    @empty <p>No cash-out requests for this branch.</p> @endforelse
    {{ $requests->links() }}
</div>
@endsection

@push('script')
<script>
    document.querySelectorAll('form[data-cashout-confirm]').forEach(function (form) {
        let confirming = false;
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            if (confirming || !form.reportValidity()) return;
            confirming = true;
            Swal.fire({
                title: form.dataset.confirmTitle,
                text: form.dataset.confirmMessage,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: form.dataset.confirmButton,
                cancelButtonText: 'Go back',
                focusCancel: true,
                allowOutsideClick: false
            }).then(function (result) {
                if (result.isConfirmed || result.value === true) {
                    form.querySelectorAll('button[type="submit"], button:not([type])').forEach(function (button) { button.disabled = true; });
                    HTMLFormElement.prototype.submit.call(form);
                } else {
                    confirming = false;
                }
            }).catch(function () { confirming = false; });
        });
    });
</script>
@endpush
