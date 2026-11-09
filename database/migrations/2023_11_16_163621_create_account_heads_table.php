<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_heads', function (Blueprint $table) {
            $table->id();
            $table->integer('reserved_id')->nullable();
            $table->unsignedBigInteger('sister_concern_id')->nullable();
            $table->unsignedBigInteger('account_group_id')->nullable();
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->integer('employee_id')->nullable();
            $table->tinyInteger('payment_mode')->default(0)->comment('0=none,1=bank,2=cash,3=Mobile banking');
            $table->string('name');
            $table->double('bank_commission_percent', 10, 2)->default(0);
            $table->bigInteger('code')->nullable();
            $table->enum('nature', ['Debit', 'Credit'])->default('Debit');
            $table->text('comment')->nullable();
            $table->enum('head_type', ['primary', 'savings'])->default('primary');
            $table->tinyInteger('is_control_account')->default(0)->comment('1 = control account, 0 = non-control account');
            $table->tinyInteger('is_system')->default(0)->comment('1 = system, 0 = non-system');
            $table->tinyInteger('is_editable')->default(1)->comment('1 = editable, 0 = non-editable');
            $table->tinyInteger('status')->default(1)->comment('1 = active, 0 = inactive');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_heads');
    }
};
