@extends('layouts.app')
@section('title', 'Loan Payable Summary')
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-outline card-danger">
                <div class="card-header">
                    <h3 class="card-title">Filter Loan Payable Summary (Loans you TOOK)</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('company_loan.reports.payable_summary') }}" method="GET">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Start Date</label>
                                    <input type="text" name="start_date" class="form-control date-picker" value="{{ request('start_date', date('01-m-Y')) }}" autocomplete="off">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>End Date</label>
                                    <input type="text" name="end_date" class="form-control date-picker" value="{{ request('end_date', date('t-m-Y')) }}" autocomplete="off">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <button type="submit" class="btn btn-danger btn-block bg-gradient-danger">
                                        <i class="fa fa-search"></i> Search Payable Loans
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            @if(count($summary) > 0)
                <div class="card card-outline card-danger">
                    <div class="card-header">
                        <h3 class="card-title">Payable Loans Summary ({{ request('start_date') }} to {{ request('end_date') }})</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-striped">
                            <thead class="bg-light">
                                <tr>
                                    <th>Loan ID</th>
                                    <th>Lender (Owed To)</th>
                                    <th>Amount (Principal)</th>
                                    <th>Total Interest</th>
                                    <th>Total Paid</th>
                                    <th>Remaining to Pay</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($summary as $loan)
                                    @php 
                                        $paidAmount = $loan->installments->where('status', 2)->sum('total_amount');
                                        $totalLoanAmount = $loan->amount + $loan->installments->sum('interest_amount');
                                        $balance = $totalLoanAmount - $paidAmount;
                                    @endphp
                                    <tr>
                                        <td>{{ $loan->id }}</td>
                                        <td>{{ $loan->lender->name }}</td>
                                        <td class="text-right">{{ number_format($loan->amount, 2) }}</td>
                                        <td class="text-right">{{ number_format($loan->installments->sum('interest_amount'), 2) }}</td>
                                        <td class="text-right text-success font-weight-bold">{{ number_format($paidAmount, 2) }}</td>
                                        <td class="text-right text-danger font-weight-bold">{{ number_format($balance, 2) }}</td>
                                        <td>
                                            @if($loan->status == 1) <span class="badge badge-warning">Pending</span>
                                            @elseif($loan->status == 2) <span class="badge badge-success">Approved</span>
                                            @elseif($loan->status == 3) <span class="badge badge-info">Closed</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @elseif(request('start_date'))
                <div class="alert alert-warning">No payable loans found for the selected criteria.</div>
            @endif
        </div>
    </div>
@endsection
@section('script')
    <script>
        $(function () {
            $('.date-picker').datepicker({
                autoclose: true,
                format: 'dd-mm-yyyy',
                todayHighlight: true
            });
        });
    </script>
@endsection
