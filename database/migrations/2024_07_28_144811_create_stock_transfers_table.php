<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('from_sister_concern_id');
            $table->unsignedBigInteger('to_sister_concern_id');
            $table->string('order_no');
            $table->date('date');
            $table->integer('from_warehouse_id');
            $table->integer('to_warehouse_id');
            $table->tinyInteger('status')->default(1)->comment('1=Pending, 2=Partially Received, 3=Fully Received, 4=Cancelled');
            $table->tinyInteger('is_return')->default(0)->comment('0=Normal Transfer, 1=Return Transfer');
            $table->string('return_reason')->nullable()->comment('Reason for return (Damage, Unsold, etc.)');
            $table->unsignedBigInteger('parent_transfer_id')->nullable()->comment('For return transfers, references original transfer id');
            $table->string('challan_no')->nullable()->comment('Auto-generated delivery challan number');
            $table->string('vehicle_no')->nullable();
            $table->string('driver_name')->nullable();
            $table->string('driver_phone')->nullable();
            $table->string('notes')->nullable();
            $table->unsignedBigInteger('create_user_id');
            $table->unsignedBigInteger('received_user_id')->nullable();
            $table->unsignedBigInteger('alter_user_id')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('stock_transfers'); }
};
