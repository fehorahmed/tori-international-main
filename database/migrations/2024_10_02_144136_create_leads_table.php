<?php

use App\Enumeration\LeadStatus;
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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sister_concern_id');
            $table->string('order_no')->nullable();
            $table->date('date')->nullable();
            $table->unsignedBigInteger('crm_client_id');
            $table->timestamp('contacted_at')->nullable();
            $table->unsignedBigInteger('assigned_to');

            $table->longText('notes')->nullable(); 
            $table->unsignedBigInteger('sr_id')->nullable();
            $table->integer('user_id')->nullable();
            $table->boolean('is_converted')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
