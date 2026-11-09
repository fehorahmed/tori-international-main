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
        Schema::create('customer_devices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->comment('Reference to suppliers.id');
            $table->string('device_id')->index()->comment('Unique device UUID/Hardware ID');
            $table->string('device_name')->nullable()->comment('e.g. Samsung S23, iPhone 15');
            $table->string('platform')->nullable()->comment('android, ios');
            $table->text('fcm_token')->nullable()->comment('Firebase Push Notification Token');
            $table->boolean('is_biometric_registered')->default(0)->comment('1=Biometric active on this device');
            $table->timestamp('last_active_at')->nullable();
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('suppliers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_devices');
    }
};

