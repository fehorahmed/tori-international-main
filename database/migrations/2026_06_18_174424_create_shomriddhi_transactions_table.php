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
        Schema::create('shomriddhi_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('voucher_id')->nullable()->comment('Links to Accounts Voucher');
            $table->unsignedBigInteger('customer_id');
            $table->enum('type', ['deposit', 'withdraw','interest_capitalization']);
            $table->double('amount', 10, 2);
            $table->date('date');
            $table->text('note')->nullable();
            $table->boolean('is_opening_balance')->default(false)->comment('1 = Yes, 0 = No');
            $table->enum('status', ['Pending', 'Approved', 'Rejected'])->default('Pending');
            $table->unsignedBigInteger('created_by'); // Who received/gave cash in field
            $table->unsignedBigInteger('approved_by')->nullable(); // Who approved in office
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable(); // Who deleted it
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shomriddhi_transactions');
    }
};
