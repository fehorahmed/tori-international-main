<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dps_nominees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('dps_account_id');
            $table->unsignedBigInteger('nominee_id')->comment('References nominees table (global nominees)');
            $table->decimal('share_percentage', 5, 2)->default(100);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dps_nominees');
    }
};
