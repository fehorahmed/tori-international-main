<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dps_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('dps_account_id');
            $table->integer('installment_no')->nullable()->comment('Which installment number this payment covers');
            $table->enum('type', [
                'Installment',          // Regular monthly payment
                'Penalty',              // Penalty charged for missed installment
                'Encashment',           // Normal maturity payout
                'Premature_Encashment', // Early withdrawal
            ]);
            $table->enum('status', ['Pending', 'Approved', 'Rejected'])->default('Approved');
            $table->decimal('amount', 15, 2);
            $table->decimal('penalty_amount', 15, 2)->default(0)->comment('Penalty amount charged (if any)');
            $table->unsignedBigInteger('payment_account_head_id')->nullable()->comment('Cash/Bank head used for payment');
            $table->unsignedBigInteger('voucher_id')->nullable();
            $table->date('date');
            $table->text('note')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dps_transactions');
    }
};
