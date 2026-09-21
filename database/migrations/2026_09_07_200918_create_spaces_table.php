<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spaces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parking_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->unsignedTinyInteger('floor')->default(1);
            $table->enum('type', ['car', 'moto', 'ev', 'disabled', 'valet'])->default('car');
            $table->enum('status', ['available', 'occupied', 'reserved', 'maintenance'])->default('available');
            $table->unsignedBigInteger('current_reservation_id')->nullable();
            $table->timestamps();
            $table->unique(['parking_id', 'code']);
            $table->index(['parking_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spaces');
    }
};
