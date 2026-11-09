@extends('layouts.app')
@section('title', 'SMS Templates')

@section('style')
    <link href="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/css/bootstrap4-toggle.min.css" rel="stylesheet">
    <style>
        .template-body-preview {
            background: #f8fafc;
            border-radius: 8px;
            padding: 12px;
            font-family: monospace;
            font-size: 0.85rem;
            color: #475569;
            margin-top: 15px;
            margin-bottom: 15px;
            white-space: pre-wrap;
            border-left: 3px solid #3b82f6;
        }
    </style>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-default">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-file-alt text-primary mr-2"></i> SMS Templates</h3>
                <div class="card-tools">
                    <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addTemplateModal">
                        <i class="fas fa-plus"></i> Create Template
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
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0 pl-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="row">
                    @forelse($templates as $template)
                        <div class="col-md-4 mb-4">
                            <div class="card card-outline card-primary h-100">
                                <div class="card-header">
                                    <h5 class="card-title font-weight-bold mb-0" style="font-size: 1.1rem;">{{ $template->title }}</h5>
                                    <div class="card-tools">
                                        <button class="btn btn-tool" data-toggle="modal" data-target="#editTemplateModal{{ $template->id }}">
                                            <i class="fas fa-edit text-info"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <div class="mb-2">
                                        <small class="text-muted ml-2"><i class="fas fa-bolt text-warning"></i> {{ $template->event_name }}</small>
                                    </div>
                                    <div class="template-body-preview flex-grow-1">{{ $template->body }}</div>
                                </div>
                                <div class="card-footer d-flex justify-content-between align-items-center">
                                    <span class="small font-weight-bold text-muted">Status</span>
                                    <input type="checkbox" class="toggle-status" data-id="{{ $template->id }}" {{ $template->is_active ? 'checked' : '' }} data-toggle="toggle" data-size="sm" data-onstyle="success" data-offstyle="secondary">
                                </div>
                            </div>
                        </div>

            <!-- Edit Modal -->
            <div class="modal fade" id="editTemplateModal{{ $template->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                    <div class="modal-content premium-card">
                        <div class="modal-header premium-header border-0">
                            <h5 class="modal-title font-weight-bold"><i class="fas fa-edit mr-2"></i> Edit Template</h5>
                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form action="{{ route('sms_management.templates.update', $template->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="modal-body p-4">
                                <div class="row">
                                    <div class="col-md-12 form-group">
                                        <label class="font-weight-bold text-dark">Event Trigger</label>
                                        <input type="text" class="form-control form-control-premium bg-light" value="{{ $template->event_name }}" readonly>
                                        <small class="text-muted">Cannot be changed after creation.</small>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="font-weight-bold text-dark">Template Title <span class="text-danger">*</span></label>
                                    <input type="text" name="title" class="form-control form-control-premium" value="{{ $template->title }}" required>
                                </div>
                                <div class="form-group">
                                    <label class="font-weight-bold text-dark">Message Body <span class="text-danger">*</span></label>
                                    <textarea name="body" class="form-control form-control-premium" rows="4" required>{{ $template->body }}</textarea>
                                </div>
                                <div class="form-group">
                                    <label class="font-weight-bold text-dark">Available Variables</label>
                                    <input type="text" name="available_variables" class="form-control form-control-premium" value="{{ $template->available_variables }}" placeholder="e.g., @{{name}}, @{{amount}}, @{{invoice_no}}">
                                    <small class="text-muted">Comma separated list of variables supported by the Listener.</small>
                                </div>
                            </div>
                            <div class="modal-footer border-0 p-4 pt-0">
                                <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-premium">Update Template</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <div class="mb-4 text-muted" style="font-size: 3rem;"><i class="fas fa-file-invoice"></i></div>
                <h5 class="font-weight-bold text-dark">No Templates Found</h5>
                <p class="text-muted">You haven't created any SMS templates yet.</p>
                <button class="btn btn-primary mt-2" data-toggle="modal" data-target="#addTemplateModal">Create First Template</button>
            </div>
        @endforelse
                </div> <!-- End row -->
            </div> <!-- End card-body -->
        </div> <!-- End card -->
    </div> <!-- End col-12 -->
</div> <!-- End Main Row -->

<!-- Add Modal -->
<div class="modal fade" id="addTemplateModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content premium-card">
            <div class="modal-header premium-header border-0">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-plus-circle mr-2"></i> Create New Template</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('sms_management.templates.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-12 form-group">
                            <label class="font-weight-bold text-dark">Event Trigger (Unique Key) <span class="text-danger">*</span></label>
                            <input type="text" name="event_name" class="form-control form-control-premium" placeholder="e.g. SaleInvoiceCreated" required>
                            <small class="text-muted">Must exactly match the Event class name.</small>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold text-dark">Template Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control form-control-premium" placeholder="e.g. Welcome Message" required>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold text-dark">Message Body <span class="text-danger">*</span></label>
                        <textarea name="body" class="form-control form-control-premium" rows="4" placeholder="Dear @{{name}}, your OTP is @{{otp}}" required></textarea>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold text-dark">Available Variables (Documentation purpose)</label>
                        <input type="text" name="available_variables" class="form-control form-control-premium" placeholder="e.g., @{{name}}, @{{amount}}, @{{otp}}">
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-premium">Save Template</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/js/bootstrap4-toggle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        $('.toggle-status').change(function() {
            let id = $(this).data('id');
            
            $.ajax({
                url: `/admin/sms-management/templates/${id}/toggle-status`,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if(response.success) {
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true,
                        });

                        Toast.fire({
                            icon: 'success',
                            title: response.message
                        });
                    }
                }
            });
        });
    });
</script>
@endsection
