<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fixed_asset_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');                              // "Furniture & Fixture"
            $table->enum('depreciation_method', ['SLM', 'WDV', 'none'])->default('SLM');
            $table->decimal('depreciation_rate', 8, 2)->nullable();   // % for WDV
            $table->integer('useful_life_years')->nullable();          // years for SLM
            $table->unsignedBigInteger('account_head_id')->nullable(); // FK → account_heads (auto-created)
            $table->unsignedBigInteger('accumulated_dep_head_id')->nullable(); // FK → account_heads (shared Acc. Dep.)
            $table->string('note')->nullable();
            $table->timestamps();

            $table->foreign('account_head_id')->references('id')->on('account_heads')->nullOnDelete();
            $table->foreign('accumulated_dep_head_id')->references('id')->on('account_heads')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fixed_asset_categories');
    }
};
