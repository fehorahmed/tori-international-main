<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sister_concern_id')->index();
            $table->unsignedBigInteger('sale_order_id')->index();
            $table->date('date');
            $table->unsignedBigInteger('product_id')->index();
            $table->unsignedBigInteger('inventory_id')->nullable();
            $table->string('product_code')->nullable();
            $table->double('quantity', 8, 2)->default(0);
            $table->double('return_quantity', 20, 2)->default(0);
            $table->double('unit_price', 50, 2);
            $table->double('selling_price', 50, 2);
            $table->double('interest_rate', 20, 2)->default(0);
            $table->double('interest_amount', 20, 2)->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_order_items');
    }
};
