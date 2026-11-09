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
        Schema::create('sr_social_engagements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sister_concern_id')->nullable();
            $table->unsignedBigInteger('sr_id');
            $table->string('platform'); // youtube, facebook
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('screenshot')->nullable();
            $table->date('date')->nullable();
            $table->string('lat')->nullable();
            $table->string('long')->nullable();
            $table->text('location')->nullable();
             $table->unsignedBigInteger('user_id'); // Who created the entry
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sr_social_engagements');
    }
};
