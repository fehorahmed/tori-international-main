@extends('layouts.app')
@section('title', 'Transaction Settings')
@section('content')
<div class="container-fluid">
    <p>Configure membership transaction limits and charges. Changes apply to new transaction quotes; existing cash-out requests retain their quoted amounts.</p>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
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
                    <div class="col-md-3 form-group"><label>Maximum amount per transaction (Tk)</label><input class="form-control" name="maximum_amount" type="number" min="1" max="10000000" step="0.01" required value="{{ $value('maximum_amount') }}"></div>
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
