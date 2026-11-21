<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('shomriddhi_interest_rate_versions', function (Blueprint $table) {
            $table->id();
            $table->dateTime('effective_at')->index();
            $table->json('slabs');
        });
        DB::table('shomriddhi_interest_rate_versions')->insert([
            'effective_at' => now(),
            'slabs' => json_encode(DB::table('shomriddhi_interest_slabs')->orderBy('id')->get(['min_amount', 'max_amount', 'interest_rate'])->toArray()),
        ]);
    }
    public function down(): void { Schema::dropIfExists('shomriddhi_interest_rate_versions'); }
};
