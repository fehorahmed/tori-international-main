<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void { Schema::table('customer_devices', function (Blueprint $table) { $table->string('biometric_token_hash')->nullable(); }); }
    public function down(): void { Schema::table('customer_devices', fn (Blueprint $table) => $table->dropColumn('biometric_token_hash')); }
};
