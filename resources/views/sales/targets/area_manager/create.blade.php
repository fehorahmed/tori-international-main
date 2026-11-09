@extends('layouts.app')
@section('title', isset($type) ? 'Edit Area Manager Targets' : 'Set Area Manager Targets')
@section('content')
    <div class="row">
        <div class="col-12">
            <form action="{{ route('sales_targets.area_manager.store') }}" method="POST">
                @csrf
                <input type="hidden" name="target_id" value="{{ $target_id ?? '' }}">
                <div class="card card-default">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="month">Select Month</label>
                                    <input type="month" name="month" id="month" class="form-control" value="{{ $month ?? date('Y-m') }}" required {{ isset($type) ? 'readonly' : '' }}>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="target_type">Target Type</label>
                                    <select name="target_type" id="target_type" class="form-control select2" required {{ isset($type) ? 'disabled' : '' }}>
                                        <option value="Sales" {{ (isset($type) && $type == 'Sales') ? 'selected' : '' }}>Sales</option>
                                        {{-- <option value="Microfinance" {{ (isset($type) && $type == 'Microfinance') ? 'selected' : '' }}>Microfinance</option> --}}
                                        {{-- <option value="Collection" {{ (isset($type) && $type == 'Collection') ? 'selected' : '' }}>Collection</option> --}}
                                    </select>
                                    @if(isset($type))
                                        <input type="hidden" name="target_type" value="{{ $type }}">
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                <tr>
                                    <th>S/L</th>
                                    <th>Area Manager Name</th>
                                    <th>Mobile No.</th>
                                    <th>Target Amount</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($areaManagers as $am)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $am->name }}</td>
                                        <td>{{ $am->mobile_no }}</td>
                                        <td>
                                            <input type="number" step="0.01" name="targets[{{ $am->id }}][amount]" 
                                                   class="form-control" 
                                                   value="{{ $am->target->amount ?? '' }}" 
                                                   placeholder="0.00" required>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer text-right">
                        <a href="{{ route('sales_targets.area_manager.index') }}" class="btn btn-default">Cancel</a>
                        <button type="submit" class="btn btn-info bg-gradient-info">Save All Targets</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
