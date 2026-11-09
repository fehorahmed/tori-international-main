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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sister_concern_id');
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('sale_order_id');
            $table->string('sale_type');
            $table->unsignedBigInteger('technician_id');
            $table->string('service_no')->unique();
            $table->date('date');
            $table->tinyInteger('status')->default(1)->comment('1=Pending, 2=In Progress, 3=Waiting for Approval, 4=Completed, 5=Delivered');
            $table->decimal('service_charge', 15, 2)->default(0);
            $table->unsignedBigInteger('payment_account_head_id')->nullable();
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('service_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('sale_order_item_id');
            $table->text('cause')->nullable();
            $table->boolean('warranty_status')->default(1);
            $table->timestamps();
        });

        Schema::create('service_utilized_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('inventory_id');
            $table->decimal('quantity', 15, 2);
            $table->decimal('unit_price', 15, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_utilized_items');
        Schema::dropIfExists('service_items');
        Schema::dropIfExists('services');
    }
};
