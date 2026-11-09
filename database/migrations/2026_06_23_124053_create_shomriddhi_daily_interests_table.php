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
        Schema::create('shomriddhi_daily_interests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('origin_sister_concern_id')->constrained('sister_concerns')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('suppliers')->onDelete('cascade');
            $table->date('date');
            $table->decimal('principal_balance', 15, 2);
            $table->decimal('interest_rate', 5, 2);
            $table->decimal('earned_interest', 15, 4);
            $table->string('status')->default('Pending')->comment('Pending, Capitalized');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shomriddhi_daily_interests');
    }
};
