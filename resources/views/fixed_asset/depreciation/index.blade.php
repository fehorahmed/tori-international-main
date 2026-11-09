@extends('layouts.app')
@section('title', 'Monthly Depreciation')

@section('content')
<div class="row justify-content-center">
    <!-- Action Widget -->
    <div class="col-md-12">
        <div class="card card-outline card-primary shadow-sm">
            <div class="card-header">
                <h3 class="card-title font-weight-bold"><i class="fas fa-cogs mr-1"></i> Run Depreciation</h3>
            </div>
            <form action="{{ route('fixed-assets.depreciation.run') }}" method="POST" id="run-depreciation-form">
                @csrf
                <div class="card-body">
                    <div class="alert alert-info shadow-sm text-sm">
                        <i class="fas fa-info-circle"></i> <strong>Note:</strong> Running depreciation will automatically calculate the monthly depreciation for all active assets based on their selected methods and generate Journal Vouchers.
                    </div>
                    
                    <div class="row align-items-end mt-4">
                        <div class="col-md-8">
                            <div class="form-group mb-0">
                                <label>Select Month & Year <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                    </div>
                                    <input type="month" name="period" class="form-control font-weight-bold text-center" value="{{ $currentPeriod }}" max="{{ $currentPeriod }}" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <button type="button" class="btn btn-info bg-gradient-info font-weight-bold btn-block shadow-sm mb-2" id="preview-depreciation-btn">
                                <i class="fas fa-eye mr-1"></i> Preview 
                            </button>
                            <button type="button" class="btn btn-warning bg-gradient-warning font-weight-bold btn-block shadow-sm" id="run-depreciation-btn">
                                <i class="fas fa-play-circle mr-1"></i> Execute
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="row">
    <!-- Logs Table -->
    <div class="col-md-12 mt-4">
        <div class="card card-outline card-info shadow-sm">
            <div class="card-header">
                <h3 class="card-title font-weight-bold"><i class="fas fa-history mr-1"></i> Depreciation Logs</h3>
                <div class="card-tools">
                    <div class="input-group input-group-sm" style="width: 250px;">
                        <input type="month" id="filter_period" class="form-control float-right" placeholder="Filter by Month">
                        <div class="input-group-append">
                            <span class="input-group-text"><i class="fas fa-filter"></i> Filter</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped" id="logs-table" width="100%">
                        <thead class="bg-light">
                            <tr>
                                <th>Code</th>
                                <th>Asset Name</th>
                                <th>Period</th>
                                <th>Acc. Dep.</th>
                                <th>Val. Before</th>
                                <th>Depreciation</th>
                                <th>Val. After</th>
                                <th>Voucher</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fas fa-eye"></i> Depreciation Preview for <span id="preview-month-title"></span></h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <strong>Total Depreciation Amount: </strong> ৳ <span id="preview-total-amount">0.00</span> 
                    (Assets: <span id="preview-assets-count">0</span>)
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-sm">
                        <thead class="bg-light">
                            <tr>
                                <th>Asset Code</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th class="text-right">Acc. Dep.</th>
                                <th class="text-right">Val. Before</th>
                                <th class="text-right text-danger">Depreciation</th>
                                <th class="text-right">Val. After</th>
                            </tr>
                        </thead>
                        <tbody id="preview-table-body">
                            <!-- Preview Data -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-warning bg-gradient-warning" id="confirm-run-btn">
                    <i class="fas fa-play-circle"></i> Execute Depreciation
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script>
    $(document).ready(function () {
        let table = $('#logs-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('fixed-assets.depreciation.datatable') }}",
                data: function (d) {
                    d.period = $('#filter_period').val();
                }
            },
            columns: [
                {data: 'asset_code', name: 'asset_code'},
                {data: 'asset_name', name: 'asset_name'},
                {data: 'period', name: 'period'},
                {data: 'accumulated_depreciation', name: 'accumulated_depreciation', className: 'text-right text-warning'},
                {data: 'book_value_before', name: 'book_value_before', className: 'text-right'},
                {data: 'depreciation_amount', name: 'depreciation_amount', className: 'text-right text-danger font-weight-bold'},
                {data: 'book_value_after', name: 'book_value_after', className: 'text-right'},
                {data: 'voucher_no', name: 'voucher_no', orderable: false, searchable: false, className: 'text-center'}
            ],
            order: [[2, 'desc']]
        });

        $('#filter_period').change(function() {
            table.draw();
        });

        // Preview Functionality
        $('#preview-depreciation-btn').click(function(e) {
            e.preventDefault();
            let period = $('input[name="period"]').val();
            if(!period) {
                Swal.fire('Error', 'Please select a month first.', 'error');
                return;
            }

            $.ajax({
                url: "{{ route('fixed-assets.depreciation.preview') }}",
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    period: period
                },
                beforeSend: function() {
                    Swal.fire({
                        title: 'Calculating...',
                        text: 'Please wait while we preview the data.',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                },
                success: function(response) {
                    Swal.close();
                    if(response.status) {
                        $('#preview-month-title').text(period);
                        $('#preview-total-amount').text(Number(response.total_amount).toFixed(2));
                        $('#preview-assets-count').text(response.assets_count);

                        let tbody = '';
                        response.data.forEach(function(item) {
                            tbody += `<tr>
                                <td>${item.asset_code}</td>
                                <td>${item.asset_name}</td>
                                <td>${item.category_name}</td>
                                <td class="text-right text-warning">৳ ${Number(item.accumulated_depreciation).toFixed(2)}</td>
                                <td class="text-right">৳ ${Number(item.book_value_before).toFixed(2)}</td>
                                <td class="text-right text-danger font-weight-bold">৳ ${Number(item.depreciation_amount).toFixed(2)}</td>
                                <td class="text-right">৳ ${Number(item.book_value_after).toFixed(2)}</td>
                            </tr>`;
                        });

                        $('#preview-table-body').html(tbody);
                        $('#previewModal').modal('show');
                    } else {
                        Swal.fire('Notice', response.message, 'info');
                    }
                },
                error: function() {
                    Swal.close();
                    Swal.fire('Error', 'Something went wrong while fetching preview.', 'error');
                }
            });
        });

        // Confirm from Modal
        $('#confirm-run-btn').click(function() {
            $('#previewModal').modal('hide');
            $('#run-depreciation-form').submit();
        });

        // Direct Execution
        $('#run-depreciation-btn').click(function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Are you sure?',
                text: "You want to run depreciation for this month? This cannot be undone easily.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, run it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#run-depreciation-form').submit();
                }
            })
        });
    });
</script>
@endsection
