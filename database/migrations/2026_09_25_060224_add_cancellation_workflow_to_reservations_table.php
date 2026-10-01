<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            /*
             * Estados posibles:
             * none
             * pending
             * approved
             * rejected
             */
            $table->string('cancellation_status', 20)
                ->default('none')
                ->index();

            // Motivo escrito por el conductor.
            $table->text('cancellation_reason')
                ->nullable();

            // Fecha/hora en que se solicitó.
            $table->timestamp('cancellation_requested_at')
                ->nullable();

            // Fecha/hora de revisión administrativa.
            $table->timestamp('cancellation_reviewed_at')
                ->nullable();

            // Administrador que aprobó o rechazó.
            $table->foreignId('cancellation_reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Observación del administrador.
            $table->text('cancellation_review_note')
                ->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropConstrainedForeignId(
                'cancellation_reviewed_by'
            );

            $table->dropColumn([
                'cancellation_status',
                'cancellation_reason',
                'cancellation_requested_at',
                'cancellation_reviewed_at',
                'cancellation_review_note',
            ]);
        });
    }
};