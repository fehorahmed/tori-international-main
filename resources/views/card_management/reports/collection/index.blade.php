@extends('layouts.app')
@section('title', $pageTitle)

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">{{ $pageTitle }}</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('card-management.reports.collection') }}" method="GET" class="mb-4">
                        <div class="row align-items-end">
                            <div class="col-md-3">
                                <div class="form-group mb-0">
                                    <label>Start Date</label>
                                    <input type="text" name="start_date" class="form-control date-picker" value="{{ request('start_date') ?? date('d-m-Y') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-0">
                                    <label>End Date</label>
                                    <input type="text" name="end_date" class="form-control date-picker" value="{{ request('end_date') ?? date('d-m-Y') }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-search"></i> Filter</button>
                            </div>
                            <div class="col-md-2">
                                <a href="{{ route('card-management.reports.collection') }}" class="btn btn-secondary btn-block"><i class="fa fa-times"></i> Clear</a>
                            </div>
                        </div>
                    </form>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="info-box bg-gradient-info">
                                <span class="info-box-icon"><i class="fa fa-money-bill-wave"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Collection Amount</span>
                                    <span class="info-box-number" style="font-size: 1.5rem;">{{ number_format($totalAmount, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped datatable" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th>SL</th>
                                    <th>Collection Date</th>
                                    <th>Card No</th>
                                    <th>Holder Name</th>
                                    <th>Mobile No</th>
                                    <th>Collected By</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cards as $key => $card)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ \Carbon\Carbon::parse($card->updated_at)->format('d-M-Y h:i A') }}</td>
                                        <td>{{ $card->card_number }}</td>
                                        <td>{{ $card->cardable ? $card->cardable->name : '-' }}</td>
                                        <td>{{ $card->cardable ? $card->cardable->mobile_no : '-' }}</td>
                                        <td>{{ $card->creator ? $card->creator->name : '-' }}</td>
                                        <td class="text-right">{{ number_format($card->amount, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="6" class="text-right">Total:</th>
                                    <th class="text-right">{{ number_format($totalAmount, 2) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(function () {
            $('.datatable').DataTable({
                "pagingType": "full_numbers",
                "lengthMenu": [[10, 25, 50, -1],[10, 25, 50, "All"]],
                "dom": 'lBfrtip',
                "buttons": datatableButtons(),
                "responsive": true, "autoWidth": false,
            });
        });
    </script>
@endsection
