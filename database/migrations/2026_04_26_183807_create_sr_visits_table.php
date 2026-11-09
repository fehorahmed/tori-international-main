<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('sr_visits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sister_concern_id');
            $table->unsignedBigInteger('sr_id');
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->string('image')->nullable();
            $table->date('date');
            $table->text('notes')->nullable();
            $table->string('lat')->nullable();
            $table->string('long')->nullable();
            $table->text('location')->nullable();
            $table->integer('user_id')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('sr_visits'); }
};
