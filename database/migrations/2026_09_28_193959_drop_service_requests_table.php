<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('service_requests');
    }

    public function down(): void
    {
        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('reservation_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('service_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('vehicle_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->date('requested_for');

            $table->decimal('price', 10, 2);

            $table->string('status')
                ->default('requested')
                ->index();

            $table->string('payment_status')
                ->default('unpaid')
                ->index();

            $table->text('notes')
                ->nullable();

            $table->timestamp('confirmed_at')
                ->nullable();

            $table->timestamp('started_at')
                ->nullable();

            $table->timestamp('completed_at')
                ->nullable();

            $table->timestamp('cancelled_at')
                ->nullable();

            $table->timestamps();
        });
    }
};