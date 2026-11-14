@extends('layouts.app')
@section('title', 'Transaction Settings')
@section('content')
<div class="container-fluid">
    <p>Configure membership transaction limits and charges. Changes apply to new transaction quotes; existing cash-out requests retain their quoted amounts.</p>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
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
    @foreach(['cashout' => ['Cash Out', $cashout], 'transfer' => ['Send Money / Transfer', $transfer]] as $group => [$title, $setting])
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
                <p>Charges are deducted in addition to the amount. Set charge rate to zero for free transactions.</p>
                @if($group === 'transfer')<p>Automatic journal postings are recorded under the admin saving these transfer settings. Sending and receiving members are recorded separately for each transfer.</p>@endif
                <button class="btn btn-primary" type="submit">Save {{ $title }} settings</button>
            </form>
        </div>
    </div>
    @endforeach
    <div class="card" id="cashin"><div class="card-header">Cash In</div><div class="card-body"><span class="badge badge-secondary">Not available yet</span><p class="mt-2">Cash In remains disabled until its workflow is implemented.</p></div></div>
</div>
@endsection
