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
        Schema::create('fdr_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fdr_account_id');
            $table->unsignedBigInteger('sister_concern_id')->nullable();
            $table->unsignedBigInteger('voucher_id')->nullable()->comment('Linked to Accounts Voucher');
            $table->enum('type', ['Deposit', 'Interest Payout', 'Encashment', 'Tax Deduction', 'Penalty']);
            $table->decimal('amount', 12, 2);
            $table->date('date');
            $table->text('note')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fdr_transactions');
    }
};
