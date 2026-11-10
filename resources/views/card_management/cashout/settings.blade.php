@extends('layouts.app')
@section('title', 'Cash-out Settings')
@section('content')
<div class="container-fluid">
    <h3>Cash-out Settings</h3>
    <div class="mb-3">
        <a class="btn btn-outline-primary" href="{{ route('card-management.dashboard') }}">Card Management</a>
        <a class="btn btn-outline-primary" href="{{ route('card-management.cashout.index') }}">Cash-out Counter</a>
        @if(auth()->user()->role === 'Admin')
        <a class="btn btn-outline-secondary" href="{{ route('card-management.cashout.settings') }}">Cash-out Settings</a>
        @endif
    </div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    @if(auth()->user()->role === 'Admin')
    <div class="card"><div class="card-header">Global cash-out settings</div><div class="card-body">
        <form method="POST" action="{{ route('card-management.cashout.settings.update') }}">@csrf
            <label><input type="checkbox" name="enabled" value="1" @checked($setting->enabled)> Enable new cash-out requests</label>
            <div class="row">
                <div class="col-md-3"><label>Maximum cash per transaction (Tk)</label><input class="form-control" name="maximum_amount" type="number" min="1" max="10000000" step="0.01" required value="{{ old('maximum_amount', $setting->maximum_amount) }}"></div>
                <div class="col-md-2"><label>Charge type</label><select class="form-control" name="charge_type"><option value="fixed" @selected($setting->charge_type === 'fixed')>Fixed Tk</option><option value="percentage" @selected($setting->charge_type === 'percentage')>Percentage (%)</option></select></div>
                <div class="col-md-2"><label>Charge rate</label><input class="form-control" type="number" name="charge_rate" min="0" step="0.01" required value="{{ old('charge_rate', $setting->charge_rate ?? 0) }}"></div>
                <div class="col-md-2"><label>Pending expiry (minutes)</label><input class="form-control" type="number" name="hold_minutes" min="1" max="60" value="{{ $setting->hold_minutes ?? 10 }}" required></div>
                <div class="col-md-3"><label>Charge income ledger</label><select class="form-control" name="income_head_id"><option value="">No charge</option>@foreach($incomeHeads as $head)<option value="{{ $head->id }}" @selected($head->id == $setting->income_head_id)>{{ $head->name }}</option>@endforeach</select></div>
            </div>
            <p class="mt-2">Charges are deducted in addition to cash paid. Limits apply to cash amount. Changes apply to new requests; accepted requests retain their quoted charge.</p>
            <button class="btn btn-primary">Save settings</button>
        </form>
    </div></div>
    @endif

</div>
@endsection
