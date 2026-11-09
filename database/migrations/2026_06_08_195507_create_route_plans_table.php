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
        Schema::create('route_plans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sister_concern_id')->nullable();
            $table->unsignedBigInteger('sr_id');
            $table->unsignedBigInteger('market_id');
            $table->string('day_of_week'); // e.g. Saturday, Sunday...
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('route_plans');
    }
};
