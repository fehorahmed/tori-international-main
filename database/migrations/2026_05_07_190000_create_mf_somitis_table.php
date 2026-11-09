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
        Schema::create('mf_somitis', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sister_concern_id');
            $table->unsignedBigInteger('field_officer_id')->nullable();
            $table->string('name');
            $table->string('name_bn')->nullable();
            $table->string('somiti_code')->unique();
            $table->tinyInteger('somiti_type')->default(1)->comment('1=Male, 2=Female, 3=Mixed');
            $table->integer('max_member_limit')->default(30);
            $table->string('meeting_day');
            $table->time('meeting_time')->nullable();
            $table->date('opening_date')->nullable();
            $table->date('collection_start_date')->nullable();
            $table->decimal('lat', 10, 8)->nullable();
            $table->decimal('long', 11, 8)->nullable();
            $table->boolean('status')->default(1);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('sister_concern_id')->references('id')->on('sister_concerns')->onDelete('cascade');
            $table->foreign('field_officer_id')->references('id')->on('suppliers')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mf_somitis');
    }
};
