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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('banner_image_1')->nullable();
            $table->string('banner_image_2')->nullable();
            $table->string('banner_image_3')->nullable();
            $table->string('banner_image_4')->nullable();
            $table->string('banner_image_5')->nullable();
            $table->string('banner_image_link_1')->nullable();
            $table->string('banner_image_link_2')->nullable();
            $table->string('banner_image_link_3')->nullable();
            $table->string('banner_image_link_4')->nullable();
            $table->string('banner_image_link_5')->nullable();
            $table->integer('banner_image_status_1')->default(0);
            $table->integer('banner_image_status_2')->default(0);
            $table->integer('banner_image_status_3')->default(0);
            $table->integer('banner_image_status_4')->default(0);
            $table->integer('banner_image_status_5')->default(0);
            $table->string('primary_color')->default('#70c332');
            $table->integer('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
