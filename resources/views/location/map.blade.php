<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Real-Time Map Tracking</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://js.pusher.com/7.0/pusher.min.js"></script>
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Initialize Leaflet Map
            const map = L.map('map').setView([23.8103, 90.4125], 13);

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
            fetch('/tracking/initial-locations')
                .then(response => response.json())
                .then(locations => {
                    locations.forEach(user => {
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
    </script>
</head>
<body>
    <div id="map" style="height: 100vh;"></div>
</body>
</html>
