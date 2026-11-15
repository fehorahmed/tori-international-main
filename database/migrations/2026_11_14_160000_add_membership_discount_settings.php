<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['membership_discount.enabled' => true, 'membership_discount.minimum_balance' => '1000.00'] as $key => $value) {
            DB::table('membership_transaction_settings')->insertOrIgnore(['key' => $key, 'value' => json_encode($value), 'created_at' => now(), 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        DB::table('membership_transaction_settings')->whereIn('key', ['membership_discount.enabled', 'membership_discount.minimum_balance'])->delete();
    }
};
