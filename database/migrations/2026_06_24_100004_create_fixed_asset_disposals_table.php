<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fixed_asset_disposals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fixed_asset_id');
            $table->unsignedBigInteger('sister_concern_id');
            $table->date('disposal_date');
            $table->enum('disposal_type', ['sold', 'scrapped']);
            $table->decimal('sale_amount', 15, 2)->default(0);         // 0 if scrapped
            $table->decimal('book_value_at_disposal', 15, 2);          // book value at time of disposal
            $table->decimal('gain_loss_amount', 15, 2)->default(0);    // sale_amount - book_value (+ = gain, - = loss)
            $table->unsignedBigInteger('payment_account_head_id')->nullable(); // where money received (null if scrapped)
            $table->unsignedBigInteger('voucher_id')->nullable();       // FK → vouchers (auto JV)
            $table->string('notes')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();

            $table->foreign('fixed_asset_id')->references('id')->on('fixed_assets')->cascadeOnDelete();
            $table->foreign('sister_concern_id')->references('id')->on('sister_concerns');
            $table->foreign('payment_account_head_id')->references('id')->on('account_heads')->nullOnDelete();
            $table->foreign('voucher_id')->references('id')->on('vouchers')->nullOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fixed_asset_disposals');
    }
};
