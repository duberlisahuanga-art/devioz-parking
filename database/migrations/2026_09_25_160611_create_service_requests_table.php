<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
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

            /*
             * Fecha solicitada por el conductor
             * para realizar el servicio.
             */
            $table->date('requested_for');

            /*
             * Guardamos el precio del momento
             * para mantener el historial aunque
             * el precio del servicio cambie después.
             */
            $table->decimal('price', 10, 2);

            /*
             * Estados del servicio:
             *
             * requested
             * confirmed
             * in_progress
             * completed
             * cancelled
             */
            $table->string('status', 30)
                ->default('requested')
                ->index();

            /*
             * Estado del pago:
             *
             * unpaid
             * pending
             * paid
             * rejected
             */
            $table->string('payment_status', 30)
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

    public function down(): void
    {
        Schema::dropIfExists('service_requests');
    }
};