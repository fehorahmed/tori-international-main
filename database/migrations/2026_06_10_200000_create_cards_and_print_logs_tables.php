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
        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('origin_sister_concern_id')->nullable();
            $table->string('card_number')->unique();
            
            // Polymorphic relation to link to App\Models\Supplier or App\Models\Hrm\Employee
            $table->string('cardable_type');
            $table->unsignedBigInteger('cardable_id');
            
            $table->enum('status', ['Active', 'Suspended', 'Lost', 'Damaged', 'Expired', 'Revoked'])->default('Active');
            $table->double('amount', 10, 2)->default(0);
            $table->enum('payment_status', ['Pending', 'Received'])->default('Pending');
            $table->unsignedBigInteger('received_by')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->unsignedBigInteger('created_by');
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['cardable_type', 'cardable_id']);
            $table->foreign('origin_sister_concern_id')->references('id')->on('sister_concerns')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('card_print_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('origin_sister_concern_id')->nullable();
            $table->unsignedBigInteger('card_id');
            $table->unsignedBigInteger('printed_by');
            $table->timestamp('printed_at');
            $table->enum('print_reason', ['First Issue', 'Lost Replacement', 'Damaged Replacement', 'Renewal']);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('origin_sister_concern_id')->references('id')->on('sister_concerns')->onDelete('cascade');
            $table->foreign('card_id')->references('id')->on('cards')->onDelete('cascade');
            $table->foreign('printed_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('card_print_logs');
        Schema::dropIfExists('cards');
    }
};
