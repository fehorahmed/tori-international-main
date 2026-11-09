<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('product_utilizes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sister_concern_id');
            $table->unsignedBigInteger('warehouse_id');
            $table->string('order_no')->nullable();
            $table->date('date');
            $table->string('notes')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('product_utilizes'); }
};
