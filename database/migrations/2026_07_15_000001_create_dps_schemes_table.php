<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dps_schemes', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->integer('duration_months')->comment('Total scheme duration in months, e.g. 36, 60, 120');
            $table->enum('installment_type', ['Daily', 'Weekly', 'Monthly'])->default('Monthly')->comment('Installment frequency');
            $table->decimal('installment_amount', 15, 2)->comment('Fixed installment amount');
            $table->decimal('interest_rate', 5, 2)->comment('Annual interest rate in %');
            $table->enum('penalty_type', ['Fixed', 'Percentage'])->default('Percentage')->comment('Penalty type for missed installments');
            $table->decimal('penalty_amount', 15, 2)->default(0)->comment('Penalty amount/rate for missed installments');
            $table->enum('premature_penalty_type', ['Fixed', 'Percentage'])->default('Percentage')->comment('Penalty type for premature encashment');
            $table->decimal('premature_penalty_amount', 15, 2)->default(0)->comment('Penalty amount/rate for premature encashment');
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dps_schemes');
    }
};
