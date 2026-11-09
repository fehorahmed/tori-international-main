<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fixed_asset_depreciation_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fixed_asset_id');
            $table->unsignedBigInteger('sister_concern_id');
            $table->string('period', 7);                         // "2026-06" (YYYY-MM) — unique per asset
            $table->decimal('depreciation_amount', 15, 2);
            $table->decimal('book_value_before', 15, 2);
            $table->decimal('book_value_after', 15, 2);
            $table->unsignedBigInteger('voucher_id')->nullable();// FK → vouchers (auto JV)
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();

            $table->unique(['fixed_asset_id', 'period']);        // prevent duplicate depreciation for same period

            $table->foreign('fixed_asset_id')->references('id')->on('fixed_assets')->cascadeOnDelete();
            $table->foreign('sister_concern_id')->references('id')->on('sister_concerns');
            $table->foreign('voucher_id')->references('id')->on('vouchers')->nullOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fixed_asset_depreciation_logs');
    }
};
