<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('membership_registration_requests', fn (Blueprint $table) => $table->json('address_data')->nullable());
    }

    public function down(): void
    {
        Schema::table('membership_registration_requests', fn (Blueprint $table) => $table->dropColumn('address_data'));
    }
};
