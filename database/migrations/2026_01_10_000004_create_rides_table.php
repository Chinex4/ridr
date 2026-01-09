<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rides', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('rider_id')->index();
            $table->uuid('driver_id')->nullable()->index();
            $table->string('status', 20)->index();
            $table->decimal('pickup_lat', 10, 7);
            $table->decimal('pickup_lng', 10, 7);
            $table->string('pickup_address');
            $table->decimal('dropoff_lat', 10, 7);
            $table->decimal('dropoff_lng', 10, 7);
            $table->string('dropoff_address');
            $table->decimal('estimated_distance_km', 8, 2)->nullable();
            $table->unsignedInteger('estimated_duration_min')->nullable();
            $table->unsignedInteger('estimated_fare_amount');
            $table->unsignedInteger('final_fare_amount')->nullable();
            $table->string('currency', 3)->default('NGN');
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('arrived_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancel_by', 20)->nullable();
            $table->string('cancel_reason')->nullable();
            $table->decimal('driver_last_lat', 10, 7)->nullable();
            $table->decimal('driver_last_lng', 10, 7)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('rider_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('driver_id')->references('id')->on('users')->nullOnDelete();
            $table->index(['rider_id', 'status']);
            $table->index(['driver_id', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rides');
    }
};
