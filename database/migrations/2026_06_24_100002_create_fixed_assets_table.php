<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fixed_assets', function (Blueprint $table) {
            $table->id();
            $table->string('asset_code')->unique();              // FA-2026-001 (auto-generated)
            $table->string('name');                              // "Wood Chair", "Boss Chair"
            $table->unsignedBigInteger('category_id');          // FK → fixed_asset_categories
            $table->unsignedBigInteger('sister_concern_id');    // which branch
            $table->date('date');
            $table->decimal('total', 15, 2);            // original cost per unit
            $table->decimal('salvage_value', 15, 2)->default(0);// residual value (for SLM)
            $table->integer('useful_life_years')->nullable();    // for SLM
            $table->enum('depreciation_method', ['SLM', 'WDV', 'none'])->default('SLM');
            $table->decimal('depreciation_rate', 8, 2)->nullable(); // % for WDV
            $table->decimal('accumulated_depreciation', 15, 2)->default(0); // total depreciated so far
            $table->decimal('book_value', 15, 2);               // total - accumulated_depreciation
            $table->unsignedBigInteger('account_head_id');      // FK → account_heads (asset ledger)
            $table->unsignedBigInteger('payment_account_head_id')->nullable(); // FK → account_heads (cash/bank used)
            $table->string('location')->nullable();              // "Reception", "MD Room", "3rd Floor"
            $table->string('serial_no')->nullable();             // hardware serial number
            $table->string('vendor')->nullable();                // supplier name
            $table->date('warranty_expiry')->nullable();
            $table->unsignedBigInteger('assigned_to_user_id')->nullable(); // FK → users
            $table->enum('status', ['active', 'sold', 'scrapped', 'transferred'])->default('active');
            $table->unsignedBigInteger('purchase_voucher_id')->nullable(); // FK → vouchers
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('category_id')->references('id')->on('fixed_asset_categories');
            $table->foreign('sister_concern_id')->references('id')->on('sister_concerns');
            $table->foreign('account_head_id')->references('id')->on('account_heads');
            $table->foreign('payment_account_head_id')->references('id')->on('account_heads')->nullOnDelete();
            $table->foreign('assigned_to_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('purchase_voucher_id')->references('id')->on('vouchers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fixed_assets');
    }
};
