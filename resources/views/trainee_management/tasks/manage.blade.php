@extends('layouts.app')
@section('title','Manage Trainee Tasks')
@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card card-outline card-success shadow-sm">
                <div class="card-header bg-white">
                    <h3 class="card-title font-weight-bold">
                        <i class="fas fa-tasks mr-2 text-success"></i> Manage Tasks: {{ $assignment->supplier->name ?? 'Trainee' }}
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-info px-3 py-2">{{ \Carbon\Carbon::parse($assignment->date)->format('F, Y') }}</span>
                    </div>
                </div>
                
                <form action="{{ route('trainee.tasks.update_tasks', ['id' => $assignment->id]) }}" method="post">
                    @csrf
                    <div class="card-body">
                        <div class="alert alert-light border mb-4">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <img src="{{ $assignment->supplier->image ? asset($assignment->supplier->image) : asset('img/avatar.png') }}" 
                                         class="img-circle border" width="60" height="60" style="object-fit: cover;">
                                </div>
                                <div class="col">
                                    <h5 class="mb-1 font-weight-bold">{{ $assignment->supplier->name }}</h5>
                                    <p class="text-muted mb-0"><i class="fas fa-phone-alt mr-1"></i> {{ $assignment->supplier->mobile_no }}</p>
                                </div>
                            </div>
                        </div>

                        <h6 class="text-uppercase text-muted font-weight-bold small border-bottom pb-2 mb-3">Assign Targets</h6>
                        
                        <div class="row">
                            @foreach($taskTypes as $type)
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label class="d-flex justify-content-between">
                                            <span>{{ $type->name }}</span>
                                            <small class="text-info">{{ $type->unit }}</small>
                                        </label>
                                        <div class="input-group">
                                            @if($type->unit == 'Amount')
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">৳</span>
                                                </div>
                                            @endif
                                            <input type="number" step="any" name="targets[{{ $type->id }}]" 
                                                   class="form-control font-weight-bold" 
                                                   value="{{ $existingItems[$type->id] ?? 0 }}" 
                                                   min="0">
                                            @if($type->unit != 'Amount' && $type->unit != 'Count')
                                                <div class="input-group-append">
                                                    <span class="input-group-text">{{ $type->unit }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="card-footer bg-light text-center border-top-0 py-3">
                        <button type="submit" class="btn btn-success btn-lg px-5 shadow-sm">
                            <i class="fas fa-save mr-1"></i> Update All Targets
                        </button>
                        <a href="{{ route('trainee.tasks.index') }}" class="btn btn-default btn-lg px-5 border ml-2">Back</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
