<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('hrm_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('notification_interval_minutes')->default(60)->comment('How many minutes between reminders');
            $table->integer('response_window_minutes')->default(5)->comment('How many minutes the employee has to respond');
            $table->boolean('is_web_notification_enabled')->default(true)->comment('Toggle for in-app web notifications (Bell icon)');
            $table->boolean('is_browser_notification_enabled')->default(true)->comment('Toggle for native browser push notifications');
            $table->boolean('is_push_notification_enabled')->default(true)->comment('Toggle for android app push notifications');
            
            // Firebase Credentials
            $table->string('fcm_api_key')->nullable();
            $table->string('fcm_auth_domain')->nullable();
            $table->string('fcm_project_id')->nullable();
            $table->string('fcm_storage_bucket')->nullable();
            $table->string('fcm_messaging_sender_id')->nullable();
            $table->string('fcm_app_id')->nullable();
            $table->string('fcm_measurement_id')->nullable();
            $table->text('fcm_server_key')->nullable()->comment('Secret Server Key for backend push');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hrm_settings');
    }
};
