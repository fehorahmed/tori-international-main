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
        Schema::create('loan_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('origin_sister_concern_id')->nullable();
            $table->unsignedBigInteger('loan_account_id');
            $table->unsignedBigInteger('loan_installment_id')->nullable();
            $table->unsignedBigInteger('voucher_id')->nullable();
            $table->decimal('amount', 15, 2);
            $table->date('collection_date');
            $table->string('payment_mode')->nullable();
            $table->unsignedBigInteger('collected_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->foreign('loan_account_id')->references('id')->on('loan_accounts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_transactions');
    }
};
