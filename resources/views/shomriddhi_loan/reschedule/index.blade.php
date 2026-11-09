@extends('layouts.app')
@section('title', 'Holiday Reschedule')
@section('content')
<div class="row">
    <div class="col-md-6 mx-auto">
        <div class="card card-default">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-calendar-times mr-2"></i> Reschedule / Skip Installments due to Holiday
                </h5>
            </div>
            <form action="{{ route('shomriddhi-loan.reschedule.process') }}" method="POST" onsubmit="return confirm('Are you sure? This will shift the installment schedule for all active loans falling within this holiday.');">
                @csrf
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> This tool will find all unpaid loan installments whose due dates fall within the selected holiday period, and push their due dates (and all their subsequent installments) forward by the specified number of days.
                    </div>

                    <div class="form-group">
                        <label>Select Holiday *</label>
                        <select name="holiday_id" class="form-control select2" required>
                            <option value="">-- Select Holiday --</option>
                            @foreach($holidays as $holiday)
                                <option value="{{ $holiday->id }}">
                                    {{ $holiday->title }} ({{ date('d M Y', strtotime($holiday->start_date)) }} to {{ date('d M Y', strtotime($holiday->end_date)) }})
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Holidays are managed from the HRM module.</small>
                    </div>

                    <div class="form-group mt-3">
                        <label>Shift Schedule By (Days) *</label>
                        <input type="number" name="shift_days" class="form-control font-weight-bold" value="7" min="1" required>
                        <small class="text-danger">For Weekly collections, usually this is 7 days (1 week skip).</small>
                    </div>
                </div>
                <div class="card-footer text-right">
                    <button type="submit" class="btn btn-warning btn-lg"><i class="fas fa-fast-forward"></i> Process Reschedule</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
