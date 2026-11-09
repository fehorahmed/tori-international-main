@extends('layouts.app')
@section('title', str_replace('_', ' ', $context['child_label']) . ' Target Distribution')

@section('style')
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            --glass-bg: rgba(255, 255, 255, 0.9);
            --glass-border: rgba(255, 255, 255, 0.2);
        }

        .premium-header {
            background: var(--primary-gradient);
            border-radius: 20px;
            padding: 40px;
            color: white;
            box-shadow: 0 10px 30px rgba(99, 102, 241, 0.2);
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
        }

        .premium-header::after {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        .sr-card {
            background: white;
            border-radius: 18px;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid #f1f5f9;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sr-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            border-color: #6366f1;
        }

        .avatar-circle {
            width: 50px;
            height: 50px;
            background: #f3f4f6;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #6366f1;
            font-size: 1.2rem;
            border: 2px solid #fff;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }

        .sticky-summary {
            position: sticky;
            top: 100px;
            z-index: 10;
        }

        .summary-card {
            background: #fff;
            border-radius: 24px;
            padding: 30px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 20px 40px rgba(0,0,0,0.05);
        }

        .stat-label {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748b;
            font-weight: 600;
        }

        .input-premium {
            border-radius: 12px;
            padding: 12px 15px;
            border: 2px solid #f1f5f9;
            font-weight: 600;
            transition: all 0.2s;
        }

        .input-premium:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }

        .btn-equal {
            background: #f8fafc;
            border: 2px dashed #cbd5e1;
            color: #64748b;
            transition: all 0.2s;
        }

        .btn-equal:hover {
            background: #f1f5f9;
            border-color: #6366f1;
            color: #6366f1;
        }

        .progress-lux {
            height: 15px;
            border-radius: 30px;
            background: #f1f5f9;
            overflow: hidden;
        }
    </style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Premium Header -->
    <div class="premium-header">
        <div class="row align-items-center">
            <div class="col-md-7">
                <h1 class="display-5 font-weight-bold mb-2">{{ str_replace('_', ' ', $context['child_label']) }} Distribution</h1>
                <p class="lead opacity-8">Cascading sales targets for <strong>{{ \Carbon\Carbon::parse($formattedMonth)->format('F, Y') }}</strong></p>
            </div>
            <div class="col-md-5 text-right">
                <div class="d-flex align-items-center justify-content-end">
                    <div class="form-group mb-0 mr-3 text-left">
                        <label class="text-xs text-uppercase font-weight-bold opacity-8 mb-1 d-block">Switch Month</label>
                        <input type="month" id="monthSwitcher" class="form-control form-control-sm border-0 rounded-pill px-3" 
                               value="{{ $formattedMonth }}"
                               style="background: rgba(255,255,255,0.2); color: white;">
                    </div>
                    <div class="glass-box p-3 rounded-pill d-inline-block border">
                        <i class="fas fa-chart-line mr-2"></i> {{ $role == 'area_manager' ? 'Admin Level' : 'Hierarchy Active' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('tasks.store-distribute', $role) }}" method="POST" id="distributeForm">
        @csrf
        <input type="hidden" name="month" value="{{ $formattedMonth }}">
        <input type="hidden" name="area_manager_id" value="{{ $selectedAreaManagerId }}">
        <input type="hidden" name="branch_manager_id" value="{{ $selectedBranchManagerId }}">

        <!-- Dynamic Filters -->
        @if($role == 'branch_manager' || $role == 'sr')
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card shadow-sm border-0 rounded-lg">
                    <div class="card-body bg-light rounded-lg px-4 py-3">
                        <div class="row align-items-center">
                            @if($role == 'branch_manager')
                                <div class="col-md-6">
                                    <h6 class="font-weight-bold text-dark mb-2">Select Area Manager</h6>
                                    <select id="areaManagerFilter" class="form-control select2 custom-select border-0 shadow-sm" {{ auth()->user()->role == 'Area_Manager' ? 'disabled' : '' }}>
                                        <option value="">-- Choose Area Manager --</option>
                                        @foreach($areaManagers as $am)
                                            <option value="{{ $am->id }}" {{ $selectedAreaManagerId == $am->id ? 'selected' : '' }}>
                                                {{ $am->name }} ({{ $am->id_no }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @if(auth()->user()->role == 'Area_Manager')
                                        <small class="text-success mt-1 d-block"><i class="fas fa-check-circle"></i> Auto-locked to your profile</small>
                                    @endif
                                </div>
                            @elseif($role == 'sr')
                                <div class="col-md-6">
                                    <h6 class="font-weight-bold text-dark mb-2">Select Branch Manager</h6>
                                    <select id="branchManagerFilter" class="form-control select2 custom-select border-0 shadow-sm" {{ auth()->user()->role == 'Branch_Manager' ? 'disabled' : '' }}>
                                        <option value="">-- Choose Branch Manager --</option>
                                        @foreach($branchManagers as $bm)
                                            <option value="{{ $bm->id }}" {{ $selectedBranchManagerId == $bm->id ? 'selected' : '' }}>
                                                {{ $bm->name }} ({{ $bm->id_no }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @if(auth()->user()->role == 'Branch_Manager')
                                        <small class="text-success mt-1 d-block"><i class="fas fa-check-circle"></i> Auto-locked to your profile</small>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <div class="row">
            <!-- Child List Section -->
            <div class="col-lg-8">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="font-weight-bold text-dark mb-0">Select {{ str_replace('_', ' ', $context['child_label']) }}s & Assign Goals</h4>
                    <button type="button" class="btn btn-equal rounded-pill px-4" id="btnEqualSplit">
                        <i class="fas fa-divide mr-2"></i> Equal Split
                    </button>
                </div>

                @forelse($children as $child)
                    <div class="sr-card">
                        <div class="row align-items-center">
                            <div class="col-md-5">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle mr-3">
                                        {{ substr($child->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <h6 class="font-weight-bold mb-1 text-dark">{{ $child->name }}</h6>
                                        <div class="badge badge-soft-info text-xs">{{ $child->mobile_no }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-7">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-transparent border-0 text-muted font-weight-bold">৳</span>
                                    </div>
                                    <input type="number" name="targets[{{ $child->id }}]" 
                                           class="form-control input-premium sr-target-input" 
                                           value="{{ $child->target_value }}" 
                                           step="0.01" min="0" 
                                           placeholder="Enter target amount">
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="alert alert-warning">No {{ str_replace('_', ' ', $context['child_label']) }}s found for this selection.</div>
                @endforelse
            </div>

            <!-- Summary Panel -->
            <div class="col-lg-4">
                <div class="sticky-summary">
                    <div class="summary-card">
                        <h5 class="font-weight-bold mb-4 text-dark border-bottom pb-3">Financial Allocation</h5>
                        
                        <div class="mb-5">
                            <span class="stat-label">{{ $role == 'area_manager' ? 'Total Enterprise Target' : 'Your Assigned Limit (' . str_replace('_', ' ', $context['parent_role']) . ')' }}</span>
                            <div class="h2 font-weight-bold text-dark">
                                @if($parentTargetAmount == -1)
                                    <span class="badge badge-soft-success">Flexible / No Limit</span>
                                @else
                                    ৳{{ number_format($parentTargetAmount, 2) }}
                                @endif
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="stat-label">Distributed</span>
                                <span class="font-weight-bold text-primary" id="allocatedAmount">৳0.00</span>
                            </div>
                            <div class="progress-lux mb-2">
                                <div class="progress-bar bg-primary" id="distributionProgress" 
                                     role="progressbar" style="width: 0%; transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1);"></div>
                            </div>
                            <div class="text-right">
                                <span id="percentLabel" class="badge badge-primary rounded-pill">0%</span>
                            </div>
                        </div>

                        <div id="statusBox" class="p-4 rounded-xl mb-4 text-center border">
                            <span class="stat-label d-block mb-1">Balance Available</span>
                            <h3 class="font-weight-bold mb-0" id="remainingAmount">৳0.00</h3>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block py-3 rounded-pill font-weight-bold shadow-lg mt-4" style="font-size: 1.1rem">
                            <i class="fas fa-check-circle mr-2"></i> Confirm & Release Targets
                        </button>
                        
                        <p class="text-center text-muted mt-3 small">
                            <i class="fas fa-lock mr-1"></i> Securely distributed via Unified Task System
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@section('script')
<script>
    $(function() {
        const totalTarget = {{ $parentTargetAmount }};
        const childCount = {{ count($children) }};
        
        function calculateDistribution() {
            let allocated = 0;
            $('.sr-target-input').each(function() {
                allocated += parseFloat($(this).val()) || 0;
            });

            const percentage = totalTarget > 0 ? (allocated / totalTarget) * 100 : 0;
            const remaining = totalTarget - allocated;

            $('#allocatedAmount').text('৳' + allocated.toLocaleString(undefined, {minimumFractionDigits: 2}));
            
            if (totalTarget != -1) {
                $('#remainingAmount').text('৳' + Math.abs(remaining).toLocaleString(undefined, {minimumFractionDigits: 2}));
                $('#percentLabel').text(Math.round(percentage) + '%').show();
                $('#distributionProgress').css('width', Math.min(percentage, 100) + '%');
                
                if (percentage > 100.01) {
                    $('#distributionProgress').removeClass('bg-primary bg-success').addClass('bg-danger');
                    $('#statusBox').css({'background-color': '#fff1f2', 'border-color': '#fecaca', 'color': '#e11d48'});
                    $('#statusBox').find('.stat-label').text('Over Allocated');
                } else if (Math.abs(percentage - 100) < 0.01) {
                    $('#distributionProgress').removeClass('bg-primary bg-danger').addClass('bg-success');
                    $('#statusBox').css({'background-color': '#f0fdf4', 'border-color': '#bbf7d0', 'color': '#16a34a'});
                    $('#statusBox').find('.stat-label').text('Perfectly Balanced');
                } else {
                    $('#distributionProgress').removeClass('bg-danger bg-success').addClass('bg-primary');
                    $('#statusBox').css({'background-color': '#f0f9ff', 'border-color': '#bae6fd', 'color': '#0284c7'});
                    $('#statusBox').find('.stat-label').text('Balance Available');
                }
            } else {
                // Admin mode - Flexible (totalTarget == -1)
                $('#remainingAmount').text('৳' + allocated.toLocaleString(undefined, {minimumFractionDigits: 2}));
                $('#statusBox').find('.stat-label').text('Total Allocated');
                $('#percentLabel').hide();
                $('#distributionProgress').css('width', '100%').removeClass('bg-danger bg-success').addClass('bg-primary');
                $('#statusBox').css({'background-color': '#f0f9ff', 'border-color': '#bae6fd', 'color': '#0284c7'});
            }
        }

        $('#monthSwitcher').on('change', function() {
            let selectedMonth = $(this).val();
            let url = new URL(window.location.href);
            url.searchParams.set('month', selectedMonth);
            window.location.href = url.toString();
        });

        $('#areaManagerFilter').on('change', function() {
            let amId = $(this).val();
            let url = new URL(window.location.href);
            if (amId) {
                url.searchParams.set('area_manager_id', amId);
            } else {
                url.searchParams.delete('area_manager_id');
            }
            window.location.href = url.toString();
        });

        $('#branchManagerFilter').on('change', function() {
            let bmId = $(this).val();
            let url = new URL(window.location.href);
            if (bmId) {
                url.searchParams.set('branch_manager_id', bmId);
            } else {
                url.searchParams.delete('branch_manager_id');
            }
            window.location.href = url.toString();
        });

        $('.sr-target-input').on('input', calculateDistribution);
        
        $('#btnEqualSplit').on('click', function() {
            if (totalTarget <= 0 || childCount === 0) return;
            const equalAmount = (totalTarget / childCount).toFixed(2);
            $('.sr-target-input').val(equalAmount);
            calculateDistribution();
        });

        $('#distributeForm').on('submit', function(e) {
            let allocated = 0;
            $('.sr-target-input').each(function() {
                allocated += parseFloat($(this).val()) || 0;
            });

            // If not admin, enforce strict limit
            @if($role != 'area_manager')
            if (Math.abs(allocated - totalTarget) > 0.01) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Imbalanced Allocation',
                    text: 'The total distributed amount must equal your total target.',
                    confirmButtonColor: '#6366f1'
                });
            }
            @endif
        });

        calculateDistribution();
    });
</script>
@endsection
