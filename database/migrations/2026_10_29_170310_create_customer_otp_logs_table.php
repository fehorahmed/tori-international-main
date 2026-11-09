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
        Schema::create('customer_otp_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->nullable()->comment('Reference to suppliers.id');
            $table->string('mobile_no', 20)->index();
            $table->string('otp', 10);
            $table->string('purpose', 50)->default('login')->comment('login, set_pin, forgot_pin, transaction');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->tinyInteger('attempts')->default(0)->comment('Failed verification attempts');
            $table->boolean('is_verified')->default(0)->comment('1=Successfully verified');
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('suppliers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_otp_logs');
    }
};

