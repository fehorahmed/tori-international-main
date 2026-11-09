<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Hrm\HrmSetting;

class FirebaseChannel
{
    public function send($notifiable, Notification $notification)
    {
        $setting = HrmSetting::first();
        if (!$setting || !$setting->fcm_server_key) {
            return;
        }

        if (method_exists($notification, 'toFirebase')) {
            $data = $notification->toFirebase($notifiable);

            // Get tokens
            $tokens = [];
            
            if ($setting->is_browser_notification_enabled) {
                $webTokens = $notifiable->deviceTokens()->where('device_type', 'web')->pluck('token')->toArray();
                $tokens = array_merge($tokens, $webTokens);
            }
            if ($setting->is_push_notification_enabled) {
                $appTokens = $notifiable->deviceTokens()->where('device_type', 'android')->pluck('token')->toArray();
                $tokens = array_merge($tokens, $appTokens);
            }

            if (empty($tokens)) {
                return;
            }

            $jsonContent = trim($setting->fcm_server_key);
            if (!str_starts_with($jsonContent, '{')) {
                Log::error("FCM Send Error: 'Secret Server Key' must contain the full Service Account JSON for HTTP v1 API.");
                return;
            }

            try {
                // Generate OAuth2 token using google/auth
                $credentials = new \Google\Auth\Credentials\ServiceAccountCredentials(
                    'https://www.googleapis.com/auth/firebase.messaging',
                    json_decode($jsonContent, true)
                );
                
                $tokenArray = $credentials->fetchAuthToken();
                $accessToken = $tokenArray['access_token'] ?? null;
                
                if (!$accessToken) {
                    Log::error("FCM Send Error: Could not generate access token from the provided JSON.");
                    return;
                }
                
                $projectId = json_decode($jsonContent, true)['project_id'] ?? $setting->fcm_project_id;
                $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

                foreach ($tokens as $token) {
                    $payload = [
                        'message' => [
                            'token' => $token,
                            'notification' => [
                                'title' => $data['title'] ?? 'Notification',
                                'body' => $data['body'] ?? '',
                            ],
                            'android' => [
                                'priority' => 'HIGH',
                                'notification' => [
                                    'sound' => 'default',
                                    'channel_id' => 'high_importance_channel'
                                ]
                            ],
                            'webpush' => [
                                'notification' => [
                                    'icon' => asset('themes/backend/dist/img/AdminLTELogo.png')
                                ],
                                'fcm_options' => [
                                    'link' => $data['data']['url'] ?? url('/')
                                ]
                            ],
                            'data' => $data['data'] ?? [],
                        ]
                    ];

                    $response = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $accessToken,
                        'Content-Type' => 'application/json',
                    ])->post($url, $payload);
                    
                    if ($response->failed()) {
                        Log::error("FCM V1 Send Error for token {$token}: " . $response->body());
                    }
                }
            } catch (\Throwable $e) {
                Log::error("FCM V1 Exception: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine());
            }
        }
    }
}
