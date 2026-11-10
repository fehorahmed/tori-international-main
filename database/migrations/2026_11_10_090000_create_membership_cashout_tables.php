<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('membership_cashout_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('enabled')->default(false);
            $table->decimal('maximum_amount', 12, 2)->default(0);
            $table->string('charge_type')->default('fixed');
            $table->decimal('charge_rate', 12, 2)->default(0);
            $table->unsignedInteger('hold_minutes')->default(10);
            $table->unsignedBigInteger('income_head_id')->nullable();
            $table->timestamps();
        });
        Schema::create('membership_cashout_booths', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id')->unique();
            $table->string('qr_token', 64)->unique();
            $table->unsignedBigInteger('cash_head_id');
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });
        Schema::create('membership_cashouts', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 40)->unique();
            $table->unsignedBigInteger('customer_id')->index();
            $table->unsignedBigInteger('account_id')->index();
            $table->unsignedBigInteger('branch_id')->index();
            $table->unsignedBigInteger('cash_head_id');
            $table->unsignedBigInteger('income_head_id')->nullable();
            $table->string('idempotency_key', 64);
            $table->string('quote_hash', 64);
            $table->string('status')->default('Pending')->index();
            $table->decimal('amount', 12, 2);
            $table->decimal('fee', 12, 2);
            $table->decimal('total', 12, 2);
            $table->string('charge_type');
            $table->decimal('charge_rate', 12, 2);
            $table->timestamp('expires_at');
            $table->unsignedBigInteger('accepted_by')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->unsignedBigInteger('completed_by')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedBigInteger('closed_by')->nullable();
            $table->text('reason')->nullable();
            $table->unsignedBigInteger('voucher_id')->nullable();
            $table->unsignedBigInteger('fee_voucher_id')->nullable();
            $table->unsignedBigInteger('source_voucher_id')->nullable();
            $table->unsignedBigInteger('settlement_id')->nullable();
            $table->timestamps();
            $table->unique(['customer_id', 'idempotency_key']);
            $table->index(['account_id', 'status', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membership_cashouts');
        Schema::dropIfExists('membership_cashout_booths');
        Schema::dropIfExists('membership_cashout_settings');
    }
};
