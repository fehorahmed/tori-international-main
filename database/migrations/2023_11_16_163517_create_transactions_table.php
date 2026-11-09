<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sister_concern_id')->nullable()->index();
            $table->boolean('other_deduct')->default(0);
            $table->integer('parent_account_head_id')->nullable();
            $table->boolean('except_edit')->default(0);
            $table->date('date')->index();
            $table->integer('voucher_id')->index();
            $table->bigInteger('payment_type_id')->nullable();
            $table->unsignedBigInteger('payment_account_head_id')->nullable();
            $table->string('cheque_no')->nullable();
            $table->integer('source_account_head_id')->nullable();
            $table->integer('target_account_head_id')->nullable();
            $table->bigInteger('account_head_id')->index();
            $table->bigInteger('voucher_no_group_sl');
            $table->string('voucher_no')->index();
            $table->tinyInteger('voucher_type')->comment('1=Journal Voucher,2=Payment Voucher,3=Collection Voucher,4=Contra Voucher');
            $table->tinyInteger('transaction_type')->comment('1=debit,2=credit')->index();
            $table->double('amount', 20, 2);
            $table->double('bank_commission', 10, 2)->default(0);
            $table->unsignedBigInteger('account_head_payee_depositor_id')->nullable();
            $table->unsignedBigInteger('purchase_order_id')->nullable();
            $table->bigInteger('manufacture_finished_goods_id')->nullable();
            $table->bigInteger('sale_order_id')->nullable();
            $table->unsignedBigInteger('stock_transfer_id')->nullable();
            $table->unsignedBigInteger('stock_conversion_id')->nullable();
            $table->bigInteger('product_utilize_id')->nullable();
            $table->bigInteger('service_id')->nullable();
            $table->unsignedBigInteger('company_loan_id')->nullable();
            $table->text('notes')->nullable();
            $table->tinyInteger('payment_purpose')->nullable()->index()->comment('See App\\Enumeration\\PaymentPurpose');
            $table->boolean('is_show')->default(1);
            $table->boolean('is_opening_balance')->default(0)->index();
            $table->bigInteger('user_id')->index();
            $table->unsignedBigInteger('employee_id')->nullable()->index();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
