<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->boolean('product_type')->default(0)->comment("0=Row Products, 1=Finished Goods");
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('sub_category_id')->nullable();
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->unsignedBigInteger('unit_id');
            $table->string('name');
            $table->string('slug')->nullable();
            $table->float('alert_quantity')->default(0);
            $table->float('purchase_price')->default(0);
            $table->float('old_selling_price')->default(0);
            $table->float('selling_price')->default(0);
            $table->float('wholesale_price')->default(0)->comment('DP Price');
            $table->float('emi_interest')->default(0);
            $table->string('code')->nullable();
            $table->string('image')->nullable();
            $table->boolean('has_warranty')->default(0)->comment('1=Yes,0=No');
            $table->integer('warranty_duration')->nullable();
            $table->enum('warranty_type', ['Month', 'Year'])->nullable()->default('Month');
            $table->longText('short_description')->nullable();
            $table->longText('description')->nullable();
            $table->integer('total_review')->default(0);
            $table->integer('total_rating')->default(0);
            $table->string('youtube_video_link')->nullable();
            $table->float('rating')->default(0);
            $table->boolean('is_featured')->default(0)->comment('1=Featured,0=Not Featured');
            $table->boolean('is_trending')->default(0)->comment('1=Trending,0=Not Trending');
            $table->boolean('is_best_selling')->default(0)->comment('1=Best Selling,0=Not Best Selling');
            $table->boolean('is_new_arrival')->default(0)->comment('1=New Arrival,0=Not New Arrival');
            $table->boolean('is_used')->default(0)->comment('1=Used,0=Not Used');
            $table->boolean('is_show')->default(0)->comment('1=Show,0=Not Show');
            $table->double('discount_percent')->default(0)->comment('Discount Percentage');
            $table->double('discount')->default(0)->comment('Discount Amount');
            $table->integer('sort')->default(1);
            $table->boolean('status')->default(1)->comment('1=Active,0=Inactive');
            $table->integer('user_id')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('products');
        Schema::enableForeignKeyConstraints();
    }
};
