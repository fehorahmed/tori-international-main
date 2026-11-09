@extends('layouts.app')
@section('title', 'Shomriddhi Opening Balance')
@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card card-default">
                <div class="card-header bg-gradient-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="fa fa-plus-circle mr-2"></i> Shomriddhi Opening Balance
                    </h5>
                </div>
                <form id="openingBalanceForm" action="{{ route('shomriddhi-advance.accounts.store_opening_balance') }}" method="POST">
                    @csrf
                    <div class="card-body pb-0">
                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                            </div>
                        @endif

                        <div class="alert alert-info mb-3">
                            <i class="fas fa-info-circle"></i> 
                            Only customers who have an active Shomriddhi Account and <b>do not</b> already have an Opening Balance entry are shown here.
                        </div>

                        <div class="form-group" id="customer_wrapper">
                            <label class="small font-weight-bold text-dark">Select Customer <span class="text-danger">*</span></label>
                            <select name="customer_id" id="customer_id" class="form-control select2" style="width: 100%;" required>
                                <option value="">Select Customer</option>
                                @foreach($accounts as $account)
                                    <option value="{{ $account->customer_id }}">{{ $account->customer->name ?? 'N/A' }} - {{ $account->customer->member_no ?? 'No Mobile' }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="small font-weight-bold text-dark">Opening Date <span class="text-danger">*</span></label>
                                    <input type="text" autocomplete="off" name="date" class="form-control opening-date-picker @error('date') is-invalid @enderror"
                                        placeholder="Enter date" required readonly>
                                    @error('date') <span class="help-block text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="small font-weight-bold text-dark">Opening Amount <span class="text-danger">*</span></label>
                                    <input type="number" name="amount" class="form-control" step="0.01" min="0.01" placeholder="e.g. 1000" required>
                                    @error('amount') <span class="help-block text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="small font-weight-bold text-dark">Notes</label>
                            <input type="text" name="note" class="form-control" placeholder="Optional notes about this opening balance">
                        </div>
                    </div>
                    <div class="card-footer bg-light">
                        <button type="submit" class="btn btn-info bg-gradient-info"><i class="fa fa-save mr-1"></i> Save Opening Balance</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(function () {
            $('.select2').select2({
                placeholder: "Select a Customer",
                allowClear: true
            });

            var previousDate = '{{ \Carbon\Carbon::parse(auth()->user()->activeSisterConcern->business_date ?? auth()->user()->sisterConcern->business_date ?? now())->subDay()->format('d-m-Y') }}';
            $(".opening-date-picker").datepicker({
                dateFormat: 'dd-mm-yy',
                changeMonth: true,
                changeYear: true,
            }).val(previousDate);

            $('#openingBalanceForm').on('submit', function(e) {
                e.preventDefault();
                let form = this;
                
                let selectedMember = $('#customer_id option:selected').text();
                if(!$('#customer_id').val()) {
                    Swal.fire('Error', 'Please select a customer first!', 'error');
                    return;
                }

                Swal.fire({
                    title: 'Confirm Opening Balance?',
                    text: "You are setting the opening balance for: " + selectedMember,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, Save it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        preloaderToggle(true);
                        form.submit();
                    }
                });
            });
        });
    </script>
@endsection
