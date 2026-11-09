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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sister_concern_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('event'); // created, updated, deleted, etc.
            $table->morphs('loggable'); // loggable_id and loggable_type
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['loggable_id', 'loggable_type']);
            $table->index('user_id');
            $table->index('sister_concern_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
