<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('salary_process_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sister_concern_id');
            $table->unsignedBigInteger('salary_process_id');
            $table->integer('year');
            $table->integer('month');
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('jv_voucher_id')->nullable();
            $table->unsignedBigInteger('voucher_id')->nullable();
            $table->float('present')->default(0);
            $table->float('absent')->default(0);
            $table->float('late')->default(0);
            $table->float('no_checkout')->default(0);
            $table->float('early_checkout')->default(0);
            $table->float('late_early_checkout')->default(0);
            $table->float('public_holiday')->default(0);
            $table->float('weekend_holiday')->default(0);
            $table->float('leave')->default(0);
            $table->float('overtime')->default(0);
            $table->float('movement')->default(0);
            $table->float('basic_payable')->default(0);
            $table->float('house_rent')->default(0);
            $table->float('medical')->default(0);
            $table->float('conveyance')->default(0);
            $table->float('pf_deduction')->default(0);
            $table->float('tax_deduction')->default(0);
            $table->float('overtime_second')->default(0);
            $table->float('overtime_allowance')->default(0);
            $table->float('fair_allowance')->default(0); 
            $table->float('ta_da_allowance')->default(0); 
            $table->float('base_gross')->default(0);
            $table->float('gross')->default(0);
            $table->float('absent_deduction')->default(0);
            $table->float('late_deduction')->default(0);
            $table->float('other_deduction')->default(0);
            $table->float('penalty')->default(0);
            $table->float('income_tax_deduction')->default(0);
            $table->float('advance')->default(0);
            $table->float('total_deduction')->default(0);
            $table->float('net_pay')->default(0);
            $table->string('bank_account_no')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('salary_process_items'); }
};
