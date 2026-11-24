<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sister_concerns', function (Blueprint $table) {
            // Keep historical values, but do not persist a date for newly created branches.
            $table->date('business_date')->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        // Existing values remain available; do not restore the retired stored-date system.
    }
};
