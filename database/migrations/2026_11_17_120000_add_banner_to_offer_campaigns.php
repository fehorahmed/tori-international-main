<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('offer_campaigns', fn (Blueprint $t) => $t->string('banner_image')->nullable());
    }

    public function down(): void
    {
        Schema::table('offer_campaigns', fn (Blueprint $t) => $t->dropColumn('banner_image'));
    }
};
