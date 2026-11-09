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
        // Consolidated Savings & Investment Products
        Schema::create('mf_savings_products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->tinyInteger('product_type')->comment('1=General Savings, 2=DPS, 3=FDR');
            $table->tinyInteger('installment_frequency')->nullable()->comment('1=Daily, 2=Weekly, 3=Monthly');
            $table->enum('installment_fine_type', ['flat', 'percentage'])->default('flat');
            $table->decimal('installment_fine_amount', 12, 2)->default(0);
            $table->enum('early_closing_fine_type', ['flat', 'percentage'])->default('flat');
            $table->decimal('early_closing_fine_amount', 12, 2)->default(0);
            $table->decimal('closing_fee', 12, 2)->default(0);
            $table->decimal('interest_rate', 5, 2)->default(0);
            $table->integer('duration_months')->nullable()->comment('For DPS/FDR');
            $table->decimal('installment_amount', 12, 2)->nullable()->comment('For DPS');
            $table->decimal('opening_fee', 12, 2)->default(0);
            $table->decimal('min_deposit', 12, 2)->default(0);
            $table->decimal('max_deposit', 12, 2)->nullable();
            $table->tinyInteger('interest_posting_type')->nullable()->comment('1=At Maturity, 2=Monthly, 3=Quarterly, 4=Yearly');
            $table->boolean('status')->default(1);
            $table->timestamps();
        });

        // Consolidated Savings & Investment Accounts
        Schema::create('mf_savings_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sister_concern_id');
            $table->unsignedBigInteger('somiti_id')->nullable();
            $table->unsignedBigInteger('member_id');
            $table->string('account_no')->unique();
            $table->string('account_title')->nullable();
            $table->unsignedBigInteger('mf_savings_product_id');
            
            // Fixed Terms at the time of opening
            $table->decimal('installment_amount', 12, 2)->nullable();
            $table->tinyInteger('installment_frequency')->nullable();
            $table->integer('duration_months')->nullable();
            $table->decimal('interest_rate', 5, 2)->default(0);
            
            $table->decimal('opening_balance', 12, 2)->default(0);
            $table->decimal('current_balance', 12, 2)->default(0);
            $table->decimal('accrued_interest', 12, 4)->default(0.0000)->comment('Interest earned but not yet posted to current_balance');
            $table->date('opening_date');
            $table->date('maturity_date')->nullable();
            
            $table->unsignedBigInteger('introducer_id')->nullable();
            $table->boolean('auto_renewal')->default(false);
            
            $table->tinyInteger('status')->default(0)->comment('0=Pending, 1=Active, 2=Matured, 3=Closed');
            $table->date('closed_date')->nullable();
            $table->text('close_reason')->nullable();

            // Opening Balance Migration Fields
            $table->tinyInteger('is_opening_entry')->default(0)->comment('1=Migrated from old software as Opening Balance');
            $table->date('migration_date')->nullable()->comment('Business date when opening entry was done (Go-Live Date)');
            $table->unsignedInteger('installments_paid')->default(0)->comment('For DPS: installments already paid in old software');
            $table->decimal('opening_accrued_interest', 12, 2)->default(0)->comment('For FDR: interest accrued up to migration_date in old software');

            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('sister_concern_id')->references('id')->on('sister_concerns')->onDelete('cascade');
            $table->foreign('somiti_id')->references('id')->on('mf_somitis')->onDelete('set null');
            $table->foreign('member_id')->references('id')->on('mf_members')->onDelete('cascade');
            $table->foreign('introducer_id')->references('id')->on('mf_members')->onDelete('set null');
            $table->foreign('mf_savings_product_id')->references('id')->on('mf_savings_products')->onDelete('cascade');
        });

        // Unified Savings Transactions (Deposit/Withdraw/Interest)
        Schema::create('mf_savings_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sister_concern_id');
            $table->unsignedBigInteger('somiti_id')->nullable();
            $table->unsignedBigInteger('mf_savings_account_id');
            $table->tinyInteger('transaction_type')->comment('1=Deposit, 2=Withdraw, 3=Interest, 4=Penalty, 5=Opening');
            $table->decimal('amount', 12, 2);
            $table->date('date');
            $table->string('receipt_no')->nullable();
            $table->string('voucher_no')->nullable();
            $table->tinyInteger('payment_method')->default(1)->comment('1=Cash, 2=Bank, 3=Transfer');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('sister_concern_id')->references('id')->on('sister_concerns')->onDelete('cascade');
            $table->foreign('mf_savings_account_id')->references('id')->on('mf_savings_accounts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mf_savings_transactions');
        Schema::dropIfExists('mf_savings_accounts');
        Schema::dropIfExists('mf_savings_products');
    }
};
