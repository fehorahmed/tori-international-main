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
        Schema::table('sale_payments', function (Blueprint $table) {
            $table->unsignedBigInteger('sale_order_id')->nullable()->change();
            // Let's also update comment of type
            $table->tinyInteger('type')->comment('1=Order Payment, 2=Due Payment, 3=Opening Due Payment, 4=Advance Payment')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_payments', function (Blueprint $table) {
            //
        });
    }
};
