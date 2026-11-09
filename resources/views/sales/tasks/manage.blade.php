@extends('layouts.app')
@section('title', 'Manage Tasks - ' . ($assignment->employee->name ?? 'User'))

@section('style')
    <style>
        .task-card {
            border: none;
            border-radius: 15px;
            transition: all 0.3s ease;
            background: #fff;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        .task-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .task-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-right: 15px;
        }
        .icon-sales { background: #e0f2fe; color: #0ea5e9; }
        .icon-visit { background: #f0fdf4; color: #22c55e; }
        .icon-order { background: #fef2f2; color: #ef4444; }
        .icon-payment { background: #faf5ff; color: #a855f7; }
        
        .input-premium {
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 8px 15px;
            font-weight: 600;
            height: 40px;
            transition: all 0.3s ease;
        }
        .input-group-text {
            height: 40px;
            border: 2px solid #e2e8f0;
            font-weight: 600;
        }
        .input-premium:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }
        .unit-badge {
            background: #f1f5f9;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            color: #64748b;
        }
    </style>
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="d-flex align-items-center mb-4">
                <a href="{{ route('tasks.index', $role) }}" class="btn btn-equal rounded-circle mr-3">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h2 class="font-weight-bold mb-0">Manage Employee Goals</h2>
                    <p class="text-muted mb-0">Setting specific targets for <strong>{{ $assignment->employee->name }}</strong></p>
                </div>
            </div>

            <form action="{{ route('tasks.update-manage', ['role' => $role, 'id' => $assignment->id]) }}" method="POST">
                @csrf
                <div class="row" id="activeTasksContainer">
                    {{-- Sales Target (Fixed & Readonly) --}}
                    @foreach($taskTypes->where('slug', 'sales_target') as $type)
                        <div class="col-md-12 mb-4">
                            <div class="task-card p-4 border-left-info" style="border-left: 5px solid #0ea5e9 !important; background: #f8fafc;">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <div class="task-icon icon-sales">
                                            <i class="fas fa-chart-line"></i>
                                        </div>
                                        <div>
                                            <h5 class="font-weight-bold mb-0">{{ $type->name }} (Budgeted)</h5>
                                            <small class="text-muted">This value is managed via the Distribution module</small>
                                        </div>
                                    </div>
                                    <div style="width: 250px;">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-light border-right-0" style="border-radius: 10px 0 0 10px;">৳</span>
                                            </div>
                                            <input type="text" 
                                                   class="form-control input-premium bg-light" 
                                                   value="{{ number_format($existingItems[$type->id] ?? 0, 2) }}"
                                                   readonly title="Controlled by Distribution Module">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    {{-- Existing Active Tasks (Other than sales_target) --}}
                    @foreach($taskTypes->where('slug', '!=', 'sales_target') as $type)
                        @if(isset($existingItems[$type->id]) && $existingItems[$type->id] > 0)
                            @php
                                $iconClass = 'icon-visit';
                                $icon = 'fa-map-marker-alt';
                                if(str_contains($type->slug, 'order')) { $iconClass = 'icon-order'; $icon = 'fa-shopping-cart'; }
                                elseif(str_contains($type->slug, 'payment')) { $iconClass = 'icon-payment'; $icon = 'fa-money-bill-wave'; }
                            @endphp
                            <div class="col-md-6 task-item-row" data-type-id="{{ $type->id }}">
                                <div class="task-card p-4">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <div class="d-flex align-items-center">
                                            <div class="task-icon {{ $iconClass }}">
                                                <i class="fas {{ $icon }}"></i>
                                            </div>
                                            <div>
                                                <h5 class="font-weight-bold mb-0">{{ $type->name }}</h5>
                                                <span class="unit-badge">{{ $type->unit }}</span>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-link text-danger remove-task"><i class="fas fa-times"></i></button>
                                    </div>
                                    <div class="form-group mb-0">
                                        <div class="input-group">
                                            @if($type->unit == 'Amount')
                                                <div class="input-group-prepend"><span class="input-group-text bg-white" style="border-radius: 10px 0 0 10px; border: 2px solid #e2e8f0; border-right: none;">৳</span></div>
                                            @endif
                                            <input type="number" step="any" name="targets[{{ $type->id }}]" class="form-control input-premium" value="{{ $existingItems[$type->id] }}" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>

                {{-- Add More Button --}}
                <div class="text-center mb-5">
                    <div class="dropdown">
                        <button class="btn btn-outline-primary btn-lg rounded-pill px-5" type="button" id="dropdownAddMore" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-plus-circle mr-2"></i> Add More Goals
                        </button>
                        <div class="dropdown-menu dropdown-menu-center shadow-lg border-0" aria-labelledby="dropdownAddMore" style="border-radius: 15px; min-width: 250px;">
                            <h6 class="dropdown-header">Select Goal Type</h6>
                            @foreach($taskTypes->where('slug', '!=', 'sales_target') as $type)
                                <a class="dropdown-item py-3 add-task-btn" href="javascript:void(0)" 
                                   data-id="{{ $type->id }}" 
                                   data-name="{{ $type->name }}" 
                                   data-unit="{{ $type->unit }}"
                                   data-slug="{{ $type->slug }}">
                                    <i class="fas {{ str_contains($type->slug, 'visit') ? 'fa-map-marker-alt text-success' : (str_contains($type->slug, 'order') ? 'fa-shopping-cart text-danger' : 'fa-money-bill-wave text-purple') }} mr-3"></i>
                                    {{ $type->name }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="card card-default shadow-sm" style="border-radius: 15px; position: sticky; bottom: 20px; z-index: 100;">
                    <div class="card-body p-4 text-right">
                        <a href="{{ route('tasks.index', $role) }}" class="btn btn-light px-5 py-2 mr-2" style="border-radius: 10px;">Cancel</a>
                        <button type="submit" class="btn btn-primary px-5 py-2 shadow" style="border-radius: 10px; background: #2563eb;">
                            <i class="fas fa-save mr-2"></i> Update All Tasks
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Template for Dynamic Addition --}}
    <template id="taskTemplate">
        <div class="col-md-6 task-item-row">
            <div class="task-card p-4 animate__animated animate__fadeInUp">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="d-flex align-items-center">
                        <div class="task-icon">
                            <i class="fas"></i>
                        </div>
                        <div>
                            <h5 class="font-weight-bold mb-0">Task Name</h5>
                            <span class="unit-badge">Unit</span>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-link text-danger remove-task"><i class="fas fa-times"></i></button>
                </div>
                <div class="form-group mb-0">
                    <div class="input-group">
                        <div class="input-group-prepend currency-symbol d-none"><span class="input-group-text bg-white border-right-0" style="border-radius: 10px 0 0 10px;">৳</span></div>
                        <input type="number" step="any" name="targets[]" class="form-control input-premium" value="0" required>
                    </div>
                </div>
            </div>
        </div>
    </template>
@endsection

@section('script')
    <script>
        $(document).on('click', '.add-task-btn', function() {
            const id = $(this).data('id');
            const name = $(this).data('name');
            const unit = $(this).data('unit');
            const slug = $(this).data('slug');

            // Check if already exists
            if ($(`.task-item-row[data-type-id="${id}"]`).length > 0) {
                alert('This goal type is already added.');
                return;
            }

            const template = document.getElementById('taskTemplate').content.cloneNode(true);
            const row = $(template).find('.task-item-row');
            
            row.attr('data-type-id', id);
            row.find('h5').text(name);
            row.find('.unit-badge').text(unit);
            row.find('input').attr('name', `targets[${id}]`);

            // Icon logic
            let iconClass = 'icon-visit';
            let icon = 'fa-map-marker-alt';
            if(slug.includes('order')) { iconClass = 'icon-order'; icon = 'fa-shopping-cart'; }
            else if(slug.includes('payment')) { iconClass = 'icon-payment'; icon = 'fa-money-bill-wave'; }
            
            row.find('.task-icon').addClass(iconClass);
            row.find('.task-icon i').addClass(icon);

            if (unit === 'Amount') {
                row.find('.currency-symbol').removeClass('d-none');
            }

            $('#activeTasksContainer').append(row);
        });

        $(document).on('click', '.remove-task', function() {
            if(confirm('Are you sure you want to remove this goal from this assignment?')) {
                $(this).closest('.task-item-row').remove();
            }
        });
    </script>
@endsection
