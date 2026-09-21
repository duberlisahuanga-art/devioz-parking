<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recordings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('camera_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('path');
            $table->string('triggered_by')->default('manual');
            $table->dateTime('started_at');
            $table->dateTime('ended_at')->nullable();
            $table->unsignedInteger('duration_sec')->nullable();
            $table->decimal('size_mb', 8, 2)->nullable();
            $table->string('status')->default('recording');
            $table->timestamps();
            $table->index(['camera_id', 'status']);
            $table->index('ended_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recordings');
    }
};