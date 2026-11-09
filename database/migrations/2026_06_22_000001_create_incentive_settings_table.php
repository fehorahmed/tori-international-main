<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incentive_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sister_concern_id')->constrained('sister_concerns')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('sr_percent', 5, 2)->default(0);
            $table->decimal('bm_percent', 5, 2)->default(0);
            $table->decimal('am_percent', 5, 2)->default(0);

            // প্রতিটি role এর জন্য আলাদা active/inactive
            // যেমন: SR বন্ধ রেখে BM/AM চালু রাখা যাবে
            $table->boolean('sr_active')->default(true);
            $table->boolean('bm_active')->default(true);
            $table->boolean('am_active')->default(true);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            // Upsert key: একই branch + product এ duplicate হবে না (short name for MySQL)
            $table->unique(['sister_concern_id', 'product_id', 'deleted_at'], 'inc_settings_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incentive_settings');
    }
};
