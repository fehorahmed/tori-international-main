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
        Schema::create('stock_conversions', function (Blueprint $table) {
            $table->id();
            $table->string('order_no');
            $table->unsignedBigInteger('sister_concern_id')->nullable();
            $table->unsignedBigInteger('warehouse_id');
            $table->date('date');
            $table->string('notes')->nullable();
            $table->tinyInteger('status')->default(1)->comment('1: Pending, 2: Approved, 3: Cancelled');
            $table->unsignedBigInteger('create_user_id')->nullable();
            $table->unsignedBigInteger('approved_user_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_conversions');
    }
};
