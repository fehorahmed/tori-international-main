<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('sister_concern_id')->nullable()->index();
            $table->unsignedBigInteger('employee_id')->nullable()->index();
            $table->string('name');
            $table->string('username')->unique()->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('mobile_no')->unique()->nullable();
            $table->string('role');
            $table->string('profile_photo')->nullable();
            $table->string('signature_photo')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->boolean('status')->default(1);
            $table->boolean('is_tracking_enabled')->default(1);
            $table->boolean('theme_mode')->default(1);
            $table->rememberToken();
            $table->unsignedBigInteger('supplier_id')->nullable()->comment('Refer Supplier table SR, Supplier, Customer, Area Manager, Branch Manager data');
            $table->decimal('latest_latitude', 10, 8)->default(0);
            $table->decimal('latest_longitude', 11, 8)->default(0);
            $table->tinyInteger('is_online')->default(0);
            $table->string('otp')->nullable();
            $table->timestamp('otp_expires_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
