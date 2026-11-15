<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('membership_registration_requests', function (Blueprint $table) {
            $table->string('referral_type', 16)->nullable();
            $table->unsignedBigInteger('referrer_id')->nullable();
        });
        Schema::create('membership_referrals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->unique();
            $table->string('referrer_type', 16);
            $table->unsignedBigInteger('referrer_id');
            $table->string('referral_code', 64);
            $table->timestamps();
            $table->index(['referrer_type', 'referrer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membership_referrals');
        Schema::table('membership_registration_requests', fn (Blueprint $table) => $table->dropColumn(['referral_type', 'referrer_id']));
    }
};
