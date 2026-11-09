<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('sale_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sister_concern_id')->index();
            $table->unsignedBigInteger('area_manager_id')->nullable();
            $table->unsignedBigInteger('branch_manager_id')->nullable();
            $table->unsignedBigInteger('sr_id')->nullable()->comment('Refer Supplier table type 3 data');
            $table->unsignedBigInteger('customer_id')->nullable()->comment('Refer Supplier table type 2 data')->index();
            $table->integer('warehouse_id')->nullable()->index();
            $table->string('order_no')->nullable();
            $table->string('memo_no')->nullable();
            $table->tinyInteger('type')->default(1)->comment('1=General, 2=EMI, 3=Pre-Order, 4=Corporate, 5=Dealer, 6=Ecommerce');
            $table->unsignedBigInteger('employee_id')->nullable()->comment('sales reference');
            $table->string('referral_code')->nullable()->comment('Employee Referral Code');
            $table->double('sub_total')->default(0);
            $table->float('vat_percent')->default(0);
            $table->float('vat_amount')->default(0);
            $table->double('total')->default(0);
            $table->float('discount_percent')->default(0);
            $table->tinyInteger('discount_type')->default(2);
            $table->float('discount')->default(0);
            $table->double('paid')->default(0);
            $table->double('advance_adjust')->default(0);
            $table->double('due')->default(0);
            $table->text('notes')->nullable();
            $table->date('date')->index();
            $table->tinyInteger('is_pos')->default(0);
            $table->tinyInteger('is_delivered')->default(1);
            $table->tinyInteger('status')->default(3)->comment('1=pending, 2=processing, 3=completed, 4=cancelled');
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('alter_user_id')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('sale_orders'); }
};
