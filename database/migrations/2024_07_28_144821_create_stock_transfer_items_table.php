<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('stock_transfer_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sister_concern_id');
            $table->unsignedBigInteger('stock_transfer_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('inventory_id');
            $table->string('product_code')->nullable();
            $table->double('quantity', 8, 2);
            $table->double('received_quantity', 8, 2)->default(0)->comment('Actual quantity received by receiver');
            $table->double('return_quantity', 8, 2)->default(0)->comment('Quantity returned back to sender (shortage)');
            $table->double('unit_price', 8, 2);
            $table->double('transfer_price', 8, 2)->nullable();
            $table->string('status')->default('pending')->comment('pending, partially_received, received, cancelled');
            $table->unsignedBigInteger('create_user_id')->nullable();
            $table->unsignedBigInteger('alter_user_id')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('stock_transfer_items'); }
};
