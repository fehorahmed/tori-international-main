@extends('layouts.app')
@section('title','Master PJP Entry')
@section('style')
    <style>
        .day-row {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }
        .day-row:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .day-label {
            font-size: 1.1rem;
            font-weight: 600;
            color: #17a2b8;
            padding-top: 8px;
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-10 offset-md-1">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">Assign Permanent Journey Plan (PJP)</h3>
                </div>
                
                <form action="{{ route('route-plans.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <!-- Step 1: Select SR -->
                        <div class="form-group mb-4">
                            <label for="sr_id" class="text-lg">1. Select Sales Representative (SR) <span class="text-danger">*</span></label>
                            <select name="sr_id" id="sr_id" class="form-control select2" style="width: 100%;" required>
                                <option value="">-- Choose SR --</option>
                                @foreach($srs as $sr)
                                    <option value="{{ $sr->id }}">{{ $sr->name }} ({{ $sr->mobile_no }})</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Select an SR to view or update their weekly route plan.</small>
                        </div>

                        <!-- Step 2: Route Matrix -->
                        <div id="pjp_matrix" style="display: none;">
                            <h5 class="mb-3 border-bottom pb-2 text-info"><i class="fas fa-calendar-alt"></i> 2. Weekly Route Matrix</h5>
                            
                            @php
                                $marketsByUpazila = $markets->groupBy(function($item) {
                                    return $item->upazila->name ?? 'Other';
                                });
                            @endphp

                            @foreach($days as $day)
                                <div class="day-row row">
                                    <div class="col-md-3">
                                        <div class="day-label"><i class="far fa-clock"></i> {{ $day }}</div>
                                        <div class="mt-3 mb-2">
                                            <label style="font-size: 0.85rem;">Target Outlet Types</label>
                                            <select name="customer_types[{{ $day }}][]" class="form-control select2 customer-type-select" multiple data-day="{{ $day }}">
                                                @foreach($customerTypes as $type)
                                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mt-2 text-center p-2 bg-white border rounded">
                                            <small class="text-muted d-block">Expected Target Outlets</small>
                                            <strong class="text-success" style="font-size: 1.5rem;" id="count_{{ $day }}">0</strong>
                                        </div>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="accordion" id="accordion_{{ $day }}">
                                            @foreach($marketsByUpazila as $upazila => $upazilaMarkets)
                                                @php $uIndex = $loop->index; @endphp
                                                <div class="card mb-1" style="box-shadow: none; border: 1px solid #e9ecef;">
                                                    <div class="card-header p-2" id="heading_{{ $day }}_{{ $uIndex }}" style="background-color: #f8f9fa;">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div class="custom-control custom-checkbox">
                                                                <input type="checkbox" class="custom-control-input select-all-upazila" id="all_{{ $day }}_{{ $uIndex }}" data-day="{{ $day }}" data-index="{{ $uIndex }}">
                                                                <label class="custom-control-label font-weight-bold text-dark" for="all_{{ $day }}_{{ $uIndex }}" style="cursor:pointer;">
                                                                    <i class="fas fa-map-marker-alt text-danger mr-1"></i> {{ $upazila }} <span class="badge badge-light border ml-1">{{ count($upazilaMarkets) }} Markets</span>
                                                                </label>
                                                            </div>
                                                            <button class="btn btn-sm btn-link collapsed text-info" type="button" data-toggle="collapse" data-target="#collapse_{{ $day }}_{{ $uIndex }}" aria-expanded="false" aria-controls="collapse_{{ $day }}_{{ $uIndex }}">
                                                                <i class="fas fa-chevron-down"></i> View Markets
                                                            </button>
                                                        </div>
                                                    </div>

                                                    <div id="collapse_{{ $day }}_{{ $uIndex }}" class="collapse" aria-labelledby="heading_{{ $day }}_{{ $uIndex }}" data-parent="#accordion_{{ $day }}">
                                                        <div class="card-body p-2 bg-white">
                                                            <div class="row m-0">
                                                                @foreach($upazilaMarkets as $market)
                                                                <div class="col-md-4 col-sm-6 p-1">
                                                                    <div class="p-2" style="background:#f1f5f9; border-radius:6px; height: 100%;">
                                                                        <div class="custom-control custom-checkbox">
                                                                            <input type="checkbox" class="custom-control-input market-checkbox market-checkbox-{{ $day }}-{{ $uIndex }}" name="plans[{{ $day }}][]" value="{{ $market->id }}" id="market_{{ $day }}_{{ $market->id }}" data-day="{{ $day }}" data-index="{{ $uIndex }}">
                                                                            <label class="custom-control-label" style="font-weight: 500; font-size: 0.85rem; cursor:pointer; padding-top: 2px;" for="market_{{ $day }}_{{ $market->id }}">{{ $market->name }}</label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            
                        </div>

                    </div>
                    
                    <div class="card-footer text-right" id="submit_footer" style="display: none;">
                        <button type="submit" class="btn btn-success btn-lg"><i class="fas fa-save"></i> Save / Update PJP</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script>
    const shopCounts = @json($marketShopCounts);

    function recalculateCount(day) {
        let total = 0;
        
        let selectedTypes = $('select[name="customer_types[' + day + '][]"]').val() || [];
        
        let selectedMarkets = [];
        $('.market-checkbox[data-day="' + day + '"]:checked').each(function() {
            selectedMarkets.push($(this).val());
        });
        
        selectedMarkets.forEach(function(marketId) {
            if(shopCounts[marketId]) {
                if(selectedTypes.length === 0) {
                    Object.values(shopCounts[marketId]).forEach(function(count) {
                        total += parseInt(count);
                    });
                } else {
                    selectedTypes.forEach(function(typeId) {
                        if(shopCounts[marketId][typeId]) {
                            total += parseInt(shopCounts[marketId][typeId]);
                        }
                    });
                }
            }
        });
        
        $('#count_' + day).text(total);
    }

    $(document).ready(function() {
        $(document).on('change', '.market-checkbox, .customer-type-select', function() {
            let day = $(this).data('day');
            recalculateCount(day);
        });

        // Select All Logic
        $(document).on('change', '.select-all-upazila', function() {
            var day = $(this).data('day');
            var index = $(this).data('index');
            var isChecked = $(this).is(':checked');
            $('.market-checkbox-' + day + '-' + index).prop('checked', isChecked);
            recalculateCount(day);
        });

        $(document).on('change', '.market-checkbox', function() {
            var day = $(this).data('day');
            var index = $(this).data('index');
            var total = $('.market-checkbox-' + day + '-' + index).length;
            var checked = $('.market-checkbox-' + day + '-' + index + ':checked').length;
            $('#all_' + day + '_' + index).prop('checked', total === checked && total > 0);
        });

        // Handle SR Selection
        $('#sr_id').on('change', function() {
            var srId = $(this).val();
            
            if (!srId) {
                $('#pjp_matrix, #submit_footer').fadeOut();
                return;
            }

            // Show Matrix
            $('#pjp_matrix, #submit_footer').fadeIn();
            
            // Clear all existing selections first
            $('.market-checkbox').prop('checked', false);
            $('.select-all-upazila').prop('checked', false);

            // Fetch existing plans via AJAX
            Swal.fire({
                title: 'Loading PJP...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading() }
            });

            $.ajax({
                url: "{{ route('route-plans.get-plans') }}",
                type: "GET",
                data: { sr_id: srId },
                success: function(response) {
                    Swal.close();
                    
                    $('select.customer-type-select').val(null).trigger('change');
                    if (response.markets) {
                        $.each(response.markets, function(day, marketIds) {
                            $.each(marketIds, function(idx, marketId) {
                                $('#market_' + day + '_' + marketId).prop('checked', true);
                            });
                        });
                    }
                    
                    if (response.customer_types) {
                        $.each(response.customer_types, function(day, typeIds) {
                            $('select[name="customer_types[' + day + '][]"]').val(typeIds).trigger('change');
                        });
                    }

                    // Update all "select-all" checkboxes based on the loaded data
                    $('.select-all-upazila').each(function() {
                        var day = $(this).data('day');
                        var index = $(this).data('index');
                        var total = $('.market-checkbox-' + day + '-' + index).length;
                        var checked = $('.market-checkbox-' + day + '-' + index + ':checked').length;
                        $(this).prop('checked', total === checked && total > 0);
                    });
                    
                    $('.day-row').each(function() {
                        let day = $(this).find('.customer-type-select').data('day');
                        if (day) recalculateCount(day);
                    });
                    
                    const totalDays = response.markets ? Object.keys(response.markets).length : 0;
                    if(totalDays > 0) {
                        toastr.success('Loaded existing PJP for ' + totalDays + ' days.');
                    } else {
                        toastr.info('No existing PJP found. You can create a new one.');
                    }
                },
                error: function() {
                    Swal.close();
                    toastr.error('Failed to load existing plans.');
                }
            });
        });

        @if(isset($selectedSrId) && $selectedSrId)
            // Need a slight timeout in case Select2 initialization is happening
            setTimeout(function() {
                $('#sr_id').val('{{ $selectedSrId }}').trigger('change');
            }, 100);
        @endif
    });
</script>
@endsection
