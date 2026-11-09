@extends('layouts.app')
@section('title', 'Edit FDR Account')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-default">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-edit mr-2"></i> Edit FDR Account: {{ $account->fdr_no }}
                </h5>
                <div class="card-tools">
                    <a href="{{ route('shomriddhi-fdr.accounts.index') }}" class="btn btn-sm btn-info">
                        <i class="fa fa-list"></i> Accounts List
                    </a>
                </div>
            </div>

            <div class="card-body">
                @if(session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif
                <form action="{{ route('shomriddhi-fdr.accounts.update', $account->id) }}" method="POST" id="fdrAccountForm">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label for="customer_id">Select Customer <span class="text-danger">*</span></label>
                                <select class="form-control select2 @error('customer_id') is-invalid @enderror" id="customer_id" name="customer_id" required style="width: 100%;">
                                    <option value="">-- Choose Customer --</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}" {{ old('customer_id', $account->customer_id) == $customer->id ? 'selected' : '' }}>
                                            {{ $customer->name }} ({{ $customer->phone }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('customer_id') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label for="scheme_id">FDR Scheme <span class="text-danger">*</span></label>
                                <select class="form-control select2 @error('scheme_id') is-invalid @enderror" id="scheme_id" name="scheme_id" required style="width: 100%;">
                                    <option value="">-- Choose Scheme --</option>
                                    @foreach($schemes as $scheme)
                                        <option value="{{ $scheme->id }}" 
                                            data-interest="{{ $scheme->interest_rate }}"
                                            data-min="{{ $scheme->min_amount }}"
                                            data-max="{{ $scheme->max_amount }}"
                                            data-duration="{{ $scheme->duration }}"
                                            {{ old('scheme_id', $account->scheme_id) == $scheme->id ? 'selected' : '' }}>
                                            {{ $scheme->name }} ({{ $scheme->duration }} Months - {{ $scheme->interest_rate }}%)
                                        </option>
                                    @endforeach
                                </select>
                                <small id="scheme_hint" class="text-info"></small>
                                @error('scheme_id') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <div class="form-group">
                                <label for="principal_amount">Principal Amount <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control @error('principal_amount') is-invalid @enderror" id="principal_amount" name="principal_amount" value="{{ old('principal_amount', $account->principal_amount) }}" required min="0">
                                @error('principal_amount') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <div class="form-group">
                                <label for="interest_payout_frequency">Payout Frequency <span class="text-danger">*</span></label>
                                <select class="form-control select2 @error('interest_payout_frequency') is-invalid @enderror" id="interest_payout_frequency" name="interest_payout_frequency" required style="width: 100%;">
                                    <option value="At Maturity" {{ old('interest_payout_frequency', $account->interest_payout_frequency) == 'At Maturity' ? 'selected' : '' }}>At Maturity (Cumulative)</option>
                                    <option value="Monthly" {{ old('interest_payout_frequency', $account->interest_payout_frequency) == 'Monthly' ? 'selected' : '' }}>Monthly</option>
                                    <option value="Quarterly" {{ old('interest_payout_frequency', $account->interest_payout_frequency) == 'Quarterly' ? 'selected' : '' }}>Quarterly (3 Months)</option>
                                    <option value="Half-Yearly" {{ old('interest_payout_frequency', $account->interest_payout_frequency) == 'Half-Yearly' ? 'selected' : '' }}>Half-Yearly (6 Months)</option>
                                    <option value="Yearly" {{ old('interest_payout_frequency', $account->interest_payout_frequency) == 'Yearly' ? 'selected' : '' }}>Yearly (12 Months)</option>
                                </select>
                                @error('interest_payout_frequency') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <div class="form-group">
                                <label for="opening_date">Opening Date <span class="text-danger">*</span></label>
                                <input type="text" class="form-control date-picker @error('opening_date') is-invalid @enderror" id="opening_date" name="opening_date" value="{{ old('opening_date', \Carbon\Carbon::parse($account->opening_date)->format('d-m-Y')) }}" required>
                                @error('opening_date') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <div class="form-group">
                                <label>Maturity Date (Calculated)</label>
                                <input type="text" class="form-control bg-light font-weight-bold text-danger" id="calculated_maturity" readonly value="Select Scheme & Date">
                            </div>
                        </div>
                        
                        <div class="col-md-12 mb-3">
                            <div class="form-group mt-1">

                        <div class="col-md-12 mb-3">
                            <div class="form-group">
                                <label for="notes">Notes/Remarks (Optional)</label>
                                <textarea class="form-control" id="notes" name="notes" rows="2">{{ old('notes', $account->notes) }}</textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-12 text-right">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-sync-alt"></i> Update Application
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
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fa fa-eye mr-2"></i> Preview FDR Account Details</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-bordered table-sm">
                            <tr><th width="40%">Member</th><td id="prev_member"></td></tr>
                            <tr><th>Scheme</th><td id="prev_scheme"></td></tr>
                            <tr><th>Amount</th><td id="prev_amount" class="font-weight-bold text-success"></td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-bordered table-sm">
                            <tr><th width="40%">Payout Freq.</th><td id="prev_freq"></td></tr>
                            <tr><th>Opening Date</th><td id="prev_date"></td></tr>
                            <tr><th>Auto Renew</th><td id="prev_auto"></td></tr>
                        </table>
                    </div>
                </div>
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

        function calculateMaturityDate() {
            let openingDateStr = $('#opening_date').val(); 
            let selectedScheme = $('#scheme_id').find(':selected');
            
            if (openingDateStr && selectedScheme.val()) {
                let durationMonths = parseInt(selectedScheme.data('duration')) || 0;
                if (durationMonths > 0) {
                    let parts = openingDateStr.split('-');
                    if (parts.length === 3) {
                        let date = new Date(parts[2], parts[1] - 1, parts[0]); // YYYY, MM, DD from DD-MM-YYYY
                        date.setMonth(date.getMonth() + durationMonths);
                        
                        let day = ("0" + date.getDate()).slice(-2);
                        let monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
                        let monthIndex = date.getMonth();
                        let year = date.getFullYear();
                        
                        $('#calculated_maturity').val(day + " " + monthNames[monthIndex] + " " + year);
                        return;
                    }
                }
            }
            $('#calculated_maturity').val('Select Scheme & Date');
        }

        $('#scheme_id').change(function() {
            let selected = $(this).find(':selected');
            if(selected.val()) {
                let min = selected.data('min');
                let max = selected.data('max');
                let hint = "Min: ৳ " + min;
                if(max) hint += " | Max: ৳ " + max;
                $('#scheme_hint').text(hint);
                $('#principal_amount').attr('min', min);
                if(max) $('#principal_amount').attr('max', max);
            } else {
                $('#scheme_hint').text('');
                $('#principal_amount').removeAttr('min max');
            }
            calculateMaturityDate();
        });
        
        $('#opening_date').on('change blur', calculateMaturityDate);
        
        // Trigger on load for old inputs
        if($('#scheme_id').val()) {
            $('#scheme_id').trigger('change');
            calculateMaturityDate();
        }

        $('#fdrAccountForm').submit(function(e) {
            e.preventDefault();

            // Populate preview modal
            $('#prev_member').text($('#customer_id option:selected').text());
            $('#prev_scheme').text($('#scheme_id option:selected').text());
            $('#prev_amount').text('৳ ' + $('#principal_amount').val());
            $('#prev_freq').text($('#interest_payout_frequency').val());
            $('#prev_date').text($('#opening_date').val());
            $('#prev_auto').text($('#auto_renew').is(':checked') ? 'Yes' : 'No');
            
            $('#previewModal').modal('show');
        });

        $('#btnFinalSubmit').on('click', function() {
            Swal.fire({
                title: 'Are you sure?',
                text: "You are about to update this FDR Account application.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Update it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#previewModal').modal('hide');
                    $('#fdrAccountForm')[0].submit();
                }
            });
        });
    });
</script>
@endsection
