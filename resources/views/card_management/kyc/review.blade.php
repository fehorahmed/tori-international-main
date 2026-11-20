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
