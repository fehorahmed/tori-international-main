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
        Schema::create('inter_company_settlements', function (Blueprint $table) {
            $table->id();
            $table->string('payment_no')->nullable();
            $table->unsignedInteger('from_sister_concern_id')->index();
            $table->unsignedInteger('to_sister_concern_id')->index();
            $table->date('date');
            $table->decimal('amount', 20, 2);
            $table->unsignedInteger('from_account_head_id')->nullable()->comment('Sender Cash/Bank');
            $table->unsignedInteger('to_account_head_id')->nullable()->comment('Receiver Cash/Bank');
            $table->unsignedInteger('sender_voucher_id')->nullable();
            $table->unsignedInteger('receiver_voucher_id')->nullable();
            $table->tinyInteger('status')->default(1)->comment('1=Pending, 2=Received, 3=Rejected');
            $table->string('notes')->nullable();
            $table->unsignedInteger('create_user_id')->nullable();
            $table->unsignedInteger('received_user_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inter_company_settlements');
    }
};
