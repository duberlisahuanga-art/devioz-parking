<?php

namespace Database\Seeders;

use App\Models\Camera;
use App\Models\Parking;
use App\Models\Reservation;
use App\Models\Service;
use App\Models\Space;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | ROLES
        |--------------------------------------------------------------------------
        */

        Role::whereNotIn('name', ['admin', 'driver'])
            ->where('guard_name', 'web')
            ->delete();

        $roles = collect(['admin', 'driver'])->mapWithKeys(
            fn (string $role) => [
                $role => Role::findOrCreate($role, 'web'),
            ],
        );

        /*
        |--------------------------------------------------------------------------
        | USUARIOS DEMO BASE
        |--------------------------------------------------------------------------
        */

        $users = [
            'admin' => User::updateOrCreate(
                [
                    'email' => 'admin@devioz.test',
                ],
                [
                    'name' => 'Administrador Devioz',
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ],
            ),

            'driver' => User::updateOrCreate(
                [
                    'email' => 'driver@devioz.test',
                ],
                [
                    'name' => 'Conductor Devioz',
                    'phone' => '+51 999 999 999',
                    'terms_accepted' => true,
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ],
            ),
        ];

        foreach ($users as $key => $user) {
            $user->syncRoles([$roles[$key]]);
        }

        /*
        |--------------------------------------------------------------------------
        | ESTACIONAMIENTO DEVIOZ
        |--------------------------------------------------------------------------
        |
        | Capacidad oficial:
        | 40 plazas
        |
        | Códigos:
        | P-01 hasta P-40
        |
        */

        $parking = Parking::updateOrCreate(
            [
                'slug' => 'estacionamiento-devioz',
            ],
            [
                'name' => 'Estacionamiento Devioz',
                'address' => 'Dirección pendiente de confirmar (TODO: reemplazar con ubicación real)',
                'lat' => -12.0984,
                'lng' => -77.0365,
                'capacity' => 40,
                'owner_id' => $users['admin']->id,
                'status' => 'active',
            ],
        );

        /*
        |--------------------------------------------------------------------------
        | PLAZAS P-01 HASTA P-40
        |--------------------------------------------------------------------------
        */

        for ($spaceIndex = 1; $spaceIndex <= 40; $spaceIndex++) {
            Space::updateOrCreate(
                [
                    'parking_id' => $parking->id,
                    'code' => 'P-'.str_pad(
                        (string) $spaceIndex,
                        2,
                        '0',
                        STR_PAD_LEFT
                    ),
                ],
                [
                    'floor' => 1,
                    'type' => 'car',
                    'status' => 'available',
                    'current_reservation_id' => null,
                ],
            );
        }

        /*
        |--------------------------------------------------------------------------
        | VEHÍCULO DEMO
        |--------------------------------------------------------------------------
        */

        $vehicle = Vehicle::updateOrCreate(
            [
                'user_id' => $users['driver']->id,
                'plate' => 'ABC-123',
            ],
            [
                'brand' => 'Toyota',
                'model' => 'Corolla',
                'color' => 'Blanco',
                'type' => 'car',
            ],
        );

        /*
        |--------------------------------------------------------------------------
        | RESERVAS DEMO
        |--------------------------------------------------------------------------
        */

        $reservedSpaces = Space::where('parking_id', $parking->id)
            ->orderBy('id')
            ->take(4)
            ->get();

        $reservationData = [
            [
                'status' => 'active',
                'amount' => 8,
                'payment_status' => 'paid',
            ],
            [
                'status' => 'active',
                'amount' => 8,
                'payment_status' => 'unpaid',
            ],
            [
                'status' => 'confirmed',
                'amount' => 16,
                'payment_status' => 'paid',
            ],
            [
                'status' => 'confirmed',
                'amount' => 16,
                'payment_status' => 'unpaid',
            ],
        ];

        foreach ($reservationData as $index => $data) {
            $space = $reservedSpaces[$index];

            $reservation = Reservation::updateOrCreate(
                [
                    'space_id' => $space->id,
                    'user_id' => $users['driver']->id,
                ],
                [
                    'parking_id' => $space->parking_id,
                    'vehicle_id' => $vehicle->id,
                    'check_in_at' => Carbon::now()->subMinutes($index * 15),
                    'check_out_at' => null,
                    'expected_duration_min' => 60,
                    ...$data,
                    'currency' => 'PEN',
                ],
            );

            $space->update([
                'status' => $index < 2
                    ? 'occupied'
                    : 'reserved',
                'current_reservation_id' => $reservation->id,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | SERVICIOS
        |--------------------------------------------------------------------------
        */

        Service::updateOrCreate(
            [
                'name' => 'Lavado express',
            ],
            [
                'description' => 'Lavado exterior rápido.',
                'price' => 15,
                'duration_min' => 30,
                'active' => true,
            ],
        );

        Service::updateOrCreate(
            [
                'name' => 'Lavado + encerado',
            ],
            [
                'description' => 'Lavado completo con encerado.',
                'price' => 25,
                'duration_min' => 60,
                'active' => true,
            ],
        );

        /*
        |--------------------------------------------------------------------------
        | CÁMARAS CCTV
        |--------------------------------------------------------------------------
        |
        | Las cámaras se mantienen únicamente para videovigilancia.
        | No controlan reservas, ocupación, ingreso ni salida.
        |
        */

        Camera::updateOrCreate(
            [
                'name' => 'Cámara Entrada',
            ],
            [
                'location' => 'Entrada principal',
                'stream_url' => 'https://test-streams.mux.dev/x36xhzz/x36xhzz.m3u8',
                'source_type' => 'demo',
                'type' => 'entrada',
                'status' => 'online',
                'active' => true,
                'last_heartbeat_at' => now(),
            ],
        );

        Camera::updateOrCreate(
            [
                'name' => 'Cámara Zona A',
            ],
            [
                'location' => 'Zona de vigilancia - Plazas P-01 a P-20',
                'stream_url' => null,
                'source_type' => 'webcam',
                'type' => 'zona',
                'status' => 'online',
                'active' => true,
                'last_heartbeat_at' => now(),
            ],
        );

        Camera::updateOrCreate(
            [
                'name' => 'Cámara Zona B',
            ],
            [
                'location' => 'Zona de vigilancia - Plazas P-21 a P-40',
                'stream_url' => null,
                'source_type' => 'rtsp',
                'type' => 'zona',
                'status' => 'offline',
                'active' => true,
                'last_heartbeat_at' => null,
            ],
        );
    }
}  

