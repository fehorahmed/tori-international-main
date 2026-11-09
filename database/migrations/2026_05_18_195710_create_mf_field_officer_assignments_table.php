<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mf_field_officer_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sister_concern_id');
            $table->unsignedBigInteger('somiti_id');
            $table->unsignedBigInteger('field_officer_id');
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->date('from_date');
            $table->date('to_date')->nullable();
            $table->tinyInteger('status')->default(1)->comment('1=Active, 0=Past');
            $table->text('change_reason')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('sister_concern_id')->references('id')->on('sister_concerns')->onDelete('cascade');
            $table->foreign('somiti_id')->references('id')->on('mf_somitis')->onDelete('cascade');
            $table->foreign('field_officer_id')->references('id')->on('suppliers')->onDelete('cascade');
            $table->foreign('assigned_by')->references('id')->on('users')->onDelete('set null');

            // Indices for rapid query performance
            $table->index(['somiti_id', 'status']);
            $table->index(['field_officer_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mf_field_officer_assignments');
    }
};
