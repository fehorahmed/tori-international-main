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
        Schema::create('loan_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('origin_sister_concern_id')->nullable();
            $table->string('loan_no')->unique();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('sale_order_id')->nullable();
            $table->enum('type', ['cash', 'product']);
            $table->decimal('principal_amount', 15, 2);
            $table->decimal('profit_rate', 5, 2)->default(0);
            $table->decimal('total_payable', 15, 2);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->enum('installment_type', ['daily', 'weekly', 'monthly']);
            $table->integer('total_installments');
            $table->decimal('installment_amount', 15, 2);
            $table->date('disbursement_date')->nullable();
            $table->enum('status', ['pending', 'active', 'completed', 'defaulted'])->default('pending');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_accounts');
    }
};
