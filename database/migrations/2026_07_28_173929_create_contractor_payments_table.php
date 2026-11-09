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
        Schema::create('contractor_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contractor_id');
            $table->unsignedBigInteger('sister_concern_id')->nullable();
            $table->unsignedBigInteger('voucher_id')->nullable();
            $table->date('payment_date');
            $table->string('receipt_no')->unique();
            $table->decimal('amount', 15, 2)->default(0.00);
            $table->tinyInteger('payment_type')->default(1)->comment('1: Payment, 2: Advance');
            $table->unsignedBigInteger('payment_method_id')->nullable();
            $table->text('note')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            
            $table->foreign('contractor_id')->references('id')->on('contractors')->onDelete('cascade');
            $table->foreign('payment_method_id')->references('id')->on('account_heads')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contractor_payments');
    }
};
