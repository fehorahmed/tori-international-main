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
        Schema::create('task_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_assignment_id');
            $table->unsignedBigInteger('task_type_id');
            $table->float('target_value', 15, 2);
            $table->float('actual_value', 15, 2)->default(0);
            $table->boolean('is_archived')->default(false);
            $table->timestamps();

            $table->unique(['task_assignment_id', 'task_type_id'], 'unique_assignment_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_items');
    }
};
