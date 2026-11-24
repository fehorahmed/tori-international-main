@extends('layouts.app')
@section('title', 'Transaction Settings')
@section('content')
<div class="container-fluid">
    <p>Configure membership transaction limits and charges. Changes apply to new transaction quotes; existing cash-out requests retain their quoted amounts.</p>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    @php($window = membership_transaction_settings('transaction_window'))
    <div class="card" id="transaction-window">
        <div class="card-header"><h4 class="card-title">Daily Closing Hours — Send Money &amp; Member Cash-in</h4></div>
        <div class="card-body"><form method="post" action="{{ route('card-management.transaction-settings.update', 'transaction_window') }}">@csrf
            <input type="hidden" name="_group" value="transaction_window">
            <label><input type="checkbox" name="enabled" value="1" @checked(old('_group') === 'transaction_window' ? old('enabled', false) : $window->enabled)> Enable daily closing hours</label>
            <div class="row"><div class="col-md-4 form-group"><label>Close from (Bangladesh time)</label><input class="form-control" type="time" name="start_time" required value="{{ old('_group') === 'transaction_window' ? old('start_time', $window->start_time) : $window->start_time }}"></div>
            <div class="col-md-4 form-group"><label>Reopen at (Bangladesh time)</label><input class="form-control" type="time" name="end_time" required value="{{ old('_group') === 'transaction_window' ? old('end_time', $window->end_time) : $window->end_time }}"></div></div>
            <p>Applies every day. Overnight periods are supported (for example 23:00–05:00). Cash-out is unaffected. Disable to allow transactions all day, subject to other limits.</p>
            <button class="btn btn-primary">Save Closing Hours</button>
        </form></div>
    </div>
    <div class="card" id="membership">
        <div class="card-header"><h4 class="card-title">Membership</h4></div>
        <div class="card-body">
            <form method="POST" action="{{ route('card-management.transaction-settings.update', 'membership') }}">
                @csrf
                <input type="hidden" name="_group" value="membership">
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label for="membership-registration-fee">Membership Registration Fee (Tk)</label>
                        <input id="membership-registration-fee" class="form-control" name="registration_fee" type="number" min="0" max="10000000" step="0.01" required value="{{ old('_group') === 'membership' ? old('registration_fee', $membership->registration_fee) : $membership->registration_fee }}">
                    </div>
                    <div class="col-md-6 form-group">
                        <label for="membership-account-hold">Membership Account Hold Amount (Tk)</label>
                        <small class="d-block text-muted">Protected minimum savings balance for every member. Withdrawals, transfers and charges cannot spend this amount.</small>
                        <input id="membership-account-hold" class="form-control" name="account_hold_amount" type="number" min="0" max="10000000" step="0.01" required value="{{ old('_group') === 'membership' ? old('account_hold_amount', $membership->account_hold_amount) : $membership->account_hold_amount }}">
                    </div>
                </div>
                <div class="form-group mt-3">
                    <label>Registration fee income ledger</label>
                    <select class="form-control" name="income_head_id">
                        <option value="">Select income ledger (required for a positive fee)</option>
                        @foreach($incomeHeads as $head)
                        <option value="{{ $head->id }}" @selected($head->id == (old('_group') === 'membership' ? old('income_head_id', $membership->income_head_id) : $membership->income_head_id))>{{ $head->name }}</option>
                        @endforeach
                    </select>
                    <small>New members are charged once after a deposit or incoming transfer can cover the full fee while retaining the protected minimum balance.</small>
                </div>
                <button class="btn btn-primary" type="submit">Save Membership settings</button>
            </form>
        </div>
    </div>
    <div class="card" id="referral">
        <div class="card-header"><h4 class="card-title">Referral Bonus &amp; Commission</h4></div>
        <div class="card-body"><form method="POST" action="{{ route('card-management.transaction-settings.update', 'referral') }}">
            @csrf<input type="hidden" name="_group" value="referral">
            @php($rv = fn($key) => old('_group') === 'referral' ? old($key, $referral->{$key} ?? null) : ($referral->{$key} ?? null))
            <label><input type="checkbox" name="enabled" value="1" @checked(old('_group') === 'referral' ? old('enabled', false) : $referral->enabled)> Enable referral rewards and point redemption</label>
            <div class="row">
                <div class="col-md-4 form-group"><label>Member points per successful referral</label><input class="form-control" type="number" name="member_points" min="0" max="1000000" step="1" required value="{{ $rv('member_points') }}"></div>
                <div class="col-md-4 form-group"><label>Value of 1 point (Tk)</label><input class="form-control" type="number" name="point_value" min="0.01" max="10000" step="0.01" required value="{{ $rv('point_value') }}"></div>
                <div class="col-md-4 form-group"><label>Agent bonus per successful referral (Tk)</label><input class="form-control" type="number" name="agent_bonus" min="0" max="10000000" step="0.01" required value="{{ $rv('agent_bonus') }}"></div>
                @foreach(['expense_head_id' => ['Referral expense ledger', 5, 'Debit'], 'payable_head_id' => ['Agent commission payable ledger', 2, 'Credit']] as $field => [$label, $top, $nature])
                <div class="col-md-6 form-group"><label>{{ $label }}</label><select name="{{ $field }}" class="form-control"><option value="">Select ledger</option>
                @foreach($referralHeads->filter(fn($h) => $h->accountGroup?->top_reserved_id == $top && $h->nature === $nature) as $head)
                <option value="{{ $head->id }}" @selected($head->id == $rv($field))>{{ $head->name }}</option>@endforeach</select></div>
                @endforeach
            </div>
            <p>Bonuses are earned once, after the full registration fee is deducted. New rewards use the settings at fee collection. Members redeem whole points manually at the current point value; expense is posted at redemption. Agents accrue a payable at fee collection and admin pays later. Zero points / bonus means no reward. Changes do not backfill fees already collected.</p>
            <button class="btn btn-primary">Save Referral settings</button>
        </form></div>
    </div>
    <div class="card" id="referral-milestones">
        <div class="card-header"><h4 class="card-title">Referral Milestone Bonus</h4></div>
        <div class="card-body"><form method="POST" action="{{ route('card-management.transaction-settings.update', 'referral_milestones') }}">
            @csrf<input type="hidden" name="_group" value="referral_milestones">
            <label><input type="checkbox" name="enabled" value="1" @checked(old('_group') === 'referral_milestones' ? old('enabled', false) : $milestones->enabled)> Enable milestone cash bonuses</label>
            <p>Remaining referral points determine progress. Redeeming points lowers progress. Each milestone pays once per member for life. Bonus = milestone points × percentage × current Tk per point. Cash is credited to savings without spending or adding points. All newly qualified milestones pay; bonuses do not stack into point progress. Referral rewards must also be enabled and their expense ledger configured.</p>
            <p>Disable an existing tier to stop new awards. Editing it keeps its lifetime identity; members already paid for it cannot earn it again. Previously earned pending payments retain their original amount.</p>
            <table class="table table-bordered"><thead><tr><th>Required remaining points</th><th>Bonus (%)</th><th>Status</th></tr></thead><tbody id="milestone-rows">
            @foreach(old('_group') === 'referral_milestones' ? old('tiers', $milestones->tiers) : $milestones->tiers as $i => $tier)
            <tr><td><input type="hidden" name="tiers[{{ $i }}][id]" value="{{ $tier['id'] ?? '' }}"><input class="form-control" type="number" name="tiers[{{ $i }}][threshold]" min="1" max="1000000" required value="{{ $tier['threshold'] }}"></td>
            <td><input class="form-control" type="number" name="tiers[{{ $i }}][percentage]" min="0.01" max="1000" step="0.01" required value="{{ $tier['percentage'] }}"></td>
            <td><select class="form-control" name="tiers[{{ $i }}][enabled]"><option value="1" @selected($tier['enabled'])>Active</option><option value="0" @selected(!$tier['enabled'])>Inactive</option></select></td></tr>
            @endforeach
            </tbody></table>
            <button type="button" class="btn btn-outline-primary" id="add-milestone">Add milestone</button>
            <button type="submit" class="btn btn-primary">Save Milestone settings</button>
        </form></div>
    </div>
    <div class="card" id="membership-discount">
        <div class="card-header"><h4 class="card-title">Membership Discount</h4></div>
        <div class="card-body"><form method="POST" action="{{ route('card-management.transaction-settings.update', 'membership_discount') }}">
            @csrf<input type="hidden" name="_group" value="membership_discount">
            <label><input type="checkbox" name="enabled" value="1" @checked(old('_group') === 'membership_discount' ? old('enabled', false) : $membershipDiscount->enabled)> Enable membership discount</label>
            <div class="form-group"><label>Minimum account balance for discount (Tk)</label>
            <input class="form-control" name="minimum_balance" type="number" min="0" max="10000000" step="0.01" required value="{{ old('_group') === 'membership_discount' ? old('minimum_balance', $membershipDiscount->minimum_balance) : $membershipDiscount->minimum_balance }}"></div>
            <p>Applies only to the separate member discount. General product/package discounts are available to everyone. Total active savings balance is checked before order payment. This is separate from the protected account hold amount.</p>
            <button class="btn btn-primary" type="submit">Save Membership Discount settings</button>
        </form></div>
    </div>
    @foreach(['cashout' => ['Cash Out', $cashout], 'transfer' => ['Send Money / Transfer', $transfer], 'agent_cashin' => ['Member Cash-in', membership_transaction_settings('agent_cashin')], 'agent_cashout' => ['Agent Cash-out', membership_transaction_settings('agent_cashout')]] as $group => [$title, $setting])
    @php($value = fn($key) => old('_group') === $group ? old($key, $setting->{$key} ?? null) : ($setting->{$key} ?? null))
    <div class="card" id="{{ $group }}">
        <div class="card-header"><h4 class="card-title">{{ $title }}</h4></div>
        <div class="card-body">
            <form method="POST" action="{{ route('card-management.transaction-settings.update', $group) }}">
                @csrf
                <input type="hidden" name="_group" value="{{ $group }}">
                <label><input type="checkbox" name="enabled" value="1" @checked(old('_group') === $group ? old('enabled', false) : $setting->enabled)> Enable {{ $title }}</label>
                <div class="row">
                    <div class="col-md-3 form-group"><label>Minimum amount per transaction (Tk)</label><input class="form-control" name="minimum_amount" type="number" min="0.01" max="10000000" step="0.01" required value="{{ $value('minimum_amount') }}"></div>
                    <div class="col-md-3 form-group"><label>Maximum amount per transaction (Tk)</label><input class="form-control" name="maximum_amount" type="number" min="0.01" max="10000000" step="0.01" required value="{{ $value('maximum_amount') }}"></div>
                    <div class="col-md-2 form-group"><label>Charge type</label><select class="form-control" name="charge_type"><option value="fixed" @selected($value('charge_type') === 'fixed')>Fixed Tk</option><option value="percentage" @selected($value('charge_type') === 'percentage')>Percentage (%)</option></select></div>
                    <div class="col-md-2 form-group"><label>Charge rate</label><input class="form-control" type="number" name="charge_rate" min="0" max="10000000" step="0.01" required value="{{ $value('charge_rate') }}"></div>
                    @if($group === 'cashout')<div class="col-md-2 form-group"><label>Pending expiry (minutes)</label><input class="form-control" type="number" name="hold_minutes" min="1" max="60" value="{{ $value('hold_minutes') }}" required></div>@endif
                    <div class="col-md-3 form-group"><label>Charge income ledger</label><select class="form-control" name="income_head_id"><option value="">No charge</option>@foreach($incomeHeads as $head)<option value="{{ $head->id }}" @selected($head->id == $value('income_head_id'))>{{ $head->name }}</option>@endforeach</select></div>
                </div>
                @if($group === 'agent_cashin')<p>Member Cash-in is free. Charge fields are ignored; only an active Agent can initiate it.</p>@endif
                @if($group === 'agent_cashout')
                <div class="row"><div class="col-md-3 form-group"><label>Agent commission type</label><select class="form-control" name="commission_type"><option value="fixed" @selected($value('commission_type') === 'fixed')>Fixed Tk</option><option value="percentage" @selected($value('commission_type') === 'percentage')>Percentage (%)</option></select></div>
                <div class="col-md-3 form-group"><label>Agent commission rate</label><input name="commission_rate" class="form-control" type="number" min="0" max="10000000" step="0.01" required value="{{ $value('commission_rate') }}"></div>
                <div class="col-md-6 form-group"><label>Commission expense ledger</label><select name="expense_head_id" class="form-control"><option value="">Select expense ledger</option>@foreach($referralHeads->filter(fn($h) => $h->nature === 'Debit' && $h->accountGroup?->top_reserved_id == 5) as $head)<option value="{{ $head->id }}" @selected($value('expense_head_id') == $head->id)>{{ $head->name }}</option>@endforeach</select></div></div>
                <p>Customer charge and Agent commission are independent. Commission is credited to the Agent's existing savings after a successful cash-out.</p>
                @endif
                <p>Charges are deducted in addition to the amount. Set charge rate to zero for free transactions.</p>
                @if($group === 'transfer')<p>Automatic journal postings are recorded under the admin saving these transfer settings. Sending and receiving members are recorded separately for each transfer.</p>@endif
                <button class="btn btn-primary" type="submit">Save {{ $title }} settings</button>
            </form>
        </div>
    </div>
    @endforeach

</div>
@endsection

@push('script')
<script>
(() => {
    const rows = document.getElementById('milestone-rows');
    let index = rows.children.length;
    document.getElementById('add-milestone').addEventListener('click', () => {
        const i = index++;
        const row = document.createElement('tr');
        row.innerHTML = `<td><input type="hidden" name="tiers[${i}][id]" value=""><input class="form-control" type="number" name="tiers[${i}][threshold]" min="1" max="1000000" required></td><td><input class="form-control" type="number" name="tiers[${i}][percentage]" min="0.01" max="1000" step="0.01" required></td><td><select class="form-control" name="tiers[${i}][enabled]"><option value="1">Active</option><option value="0">Inactive</option></select></td>`;
        rows.appendChild(row);
    });
})();
</script>
@endpush
