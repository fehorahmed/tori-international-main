@extends('layouts.app')

@section('title', 'Shomriddhi Collection Form')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card card-default mt-4">
                <div class="card-header">
                    <h3 class="card-title mb-0"><i class="fas fa-piggy-bank mr-2"></i> Select Customer to Print Form</h3>
                </div>
                <div class="card-body">
                <form action="{{ route('forms_hub.shomriddhi.generate_print') }}" method="GET" target="_blank">
                    <div class="form-group">
                        <label for="account_id">Customer <span class="text-danger">*</span></label>
                        <select name="account_id" id="account_id" class="form-control select2" required>
                            <option value="">Select Customer</option>
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}">{{ $account->customer->name ?? 'N/A' }} ({{ $account->customer->mobile_no ?? 'N/A' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group text-right">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-print"></i> Search & Print</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        $('.select2').select2({
            theme: 'bootstrap4',
            placeholder: "Select Customer"
        });
    });
</script>
@endsection
