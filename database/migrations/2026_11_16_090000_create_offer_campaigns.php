<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offer_campaigns', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();
            $t->dateTime('starts_at');
            $t->dateTime('ends_at');
            $t->boolean('is_active')->default(true);
            $t->unsignedBigInteger('created_by');
            $t->timestamps();
            $t->index(['is_active', 'starts_at', 'ends_at']);
        });
        Schema::create('offer_campaign_products', function (Blueprint $t) {
            $t->id();
            $t->foreignId('campaign_id')->constrained('offer_campaigns');
            $t->unsignedBigInteger('product_id');
            $t->string('discount_type', 16);
            $t->decimal('discount_value', 15, 2);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
            $t->unique(['campaign_id', 'product_id']);
            $t->index(['product_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offer_campaign_products');
        Schema::dropIfExists('offer_campaigns');
    }
};
