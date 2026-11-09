<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('salary_processes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sister_concern_id');
            $table->unsignedBigInteger('payment_mode_account_head_id');
            $table->integer('year');
            $table->integer('month');
            $table->string('cheque_no')->nullable();
            $table->date('cheque_date')->nullable();
            $table->date('process_date');
            $table->unsignedBigInteger('create_user_id');
            $table->unsignedBigInteger('alter_user_id')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('salary_processes'); }
};
