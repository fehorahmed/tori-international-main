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
        Schema::create('company_loans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lender_concern_id');
            $table->unsignedBigInteger('borrower_concern_id');
            $table->decimal('amount', 18, 2);
            $table->decimal('interest_rate', 5, 2);
            $table->string('loan_type')->comment('Daily, Weekly, Monthly, Yearly');
            $table->integer('duration');
            $table->string('calculation_method')->comment('Flat, Reducing');
            $table->date('start_date');
            $table->tinyInteger('status')->default(1)->comment('1=Pending, 2=Approved, 3=Rejected, 4=Completed');
            $table->unsignedBigInteger('lender_account_head_id')->nullable();
            $table->unsignedBigInteger('borrower_account_head_id')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes — frequently used in WHERE clauses
            $table->index('lender_concern_id');
            $table->index('borrower_concern_id');
            $table->index('status');
            $table->index(['lender_concern_id', 'status']);
            $table->index(['borrower_concern_id', 'status']);
        });

        Schema::create('company_loan_installments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_loan_id');
            $table->integer('installment_no');
            $table->date('due_date');
            $table->decimal('total_amount', 18, 2);
            $table->decimal('principal_amount', 18, 2);
            $table->decimal('interest_amount', 18, 2);
            $table->decimal('paid_amount', 18, 2)->default(0);
            $table->date('payment_date')->nullable();
            $table->tinyInteger('status')->default(1)->comment('1=Unpaid, 2=Paid');
            $table->timestamps();
            $table->softDeletes();

            // Indexes — used in dashboard SUM queries, due installment reports, and payment checks
            $table->index('company_loan_id');
            $table->index('status');
            $table->index('due_date');
            $table->index(['company_loan_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_loan_installments');
        Schema::dropIfExists('company_loans');
    }
};
