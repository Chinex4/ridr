<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ride_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('ride_id')->index();
            $table->string('type', 50)->index();
            $table->uuid('actor_user_id')->nullable()->index();
            $table->json('meta')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('ride_id')->references('id')->on('rides')->cascadeOnDelete();
            $table->foreign('actor_user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ride_events');
    }
};
