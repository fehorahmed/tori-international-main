<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sister_concern_id')->index();
            $table->unsignedBigInteger('employee_id')->index();
            $table->dateTime('punch_time')->index();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->string('location_address')->nullable();
            $table->string('attendance_image')->nullable();
            $table->text('note')->nullable();
            $table->unsignedBigInteger('create_user_id')->nullable();
            $table->unsignedBigInteger('alter_user_id')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('attendances'); }
};
