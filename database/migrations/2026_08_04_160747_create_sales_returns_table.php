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
        Schema::create('sales_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sister_concern_id')->constrained();
            $table->foreignId('customer_id')->constrained('suppliers');
            $table->foreignId('sale_order_id')->nullable()->constrained();
            $table->foreignId('return_warehouse_id')->constrained('warehouses');
            $table->string('return_no')->unique();
            $table->date('date');
            
            $table->decimal('total_return_amount', 12, 2)->default(0); 
            $table->decimal('return_deduction', 12, 2)->default(0);
            $table->decimal('total_replacement_amount', 12, 2)->default(0);
            $table->decimal('net_payable_or_receivable', 12, 2)->default(0);
            
            $table->tinyInteger('status')->default(1); // 1 = Pending, 2 = Approved
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
        Schema::dropIfExists('sales_returns');
    }
};
