<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('membership_milestone_bonuses', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('customer_id'); $t->uuid('tier_id');
            $t->unsignedInteger('threshold'); $t->unsignedBigInteger('qualifying_points');
            $t->decimal('percentage', 7, 2); $t->decimal('point_value', 15, 2); $t->decimal('amount', 15, 2);
            $t->unsignedBigInteger('account_id')->nullable(); $t->unsignedBigInteger('voucher_id')->nullable()->unique();
            $t->timestamp('paid_at')->nullable(); $t->timestamps(); $t->unique(['customer_id', 'tier_id']); $t->unique(['customer_id', 'threshold']);
        });
    }
    public function down(): void { Schema::dropIfExists('membership_milestone_bonuses'); }
};
