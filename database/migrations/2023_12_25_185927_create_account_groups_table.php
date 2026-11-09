<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_groups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_group_id')->nullable();
            $table->unsignedBigInteger('reserved_id')->nullable();
            $table->string('name');
            $table->string('note_no')->nullable();
            $table->text('comment')->nullable(); 
            $table->tinyInteger('is_editable')->default(1)->comment('1 = editable, 0 = non-editable');
            $table->tinyInteger('is_leaf')->default(1)->comment('1 = can have heads, 0 = has child groups');
            $table->tinyInteger('status')->default(1)->comment('1 = active, 0 = inactive');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_groups');
    }
};
