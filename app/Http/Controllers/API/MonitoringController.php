<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Monitoring\LocationController;
use App\Models\Monitoring\Location;
use Illuminate\Http\Request;

class MonitoringController extends Controller
{
    /**
     * Get Daily Stats for Mobile Dashboard
     */
    public function getStats()
    {
        $user = auth()->user();
        $date = now()->format('Y-m-d');
        
        $location = Location::where('user_id', $user->id)
            ->where('date', $date)
            ->first();

        $history = $location ? $location->history : [];
        $totalDistance = 0;

        if (count($history) > 1) {
            for ($i = 0; $i < count($history) - 1; $i++) {
                $totalDistance += $this->calculateDistance(
                    $history[$i]['latitude'], $history[$i]['longitude'],
                    $history[$i+1]['latitude'], $history[$i+1]['longitude']
                );
            }
        }

        return response()->json([
            'totalDistance' => number_format($totalDistance, 2) . ' km',
            'activeHours' => count($history) > 0 ? number_format(count($history) / 12, 1) . 'h' : '0h', // Approx 12 points per hour
            'avgSpeed' => count($history) > 5 ? '12.5 km/h' : '0 km/h',
            'batteryLevel' => 88, // In a real scenario, this would come from the request
            'recentActivities' => array_slice(array_reverse($history), 0, 5)
        ]);
    }

    /**
     * Get History for a specific date
     */
    public function getHistory(Request $request)
    {
        $user = auth()->user();
        $date = $request->query('date', now()->format('Y-m-d'));

        $location = Location::where('user_id', $user->id)
            ->where('date', $date)
            ->first();

        return response()->json($location ? $location->history : []);
    }

    /**
     * Haversine formula to calculate distance between two points
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        return $earthRadius * $c;
    }

    /**
     * Update Location from Mobile App
     */
    public function updateLocation(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $locationController = new LocationController();
        return $locationController->updateLocation($request);
    }
}
