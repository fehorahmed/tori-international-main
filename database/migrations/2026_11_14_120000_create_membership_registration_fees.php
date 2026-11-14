<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('membership_registration_fees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->unique();
            $table->decimal('amount', 15, 2);
            $table->unsignedBigInteger('voucher_id')->nullable()->unique();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
        DB::table('membership_transaction_settings')->insertOrIgnore([
            'key' => 'membership.income_head_id', 'value' => 'null',
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('membership_registration_fees');
        DB::table('membership_transaction_settings')->where('key', 'membership.income_head_id')->delete();
    }
};
