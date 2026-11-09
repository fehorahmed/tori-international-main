@extends('layouts.print')
@section('title', 'DPS Closed Accounts Report')
@section('content')
<div class="text-center mb-4">
    <h3 class="font-weight-bold">DPS Closed Accounts Report</h3>
    <p class="mb-0">Date: {{ date('d-M-Y h:i A') }}</p>
</div>

<table class="table table-bordered table-sm">
    <thead class="bg-light">
        <tr>
            <th>SL</th>
            <th>DPS No</th>
            <th>Customer Name</th>
            <th>Mobile</th>
            <th>Scheme</th>
            <th>Closed Date</th>
            <th>Notes</th>
        </tr>
    </thead>
    <tbody>
        @forelse($accounts as $account)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td>{{ $account->dps_no }}</td>
                <td>{{ $account->customer->name ?? '' }}</td>
                <td>{{ $account->customer->mobile_no ?? '' }}</td>
                <td>{{ $account->scheme->name ?? '' }}</td>
                <td class="text-center">{{ \Carbon\Carbon::parse($account->updated_at)->format('d-M-Y') }}</td>
                <td>{{ $account->notes }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center">No closed accounts found.</td>
            </tr>
        @endforelse
    </tbody>
</table>
@endsection
