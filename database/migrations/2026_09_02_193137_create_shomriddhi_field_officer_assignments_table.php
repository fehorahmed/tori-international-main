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
        Schema::create('shomriddhi_field_officer_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shomriddhi_group_id');
            $table->unsignedBigInteger('field_officer_id');
            $table->date('from_date');
            $table->date('to_date')->nullable();
            $table->tinyInteger('status')->default(1)->comment('1=Active, 0=Inactive');
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->unsignedBigInteger('sister_concern_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shomriddhi_field_officer_assignments');
    }
};
