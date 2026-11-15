<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('membership_referral_rewards', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('referral_id')->unique();
            $t->unsignedBigInteger('registration_fee_id')->unique();
            $t->string('referrer_type', 16);
            $t->unsignedBigInteger('referrer_id');
            $t->unsignedBigInteger('branch_id');
            $t->unsignedInteger('points')->default(0);
            $t->decimal('amount', 15, 2)->default(0);
            $t->unsignedBigInteger('payable_head_id')->nullable();
            $t->unsignedBigInteger('voucher_id')->nullable()->unique();
            $t->unsignedBigInteger('payment_voucher_id')->nullable()->unique();
            $t->unsignedBigInteger('paid_by')->nullable();
            $t->timestamp('paid_at')->nullable();
            $t->timestamps();
            $t->index(['referrer_type', 'referrer_id']);
        });
        Schema::create('membership_point_redemptions', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('customer_id');
            $t->uuid('request_key');
            $t->unsignedInteger('points');
            $t->decimal('point_value', 15, 2);
            $t->decimal('amount', 15, 2);
            $t->unsignedBigInteger('account_id');
            $t->unsignedBigInteger('voucher_id')->unique();
            $t->timestamps();
            $t->unique(['customer_id', 'request_key']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('membership_point_redemptions');
        Schema::dropIfExists('membership_referral_rewards');
    }
};
