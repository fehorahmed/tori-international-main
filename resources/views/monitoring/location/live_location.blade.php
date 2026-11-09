@extends('layouts.app')
@section('title','Live Location')
@section('style')
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <style>
        .avatar-sm {
            width: 2rem;
            height: 2rem;
        }
        .avatar {
            position: relative;
            width: 2.375rem;
            height: 2.375rem;
            cursor: pointer;
        }
        .avatar-sm .avatar-initial {
            font-size: .8125rem;
        }
        .avatar .avatar-initial {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            text-transform: uppercase;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            background-color: #eeedf0;
            font-size: .9375rem;
        }
        .bg-label-primary {
            background-color: #e7e7ff !important;
            color: #696cff !important;
        }
        .rounded-circle {
            border-radius: 50% !important;
        }
        #userList {
            max-height: 400px; 
            overflow-y: auto; 
        }
        #userList::-webkit-scrollbar {
            width: 8px; 
        }
        #userList::-webkit-scrollbar-track {
            background: #f1f1f1; 
            border-radius: 10px;
        }
        #userList::-webkit-scrollbar-thumb {
            background: #888; 
            border-radius: 10px;
        }
        #userList::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
        .active-user-item {
            background-color: #f0f7ff !important;
            border-left: 4px solid #007bff !important;
        }
        .leaflet-marker-icon {
            transition: all 0.5s linear;
        }
        .dot {
            height: 12px;
            width: 12px;
            border-radius: 50%;
            display: inline-block;
            border: 2px solid #fff;
            box-shadow: 0 0 4px rgba(0,0,0,0.2);
        }
    </style>
@endsection
@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="card card-default">
                <div class="card-header">
                    <h5 class="mb-0 text-primary fw-bold">User Monitoring</h5>
                    <input type="text" id="employeeSearch" class="form-control mt-2" placeholder="Search employees...">
                </div>
                <!-- /.card-header -->
                <div class="card-body" id="userList">
                    @foreach ($users as $user)
                        <div class="card mb-2 p-2 shadow-sm position-relative user-card-item" id="card_user_{{ $user->id }}">
                            <div class="status-indicator position-absolute" style="top: 8px; right: 8px;">
                                <span class="dot {{ $user->is_online ? 'bg-success' : 'bg-danger' }}"></span>
                            </div>
                            <div class="d-flex justify-content-start align-items-center user-name">
                                <div class="avatar-wrapper">
                                    <div class="avatar avatar-sm me-4">
                                        <span class="avatar-initial rounded-circle bg-label-primary">{{ shortName($user->name) }}</span>
                                    </div>
                                </div>
                                <div class="d-flex flex-column">
                                    <a href="#" class="text-heading text-truncate fw-medium">{{ $user->name }}</a>
                                    <small>ID NO: {{ $user->id }}</small>
                                </div>
                            </div>
                            <button class="btn btn-sm btn-outline-primary mt-2 w-100 focus-btn" 
                                id="user_id_{{ $user->id }}" 
                                data-user-id="{{ $user->id }}" 
                                data-latitude="{{ $user->latest_latitude }}" 
                                data-longitude="{{ $user->latest_longitude }}" 
                                data-name="{{ $user->name }}" 
                                onclick="focusOnMap(this)">
                                Focus on Map
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card card-default">
                <div class="card-body p-0">
                    <div id="map" style="height: 90vh; border-radius: 8px;"></div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
        let map;
        let markers = {}; 
        let selectedUserId = null; // Gloabl tracking of who we are following

        document.addEventListener("DOMContentLoaded", function () {
            // Define different map layers
            const osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            });

            const topo = L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
                attribution: 'Map data: &copy; OpenTopoMap contributors'
            });

            const satellite = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EBP, and the GIS User Community'
            });

            // Initialize map
            map = L.map('map', {
                center: [23.8103, 90.4125],
                zoom: 13,
                layers: [osm]
            });

            const baseMaps = {
                "Standard Street": osm,
                "Topographic": topo,
                "Satellite View": satellite
            };

            L.control.layers(baseMaps).addTo(map);

            // Initialize Socket.io
            const socket = io("{{ config('app.socket_url') }}"); 

            // Join the specific branch room to isolate tracking data
            socket.emit('join-dashboard', { 
                sister_concern_id: '{{ auth()->user()->activeConcernId() }}' 
            });

        socket.on('location-updated', (data) => {
            let { user_id, latitude, longitude, name } = data;
            const uid = String(user_id); 
            
            if (markers[uid]) {
                markers[uid].setLatLng([latitude, longitude]);
                markers[uid].getPopup().setContent(`<b>${name}</b><br>Lat: ${latitude.toFixed(5)}, Lng: ${longitude.toFixed(5)}`);
            } else {
                const marker = L.marker([latitude, longitude]).addTo(map)
                    .bindPopup(`<b>${name}</b><br>Lat: ${latitude.toFixed(5)}, Lng: ${longitude.toFixed(5)}`);
                markers[uid] = marker;
            }

            // [NEW] Update Sidebar Button Attributes in Real-time
            const focusBtn = document.getElementById(`user_id_${uid}`);
            if (focusBtn) {
                focusBtn.setAttribute('data-latitude', latitude);
                focusBtn.setAttribute('data-longitude', longitude);
                
                // Update specific status UI (Online Indicator)
                const userCard = document.getElementById(`card_user_${uid}`);
                if (userCard) {
                    $(userCard).find('.dot').removeClass('bg-danger').addClass('bg-success');
                }
            }

            // AUTO-FOLLOW with smooth Sliding
            if (String(selectedUserId) === uid) {
                const currentCenter = map.getCenter();
                const distance = currentCenter.distanceTo([latitude, longitude]);
                
                // Only move map if user has moved more than 2 meters to avoid "jitter/shaking"
                if (distance > 2) {
                    map.panTo([latitude, longitude], {
                        animate: true,
                        duration: 0.5, // Fast and smooth update
                        easeLinearity: 0.25
                    });
                }
            }
        });

        // Fetch initial locations
        fetch('{{ route('tracking.initial_location') }}')
            .then(response => response.json())
            .then(locations => {
                locations.forEach(user => {
                    const uid = String(user.id);
                    if (user.position && !markers[uid]) {
                        const marker = L.marker(user.position).addTo(map)
                            .bindPopup(`<b>${user.name}</b>`);
                        markers[uid] = marker;
                    }
                });
                
                // Fix map container size after markers are loaded
                setTimeout(() => map.invalidateSize(), 500);
            })
            .catch(error => console.error('Error fetching initial locations:', error));
    });

    function focusOnMap(button) {
        let userId = String(button.getAttribute("data-user-id"));
        let latitude = parseFloat(button.getAttribute("data-latitude"));
        let longitude = parseFloat(button.getAttribute("data-longitude"));
        let userName = button.getAttribute("data-name");

        selectedUserId = userId;

        $('.user-card-item').removeClass('active-user-item');
        $(`#card_user_${userId}`).addClass('active-user-item');

        // Cinematic FlyTo for perfect centering
        map.flyTo([latitude, longitude], 16, {
            animate: true,
            duration: 2
        });

        if (markers[userId]) {
            setTimeout(() => markers[userId].openPopup(), 1000);
        } else {
            const marker = L.marker([latitude, longitude]).addTo(map)
                .bindPopup(`<b>${userName}</b><br>Lat: ${latitude.toFixed(5)}, Lng: ${longitude.toFixed(5)}`);
            markers[userId] = marker;
            setTimeout(() => marker.openPopup(), 1000);
        }
    }
    </script>
    <script>
        $(document).ready(function() {
            $('#employeeSearch').on('keyup', function() {
                var query = $(this).val();
                $.ajax({
                    url: '{{ route('tracking.search_user') }}',
                    method: 'GET',
                    data: { query: query },
                    success: function(response) {
                        $('#userList').html(response);
                    }
                });
            });
        });
    </script>
@endsection
