<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sister_concern_id')->index();
            $table->string('id_no');
            $table->string('name');
            $table->unsignedBigInteger('department_id')->index();
            $table->unsignedBigInteger('designation_id')->index();
            $table->string('email')->nullable();
            $table->string('mobile_no')->nullable();
            $table->tinyInteger('marital_status')->nullable()->comment('1=Married, 2=Single, 3=Widowed, 4=Divorced');
            $table->tinyInteger('gender')->nullable()->comment('1=Male, 2=Female, 3=Other');
            $table->string('blood_group')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->unsignedBigInteger('current_division_id')->nullable();
            $table->unsignedBigInteger('current_district_id')->nullable();
            $table->unsignedBigInteger('current_upazila_id')->nullable();
            $table->unsignedBigInteger('current_union_id')->nullable();
            $table->unsignedBigInteger('current_pourashava_id')->nullable();
            $table->unsignedBigInteger('current_village_id')->nullable();
            $table->unsignedBigInteger('permanent_division_id')->nullable();
            $table->unsignedBigInteger('permanent_district_id')->nullable();
            $table->unsignedBigInteger('permanent_upazila_id')->nullable();
            $table->unsignedBigInteger('permanent_union_id')->nullable();
            $table->unsignedBigInteger('permanent_pourashava_id')->nullable();
            $table->unsignedBigInteger('permanent_village_id')->nullable();
            $table->string('nid', 250)->nullable();
            $table->string('passport', 250)->nullable();
            $table->string('certificate', 250)->nullable();
            $table->string('cv', 250)->nullable();
            $table->string('photo')->nullable();
            $table->string('signature_photo')->nullable();
            $table->double('gross_salary', 50, 2)->default(0);
            $table->string('bank_name', 200)->nullable();
            $table->string('branch_name', 200)->nullable();
            $table->string('routing_number', 200)->nullable();
            $table->string('bank_account_no')->nullable();
            $table->unsignedBigInteger('shift_id')->index();
            $table->unsignedBigInteger('salary_structure_id')->nullable()->index();
            $table->unsignedBigInteger('create_user_id');
            $table->unsignedBigInteger('alter_user_id')->nullable();
            $table->unsignedBigInteger('employee_status_id')->index();
            $table->tinyInteger('status')->default(1)->comment('1 = active, 0 = inactive');
            $table->softDeletes();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('employees'); }
};
