<div class="modal fade" id="historyModal" tabindex="-1" role="dialog" aria-labelledby="historyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="historyModalLabel"><i class="fa fa-history"></i> Audit History</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Employee:</strong> {{ $salary_process_item->employee->name ?? 'N/A' }}
                    </div>
                    <div class="col-md-6 text-right">
                        <strong>Month:</strong> {{ numberToMonthName($salary_process_item->month) }}, {{ $salary_process_item->year }}
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="bg-light">
                            <tr>
                                <th>Date & Time</th>
                                <th>User</th>
                                <th>Action</th>
                                <th>Changes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                                <tr>
                                    <td style="white-space: nowrap;">
                                        {{ $log->created_at->format('d M, Y') }}<br>
                                        <small class="text-muted">{{ $log->created_at->format('h:i A') }}</small>
                                    </td>
                                    <td>{{ $log->user->name ?? 'System' }}</td>
                                    <td>
                                        @if($log->event == 'created')
                                            <span class="badge badge-success">Created</span>
                                        @elseif($log->event == 'updated')
                                            <span class="badge badge-info">Updated</span>
                                        @else
                                            <span class="badge badge-danger">{{ ucfirst($log->event) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($log->event == 'updated' && $log->old_values && $log->new_values)
                                            <ul class="mb-0 pl-3">
                                                @foreach($log->new_values as $key => $newValue)
                                                    @if(isset($log->old_values[$key]) && $log->old_values[$key] != $newValue)
                                                        <li>
                                                            <strong>{{ ucwords(str_replace('_', ' ', $key)) }}:</strong> 
                                                            <span class="text-danger"><del>{{ $log->old_values[$key] }}</del></span> 
                                                            <i class="fa fa-arrow-right mx-1 text-muted"></i> 
                                                            <span class="text-success font-weight-bold">{{ $newValue }}</span>
                                                        </li>
                                                    @endif
                                                @endforeach
                                            </ul>
                                        @elseif($log->event == 'created')
                                            <span class="text-muted">Initial Record Creation</span>
                                        @else
                                            <span class="text-muted">No specific changes tracked</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-danger">No audit history found for this record.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
