<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('inventory_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sister_concern_id')->index();
            $table->string('batch_no')->nullable();
            $table->integer('type')->comment('1=purchase,2=sale');
            $table->unsignedBigInteger('warehouse_id')->default(1)->index();
            $table->unsignedBigInteger('inventory_id');
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->unsignedBigInteger('purchase_order_id')->nullable();
            $table->unsignedBigInteger('manufacture_finished_goods_id')->nullable();
            $table->unsignedBigInteger('manufacture_finished_goods_raw_material_item_id')->nullable();
            $table->unsignedBigInteger('sale_order_id')->nullable();
            $table->unsignedBigInteger('sales_return_id')->nullable();
            $table->unsignedBigInteger('purchase_return_id')->nullable();
            $table->unsignedBigInteger('stock_transfer_id')->nullable();
            $table->unsignedBigInteger('stock_transfer_item_id')->nullable();
            $table->unsignedBigInteger('stock_conversion_id')->nullable();
            $table->unsignedBigInteger('product_utilize_id')->nullable();
            $table->unsignedBigInteger('product_id')->index();
            $table->string('product_code')->nullable();
            $table->float('quantity');
            $table->float('unit_price', 50)->default(0);
            $table->text('notes')->nullable();
            $table->date('date')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->softDeletes();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('inventory_logs'); }
};
