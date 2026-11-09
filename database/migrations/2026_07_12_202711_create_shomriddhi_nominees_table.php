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
        Schema::create('shomriddhi_nominees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('nominee_id');
            $table->enum('account_type', ['Savings', 'DPS', 'FDR', 'Loan']);
            $table->unsignedBigInteger('account_id');
            $table->decimal('share_percentage', 5, 2)->default(100);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shomriddhi_nominees');
    }
};
