<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('membership_registration_requests', function (Blueprint $table) {
            $table->id();
            $table->string('mobile_no', 20)->unique();
            $table->string('session_hash', 64)->unique();
            $table->string('device_id', 128);
            $table->string('device_name')->nullable();
            $table->string('platform', 32)->nullable();
            $table->unsignedBigInteger('branch_id');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('website_password');
            $table->string('referral_code', 64)->nullable();
            $table->timestamp('terms_accepted_at');
            $table->string('otp_hash')->nullable();
            $table->unsignedTinyInteger('otp_attempts')->default(0);
            $table->timestamp('otp_expires_at')->nullable();
            $table->timestamp('otp_sent_at');
            $table->string('verification_hash', 64)->nullable();
            $table->timestamp('verified_until')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable()->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membership_registration_requests');
    }
};
