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
        Schema::create('stock_conversion_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('stock_conversion_id');
            $table->unsignedBigInteger('from_product_id');
            $table->unsignedBigInteger('from_inventory_id');
            $table->double('quantity', 10, 2);
            $table->double('from_unit_price', 10, 2);
            $table->unsignedBigInteger('to_product_id');
            $table->double('unit_conversion_cost', 10, 2)->default(0);
            $table->double('to_unit_price', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_conversion_items');
    }
};
