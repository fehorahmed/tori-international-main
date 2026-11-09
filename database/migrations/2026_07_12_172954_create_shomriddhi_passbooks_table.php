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
        Schema::create('shomriddhi_passbooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shomriddhi_account_id')->constrained()->cascadeOnDelete();
            $table->string('passbook_number')->unique();
            $table->enum('status', ['Active', 'Filled', 'Lost', 'Damaged', 'Replaced'])->default('Active');
            $table->string('reason')->nullable();
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shomriddhi_passbooks');
    }
};
