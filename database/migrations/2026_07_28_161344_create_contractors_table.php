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
        Schema::create('contractors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('origin_sister_concern_id')->nullable();
            $table->foreignId('contractor_type_id')->nullable()->constrained('contractor_types')->onDelete('restrict');
            $table->string('name');
            $table->string('nid_number')->nullable();
            $table->date('dob')->nullable();
            $table->string('company_name')->nullable();
            $table->string('email')->nullable();
            $table->string('mobile_no')->nullable();
            $table->unsignedBigInteger('division_id')->nullable();
            $table->unsignedBigInteger('district_id')->nullable();
            $table->unsignedBigInteger('upazila_id')->nullable();
            $table->unsignedBigInteger('union_id')->nullable();
            $table->unsignedBigInteger('pourashava_id')->nullable();
            $table->unsignedBigInteger('village_id')->nullable();
            $table->string('image')->nullable();
            $table->text('address')->nullable();
            $table->unsignedBigInteger('account_head_id')->nullable();
            $table->tinyInteger('status')->default(1)->comment('1=active,0=inactive');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contractors');
    }
};
