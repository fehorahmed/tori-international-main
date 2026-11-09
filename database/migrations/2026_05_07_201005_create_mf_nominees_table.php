<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mf_nominees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mf_member_id');
            $table->string('name');
            $table->string('relation')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->date('dob')->nullable();
            $table->string('blood_group')->nullable();
            $table->string('nid_no')->nullable();
            $table->string('gender')->nullable();
            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->decimal('share_percentage', 5, 2)->default(100.00);
            
            $table->unsignedBigInteger('division_id')->nullable();
            $table->unsignedBigInteger('district_id')->nullable();
            $table->unsignedBigInteger('upazila_id')->nullable();
            $table->unsignedBigInteger('union_id')->nullable();
            $table->unsignedBigInteger('pourashava_id')->nullable();
            $table->unsignedBigInteger('village_id')->nullable();
            $table->string('house_road')->nullable();
            
            $table->string('photo')->nullable();
            $table->string('signature_photo')->nullable();
            $table->string('nid_photo_front')->nullable();
            $table->string('nid_photo_back')->nullable();
            
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('mf_member_id')->references('id')->on('mf_members')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mf_nominees');
    }
};
