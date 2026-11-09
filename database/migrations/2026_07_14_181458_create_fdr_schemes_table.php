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
        Schema::create('fdr_schemes', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->integer('duration')->comment('Duration in months');
            $table->decimal('interest_rate', 5, 2)->comment('Interest rate in %');
            $table->decimal('penalty_rate', 5, 2)->nullable()->comment('Penalty rate in % if withdrawn early');
            $table->decimal('min_amount', 15, 2)->default(0);
            $table->decimal('max_amount', 15, 2)->nullable();
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fdr_schemes');
    }
};
