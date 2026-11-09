<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dps_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('dps_no')->unique()->comment('Auto-generated: DPS-YYYYMMDD-XXXX');
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('scheme_id');
            $table->unsignedBigInteger('origin_sister_concern_id')->nullable();

            // Scheme snapshot (copied at creation so scheme changes don't affect active accounts)
            $table->enum('installment_type', ['Daily', 'Weekly', 'Monthly'])->default('Monthly')->comment('Installment frequency, copied from scheme');
            $table->decimal('installment_amount', 15, 2)->comment('Installment amount, fixed at account opening');
            $table->integer('total_installments')->comment('Total number of installments');
            $table->decimal('interest_rate', 5, 2)->comment('Annual interest rate, fixed at account opening');

            // Running counters
            $table->integer('paid_installments')->default(0);
            $table->integer('missed_installments')->default(0);
            $table->decimal('total_deposited', 15, 2)->default(0)->comment('Sum of all paid installment amounts');
            $table->decimal('total_penalty_charged', 15, 2)->default(0)->comment('Total penalty charged so far');

            // Dates
            $table->date('opening_date');
            $table->date('maturity_date')->comment('Auto: opening_date + duration_months');
            $table->date('next_installment_date')->nullable()->comment('Date by which next installment must be paid');

            // Pre-calculated maturity info (at account opening)
            $table->decimal('payable_at_maturity', 15, 2)->default(0)->comment('Total maturity amount = principal + interest');

            // Settings
            $table->boolean('auto_renew')->default(false);

            // Status & Audit
            $table->enum('status', ['Pending', 'Active', 'Matured', 'Closed', 'Defaulted'])->default('Pending');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dps_accounts');
    }
};
