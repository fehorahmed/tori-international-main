@extends('layouts.app')
@section('title', 'SMS Gateways')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-default">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-satellite-dish text-info mr-2"></i> SMS API Gateways</h3>
                <div class="card-tools">
                    <button class="btn btn-info btn-sm bg-gradient-info" data-toggle="modal" data-target="#addGatewayModal">
                        <i class="fas fa-plus"></i> Add New Gateway
                    </button>
                </div>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                <div class="row">
        @forelse($gateways as $gateway)
            <div class="col-md-4 mb-4">
                <div class="gateway-card {{ $gateway->is_active ? 'active-gateway' : '' }}">
                    @if($gateway->is_active)
                        <div class="active-badge">ACTIVE</div>
                    @endif
                    
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="gateway-icon">
                            <i class="fas fa-server"></i>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light rounded-circle" type="button" data-toggle="dropdown">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-right shadow-sm border-0" style="border-radius: 12px;">
                                <a class="dropdown-item py-2" href="#" data-toggle="modal" data-target="#editGatewayModal{{ $gateway->id }}">
                                    <i class="fas fa-edit text-primary mr-2"></i> Edit Configuration
                                </a>
                                @if(!$gateway->is_active)
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item py-2 activate-btn" href="javascript:void(0)" data-id="{{ $gateway->id }}">
                                        <i class="fas fa-check-circle text-success mr-2"></i> Make Active
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <h5 class="gateway-title">{{ $gateway->provider_name }}</h5>
                    <p class="text-muted small mb-3">Sender ID: <span class="font-weight-bold text-dark">{{ $gateway->sender_id ?? 'N/A' }}</span></p>
                    
                    <div class="bg-light p-2 rounded small text-truncate" style="font-family: monospace;">
                        <i class="fas fa-key text-muted mr-1"></i> 
                        {{ substr($gateway->api_key, 0, 10) }}••••••••••
                    </div>
                </div>
            </div>

            <!-- Edit Modal -->
            <div class="modal fade" id="editGatewayModal{{ $gateway->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content premium-card">
                        <div class="modal-header premium-header border-0">
                            <h5 class="modal-title font-weight-bold"><i class="fas fa-edit mr-2"></i> Edit Gateway</h5>
                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form action="{{ route('sms_management.gateways.update', $gateway->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="modal-body p-4">
                                <div class="form-group">
                                    <label class="font-weight-bold text-dark">Provider Name <span class="text-danger">*</span></label>
                                    <select name="provider_name" class="form-control form-control-premium" required>
                                        <option value="SSL Wireless" {{ $gateway->provider_name == 'SSL Wireless' ? 'selected' : '' }}>SSL Wireless</option>
                                        <option value="BulkSMS BD" {{ $gateway->provider_name == 'BulkSMS BD' ? 'selected' : '' }}>BulkSMS BD</option>
                                        <option value="Twilio" {{ $gateway->provider_name == 'Twilio' ? 'selected' : '' }}>Twilio</option>
                                        <option value="Custom API" {{ $gateway->provider_name == 'Custom API' ? 'selected' : '' }}>Custom API</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="font-weight-bold text-dark">API URL <span class="text-danger">*</span></label>
                                    <input type="url" name="api_url" class="form-control form-control-premium" value="{{ $gateway->api_url }}" required>
                                </div>
                                <div class="form-group">
                                    <label class="font-weight-bold text-dark">API Token/Key <span class="text-danger">*</span></label>
                                    <input type="text" name="api_key" class="form-control form-control-premium" value="{{ $gateway->api_key }}" required>
                                </div>
                                <div class="form-group">
                                    <label class="font-weight-bold text-dark">Sender ID</label>
                                    <input type="text" name="sender_id" class="form-control form-control-premium" value="{{ $gateway->sender_id }}">
                                </div>
                            </div>
                            <div class="modal-footer border-0 p-4 pt-0">
                                <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-premium">Update Gateway</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <div class="mb-4 text-muted" style="font-size: 3rem;"><i class="fas fa-plug"></i></div>
                <h5 class="font-weight-bold text-dark">No Gateways Configured</h5>
                <p class="text-muted">You haven't set up any SMS API providers yet. Add one to get started.</p>
                <button class="btn btn-primary mt-2" data-toggle="modal" data-target="#addGatewayModal">Add Gateway Now</button>
            </div>
        @endforelse
                </div> <!-- End Row -->
            </div> <!-- End Card Body -->
        </div> <!-- End Card -->
    </div> <!-- End col-12 -->
</div> <!-- End Main Row -->

<!-- Add Modal -->
<div class="modal fade" id="addGatewayModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content premium-card">
            <div class="modal-header premium-header border-0">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-plus-circle mr-2"></i> Add New Gateway</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('sms_management.gateways.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="form-group">
                        <label class="font-weight-bold text-dark">Provider Name <span class="text-danger">*</span></label>
                        <select name="provider_name" class="form-control form-control-premium" required>
                            <option value="">Select Provider...</option>
                            <option value="SSL Wireless">SSL Wireless</option>
                            <option value="BulkSMS BD">BulkSMS BD</option>
                            <option value="Twilio">Twilio</option>
                            <option value="Custom API">Custom API</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold text-dark">API URL <span class="text-danger">*</span></label>
                        <input type="url" name="api_url" class="form-control form-control-premium" placeholder="https://api.provider.com/send" required>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold text-dark">API Token/Key <span class="text-danger">*</span></label>
                        <input type="text" name="api_key" class="form-control form-control-premium" placeholder="Your secret API token" required>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold text-dark">Sender ID</label>
                        <input type="text" name="sender_id" class="form-control form-control-premium" placeholder="Optional Sender Name/ID">
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-premium">Save Gateway</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        $('.activate-btn').click(function() {
            let id = $(this).data('id');
            
            Swal.fire({
                title: 'Activate this Gateway?',
                text: "All out-going SMS will be routed through this API.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, Make Active!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/admin/sms-management/gateways/${id}/make-active`,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if(response.success) {
                                Swal.fire(
                                    'Activated!',
                                    response.message,
                                    'success'
                                ).then(() => {
                                    location.reload();
                                });
                            }
                        }
                    });
                }
            })
        });
    });
</script>
@endsection
