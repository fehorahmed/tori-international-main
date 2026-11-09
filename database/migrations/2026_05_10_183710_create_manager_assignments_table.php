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
        Schema::create('manager_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('manager_id');
            $table->unsignedBigInteger('sister_concern_id');
            $table->enum('manager_type', ['Area_Manager', 'Branch_Manager']);
            $table->date('assigned_date');
            $table->date('unassigned_date')->nullable();
            $table->boolean('status')->default(1); // 1 = Active, 0 = Inactive/Transferred
            $table->timestamps();

            $table->foreign('sister_concern_id')->references('id')->on('sister_concerns')->onDelete('cascade');
            $table->foreign('manager_id')->references('id')->on('employees')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manager_assignments');
    }
};
