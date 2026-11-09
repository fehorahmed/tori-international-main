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
        Schema::create('purchase_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sister_concern_id')->constrained();
            $table->foreignId('supplier_id')->constrained('suppliers');
            $table->foreignId('purchase_order_id')->nullable()->constrained('purchase_orders');
            $table->foreignId('return_warehouse_id')->constrained('warehouses');
            $table->string('return_no')->unique();
            $table->date('date');
            
            $table->decimal('total_return_value', 12, 2)->default(0);
            
            $table->tinyInteger('status')->default(1); // 1 = Pending, 2 = Sent to Supplier, 3 = Replaced by Supplier
            $table->foreignId('return_voucher_id')->nullable()->constrained('vouchers')->nullOnDelete();
            $table->foreignId('replacement_voucher_id')->nullable()->constrained('vouchers')->nullOnDelete();
            $table->foreignId('create_user_id')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_returns');
    }
};
