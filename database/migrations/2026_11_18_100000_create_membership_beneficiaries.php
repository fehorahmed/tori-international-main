<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('membership_beneficiaries', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('customer_id');
            $t->unsignedBigInteger('recipient_id');
            $t->string('nickname', 60)->nullable();
            $t->timestamps();
            $t->unique(['customer_id', 'recipient_id'], 'beneficiary_member_recipient_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membership_beneficiaries');
    }
};
