<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sister_concern_id')->nullable()->index();
            $table->date('date')->index();
            $table->unsignedBigInteger('payment_type_id')->nullable();
            $table->unsignedBigInteger('payment_account_head_id')->nullable();
            $table->string('cheque_no')->nullable();
            $table->bigInteger('voucher_no_group_sl');
            $table->string('voucher_no')->index();
            $table->string('rm_no')->nullable();
            $table->tinyInteger('voucher_type')->comment('1=Journal Voucher,2=Payment Voucher,3=Collection Voucher')->index();
            $table->double('amount', 100, 2);
            $table->double('bank_commission', 10, 2)->default(0);
            $table->unsignedBigInteger('account_head_payee_depositor_id')->nullable();
            $table->unsignedBigInteger('purchase_order_id')->nullable();
            $table->integer('manufacture_finished_goods_id')->nullable();
            $table->bigInteger('sale_order_id')->nullable();
            $table->integer('inventory_log_id')->nullable();
            $table->unsignedBigInteger('stock_transfer_id')->nullable();
            $table->unsignedBigInteger('stock_conversion_id')->nullable();
            $table->bigInteger('product_utilize_id')->nullable();
            $table->bigInteger('service_id')->nullable();
            $table->unsignedBigInteger('company_loan_id')->nullable(); 
            $table->text('notes')->nullable();
            $table->integer('collection_receive_status')->default(0);
            $table->boolean('due_payment')->default(0);
            $table->tinyInteger('payment_purpose')->nullable()->index()->comment('See App\\Enumeration\\PaymentPurpose');
            $table->boolean('is_show')->default(1);
            $table->boolean('is_opening_balance')->default(0)->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
