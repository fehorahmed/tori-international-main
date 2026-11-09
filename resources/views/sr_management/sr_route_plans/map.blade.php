@extends('layouts.app')

@section('title', $market->name . ' - Map View')

@section('style')
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<style>
    /* Full height map layout */
    .content-wrapper {
        padding: 0 !important;
    }
    .content-header {
        display: none !important;
    }
    .content {
        padding: 0 !important;
    }
    #marketMapContainer {
        height: calc(100vh - 60px); /* Adjust based on top navbar height */
        width: 100%;
        position: relative;
    }
    #leafletFullScreenMap {
        height: 100%;
        width: 100%;
        z-index: 1;
    }
    .map-top-bar {
        position: absolute;
        top: 15px;
        left: 15px;
        right: 15px;
        z-index: 999;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: rgba(255, 255, 255, 0.95);
        padding: 10px 15px;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    .leaflet-control-attribution {
        display: none !important;
    }
    .shop-label {
        background-color: rgba(255, 255, 255, 0.9);
        border: 1px solid #17a2b8;
        border-radius: 4px;
        color: #17a2b8;
        font-weight: bold;
        font-size: 11px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.2);
        padding: 3px 6px;
        white-space: nowrap;
    }
</style>
@endsection

@section('content')
<div id="marketMapContainer">
    <!-- Top Floating Bar -->
    <div class="map-top-bar" style="z-index: 10000;">
        <div>
            <button onclick="window.close()" class="btn btn-sm btn-outline-dark mr-3 shadow-sm" style="border-radius: 50%; width: 35px; height: 35px; padding: 5px 0;">
                <i class="fas fa-arrow-left"></i>
            </button>
            <strong style="font-size: 1.1rem; color: #343a40;">{{ $market->name }}</strong>
        </div>
        <div>
            <span class="badge badge-info shadow-sm px-3 py-2" style="font-size: 13px;">{{ $market->shops->count() }} Shops</span>
        </div>
    </div>

    <!-- The Map -->
    <div id="leafletFullScreenMap"></div>
</div>
@endsection

@section('script')
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
    $(document).ready(function() {
        // Prepare shop data
        var shops = @json($market->shops->filter(function($s) { return $s->lat && $s->long; })->values());
        
        // Initialize Map
        var map = L.map('leafletFullScreenMap', {
            zoomControl: false // Move zoom control if needed
        });
        
        L.control.zoom({ position: 'bottomright' }).addTo(map);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        var bounds = [];

        // Watch SR's live location and put a pin
        var userMarker = null;
        if (navigator.geolocation) {
            navigator.geolocation.watchPosition(function(position) {
                var lat = position.coords.latitude;
                var lng = position.coords.longitude;

                if (userMarker) {
                    userMarker.setLatLng([lat, lng]);
                } else {
                    var userIcon = L.divIcon({
                        html: '<div style="background-color: #dc3545; color: white; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 3px solid white; box-shadow: 0 2px 6px rgba(0,0,0,0.4); font-size: 18px;"><i class="fas fa-street-view"></i></div>',
                        className: '',
                        iconSize: [36, 36],
                        iconAnchor: [18, 18]
                    });
                    
                    userMarker = L.marker([lat, lng], {icon: userIcon, zIndexOffset: 1000}).addTo(map);
                    userMarker.bindTooltip('📍 Me', {
                        permanent: true,
                        direction: 'right',
                        className: 'shop-label',
                        offset: [15, 0]
                    });
                    
                    // Add user location to bounds if it's the first time
                    bounds.push([lat, lng]);
                    if (bounds.length > 0) {
                        map.fitBounds(bounds, {padding: [50, 50]});
                    }
                }
            }, function(error) {
                console.log("Geolocation error:", error);
            }, { enableHighAccuracy: true });
        }

        // Add Shop pins
        if(shops.length > 0) {
            shops.forEach(function(shop) {
                var shopIcon = L.divIcon({
                    html: '<div style="background-color: #007bff; color: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3); font-size: 14px;"><i class="fas fa-store"></i></div>',
                    className: '',
                    iconSize: [32, 32],
                    iconAnchor: [16, 16]
                });

                var m = L.marker([shop.lat, shop.long], { icon: shopIcon, title: shop.name }).addTo(map);
                
                // Show Shop name permanently above the pin
                m.bindTooltip(shop.name, {
                    permanent: true,
                    direction: 'top',
                    className: 'shop-label',
                    offset: [0, -18]
                });
                
                var popupContent = `
                    <div style="min-width: 150px;">
                        <h6 style="margin: 0 0 5px 0; font-weight: bold; color: #007bff;">🏪 ${shop.name}</h6>
                        <p style="margin: 0; font-size: 13px;"><i class="fas fa-phone-alt text-secondary"></i> ${shop.mobile_no || 'N/A'}</p>
                        ${shop.address ? `<p style="margin: 3px 0 0 0; font-size: 12px; color: #666;">${shop.address}</p>` : ''}
                    </div>
                `;
                
                m.bindPopup(popupContent);
                bounds.push([shop.lat, shop.long]);
            });

            // Auto zoom/pan to fit all shops
            map.fitBounds(bounds, {padding: [50, 50]});
        } else {
            // Default center if no shops
            map.setView([23.8103, 90.4125], 7); 
        }
    });
</script>
@endsection
