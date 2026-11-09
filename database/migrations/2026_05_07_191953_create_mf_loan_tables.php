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
        // Loan Products (Schemes)
        Schema::create('mf_loan_products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->decimal('interest_rate', 5, 2);
            $table->tinyInteger('interest_type')->default(1)->comment('1=Flat, 2=Reducing');
            $table->tinyInteger('installment_frequency')->comment('1=Daily, 2=Weekly, 3=Monthly');
            $table->decimal('insurance_fee_percentage', 5, 2)->default(0);
            $table->string('insurance_fee_type')->default('percentage');
            $table->string('late_penalty_type')->default('percentage');
            $table->decimal('late_penalty_rate', 12, 2)->default(0.00);
            $table->decimal('processing_fee_percentage', 5, 2)->default(0);
            $table->integer('grace_period_days')->default(0);
            $table->boolean('status')->default(1);
            $table->timestamps();
        });

        // Member Loans
        Schema::create('mf_loans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sister_concern_id');
            $table->unsignedBigInteger('somiti_id')->nullable();
            $table->unsignedBigInteger('member_id');
            $table->unsignedBigInteger('mf_loan_product_id');
            $table->date('application_date')->nullable();
            $table->string('loan_no')->unique();
            $table->string('loan_purpose')->nullable();
            $table->decimal('applied_amount', 12, 2);
            $table->decimal('approved_amount', 12, 2)->default(0);
            $table->decimal('disbursed_amount', 12, 2)->default(0);
            
            // Fees
            $table->decimal('insurance_amount', 12, 2)->default(0);
            $table->decimal('processing_fee', 12, 2)->default(0);
            $table->decimal('net_disbursed_amount', 12, 2)->default(0);
            
            $table->integer('total_installments');
            $table->integer('grace_period_days')->default(0);
            $table->date('disbursement_date')->nullable();
            $table->date('first_installment_date')->nullable();
            $table->tinyInteger('status')->default(1)->comment('1=Pending, 2=Approved, 3=Active, 4=Closed, 5=Rejected');
            
            // Guarantor Info
            $table->string('guarantor_name')->nullable();
            $table->string('guarantor_phone')->nullable();
            $table->string('guarantor_nid')->nullable();
            $table->string('guarantor_photo')->nullable();
            $table->text('collateral_description')->nullable();
            
            // Opening Balance Migration Fields
            $table->tinyInteger('is_opening_entry')->default(0)->comment('1=Migrated from old software as Opening Balance');
            $table->date('migration_date')->nullable()->comment('Business date when opening entry was done (Go-Live Date)');
            $table->unsignedInteger('installments_paid')->default(0)->comment('Number of installments already paid in old software');
            $table->decimal('outstanding_principal', 12, 2)->default(0)->comment('Remaining principal at migration_date from old software');

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('sister_concern_id')->references('id')->on('sister_concerns')->onDelete('cascade');
            $table->foreign('somiti_id')->references('id')->on('mf_somitis')->onDelete('set null');
            $table->foreign('member_id')->references('id')->on('mf_members')->onDelete('cascade');
            $table->foreign('mf_loan_product_id')->references('id')->on('mf_loan_products')->onDelete('cascade');
        });

        // Individual Installments (Schedule)
        Schema::create('mf_installments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sister_concern_id');
            $table->unsignedBigInteger('somiti_id')->nullable();
            $table->unsignedBigInteger('mf_loan_id');
            $table->integer('installment_no');
            $table->date('due_date');
            $table->decimal('principal_amount', 12, 2);
            $table->decimal('interest_amount', 12, 2);
            $table->decimal('total_due', 12, 2);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('penalty_amount', 12, 2)->default(0);
            $table->decimal('waived_amount', 12, 2)->default(0);
            $table->date('paid_date')->nullable();
            $table->tinyInteger('status')->default(1)->comment('1=Pending, 2=Paid, 3=Partial');
            $table->tinyInteger('is_opening_paid')->default(0)->comment('1=Paid in old software before migration');
            $table->timestamps();

            $table->foreign('sister_concern_id')->references('id')->on('sister_concerns')->onDelete('cascade');
            $table->foreign('mf_loan_id')->references('id')->on('mf_loans')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mf_installments');
        Schema::dropIfExists('mf_loans');
        Schema::dropIfExists('mf_loan_products');
    }
};
