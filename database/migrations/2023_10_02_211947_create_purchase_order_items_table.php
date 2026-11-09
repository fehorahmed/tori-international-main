<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sister_concern_id')->index();
            $table->unsignedBigInteger('purchase_order_id')->index();
            $table->unsignedBigInteger('product_id')->index();
            $table->string('product_code')->nullable();
            $table->string('batch_no')->nullable();
            $table->double('quantity', 8, 2);
            $table->double('received_quantity', 100, 2)->default(0);
            $table->double('decline_quantity', 100, 2)->default(0);
            $table->double('return_quantity', 100, 2)->default(0);
            $table->double('unit_price', 50, 2)->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
    }
};
