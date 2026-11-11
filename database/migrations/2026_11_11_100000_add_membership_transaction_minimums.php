<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['cashout.minimum_amount', 'transfer.minimum_amount'] as $key) {
            DB::table('membership_transaction_settings')->insertOrIgnore([
                'key' => $key, 'value' => json_encode('1.00'), 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('membership_transaction_settings')->whereIn('key', ['cashout.minimum_amount', 'transfer.minimum_amount'])->delete();
    }
};
