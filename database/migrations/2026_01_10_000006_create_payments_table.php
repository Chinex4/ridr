<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('ride_id')->index();
            $table->uuid('rider_id')->index();
            $table->string('provider', 20)->default('paystack');
            $table->string('reference')->unique();
            $table->string('authorization_code')->nullable();
            $table->string('status', 20)->default('initiated')->index();
            $table->unsignedInteger('amount');
            $table->string('currency', 3)->default('NGN');
            $table->timestamp('paid_at')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamps();

            $table->foreign('ride_id')->references('id')->on('rides')->cascadeOnDelete();
            $table->foreign('rider_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
