@extends('layouts.app')
@section('title', 'Invoice Wise Profit / Loss')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-default">
            <div class="card-header">
                <div class="card-title">Filter Data</div>
            </div>
            <div class="card-body">
                <form id="search-form">
                    @include('layouts.partial.__report_branch_filter')
                    <div class="row">
                        <div class="col-12 col-md-3">
                            <div class="form-group">
                                <label for="start_date" class="col-form-label">Start Date <span class="text-danger">*</span> <small class="text-muted font-weight-normal">(Default Today)</small></label>
                                <input required type="text" autocomplete="off" value="{{ old('start_date', request('start_date', now()->format('d-m-Y'))) }}" name="start_date" class="form-control date-picker" id="start_date">
                            </div>
                        </div>
                        <div class="col-12 col-md-3">
                            <div class="form-group">
                                <label for="end_date" class="col-form-label">End Date <span class="text-danger">*</span> <small class="text-muted font-weight-normal">(Default Today)</small></label>
                                <input required type="text" autocomplete="off" value="{{ old('end_date', request('end_date', now()->format('d-m-Y'))) }}" name="end_date" class="form-control date-picker" id="end_date">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button type="submit" name="search" class="btn btn-info bg-gradient-info form-control">Search</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12" id="report-container">
        <!-- Data will be loaded here via AJAX -->
    </div>
</div>
@endsection

@section('script')
<script>
    $(function () {
        $('#search-form').submit(function (e) {
            e.preventDefault();
            let btn = $(this).find('[type=submit]');
            btn.prop('disabled', true).text('Loading...');

            $.ajax({
                url: '{{ route('report.invoice_profit_loss') }}',
                data: $(this).serialize() + '&search=1',
                success: function (response) {
                    $('#report-container').html(response);
                    btn.prop('disabled', false).text('Search');
                },
                error: function () {
                    toastr.error('Something went wrong!');
                    btn.prop('disabled', false).text('Search');
                }
            });
        });
    });
</script>
@endsection
