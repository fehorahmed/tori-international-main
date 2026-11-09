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
        Schema::create('fdr_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('fdr_no')->unique();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('scheme_id');
            $table->unsignedBigInteger('origin_sister_concern_id')->nullable();
            $table->decimal('principal_amount', 12, 2);
            $table->date('opening_date');
            $table->date('maturity_date');
            $table->decimal('payable_amount_at_maturity', 12, 2)->default(0);
            $table->boolean('auto_renew')->default(false);
            $table->enum('interest_payout_frequency', ['Monthly', 'Quarterly', 'Half-Yearly', 'Yearly', 'At Maturity'])->default('At Maturity');
            $table->date('next_interest_date')->nullable();
            $table->enum('status', ['Pending', 'Active', 'Matured', 'Closed'])->default('Pending');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fdr_accounts');
    }
};
