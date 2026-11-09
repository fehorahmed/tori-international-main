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
        Schema::create('fdr_nominees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fdr_account_id');
            $table->unsignedBigInteger('nominee_id');
            $table->decimal('share_percentage', 5, 2)->default(100);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fdr_nominees');
    }
};
