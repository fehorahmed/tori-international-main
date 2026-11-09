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
        Schema::create('shomriddhi_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('account_number')->nullable()->unique();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('origin_sister_concern_id')->nullable();
            $table->date('opening_date')->nullable();
            $table->decimal('principal_balance', 15, 2)->default(0);
            $table->decimal('accrued_interest', 15, 4)->default(0);
            $table->date('last_interest_date')->nullable();
            $table->enum('status', ['Active', 'Closed'])->default('Active');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shomriddhi_accounts');
    }
};
