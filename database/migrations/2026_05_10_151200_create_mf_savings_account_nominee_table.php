<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mf_savings_account_nominee', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mf_savings_account_id');
            $table->unsignedBigInteger('mf_nominee_id');
            $table->decimal('share_percentage', 5, 2)->default(100.00);
            $table->timestamps();

            $table->foreign('mf_savings_account_id', 'sa_nom_sa_id_foreign')->references('id')->on('mf_savings_accounts')->onDelete('cascade');
            $table->foreign('mf_nominee_id', 'sa_nom_nom_id_foreign')->references('id')->on('mf_nominees')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mf_savings_account_nominee');
    }
};
