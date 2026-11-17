<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('membership_point_transactions')) Schema::create('membership_point_transactions', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('customer_id')->index();
            $t->foreignId('checkout_id')->constrained('package_order_checkouts');
            $t->string('type', 16);
            $t->decimal('points', 20, 6);
            $t->decimal('point_value', 15, 2);
            $t->decimal('amount', 15, 2);
            $t->unsignedBigInteger('voucher_id')->unique();
            $t->unsignedBigInteger('original_transaction_id')->nullable()->unique();
            $t->uuid('request_key');
            $t->timestamps();
            $t->unique(['checkout_id', 'type']);
        });
        if (!Schema::hasIndex('membership_point_transactions', 'point_tx_request_unique')) {
            Schema::table('membership_point_transactions', fn (Blueprint $t) => $t->unique(['customer_id', 'request_key', 'type'], 'point_tx_request_unique'));
        }
        Schema::table('membership_milestone_bonuses', fn (Blueprint $t) => $t->decimal('qualifying_points', 20, 6)->change());
    }
    public function down(): void
    {
        Schema::dropIfExists('membership_point_transactions');
        // Keep decimal milestone snapshots so rollback never truncates historical progress.
    }
};
