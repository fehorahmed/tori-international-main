<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incentive_ledger', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sister_concern_id')->constrained('sister_concerns');
            $table->foreignId('employee_id')->constrained('users');
            $table->date('date');

            // 'earned' | 'paid'
            $table->enum('type', ['earned', 'paid']);

            // Positive for earned, Negative for paid
            $table->decimal('amount', 12, 2);

            // Which role earned this: 'sr' | 'branch_manager' | 'area_manager' | 'payment'
            $table->string('role_type', 30);

            // Polymorphic reference
            $table->string('reference_type');
            $table->unsignedBigInteger('reference_id');

            // payment status
            $table->enum('payment_status', ['unpaid', 'paid'])->default('unpaid');

            $table->text('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Index
            $table->index(['employee_id', 'sister_concern_id', 'payment_status', 'type'], 'emp_concern_status_type_idx');
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incentive_ledger');
    }
};
