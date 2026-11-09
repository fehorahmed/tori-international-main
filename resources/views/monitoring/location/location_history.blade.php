@extends('layouts.app')
@section('title','Location History')
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
    </style>
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    <h5 class="mb-0">Date Filter</h5>
                    <input type="text" class="form-control date-picker mt-2" id="date_filter" value="{{ date('d-m-Y') }}" placeholder="DD-MM-YYYY">
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-default">
                <div class="card-header">
                    <h5 class="mb-0">User List</h5>
                    <input type="text" id="employeeSearch" class="form-control mt-2" placeholder="Search employees...">
                </div>
                <!-- /.card-header -->
                <div class="card-body" id="userList">
                    @foreach ($users as $user)
                        <div class="card mb-2 p-2 shadow-sm position-relative">
                            <div class="status-indicator position-absolute" style="top: 8px; right: 8px;">
                                <span class="dot bg-danger"></span>
                            </div>
                            <div>
                            <div class="d-flex justify-content-start align-items-center user-name">
                                <div class="avatar-wrapper">
                                    <div class="avatar avatar-sm me-4">
                                        <span class="avatar-initial rounded-circle bg-label-primary">{{ shortName($user->name) }}</span>
                                    </div>
                                </div>
                                <div class="d-flex flex-column">
                                    <a href="#" class="text-heading text-truncate fw-medium">{{ $user->name }}</a>
                                    <small>ID NO: ID480</small>
                                </div>
                            </div>
                                {{-- <p class="mb-1 mt-3">Designation: {{ $client->designation }}</p> --}}
                                {{-- <p class="mb-1">Last Updated: 12 hours ago</p> --}}
                            </div>
                            <button class="btn btn-sm btn-outline-primary mt-2 w-100" onclick="focusOnMap({{ $user->latest_latitude }}, {{ $user->latest_longitude }}, '{{ $user->name }}', {{ $user->id }})">
                                Focus on Map
                            </button>
                        </div>
                    @endforeach
                    
                </div>

            </div>
        </div>
        <div class="col-md-8">
            <div class="card card-default">
                <div class="card-body">
                    <div id="map" style="height: 90vh;"></div>
                </div>
                <!-- /.card-body -->
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
        let map;
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

            // Initialize map with Standard or Satellite
            map = L.map('map', {
                center: [23.8103, 90.4125],
                zoom: 13,
                layers: [osm] // default layer
            });

            // Add layer control (Switcher)
            const baseMaps = {
                "Standard Street": osm,
                "Topographic": topo,
                "Satellite View": satellite
            };

            L.control.layers(baseMaps).addTo(map);

        });
        function focusOnMap(lat, lng, userName, userId) {
            let date = $('#date_filter').val();

            map.setView([lat, lng], 13); 

            // Clear existing polylines
            if (window.historyPolyline) {
                map.removeLayer(window.historyPolyline);
            }
            const userHistoryBaseUrl = "{{ route('tracking.user_history', ['userId' => ':userId', 'date' => ':date']) }}";
            let url = userHistoryBaseUrl.replace(':userId', userId).replace(':date', date);
            // Fetch user's location history
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    console.log(data);
                    if (data.length === 0) {
                        alert("No tracking history found for this user.");
                        return;
                    }

                    // Convert data into an array of LatLng points
                    let coordinates = data.map((point)=>[point.latitude, point.longitude]);

                    // Draw polyline
                    window.historyPolyline = L.polyline(coordinates, {
                        color: 'blue',
                        weight: 4,
                        opacity: 0.7,
                    }).addTo(map);

                    // Add marker at the start point
                    let startPoint = coordinates[0];
                    let startStreetViewUrl = `https://www.google.com/maps/@?api=1&map_action=pano&viewpoint=${startPoint[0]},${startPoint[1]}`;
                    
                    L.marker(startPoint)
                        .addTo(map)
                        .bindPopup(`
                            <b>${userName} (Start)</b><br>
                            <a href="${startStreetViewUrl}" target="_blank" class="btn btn-xs btn-primary text-white mt-1" style="font-size: 10px; padding: 2px 5px;">View Street View</a>
                        `);

                    data.forEach((coord, index) => {
                        if(coord.order_status == 1){
                            let orderStreetViewUrl = `https://www.google.com/maps/@?api=1&map_action=pano&viewpoint=${coord.latitude},${coord.longitude}`;
                            L.marker([coord.latitude, coord.longitude])
                            .addTo(map)
                            .bindPopup(`
                                Order No: ${coord.order_no}<br>
                                <a href="${orderStreetViewUrl}" target="_blank" class="btn btn-xs btn-primary text-white mt-1" style="font-size: 10px; padding: 2px 5px;">View Street View</a>
                            `);
                        }
                    });

                    // Add marker at the end point
                    let endPoint = coordinates[coordinates.length - 1];
                    let endStreetViewUrl = `https://www.google.com/maps/@?api=1&map_action=pano&viewpoint=${endPoint[0]},${endPoint[1]}`;

                    L.marker(endPoint)
                        .addTo(map)
                        .bindPopup(`
                            <b>${userName} (End)</b><br>
                            <a href="${endStreetViewUrl}" target="_blank" class="btn btn-xs btn-primary text-white mt-1" style="font-size: 10px; padding: 2px 5px;">View Street View</a>
                        `)
                        .openPopup();

                    // Fit map to polyline
                    map.fitBounds(window.historyPolyline.getBounds());
                })
                .catch(error => console.error('Error fetching tracking history:', error));
        }
    </script>
    <script>
        $(document).ready(function() {
            $('#employeeSearch').on('keyup', function() {
                var query = $(this).val();

                if (query.length > 1) { 
                    $.ajax({
                        url: '{{ route('tracking.search_user') }}',
                        method: 'GET',
                        data: { query: query },
                        success: function(response) {
                            console.log(response);
                            $('#userList').html(response);
                        },
                        error: function() {
                            $('#userList').html('Error occurred while searching');
                        }
                    });
                } else {
                    $('#userList').html('');
                }
            });
        });
        
    </script>
@endsection
