@extends('layouts.app')
@section('title', 'Issue Member Card')
@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card card-default">
                <div class="card-header bg-gradient-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="fa fa-plus-circle mr-2"></i> Issue Member Card (সদস্য কার্ড তৈরি করুন)
                    </h5>
                </div>
                <form id="issueCardForm" action="{{ route('card-management.member.store') }}" method="POST">
                    @csrf
                    <div class="card-body pb-0">
                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                            </div>
                        @endif

                        <div class="form-group" id="customer_wrapper">
                            <label class="small font-weight-bold text-dark">Select Member <span class="text-danger">*</span></label>
                            <select name="holder_id" id="holder_id_customer" class="form-control select2" style="width: 100%;" required>
                                <option value="">Select Member</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->mobile_no ?? 'No Mobile' }})</option>
                                @endforeach
                            </select>
                            <span class="text-muted small">Only members who do not currently have an active card are shown.</span>
                        </div>

                        <div class="form-group">
                            <label class="small font-weight-bold text-dark">Card Price / Amount</label>
                            <input type="number" name="amount" class="form-control" value="{{ $feeAmount }}" readonly>
                            <span class="text-muted small">This amount will be recorded as card issuance fee.</span>
                        </div>
                    </div>
                    <div class="card-footer bg-light">
                        <button type="submit" class="btn btn-info bg-gradient-info btn-sm"><i class="fa fa-check-circle mr-1"></i> Issue Card</button>
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
                placeholder: "Select a Member",
                allowClear: true
            });

            $('#issueCardForm').on('submit', function(e) {
                e.preventDefault();
                let form = this;
                
                let selectedMember = $('#holder_id_customer option:selected').text();
                if(!$('#holder_id_customer').val()) {
                    Swal.fire('Error', 'Please select a member first!', 'error');
                    return;
                }

                Swal.fire({
                    title: 'Are you sure?',
                    text: "You are about to issue a card to: " + selectedMember,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, Issue Card!'
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
