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
