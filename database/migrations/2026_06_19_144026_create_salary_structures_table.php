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
        Schema::create('salary_structures', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->float('basic_percent')->default(60);
            $table->float('house_rent_percent')->default(30);
            $table->float('medical_percent')->default(10);
            $table->float('ta_da_fixed')->default(0); // Fixed amount instead of percent
            $table->float('pf_percent')->default(0); // Provident fund deduction percent
            $table->float('tax_percent')->default(0); // Tax deduction percent
            $table->tinyInteger('status')->default(1);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_structures');
    }
};
