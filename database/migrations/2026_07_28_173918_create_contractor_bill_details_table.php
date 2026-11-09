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
        Schema::create('contractor_bill_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contractor_bill_id');
            $table->unsignedBigInteger('expense_head_id');
            $table->decimal('amount', 15, 2)->default(0.00);
            $table->string('remarks')->nullable();
            $table->timestamps();
            
            $table->foreign('contractor_bill_id')->references('id')->on('contractor_bills')->onDelete('cascade');
            $table->foreign('expense_head_id')->references('id')->on('account_heads')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contractor_bill_details');
    }
};
