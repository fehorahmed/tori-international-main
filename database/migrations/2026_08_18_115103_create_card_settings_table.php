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
        Schema::create('card_settings', function (Blueprint $table) {
            $table->id();
            $table->double('card_fee', 10, 2)->default(100);
            
            // First Issue Card Rules
            $table->double('first_card_agent_commission', 10, 2)->default(0);
            $table->double('first_card_member_bonus', 10, 2)->default(0);
            $table->double('first_card_company_income', 10, 2)->default(100);
            
            // Re-Issue (Lost/Damaged/Renewal) Card Rules
            $table->double('replacement_card_agent_commission', 10, 2)->default(0);
            $table->double('replacement_card_member_bonus', 10, 2)->default(0);
            $table->double('replacement_card_company_income', 10, 2)->default(100);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('card_settings');
    }
};
