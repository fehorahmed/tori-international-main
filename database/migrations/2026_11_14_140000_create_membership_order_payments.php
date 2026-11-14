<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('membership_order_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('checkout_id')->unique();
            $table->unsignedBigInteger('customer_id')->index();
            $table->unsignedBigInteger('account_id');
            $table->decimal('amount', 15, 2);
            $table->json('voucher_ids');
            $table->unsignedBigInteger('settlement_id')->nullable();
            $table->json('refund_voucher_ids')->nullable();
            $table->string('status')->default('Paid');
            $table->timestamp('refunded_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membership_order_payments');
    }
};
