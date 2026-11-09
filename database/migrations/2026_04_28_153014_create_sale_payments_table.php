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
        Schema::create('sale_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sister_concern_id');
            $table->unsignedBigInteger('area_manager_id')->nullable();
            $table->unsignedBigInteger('branch_manager_id')->nullable();
            $table->unsignedBigInteger('sr_id')->nullable()->comment('Refer Supplier table type 3 data');
            $table->unsignedBigInteger('customer_id')->nullable()->comment('Refer Supplier table type 2 data');
            $table->unsignedBigInteger('sale_order_id');
            $table->unsignedBigInteger('transaction_id')->nullable(); 
            $table->unsignedBigInteger('voucher_id')->nullable();
            $table->tinyInteger('type')->comment('1=Order Payment, 2=Due Payment');
            $table->double('amount', 20, 2);
            $table->date('date');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_payments');
    }
};
