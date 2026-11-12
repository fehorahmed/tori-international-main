<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $t) {
            $t->string('catalog_type')->default('single')->index();
            $t->unsignedBigInteger('package_warehouse_id')->nullable();
        });
        Schema::create('product_package_items', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('package_product_id')->index();
            $t->unsignedBigInteger('product_id')->index();
            $t->unsignedInteger('quantity');
            $t->timestamps();
            $t->unique(['package_product_id', 'product_id']);
        });
        Schema::create('package_order_checkouts', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('sale_order_id')->unique();
            $t->unsignedBigInteger('customer_id')->index();
            $t->unsignedBigInteger('warehouse_id')->index();
            $t->uuid('request_key');
            $t->string('request_hash', 64);
            $t->json('billing');
            $t->json('lines');
            $t->decimal('shipping_charge', 12, 2)->default(0);
            $t->string('payment_method');
            $t->string('status')->default('Pending')->index();
            $t->timestamps();
            $t->unique(['customer_id', 'request_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_order_checkouts');
        Schema::dropIfExists('product_package_items');
        Schema::table('products', function (Blueprint $t) {
            $t->dropColumn(['catalog_type', 'package_warehouse_id']);
        });
    }
};
