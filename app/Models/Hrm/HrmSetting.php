<?php

namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrmSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'notification_interval_minutes',
        'response_window_minutes',
        'is_web_notification_enabled',
        'is_browser_notification_enabled',
        'is_push_notification_enabled',
        'fcm_api_key',
        'fcm_auth_domain',
        'fcm_project_id',
        'fcm_storage_bucket',
        'fcm_messaging_sender_id',
        'fcm_app_id',
        'fcm_measurement_id',
        'fcm_server_key',
    ];
}
