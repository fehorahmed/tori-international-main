<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('product_utilize_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sister_concern_id');
            $table->unsignedBigInteger('product_utilize_id');
            $table->unsignedBigInteger('product_id');
            $table->double('quantity');
            $table->double('unit_price');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('product_utilize_items'); }
};
