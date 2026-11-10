<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('membership_transfer_settings', function (Blueprint $t) {
            $t->id();
            $t->boolean('enabled')->default(false);
            $t->decimal('maximum_amount', 12, 2)->default(0);
            $t->string('charge_type')->default('fixed');
            $t->decimal('charge_rate', 12, 2)->default(0);
            $t->unsignedBigInteger('income_head_id')->nullable();
            $t->unsignedBigInteger('posting_user_id')->nullable();
            $t->timestamps();
        });
        Schema::create('membership_transfers', function (Blueprint $t) {
            $t->id();
            $t->string('reference')->unique();
            foreach (['sender_id', 'recipient_id', 'sender_account_id', 'recipient_account_id'] as $c) {
                $t->unsignedBigInteger($c)->index();
            }
            $t->uuid('idempotency_key');
            $t->string('quote_hash', 64);
            foreach (['amount', 'fee', 'total'] as $c) {
                $t->decimal($c, 12, 2);
            }
            foreach (['voucher_id', 'recipient_voucher_id', 'fee_voucher_id', 'settlement_id'] as $c) {
                $t->unsignedBigInteger($c)->nullable();
            }
            $t->timestamps();
            $t->unique(['sender_id', 'idempotency_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membership_transfers');
        Schema::dropIfExists('membership_transfer_settings');
    }
};
