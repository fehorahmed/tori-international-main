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
        Schema::create('mf_members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sister_concern_id');
            $table->unsignedBigInteger('somiti_id')->nullable();
            $table->string('member_no')->unique();
            $table->string('name')->comment('English Name');
            $table->string('name_bn')->nullable()->comment('Bengali Name');
            $table->string('mobile_no')->nullable();
            $table->string('spouse_name')->nullable();
            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('gender')->nullable();
            $table->tinyInteger('marital_status')->nullable()->comment('1=Married, 2=Single, 3=Widowed, 4=Divorced');
            $table->string('religion')->nullable();
            $table->string('education')->nullable();
            $table->string('blood_group')->nullable();
            $table->date('dob')->nullable();
            $table->string('nid_no')->nullable();
            $table->integer('total_family_members')->nullable();
            $table->string('birth_certificate_no')->nullable();
            $table->string('profession')->nullable();
            $table->decimal('monthly_income', 12, 2)->nullable();
            
            $table->date('admission_date')->nullable();
            $table->decimal('admission_fee', 12, 2)->default(0);
            $table->decimal('form_fee', 12, 2)->default(0);
            $table->decimal('passbook_fee', 12, 2)->default(0);
            $table->tinyInteger('member_category')->default(1)->comment('1=General, 2=Lead, 3=Somiti Head');
            
            // Present Address
            $table->unsignedBigInteger('present_division_id')->nullable();
            $table->unsignedBigInteger('present_district_id')->nullable();
            $table->unsignedBigInteger('present_upazila_id')->nullable();
            $table->unsignedBigInteger('present_union_id')->nullable();
            $table->unsignedBigInteger('present_pourashava_id')->nullable();
            $table->unsignedBigInteger('present_village_id')->nullable();
            $table->string('present_house_road')->nullable();
            
            // Permanent Address
            $table->unsignedBigInteger('permanent_division_id')->nullable();
            $table->unsignedBigInteger('permanent_district_id')->nullable();
            $table->unsignedBigInteger('permanent_upazila_id')->nullable();
            $table->unsignedBigInteger('permanent_union_id')->nullable();
            $table->unsignedBigInteger('permanent_pourashava_id')->nullable();
            $table->unsignedBigInteger('permanent_village_id')->nullable();
            $table->string('permanent_house_road')->nullable();
            
            $table->string('photo')->nullable();
            $table->string('signature_photo')->nullable();
            $table->string('nid_photo_front')->nullable();
            $table->string('nid_photo_back')->nullable();
            $table->string('document')->nullable();
            
            $table->unsignedBigInteger('supplier_id')->nullable()->comment('Linked Customer (Supplier)');
            
            $table->boolean('status')->default(1)->comment('1=Active, 0=Inactive');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('sister_concern_id')->references('id')->on('sister_concerns')->onDelete('cascade');
            $table->foreign('somiti_id')->references('id')->on('mf_somitis')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mf_members');
    }
};
