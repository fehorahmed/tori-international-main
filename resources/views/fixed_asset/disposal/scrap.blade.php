@extends('layouts.app')
@section('title', 'Scrap Asset')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card card-default">
            <div class="card-header">
                <h3 class="card-title text-danger"><i class="fas fa-dumpster"></i> Scrap Asset: {{ $fixedAsset->asset_code }}</h3>
            </div>
            <div class="card-body">
                <div class="row">
                <!-- Scrap Form -->
                <div class="col-md-12">
                    <form action="{{ route('fixed-assets.scrap-store', $fixedAsset->id) }}" id="scrap-form" class="form-horizontal" method="POST">
                        @csrf
                        <div class="row">
                            <table class="table table-bordered">
                                <tbody>
                                    <tr>
                                        <td><strong>Asset Name</strong></td>
                                        <td>{{ $fixedAsset->name }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Original Cost</strong></td>
                                        <td>৳ {{ number_format($fixedAsset->total, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Accumulated Dep.</strong></td>
                                        <td class="text-warning">৳ {{ number_format($fixedAsset->accumulated_depreciation, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Current Book Value</strong></td>
                                        <td class="text-danger font-weight-bold">
                                            ৳ {{ number_format($fixedAsset->book_value, 2) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Scrap / Write-off Date <span class="text-danger">*</span></label>
                                <input type="text" name="disposal_date" class="form-control @error('disposal_date') is-invalid @enderror business-date-picker" value="{{ old('disposal_date', date('Y-m-d')) }}" required>
                                @error('disposal_date') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-6 form-group">
                                <label>Recognized Loss Amount</label>
                                <input type="text" class="form-control text-danger font-weight-bold bg-light" value="৳ {{ number_format($fixedAsset->book_value, 2) }}" readonly>
                                <small class="text-muted">The total remaining book value is recorded as loss.</small>
                            </div>

                            <div class="col-md-12 form-group">
                                <label>Reason for Scrapping <span class="text-danger">*</span></label>
                                <textarea name="notes" class="form-control" rows="3" placeholder="e.g. Beyond repair, physically damaged" required>{{ old('notes') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.card-body -->
            <div class="card-footer">
                <button type="submit" class="btn btn-danger bg-gradient-danger">Process Write-off</button>
                <a href="{{ route('fixed-assets.show', $fixedAsset->id) }}" class="btn btn-secondary float-right">Cancel</a>
            </div>
        </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function () {
        $('body').on('submit', '#scrap-form', function (e) {
            e.preventDefault();
            Swal.fire({
                title: 'Are you sure?',
                text: "You are about to scrap this asset. This action will record a financial loss and cannot be undone easily.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, Scrap it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            })
        });
    });
</script>
@endsection
