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
        Schema::create('employee_educations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('degree_name'); // e.g., B.Sc in CSE, HSC
            $table->string('institute'); // e.g., Dhaka University
            $table->string('board_or_university')->nullable(); // e.g., Dhaka Board
            $table->string('passing_year')->nullable();
            $table->string('result')->nullable(); // e.g., CGPA 3.50, 1st Class
            $table->string('document_path')->nullable(); // Uploaded certificate path
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_educations');
    }
};
