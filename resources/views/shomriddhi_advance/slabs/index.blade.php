@extends('layouts.app')

@section('title', $pageTitle)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-4">
            <div class="card card-default">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-plus mr-1"></i> Add New Slab</h5>
                </div>
                <form action="{{ route('shomriddhi-advance.slabs.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label>Min Amount <span class="text-danger">*</span></label>
                            <input type="number" name="min_amount" class="form-control" step="0.01" min="0" required placeholder="e.g. 1000">
                        </div>
                        <div class="form-group">
                            <label>Max Amount <small class="text-muted">(Leave empty for no limit)</small></label>
                            <input type="number" name="max_amount" class="form-control" step="0.01" min="0" placeholder="e.g. 99999">
                        </div>
                        <div class="form-group">
                            <label>Interest Rate (%) <span class="text-danger">*</span></label>
                            <input type="number" name="interest_rate" class="form-control" step="0.01" min="0" max="100" required placeholder="e.g. 3.00">
                        </div>
                    </div>
                    <div class="card-footer text-right">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Slab</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card card-default">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-list mr-1"></i> Current Interest Slabs</h5>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            {{ session('success') }}
                        </div>
                    @endif
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="bg-light">
                                <tr>
                                    <th width="5%">#</th>
                                    <th>Min Amount (৳)</th>
                                    <th>Max Amount (৳)</th>
                                    <th>Interest Rate (%)</th>
                                    <th width="15%" class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($slabs as $index => $slab)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td class="font-weight-bold">{{ number_format($slab->min_amount, 2) }}</td>
                                    <td>
                                        @if($slab->max_amount)
                                            {{ number_format($slab->max_amount, 2) }}
                                        @else
                                            <span class="badge badge-success">Unlimited / Infinity</span>
                                        @endif
                                    </td>
                                    <td><span class="badge badge-info" style="font-size: 14px;">{{ number_format($slab->interest_rate, 2) }}%</span></td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#editModal{{ $slab->id }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form action="{{ route('shomriddhi-advance.slabs.destroy', $slab->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this slab?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                        </form>

                                        <!-- Edit Modal -->
                                        <div class="modal fade text-left" id="editModal{{ $slab->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                            <div class="modal-dialog modal-md" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-gradient-success text-white">
                                                        <h5 class="modal-title"><i class="fas fa-edit mr-2"></i> Edit Interest Slab</h5>
                                                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <form action="{{ route('shomriddhi-advance.slabs.update', $slab->id) }}" method="POST">
                                                        @csrf
                                                        <div class="modal-body">
                                                            <div class="form-group">
                                                                <label>Min Amount <span class="text-danger">*</span></label>
                                                                <input type="number" name="min_amount" class="form-control" step="0.01" min="0" required value="{{ $slab->min_amount }}">
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Max Amount <small class="text-muted">(Leave empty for no limit)</small></label>
                                                                <input type="number" name="max_amount" class="form-control" step="0.01" min="0" value="{{ $slab->max_amount }}">
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Interest Rate (%) <span class="text-danger">*</span></label>
                                                                <input type="number" name="interest_rate" class="form-control" step="0.01" min="0" max="100" required value="{{ $slab->interest_rate }}">
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Update Changes</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No interest slabs configured yet.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="alert alert-info mt-3 shadow-sm border-0">
                <h5><i class="icon fas fa-info"></i> How it works:</h5>
                <p class="mb-0 text-sm">
                    The system checks the customer's <strong>Principal Balance</strong> every night at 12:00 AM. 
                    It applies the <strong>Interest Rate (%)</strong> from the slab where the balance falls between the <strong>Min Amount</strong> and <strong>Max Amount</strong>. 
                    For the highest tier, leave the Max Amount empty.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
