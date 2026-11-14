<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['membership.registration_fee', 'membership.account_hold_amount'] as $key) {
            DB::table('membership_transaction_settings')->insertOrIgnore([
                'key' => $key, 'value' => json_encode('0.00'), 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('membership_transaction_settings')->whereIn('key', ['membership.registration_fee', 'membership.account_hold_amount'])->delete();
    }
};
