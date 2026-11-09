@extends('layouts.app')
@section('title', 'Edit FDR Scheme')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-default">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-edit mr-2"></i> Edit Scheme: {{ $scheme->name }}
                </h5>
                <div class="card-tools">
                    <a href="{{ route('shomriddhi-fdr.schemes.index') }}" class="btn btn-sm btn-info">
                        <i class="fa fa-list"></i> Scheme List
                    </a>
                </div>
            </div>

            <div class="card-body">
                <form action="{{ route('shomriddhi-fdr.schemes.update', $scheme->id) }}" method="POST" id="schemeForm">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label for="name">Scheme Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $scheme->name) }}" required>
                                @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label for="duration">Duration (Months) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('duration') is-invalid @enderror" id="duration" name="duration" value="{{ old('duration', $scheme->duration) }}" required min="1">
                                @error('duration') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label for="interest_rate">Interest Rate (%) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control @error('interest_rate') is-invalid @enderror" id="interest_rate" name="interest_rate" value="{{ old('interest_rate', $scheme->interest_rate) }}" required min="0">
                                @error('interest_rate') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label for="penalty_rate">Penalty Rate (%)</label>
                                <input type="number" step="0.01" class="form-control @error('penalty_rate') is-invalid @enderror" id="penalty_rate" name="penalty_rate" value="{{ old('penalty_rate', $scheme->penalty_rate) }}" min="0">
                                @error('penalty_rate') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <div class="form-group">
                                <label for="min_amount">Minimum Deposit <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control @error('min_amount') is-invalid @enderror" id="min_amount" name="min_amount" value="{{ old('min_amount', $scheme->min_amount) }}" required min="0">
                                @error('min_amount') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <div class="form-group">
                                <label for="max_amount">Maximum Deposit</label>
                                <input type="number" step="0.01" class="form-control @error('max_amount') is-invalid @enderror" id="max_amount" name="max_amount" value="{{ old('max_amount', $scheme->max_amount) }}" min="0">
                                @error('max_amount') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <div class="form-group">
                                <label for="status">Status <span class="text-danger">*</span></label>
                                <select class="form-control select2 @error('status') is-invalid @enderror" id="status" name="status" required style="width: 100%;">
                                    <option value="Active" {{ old('status', $scheme->status) == 'Active' ? 'selected' : '' }}>Active</option>
                                    <option value="Inactive" {{ old('status', $scheme->status) == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @error('status') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-12 text-right">
                            <button type="submit" class="btn btn-success">
                                <i class="fa fa-sync-alt"></i> Update Scheme
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Preview Modal --}}
<div class="modal fade" id="previewModal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fa fa-eye mr-2"></i> Preview Scheme Details</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered">
                    <tr><th>Scheme Name</th><td id="prev_name"></td></tr>
                    <tr><th>Duration</th><td id="prev_duration"></td></tr>
                    <tr><th>Interest Rate</th><td id="prev_interest"></td></tr>
                    <tr><th>Penalty Rate</th><td id="prev_penalty"></td></tr>
                    <tr><th>Min Deposit</th><td id="prev_min"></td></tr>
                    <tr><th>Max Deposit</th><td id="prev_max"></td></tr>
                    <tr><th>Status</th><td id="prev_status"></td></tr>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Edit</button>
                <button type="button" class="btn btn-success" id="btnFinalSubmit"><i class="fa fa-sync-alt"></i> Final Update</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        $('.select2').select2();

        $('#schemeForm').on('submit', function(e) {
            e.preventDefault();
            
            // Populate preview modal
            $('#prev_name').text($('#name').val());
            $('#prev_duration').text($('#duration').val() + ' Months');
            $('#prev_interest').text($('#interest_rate').val() + '%');
            $('#prev_penalty').text($('#penalty_rate').val() ? $('#penalty_rate').val() + '%' : 'None');
            $('#prev_min').text('৳ ' + $('#min_amount').val());
            $('#prev_max').text($('#max_amount').val() ? '৳ ' + $('#max_amount').val() : 'No Limit');
            $('#prev_status').text($('#status').val());
            
            $('#previewModal').modal('show');
        });

        $('#btnFinalSubmit').on('click', function() {
            Swal.fire({
                title: 'Are you sure?',
                text: "You are about to update this FDR Scheme.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Update it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#previewModal').modal('hide');
                    $('#schemeForm')[0].submit();
                }
            });
        });
    });
</script>
@endsection
