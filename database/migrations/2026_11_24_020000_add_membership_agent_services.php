<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('membership_agents', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('customer_id')->unique();
            $t->string('agent_code', 40)->unique();
            $t->string('status', 20)->default('Active');
            $t->unsignedBigInteger('approved_by');
            $t->timestamp('approved_at');
            $t->timestamps();
        });
        Schema::table('membership_transfers', function (Blueprint $t) {
            $t->string('channel', 30)->default('transfer')->index();
        });
        Schema::create('membership_agent_transactions', function (Blueprint $t) {
            $t->id();
            $t->string('reference', 40)->unique();
            $t->string('type', 30);
            $t->unsignedBigInteger('agent_id')->index();
            $t->unsignedBigInteger('customer_id')->index();
            $t->unsignedBigInteger('initiator_id');
            $t->unsignedBigInteger('transfer_id')->unique();
            $t->uuid('idempotency_key');
            $t->string('quote_hash', 64);
            $t->decimal('amount', 14, 2);
            $t->decimal('customer_charge', 14, 2)->default(0);
            $t->decimal('agent_commission', 14, 2)->default(0);
            $t->json('settings_snapshot');
            $t->unsignedBigInteger('commission_voucher_id')->nullable();
            $t->string('status', 20)->default('Completed');
            $t->timestamp('completed_at');
            $t->timestamps();
            $t->unique(['initiator_id', 'idempotency_key'], 'agent_initiator_request_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membership_agent_transactions');
        Schema::table('membership_transfers', fn (Blueprint $t) => $t->dropColumn('channel'));
        Schema::dropIfExists('membership_agents');
    }
};
