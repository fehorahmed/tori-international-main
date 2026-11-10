<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_devices', function (Blueprint $table) {
            $table->string('trusted_token_hash', 64)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('customer_devices', fn (Blueprint $table) => $table->dropColumn('trusted_token_hash'));
    }
};
