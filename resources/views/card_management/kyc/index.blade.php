@extends('layouts.app')
@section('title', 'Membership KYC Review')
@section('content')
<div class="card">
    <div class="card-header"><h3 class="card-title">Membership KYC — Active Branch</h3></div>
    <div class="card-body">
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
        @forelse($submissions as $submission)
        <div class="border rounded p-3 mb-3">
            <h4>{{ $submission->submitted_name }} — {{ $submission->status }}</h4>
            <p>Member: {{ $submission->customer->member_no }} | NID: {{ $submission->nid_number }} | Submitted: {{ $submission->created_at }}</p>
            <div class="row">
                @foreach(['selfie' => 'Camera selfie', 'nid_front' => 'NID front', 'nid_back' => 'NID back'] as $kind => $label)
                <div class="col-md-4 mb-3">
                    <p>{{ $label }}</p>
                    <a href="{{ route('card-management.kyc.document', [$submission, $kind]) }}" target="_blank" rel="noopener">
                        <img src="{{ route('card-management.kyc.document', [$submission, $kind]) }}" alt="{{ $label }}" style="max-width:100%;height:200px;object-fit:contain">
                    </a>
                </div>
                @endforeach
            </div>
            @if($submission->status === 'Pending')
            <form method="POST" action="{{ route('card-management.kyc.review', $submission) }}">
                @csrf
                <label for="message-{{ $submission->id }}">Review note (required for rejection)</label>
                <textarea id="message-{{ $submission->id }}" name="reviewer_message" class="form-control mb-2" maxlength="2000"></textarea>
                <button class="btn btn-success" name="status" value="Verified">Approve</button>
                <button class="btn btn-danger" name="status" value="Rejected">Reject</button>
            </form>
            @else
                <p>Review: {{ $submission->reviewer_message }} | {{ $submission->reviewed_at }}</p>
            @endif
        </div>
        @empty
            <p>No KYC submissions for this branch.</p>
        @endforelse
        {{ $submissions->links() }}
    </div>
</div>
@endsection
