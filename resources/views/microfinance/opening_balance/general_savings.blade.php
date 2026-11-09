@extends('layouts.app')
@section('title', $pageTitle)
@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card card-outline card-success">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-database mr-2"></i>{{ $pageTitle }}</h3>
                <div class="card-tools">
                    <span class="badge badge-warning">Migration Date: {{ date('d-m-Y', strtotime(auth()->user()->business_date)) }}</span>
                </div>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-1"></i>
                    Enter the current balance of the General Savings account from the old software.
                    GL Entry: <strong>Dr. Opening Balance Equity → Cr. MFI Savings Liability</strong>
                </div>

                <form id="openingBalanceForm" action="{{ route('microfinance.opening-balance.general-savings.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Somiti <span class="text-danger">*</span></label>
                                <select id="somiti_id" class="form-control select2" required>
                                    <option value="">-- Select Somiti --</option>
                                    @foreach($somitis as $somiti)
                                        <option value="{{ $somiti->id }}">
                                            {{ $somiti->name }} ({{ $somiti->somiti_no }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Member <span class="text-danger">*</span></label>
                                <select name="member_id" id="member_id" class="form-control select2" required>
                                    <option value="">-- Select Member --</option>
                                    @foreach($members as $member)
                                        <option value="{{ $member->id }}" data-somiti-id="{{ $member->somiti_id }}" {{ old('member_id') == $member->id ? 'selected' : '' }}>
                                            {{ $member->name }} ({{ $member->member_no }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Product <span class="text-danger">*</span></label>
                                <select name="mf_savings_product_id" class="form-control" required>
                                    <option value="">-- Select Product --</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" {{ old('mf_savings_product_id') == $product->id ? 'selected' : '' }}>
                                            {{ $product->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Original Opening Date <span class="text-danger">*</span></label>
                                <input type="text" name="opening_date" class="form-control date-picker" required
                                    value="{{ old('opening_date') }}" placeholder="dd-mm-yyyy" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Current Balance <span class="text-danger">*</span></label>
                                <input type="number" name="current_balance" class="form-control" required
                                    step="0.01" min="0.01" value="{{ old('current_balance') }}" placeholder="0.00">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Account Title (Optional)</label>
                                <input type="text" name="account_title" class="form-control"
                                    value="{{ old('account_title') }}" placeholder="Account holder name">
                            </div>
                        </div>
                    </div>
                    <div class="form-group text-right">
                        <button type="submit" class="btn btn-success px-4">
                            <i class="fas fa-save mr-1"></i> Save Opening Balance
                        </button>
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
        // Handle Somiti -> Member filtering
        let allMembers = $('#member_id option').clone();
        $('#somiti_id').on('change', function() {
            let somitiId = $(this).val();
            $('#member_id').empty().append('<option value="">-- Select Member --</option>');
            if(somitiId) {
                let filtered = allMembers.filter(function() {
                    return $(this).data('somiti-id') == somitiId;
                });
                $('#member_id').append(filtered);
            }
            $('#member_id').trigger('change');
        });

        // Handle Form Submission Confirmation
        $('#openingBalanceForm').on('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Are you sure?',
                text: 'Once entered, this cannot be changed!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, save it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            });
        });
    });
</script>
@endsection
