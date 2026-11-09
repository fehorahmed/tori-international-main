<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeocodingService
{
    /**
     * Get Address from Lat/Lon using a robust fallback strategy.
     * It attempts each provider in order of speed and limit size.
     *
     * @param float $lat
     * @param float $lon
     * @return string|null
     */
    public function getAddress($lat, $lon)
    {
        $providers = [
            'getMapboxAddress',
            'getLocationIqAddress',
            'getOpenCageAddress',
            'getNominatimAddress'
        ];

        foreach ($providers as $provider) {
            try {
                $address = $this->{$provider}($lat, $lon);
                
                if ($address) {
                    return $address; // Success! Return immediately.
                }
            } catch (\Exception $e) {
                // Log the failure to trace API issues, but continue to the next provider
                Log::warning("Geocoding Service ({$provider}) failed: " . $e->getMessage());
                continue;
            }
        }

        // If all providers fail, return null. 
        // The controller will fallback to returning the raw Lat/Lon string.
        return null;
    }

    /**
     * 1st Choice: Mapbox Geocoding API (100,000 free requests/month)
     */
    private function getMapboxAddress($lat, $lon)
    {
        $apiKey = config('services.mapbox.key');
        if (!$apiKey) return null; // Skip if API Key is not set

        $url = "https://api.mapbox.com/geocoding/v5/mapbox.places/{$lon},{$lat}.json";
        
        $response = Http::timeout(5)->get($url, [
            'access_token' => $apiKey,
            'types' => 'address,poi,place,locality,neighborhood',
            'limit' => 1
        ]);

        if ($response->successful() && !empty($response->json('features'))) {
            return $response->json('features')[0]['place_name'] ?? null;
        }

        if ($response->status() === 429) {
            throw new \Exception("Mapbox Rate Limit Exceeded");
        }

        return null;
    }

    /**
     * 2nd Choice: LocationIQ Geocoding API (5,000 free requests/day)
     */
    private function getLocationIqAddress($lat, $lon)
    {
        $apiKey = config('services.locationiq.key');
        if (!$apiKey) return null; // Skip if API Key is not set

        $url = "https://us1.locationiq.com/v1/reverse.php";
        
        $response = Http::timeout(5)->get($url, [
            'key' => $apiKey,
            'lat' => $lat,
            'lon' => $lon,
            'format' => 'json'
        ]);

        if ($response->successful()) {
            return $response->json('display_name') ?? null;
        }

        if ($response->status() === 429) {
            throw new \Exception("LocationIQ Rate Limit Exceeded");
        }

        return null;
    }

    /**
     * 3rd Choice: OpenCage Geocoder API (2,500 free requests/day)
     */
    private function getOpenCageAddress($lat, $lon)
    {
        $apiKey = config('services.opencage.key');
        if (!$apiKey) return null; // Skip if API Key is not set

        $url = "https://api.opencagedata.com/geocode/v1/json";
        
        $response = Http::timeout(5)->get($url, [
            'key' => $apiKey,
            'q' => "{$lat}+{$lon}",
            'no_annotations' => 1
        ]);

        if ($response->successful() && !empty($response->json('results'))) {
            return $response->json('results')[0]['formatted'] ?? null;
        }

        // OpenCage returns 402 if free quota is completely exhausted
        if ($response->status() === 402 || $response->status() === 429) {
            throw new \Exception("OpenCage Rate Limit or Quota Exceeded");
        }

        return null;
    }

    /**
     * Last Resort: Nominatim (OpenStreetMap) API 
     * (Free, no key required, but strict 1 req/sec limit)
     */
    private function getNominatimAddress($lat, $lon)
    {
        $url = "https://nominatim.openstreetmap.org/reverse";
        
        $response = Http::withHeaders([
            'User-Agent' => 'MK-ERP-App/1.0 (contact@yourdomain.com)'
        ])->timeout(7)->get($url, [
            'format' => 'json',
            'lat' => $lat,
            'lon' => $lon,
            'addressdetails' => 1
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $locationName = $data['display_name'] ?? null;

            if (!$locationName && isset($data['address'])) {
                $address = $data['address'];
                $locationName = $address['city'] ??
                                $address['town'] ??
                                $address['village'] ??
                                $address['county'] ??
                                $address['state'] ??
                                $address['country'] ?? null;
            }

            return $locationName;
        }

        if ($response->status() === 429) {
            throw new \Exception("Nominatim Rate Limit Exceeded");
        }

        return null;
    }
}
