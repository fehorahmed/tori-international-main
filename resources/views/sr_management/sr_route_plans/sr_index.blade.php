@extends('layouts.app')

@section('title', 'Today\'s Route Plan')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0 font-weight-bold text-dark">
                <i class="fas fa-calendar-day text-info mr-2"></i>Today's Route: <span class="text-primary">{{ $today }}</span>
            </h4>
        </div>
        
        @if($markets->isEmpty())
            <div class="alert alert-warning shadow-sm rounded">
                <i class="fas fa-exclamation-triangle"></i> You have no markets assigned for today. Enjoy your day off or contact your manager!
            </div>
        @else
            <div id="accordion">
                @foreach($markets as $market)
                    <div class="card card-info card-outline mb-3 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                        <div class="card-header bg-white p-0" id="heading-{{ $market->id }}">
                            <h5 class="mb-0">
                                <div class="d-flex justify-content-between align-items-center w-100 text-left p-3">
                                    <div class="flex-grow-1" data-toggle="collapse" data-target="#collapse-{{ $market->id }}" aria-expanded="false" aria-controls="collapse-{{ $market->id }}" style="cursor: pointer;">
                                        <h6 class="font-weight-bold mb-1" style="font-size: 1.1rem; color: #343a40;"><i class="fas fa-map-marker-alt text-danger mr-2"></i>{{ $market->name }}</h6>
                                        <div class="mt-1">
                                            <small class="text-muted"><i class="fas fa-map text-secondary"></i> {{ $market->upazila->name ?? 'Unknown Upazila' }}</small>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        @if($market->shops->where('lat', '!=', null)->where('long', '!=', null)->count() > 0)
                                            <a href="{{ route('sr_route_plans.map', $market->id) }}" target="_blank" class="btn btn-sm btn-outline-primary mr-3">
                                                <i class="fas fa-map-marked-alt"></i> Map
                                            </a>
                                        @endif
                                        <span class="badge badge-info badge-pill px-3 py-2 shadow-sm" data-toggle="collapse" data-target="#collapse-{{ $market->id }}" style="font-size: 14px; cursor: pointer;">{{ $market->shops->count() }} Shops <i class="fas fa-chevron-down ml-1"></i></span>
                                    </div>
                                </div>
                            </h5>
                        </div>

                        <div id="collapse-{{ $market->id }}" class="collapse" aria-labelledby="heading-{{ $market->id }}" data-parent="#accordion">
                            <div class="card-body p-3" style="background-color: #f8f9fa;">
                                @if($market->shops->isEmpty())
                                    <div class="text-center text-muted my-3 py-3 bg-white rounded border">
                                        <i class="fas fa-store-slash fa-2x mb-2 text-light"></i>
                                        <p class="mb-0">No shops registered in this market yet.</p>
                                    </div>
                                @else
                                    <div class="row">
                                        @foreach($market->shops as $shop)
                                            <div class="col-md-6 col-lg-4 col-12 mb-3">
                                                <div class="card shadow-sm border-0 h-100" style="border-radius: 10px;">
                                                    <div class="card-body p-3">
                                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                                            <h6 class="font-weight-bold mb-0 text-primary" style="line-height: 1.4;"><i class="fas fa-store text-secondary mr-1"></i> {{ $shop->name }}</h6>
                                                            <span class="badge badge-{{ $shop->type == 'Dealer' ? 'warning' : 'success' }} ml-2" style="font-size:10px; letter-spacing: 0.5px;">{{ strtoupper($shop->type) }}</span>
                                                        </div>
                                                        <div class="mb-3 pl-4">
                                                            <p class="mb-1 text-muted" style="font-size: 0.85rem;"><i class="fas fa-phone-alt text-secondary mr-1" style="width: 15px;"></i> {{ $shop->mobile_no ?? 'N/A' }}</p>
                                                            <p class="mb-0 text-muted" style="font-size: 0.85rem;"><i class="fas fa-map-pin text-secondary mr-1" style="width: 15px;"></i> {{ \Illuminate\Support\Str::limit($shop->address ?: ($market->name . ', ' . ($market->upazila->name ?? '')), 40) }}</p>
                                                            <p class="mb-0 mt-1 distance-text" id="distance-{{ $shop->id }}" data-lat="{{ $shop->lat }}" data-lng="{{ $shop->long }}" style="font-size: 0.85rem; font-weight: bold; color: #ff9800;">
                                                                <i class="fas fa-route text-secondary mr-1" style="width: 15px;"></i> Calculating distance...
                                                            </p>
                                                        </div>
                                                        
                                                        @if($shop->is_visited_today)
                                                            <button class="btn btn-success btn-block" disabled style="border-radius: 8px; font-weight: 600;">
                                                                <i class="fas fa-check-circle mr-1"></i> Already Visited
                                                            </button>
                                                        @else
                                                            <a href="{{ route('sr-visits.create') }}?s={{ encrypt($shop->id) }}" id="visit-btn-{{ $shop->id }}" class="btn btn-info btn-block text-white visit-btn disabled" style="border-radius: 8px; font-weight: 600; opacity: 0.6; pointer-events: none;">
                                                                <i class="fas fa-location-arrow mr-1"></i> Visit Now
                                                            </a>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        // Haversine distance calculation in meters
        function calculateDistance(lat1, lon1, lat2, lon2) {
            var R = 6371e3; // metres
            var φ1 = lat1 * Math.PI/180;
            var φ2 = lat2 * Math.PI/180;
            var Δφ = (lat2-lat1) * Math.PI/180;
            var Δλ = (lon2-lon1) * Math.PI/180;

            var a = Math.sin(Δφ/2) * Math.sin(Δφ/2) +
                    Math.cos(φ1) * Math.cos(φ2) *
                    Math.sin(Δλ/2) * Math.sin(Δλ/2);
            var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));

            var d = R * c;
            return d;
        }

        var lastLat = null;
        var lastLng = null;
        var lastProcessTime = 0;

        function updateDistances() {
            if (!lastLat || !lastLng) return;
            
            // Smart Calculation: Only process shops inside expanded (visible) markets!
            $('.distance-text:visible').each(function() {
                var shopLat = parseFloat($(this).data('lat'));
                var shopLng = parseFloat($(this).data('lng'));
                var shopId = $(this).attr('id').split('-')[1];
                
                if (shopLat && shopLng) {
                    var distance = calculateDistance(lastLat, lastLng, shopLat, shopLng);
                    var distanceText = "";
                    
                    if (distance > 1000) {
                        distanceText = (distance / 1000).toFixed(2) + " km away";
                    } else {
                        distanceText = Math.round(distance) + " meters away";
                    }
                    
                    $(this).html('<i class="fas fa-route text-secondary mr-1" style="width: 15px;"></i> ' + distanceText);

                    // Enable button if within 50 meters
                    if (distance <= 50) {
                        $(this).css('color', '#28a745'); // Green
                        $('#visit-btn-' + shopId).removeClass('disabled').css({'opacity': '1', 'pointer-events': 'auto'});
                    } else {
                        $(this).css('color', '#dc3545'); // Red
                        $('#visit-btn-' + shopId).addClass('disabled').css({'opacity': '0.6', 'pointer-events': 'none'});
                    }
                } else {
                    $(this).html('<i class="fas fa-exclamation-circle text-danger mr-1" style="width: 15px;"></i> GPS Not Setup');
                }
            });
        }

        // When a user expands an accordion, instantly calculate distances for that specific market
        $('.collapse').on('shown.bs.collapse', function () {
            updateDistances();
        });

        if (navigator.geolocation) {
            navigator.geolocation.watchPosition(function(position) {
                lastLat = position.coords.latitude;
                lastLng = position.coords.longitude;
                
                var currentTime = new Date().getTime();
                // Throttle: Only run distance calculation every 3 seconds (3000ms) max to save battery
                if (currentTime - lastProcessTime < 3000) {
                    return;
                }
                lastProcessTime = currentTime;

                updateDistances();
            }, function(error) {
                $('.distance-text').html('<i class="fas fa-exclamation-triangle text-danger mr-1" style="width: 15px;"></i> Location access denied');
            }, { enableHighAccuracy: true });
        } else {
            $('.distance-text').html('<i class="fas fa-exclamation-triangle text-danger mr-1" style="width: 15px;"></i> Geolocation not supported');
        }
    });
</script>
@endsection
