<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emi_sale_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sale_order_id')->unique();
            $table->string('customer_nid_attachment')->nullable();
            $table->string('customer_photo_attachment')->nullable();
            $table->string('guarantor_name')->nullable();
            $table->string('guarantor_phone')->nullable();
            $table->string('guarantor_nid')->nullable();
            $table->string('guarantor_nid_attachment')->nullable();
            $table->string('security_cheque_attachment')->nullable();
            $table->string('agreement_attachment')->nullable();
            $table->tinyInteger('late_fee_type')->default(1)->comment('1=Fixed, 2=Percentage');
            $table->double('late_fee_rate', 15, 2)->default(10.00)->comment('Fixed BDT or Percentage rate per day');
            $table->timestamps();

            $table->foreign('sale_order_id')
                ->references('id')
                ->on('sale_orders')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emi_sale_details');
    }
};
