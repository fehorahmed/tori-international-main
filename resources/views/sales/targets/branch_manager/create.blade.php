@extends('layouts.app')
@section('title', isset($type) ? 'Edit Branch Manager Targets' : 'Set Branch Manager Targets')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-outline card-info" id="my-target-box" style="display: none;">
                <div class="card-header">
                    <h3 class="card-title">My Assigned Target Reference</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="info-box bg-light border">
                                <div class="info-box-content">
                                    <span class="info-box-text">My Target</span>
                                    <span class="info-box-number text-primary" id="my-target-val" style="font-size: 1.4rem">0.00 TK</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box bg-light border">
                                <div class="info-box-content">
                                    <span class="info-box-text">Remaining to Assign</span>
                                    <span class="info-box-number text-danger" id="remaining-val" style="font-size: 1.4rem">0.00 TK</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <form action="{{ route('sales_targets.branch_manager.store') }}" method="POST">
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
                                    <th>Branch Name</th>
                                    <th>Branch Manager</th>
                                    <th>Target Amount</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($branches as $branch)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $branch->name }}</td>
                                        <td>{{ $branch->branchManager->name ?? 'N/A' }}</td>
                                        <td>
                                            @if($branch->branch_manager_id)
                                                <input type="number" step="0.01" name="targets[{{ $branch->id }}][amount]" 
                                                       class="form-control branch-amount" 
                                                       value="{{ $branch->target->amount ?? '' }}" 
                                                       placeholder="0.00" required>
                                            @else
                                                <span class="text-danger">Manager Not Assigned</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="3" class="text-right">Total Assigned:</th>
                                        <th id="total-assigned">0.00 TK</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer text-right">
                        <a href="{{ route('sales_targets.branch_manager.index') }}" class="btn btn-default">Cancel</a>
                        <button type="submit" class="btn btn-info bg-gradient-info">Save All Branch Targets</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(function () {
            function updateCalculations() {
                let month = $('#month').val();
                let type = $('#target_type').val();
                
                // Fetch AM target
                $.ajax({
                    url: "{{ route('sales_targets.get_am_target') }}",
                    data: { month: month, type: type }
                }).done(function(target) {
                    if (target) {
                        $('#my-target-box').show();
                        let amAmount = parseFloat(target.amount) || 0;
                        $('#my-target-val').text(amAmount.toLocaleString() + ' TK');
                        
                        let totalBranch = 0;
                        $('.branch-amount').each(function() {
                            totalBranch += parseFloat($(this).val()) || 0;
                        });
                        
                        $('#total-assigned').text(totalBranch.toLocaleString() + ' TK');
                        let remaining = amAmount - totalBranch;
                        $('#remaining-val').text(remaining.toLocaleString() + ' TK');
                        
                        if (remaining < 0) {
                            $('#remaining-val').removeClass('text-success').addClass('text-danger');
                        } else {
                            $('#remaining-val').removeClass('text-danger').addClass('text-success');
                        }
                    } else {
                        $('#my-target-box').hide();
                    }
                });
            }

            $('#month, #target_type').change(updateCalculations);
            $(document).on('input', '.branch-amount', updateCalculations);
            
            // Initial run
            updateCalculations();
        });
    </script>
@endsection
