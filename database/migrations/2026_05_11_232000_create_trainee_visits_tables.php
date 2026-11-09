<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trainee_visit_types', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sister_concern_id');
            $table->string('name');
            $table->boolean('status')->default(1);
            $table->timestamps();
        });

        Schema::create('trainee_visit_clients', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sister_concern_id');
            $table->unsignedBigInteger('trainee_id'); // Points to Supplier ID
            $table->string('name');
            $table->string('mobile_no');
            $table->string('email')->nullable();
            $table->unsignedBigInteger('division_id')->nullable();
            $table->unsignedBigInteger('district_id')->nullable();
            $table->unsignedBigInteger('upazila_id')->nullable();
            $table->unsignedBigInteger('pourashava_id')->nullable();
            $table->unsignedBigInteger('union_id')->nullable();
            $table->unsignedBigInteger('village_id')->nullable();
            $table->timestamps();
        });

        Schema::create('trainee_visits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sister_concern_id');
            $table->unsignedBigInteger('trainee_id'); // Points to Supplier ID
            $table->unsignedBigInteger('task_id')->nullable();
            $table->unsignedBigInteger('task_item_id')->nullable();
            $table->unsignedBigInteger('trainee_visit_type_id')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('trainee_visit_client_id')->nullable();
            $table->string('image')->nullable();
            $table->date('date');
            $table->text('notes')->nullable();
            $table->string('lat')->nullable();
            $table->string('long')->nullable();
            $table->text('location')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trainee_visits');
        Schema::dropIfExists('trainee_visit_clients');
        Schema::dropIfExists('trainee_visit_types');
    }
};
