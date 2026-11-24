<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fdr_schemes', fn (Blueprint $t) => $t->boolean('visible_on_app')->default(false));
        Schema::table('fdr_accounts', function (Blueprint $t) {
            $t->decimal('interest_rate', 5, 2)->nullable();
            $t->unsignedInteger('duration_months')->nullable();
            $t->decimal('penalty_rate', 5, 2)->nullable();
            $t->string('funding_source', 20)->default('cash_bank');
            $t->unsignedBigInteger('savings_account_id')->nullable();
            $t->string('idempotency_key', 64)->nullable();
            $t->string('request_hash', 64)->nullable();
            $t->unsignedInteger('renewal_count')->default(0);
            $t->timestamp('closed_at')->nullable();
            $t->unique(['customer_id', 'idempotency_key'], 'fdr_customer_open_key');
        });
        // Preserve the existing scheme terms for legacy accounts; do not rewrite balances or vouchers.
        DB::statement('UPDATE fdr_accounts a JOIN fdr_schemes s ON s.id=a.scheme_id SET a.interest_rate=s.interest_rate, a.duration_months=s.duration, a.penalty_rate=COALESCE(s.penalty_rate,0)');
        Schema::table('fdr_transactions', function (Blueprint $t) {
            $t->string('posting_key', 120)->nullable()->unique();
            $t->date('period_start')->nullable();
            $t->date('period_end')->nullable();
        });
        Schema::create('fdr_encashment_requests', function (Blueprint $t) {
            $t->id();
            $t->foreignId('fdr_account_id')->constrained('fdr_accounts');
            $t->unsignedBigInteger('customer_id');
            $t->unsignedBigInteger('origin_sister_concern_id');
            $t->string('idempotency_key', 64);
            $t->string('status', 20)->default('Pending')->index();
            $t->date('requested_date');
            $t->json('preview');
            $t->text('note')->nullable();
            $t->unsignedBigInteger('reviewed_by')->nullable();
            $t->timestamp('reviewed_at')->nullable();
            $t->text('review_note')->nullable();
            $t->unsignedBigInteger('voucher_id')->nullable();
            $t->timestamps();
            $t->unique(['customer_id', 'idempotency_key'], 'fdr_encash_request_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fdr_encashment_requests');
        Schema::table('fdr_transactions', function (Blueprint $t) {
            $t->dropUnique(['posting_key']);
            $t->dropColumn(['posting_key', 'period_start', 'period_end']);
        });
        Schema::table('fdr_accounts', function (Blueprint $t) {
            $t->dropUnique('fdr_customer_open_key');
            $t->dropColumn(['interest_rate', 'duration_months', 'penalty_rate', 'funding_source', 'savings_account_id', 'idempotency_key', 'request_hash', 'renewal_count', 'closed_at']);
        });
        Schema::table('fdr_schemes', fn (Blueprint $t) => $t->dropColumn('visible_on_app'));
    }
};
