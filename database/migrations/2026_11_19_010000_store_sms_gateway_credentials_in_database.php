<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sms_gateways', function (Blueprint $table) {
            $table->text('api_key')->change();
            $table->text('username')->nullable();
            $table->text('password')->nullable();
        });
        DB::table('sms_gateways')->orderBy('id')->each(function ($row) {
            DB::table('sms_gateways')->where('id', $row->id)->update(['api_key' => Crypt::encryptString($row->api_key ?? '')]);
        });
    }

    public function down(): void
    {
        DB::table('sms_gateways')->orderBy('id')->each(function ($row) {
            DB::table('sms_gateways')->where('id', $row->id)->update(['api_key' => Crypt::decryptString($row->api_key)]);
        });
        Schema::table('sms_gateways', function (Blueprint $table) {
            $table->dropColumn(['username', 'password']);
            $table->string('api_key')->change();
        });
    }
};
