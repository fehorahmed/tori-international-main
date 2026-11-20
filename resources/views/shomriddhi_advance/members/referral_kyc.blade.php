<div class="col-md-6">
    <div class="info-card">
        <div class="info-card-header">
            <div class="icon-circle" style="background:#e8f5e9;"><i class="fas fa-user-friends text-success"></i></div>
            <h6>Referral Information</h6>
        </div>
        <div class="p-3">
            <dl class="row mb-0" style="overflow-wrap:anywhere;">
                <dt class="col-sm-5">Own Referral ID</dt>
                <dd class="col-sm-7">{{ $member->member_no ?: '—' }}</dd>
                <dt class="col-sm-5">Members Referred</dt>
                <dd class="col-sm-7">{{ $referralCount }}</dd>
                <dt class="col-sm-5">Available Points</dt>
                <dd class="col-sm-7">{{ rtrim(rtrim(number_format($referralPoints, 6, '.', ''), '0'), '.') ?: '0' }}
                </dd>
                @if ($referredBy)
                    <dt class="col-sm-5">Referred By</dt>
                    <dd class="col-sm-7">{{ $referredBy['name'] ?: 'Unavailable' }}</dd>
                    <dt class="col-sm-5">Referrer Type</dt>
                    <dd class="col-sm-7">{{ ucfirst($referredBy['type']) }}</dd>
                    <dt class="col-sm-5">Code at Registration</dt>
                    <dd class="col-sm-7">{{ $referredBy['code'] }}</dd>
                    <dt class="col-sm-5">Referrer Bonus</dt>
                    <dd class="col-sm-7">
                        @if (!$referralReward)
                            Not awarded yet
                        @elseif($referralReward->referrer_type === 'member')
                            {{ $referralReward->points }} points awarded
                        @else
                            Tk {{ number_format($referralReward->amount, 2) }} —
                            {{ $referralReward->paid_at ? 'Paid' : 'Payment pending' }}
                        @endif
                    </dd>
                @else
                    <dt class="col-sm-5">Referred By</dt>
                    <dd class="col-sm-7">No referral recorded</dd>
                @endif
                <dt class="col-sm-5">Registration Fee</dt>
                <dd class="col-sm-7">
                    @if ($registrationFee)
                        Tk {{ number_format($registrationFee->amount, 2) }} —
                        {{ $registrationFee->paid_at ? 'Paid' : 'Pending' }}
                    @else
                        No fee record
                    @endif
                </dd>
            </dl>
        </div>
    </div>
</div>
<div class="col-md-6">
    <div class="info-card">
        <div class="info-card-header">
            <div class="icon-circle" style="background:#e3f2fd;"><i class="fas fa-id-card text-primary"></i></div>
            <h6>KYC Information</h6>
        </div>
        <div class="p-3">
            <dl class="row mb-0" style="overflow-wrap:anywhere;">
                <dt class="col-sm-5">KYC Status</dt>
                <dd class="col-sm-7">{{ $kycStatus === 'Verified' ? 'Approved' : $kycStatus }}</dd>
                @if ($kyc)
                    <dt class="col-sm-5">Submitted Name</dt>
                    <dd class="col-sm-7">{{ $kyc->submitted_name }}</dd>
                    <dt class="col-sm-5">NID Number</dt>
                    <dd class="col-sm-7">{{ $kyc->nid_number }}</dd>
                    <dt class="col-sm-5">Submitted At</dt>
                    <dd class="col-sm-7">{{ $kyc->created_at }}</dd>
                    <dt class="col-sm-5">Reviewed At</dt>
                    <dd class="col-sm-7">{{ $kyc->reviewed_at ?: '—' }}</dd>
                    <dt class="col-sm-5">Review Note</dt>
                    <dd class="col-sm-7" style="white-space:pre-wrap;">{{ $kyc->reviewer_message ?: '—' }}</dd>
                @endif
            </dl>
            @if ($kyc && $canViewKycDocuments)
                <div class="row mt-2">
                    @foreach (['selfie' => 'Selfie', 'nid_front' => 'NID Front', 'nid_back' => 'NID Back'] as $kind => $label)
                        <div class="col-sm-4 mb-3">
                            <a href="{{ route('card-management.kyc.document', [$kyc, $kind]) }}" target="_blank"
                                rel="noopener">
                                <img src="{{ route('card-management.kyc.document', [$kyc, $kind]) }}"
                                    alt="{{ $label }}" loading="lazy" class="img-thumbnail"
                                    style="width:100%;height:110px;object-fit:contain;">
                                {{ $label }}
                            </a>
                        </div>
                    @endforeach
                </div>
                <a class="btn btn-sm btn-outline-primary" href="{{ route('card-management.kyc.index') }}">KYC Review
                    List</a>
            @endif
        </div>
    </div>
</div>
