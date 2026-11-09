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
        Schema::create('sister_concerns', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable()->unique();
            $table->unsignedBigInteger('area_manager_id')->nullable();
            $table->unsignedBigInteger('branch_manager_id')->unique()->nullable();
            $table->string('name');
            $table->string('logo')->nullable();
            $table->string('auth_signature')->nullable();
            $table->string('official_seal')->nullable();
            $table->string('trade_license_no')->nullable();
            $table->string('tin_no')->nullable();
            $table->string('bin_no')->nullable();
            $table->string('contact')->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->string('district')->nullable();
            $table->string('upazila')->nullable();
            $table->string('post_code')->nullable();
            $table->decimal('office_rent', 10, 2)->nullable();
            $table->decimal('electricity_bill', 10, 2)->nullable();
            $table->date('establishment_date')->nullable();
            $table->date('certificate_expiry_date')->nullable();
            $table->date('business_date')->default(now());
            $table->tinyInteger('is_head_office')->default(0)->comment('1 = Head Office, 0 = Branch');
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sister_concerns');
    }
};
