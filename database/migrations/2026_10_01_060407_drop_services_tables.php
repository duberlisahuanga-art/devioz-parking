<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Eliminar módulo Servicios
        |--------------------------------------------------------------------------
        */

        Schema::dropIfExists('reservation_services');

        Schema::dropIfExists('services');
    }

    public function down(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Restaurar services
        |--------------------------------------------------------------------------
        */

        Schema::create('services', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->unsignedInteger('duration_min');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | Restaurar reservation_services
        |--------------------------------------------------------------------------
        */

        Schema::create(
            'reservation_services',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId('reservation_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->foreignId('service_id')
                    ->constrained()
                    ->restrictOnDelete();

                $table->decimal('price', 10, 2);

                $table->timestamps();

                $table->unique([
                    'reservation_id',
                    'service_id',
                ]);
            }
        );
    }
};