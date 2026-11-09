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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('origin_sister_concern_id')->nullable()->comment('Tracks origin branch, but party is global');
            $table->unsignedBigInteger('shomriddhi_group_id')->nullable();
            $table->enum('type', ['Supplier', 'Customer', 'Corporate_Customer', 'Dealer']);
            $table->string('name');
            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('spouse_name')->nullable();
            $table->enum('gender', ['Male', 'Female', 'Other'])->nullable();
            $table->enum('blood_group', ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'])->nullable();
            $table->string('nid_number')->nullable();
            $table->date('dob')->nullable();
            $table->string('member_no')->nullable();
            $table->string('shop_name')->nullable();
            $table->string('email')->nullable();
            $table->string('mobile_no')->nullable();
            $table->unsignedBigInteger('division_id')->nullable();
            $table->unsignedBigInteger('district_id')->nullable();
            $table->unsignedBigInteger('upazila_id')->nullable();
            $table->unsignedBigInteger('market_id')->nullable();
            $table->unsignedBigInteger('customer_type_id')->nullable();
            $table->unsignedBigInteger('union_id')->nullable();
            $table->unsignedBigInteger('pourashava_id')->nullable();
            $table->unsignedBigInteger('village_id')->nullable();
            $table->string('image')->nullable();
            $table->text('address')->nullable();
            $table->string('lat')->nullable();
            $table->string('long')->nullable();
            $table->text('location')->nullable();
            $table->string('mobile_banking_no')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('branch_name')->nullable();
            $table->string('bank_account_no')->nullable();
            $table->string('routing_no')->nullable();
            $table->boolean('status')->comment('1=active,0=inactive');
            $table->string('password')->nullable();
            $table->string('pin')->nullable()->comment('Hashed 6-digit PIN for Mobile App');
            $table->boolean('is_biometric_enabled')->default(0)->comment('1=Enabled, 0=Disabled');
            $table->string('biometric_token')->nullable()->comment('Device biometric auth token');
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
