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
    </style>
@endsection
@section('content')
    <div class="row">
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
                            <button class="btn btn-sm btn-outline-primary mt-2 w-100" id="user_id_{{ $user->id }}" data-latitude="{{ $user->latest_latitude }}" data-longitude="{{ $user->latest_longitude }}" data-name="{{ $user->name }}" onclick="focusOnMap(this)">
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
    <script src="https://js.pusher.com/7.0/pusher.min.js"></script>
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
        let map;
        document.addEventListener("DOMContentLoaded", function () {
            // Initialize Leaflet Map
            map = L.map('map').setView([23.8103, 90.4125], 13);

            // Add OpenStreetMap tiles
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors',
            }).addTo(map);

            let markers = {};

            // Initialize Pusher
            Pusher.logToConsole = true;
            const pusher = new Pusher('50896a7437bc375750f0', {
                cluster: 'mt1',
                encrypted: true
            });

            const channel = pusher.subscribe('location-channel');
            channel.bind('location-updated', function(data) {
                const { user_id, latitude, longitude } = data;
                console.log(data);

                let userButton = document.getElementById(`user_id_${user_id}`);
                if (userButton) {
                    userButton.setAttribute("data-latitude", latitude);
                    userButton.setAttribute("data-longitude", longitude);
                }

                // Add or update marker
                if (markers[user_id]) {
                    markers[user_id].setLatLng([latitude, longitude]);
                } else {
                    const marker = L.marker([latitude, longitude]).addTo(map);
                    markers[user_id] = marker;
                }
                
                // Optionally, fly to the new marker location
                map.setView([latitude, longitude], map.getZoom(), { animate: true, duration: 1.5 });
            });

            // Fetch initial locations from the Laravel API
            fetch('{{ route('tracking.initial_location') }}')
                .then(response => response.json())
                .then(locations => {
                    locations.forEach(user => {
                        console.log(user);
                        if (user.position) {
                            // Add initial markers for each user
                            const marker = L.marker(user.position).addTo(map);
                            markers[user.id] = marker;
                            // Optionally, add a popup with the user's name
                            marker.bindPopup(`<b>${user.name}</b>`);
                        }
                    });
                })
                .catch(error => console.error('Error fetching initial locations:', error));
        });
        function focusOnMap(button) {
            let latitude = button.getAttribute("data-latitude");
            let longitude = button.getAttribute("data-longitude");
            let userName = button.getAttribute("data-name");

            map.setView([latitude, longitude], 13); 

            L.marker([latitude, longitude]).addTo(map)
                .bindPopup('<b>' + userName + '</b>')
                .openPopup();
        }
    </script>
    <script>
        $(document).ready(function() {
            $('#employeeSearch').on('keyup', function() {
                var query = $(this).val();

                // if (query.length > 0) { 
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
                // } else {
                //     $('#userList').html('');
                // }
            });
        });
        
    </script>
@endsection
