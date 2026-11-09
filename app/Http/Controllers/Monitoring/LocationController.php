<?php

namespace App\Http\Controllers\Monitoring;

use App\Http\Controllers\Controller;
use App\Events\LocationUpdate;
use Illuminate\Http\Request;
use App\Models\Monitoring\Location;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class LocationController extends Controller
{
    public function liveLocation(){
        abort_if(!auth()->user()->hasPermissionTo('live_location'), 403, 'Unauthorized');
        $query = User::where('is_tracking_enabled', 1);
        if (auth()->user()->activeConcernId()) {
            $query->where('sister_concern_id', auth()->user()->activeConcernId());
        }
        $users = $query->get();
        return view('monitoring.location.live_location',compact('users'));
    }
    public function locationHistory(){
        abort_if(!auth()->user()->hasPermissionTo('location_history'), 403, 'Unauthorized');
        $query = User::where('is_tracking_enabled', 1);
        if (auth()->user()->activeConcernId()) {
            $query->where('sister_concern_id', auth()->user()->activeConcernId());
        }
        $users = $query->get();
        return view('monitoring.location.location_history',compact('users'));
    }

    public function updateLocation(Request $request)
    {
        try {
            $user = auth()->user();

            $newLatitude = $request->latitude;
            $newLongitude = $request->longitude;

            // Only update database if location changes
            if ($user->latest_latitude != $newLatitude || $user->latest_longitude != $newLongitude) {
                
                // Bulk update user data in one query
                $user->update([
                    'is_online' => ($user->is_online == 0) ? 1 : $user->is_online,
                    'latest_latitude' => $newLatitude,
                    'latest_longitude' => $newLongitude,
                ]);

                $currentDate = now()->format('Y-m-d');
                // $newPoint = [$newLatitude, $newLongitude];
                $newPoint = [
                    'latitude' => $newLatitude,      
                    'longitude' => $newLongitude,    
                    'order_status' => 0,      
                    'order_no' => ''  
                ];

                $location = Location::where('user_id', $user->id)
                ->where('date', $currentDate)
                ->first();

                $existingHistory = $location ? $location->history : [];
                
                // Duplicate Check: Check if the last point in history is the same as the new point
                if (!empty($existingHistory)) {
                    $lastPoint = end($existingHistory);
                    if ($lastPoint['latitude'] == $newLatitude && $lastPoint['longitude'] == $newLongitude) {
                        return response()->json(['status' => 'Location identical, skipping history update.']);
                    }
                }

                $existingHistory[] = $newPoint;

                Location::updateOrCreate(
                    ['user_id' => $user->id, 'date' => $currentDate],
                    ['history' => $existingHistory]
                );

                // Fire location update event (Internal Laravel Event)
                event(new LocationUpdate([
                    'user_id' => $user->id,
                    'latitude' => $newLatitude,
                    'longitude' => $newLongitude,
                    'name' => $user->name
                ]));

                // Send data to Node.js Socket Server (HTTP Bridge)
            }

            return response()->json(['status' => 'Location received and processing']);

        } catch (\Exception $e) {
            \Log::error('Publish failed', ['error' => $e->getMessage()]);
            return response()->json(['status' => $e->getMessage()], 500);
        }
    }
    public function initialLocations()
    {
        abort_if(!auth()->user()->hasPermissionTo('live_location'), 403, 'Unauthorized');
        $query = User::where('is_tracking_enabled', 1);
        if (auth()->user()->activeConcernId()) {
            $query->where('sister_concern_id', auth()->user()->activeConcernId());
        }
        $users = $query->get();
        
        return response()->json(
            $users->map(fn($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'position' => $user->latest_latitude != 0 
                    ? [$user->latest_latitude, $user->latest_longitude]
                    : null
            ])
        );
    }
    public function userLocationHistory($userId,$date)
    {
        abort_if(!auth()->user()->hasPermissionTo('location_history'), 403, 'Unauthorized');
        $location = Location::where('user_id', $userId)
            ->where('date', date('Y-m-d',strtotime($date)))
            ->first();

        return response()->json($location ? $location->history : []);
    }
    public function searchUser(Request $request){
        abort_if(!auth()->user()->hasPermissionTo('live_location'), 403, 'Unauthorized');
        $query = $request->get('query');

        $queryObj = User::where('is_tracking_enabled', 1)
            ->where(function($q) use ($query) {
                $q->where('name', 'like', "%$query%")
                  ->orWhere('email', 'like', "%$query%");
            });
            
        if (auth()->user()->activeConcernId()) {
            $queryObj->where('sister_concern_id', auth()->user()->activeConcernId());
        }
        
        $users = $queryObj->get();

        return view('monitoring.location.search_user_results', compact('users'));
    }
    public function setOffline(Request $request)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $user->update(['is_online' => 0]); // Update the user as offline
        }

        return response()->json(['status' => 'offline updated']);
    }
}
