<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sister_concern_id')->nullable()->index();
            $table->unsignedBigInteger('warehouse_id')->index();
            $table->string('order_no')->nullable();
            $table->unsignedBigInteger('supplier_id')->index();
            $table->double('sub_total', 100, 2)->default(0);
            $table->tinyInteger('discount_type')->default(2)->comment('1=Flat, 2=Percentage');
            $table->double('discount_percent', 10, 2)->default(0);
            $table->double('discount_amount', 50, 2)->default(0);
            $table->double('vat_percent', 10, 2)->default(0);
            $table->double('vat_amount', 50, 2)->default(0);
            $table->double('tax_percent', 10, 2)->default(0);
            $table->double('tax_amount', 50, 2)->default(0);
            $table->double('total', 100, 2);
            $table->double('advance_adjust', 100, 2)->default(0);
            $table->double('paid', 100, 2);
            $table->double('due', 100, 2);
            $table->text('notes')->nullable();
            $table->date('date')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('status')->default('pending');
            $table->softDeletes();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('purchase_orders'); }
};
