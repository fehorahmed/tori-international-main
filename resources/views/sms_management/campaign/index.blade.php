@extends('layouts.app')
@section('title', 'Bulk SMS Campaign')

@section('content')
<div class="row">
    <div class="col-12">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0 pl-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <div class="col-md-8">
        <div class="card card-default">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-paper-plane mr-2 text-info"></i> Compose Campaign</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('sms_management.campaign.send') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label>Mobile Numbers <span class="text-danger">*</span></label>
                        <textarea name="numbers" class="form-control" rows="5" placeholder="Enter numbers separated by comma. Example: 01700000000, 01800000000" required></textarea>
                        <div class="d-flex justify-content-between mt-1">
                            <small class="text-muted">Separate multiple numbers with commas.</small>
                            <small class="text-info font-weight-bold" id="number-count">0 numbers</small>
                        </div>
                    </div>

                    <div class="form-group mt-3">
                        <label>Message Text <span class="text-danger">*</span></label>
                        <textarea name="message" id="message-text" class="form-control" rows="6" placeholder="Type your promotional message here..." required></textarea>
                        <div class="d-flex justify-content-between mt-1">
                            <small class="text-muted">Avoid using special variables like @{{name}} here as they will not be parsed.</small>
                            <small class="text-info font-weight-bold" id="char-count">0 chars | 1 SMS</small>
                        </div>
                    </div>

                    <div class="mt-4 border-top pt-3 text-right">
                        <button type="submit" class="btn btn-info bg-gradient-info" onclick="return confirm('Are you sure you want to send this campaign? This cannot be undone.');">
                            <i class="fas fa-rocket"></i> Launch Campaign
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card card-default">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-lightbulb text-warning mr-2"></i> Quick Tips</h3>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <h6 class="font-weight-bold"><i class="fas fa-check text-success mr-2"></i> Valid Numbers</h6>
                    <p class="text-muted small">Ensure numbers are valid Bangladeshi formats (e.g., 01712345678). Invalid formats will be automatically skipped by the queue.</p>
                </div>

                <div class="mb-4">
                    <h6 class="font-weight-bold"><i class="fas fa-text-width text-primary mr-2"></i> SMS Length</h6>
                    <p class="text-muted small">
                        - 1 SMS = 160 characters (English)<br>
                        - 1 SMS = 70 characters (Bengali/Unicode)<br>
                        Longer messages will be charged as multiple SMS by the gateway provider.
                    </p>
                </div>

                <div>
                    <h6 class="font-weight-bold"><i class="fas fa-tachometer-alt text-danger mr-2"></i> Background Processing</h6>
                    <p class="text-muted small mb-0">Campaigns are dispatched to background queues. Track delivery in the <a href="{{ route('sms_management.logs.index') }}">Logs</a> section.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        // Count numbers
        $('textarea[name="numbers"]').on('input', function() {
            let text = $(this).val();
            if(text.trim() === '') {
                $('#number-count').text('0 numbers');
                return;
            }
            let arr = text.split(',').filter(item => item.trim() !== '');
            $('#number-count').text(arr.length + ' numbers');
        });

        // Count characters and SMS
        $('#message-text').on('input', function() {
            let text = $(this).val();
            let length = text.length;
            
            // Check for unicode (very basic check)
            let isUnicode = /[^\u0000-\u00ff]/.test(text);
            
            let limit = isUnicode ? 70 : 160;
            let smsCount = Math.ceil(length / limit);
            if(length === 0) smsCount = 1;

            $('#char-count').text(`${length} chars | ${smsCount} SMS`);
        });
    });
</script>
@endsection
