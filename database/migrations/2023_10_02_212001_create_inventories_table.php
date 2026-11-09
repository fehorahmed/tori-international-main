<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sister_concern_id')->index();
            $table->boolean('inventory_type')->default(1)->comment('0=Raw Inventory,1=Finished Goods');
            $table->unsignedBigInteger('warehouse_id')->default(1)->index();
            $table->unsignedBigInteger('product_id')->index();
            $table->string('product_code')->nullable();
            $table->string('batch_no')->nullable();
            $table->double('quantity', 20, 2)->default(0);
            $table->double('unit_price', 20, 2)->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('inventories'); }
};
