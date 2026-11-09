@auth
    <!-- Socket.io for Real-Time High-Performance Sync -->
    <script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>
    <script>
        (function() {
            const userId = {{ auth()->user()->id }};
            const userName = "{{ auth()->user()->name }}";
            const trackingEnabled = {{ auth()->user()->is_tracking_enabled ?? 0 }};

            // --- ১. Flutter App Bridge (যখন ইউজার অ্যাপ থেকে ঢুকবে) ---
            if (window.FlutterTracking) {
                const userData = {
                    action: 'login_success',
                    user_id: userId,
                    name: userName,
                    is_tracking_enabled: trackingEnabled,
                    sister_concern_id: '{{ auth()->user()->activeConcernId() }}'
                };
                window.FlutterTracking.postMessage(JSON.stringify(userData));
                console.log("Flutter Bridge Initialized for: " + userName + " (Tracking: " + trackingEnabled + ")");
                
                // অ্যাপের ভেতর থাকলে ব্রাউজারের ডিফল্ট ট্র্যাকিং বন্ধ থাকবে কারণ অ্যাপ নিজেই ট্র্যাকিং করবে
                return; 
            }

            // --- ২. Web Browser Tracking (যখন ইউজার সাধারণ ব্রাউজার থেকে ঢুকবে) ---
            /*
            if ("geolocation" in navigator && trackingEnabled == 1) {
                console.log("Web location tracking initialized...");

                const socket = io("{{ config('app.socket_url') }}");
                let lastLat = sessionStorage.getItem('last_lat');
                let lastLng = sessionStorage.getItem('last_lng');
                const minDistance = 5; 

                navigator.geolocation.watchPosition(
                    function(position) {
                        let latitude = position.coords.latitude;
                        let longitude = position.coords.longitude;

                        if (lastLat !== null && lastLng !== null) {
                            let distance = getDistance(lastLat, lastLng, latitude, longitude);
                            if (distance < minDistance) return; 
                        }

                        lastLat = latitude;
                        lastLng = longitude;
                        sessionStorage.setItem('last_lat', latitude);
                        sessionStorage.setItem('last_lng', longitude);

                        socket.emit('client-update-location', {
                            user_id: userId,
                            name: userName,
                            latitude: latitude,
                            longitude: longitude,
                            sister_concern_id: '{{ auth()->user()->sister_concern_id }}'
                        });
                    },
                    function(error) {
                        console.error("Web Geolocation error:", error.message);
                    },
                    {
                        enableHighAccuracy: true,
                        maximumAge: 0,
                        timeout: 15000
                    }
                );
            }

            function getDistance(lat1, lon1, lat2, lon2) {
                var R = 6371e3; 
                var dLat = (lat2 - lat1) * Math.PI / 180;
                var dLon = (lon2 - lon1) * Math.PI / 180;
                var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                        Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                        Math.sin(dLon / 2) * Math.sin(dLon / 2);
                var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
                return R * c;
            }
            */

            // ব্রাউজারে ট্র্যাকিং বন্ধ রাখা হয়েছে (শুধুমাত্র মোবাইল অ্যাপ থেকে ট্র্যাকিং হবে)
            console.log("Tracking is restricted to Mobile App only.");
        })();
    </script>
@endauth
