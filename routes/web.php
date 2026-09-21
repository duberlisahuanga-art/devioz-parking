<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CameraController;
use App\Http\Controllers\DemoLoginController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\VehicleController;
use App\Models\Parking;
use App\Models\Service;
use App\Models\Space;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::inertia('/', 'Welcome')
    ->name('home');

Route::post(
    'demo/login/{role}',
    DemoLoginController::class
)->name('demo.login');

Route::middleware([
    'auth',
    'verified',
])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get(
        'dashboard',
        function (Request $request) {
            $totalSpaces = Space::count();

            $occupied = Space::where(
                'status',
                'occupied'
            )->count();

            return Inertia::render(
                'Dashboard',
                [
                    'stats' => [
                        'total_spaces' =>
                            $totalSpaces,

                        'occupied' =>
                            $occupied,

                        'active_reservations' =>
                            $request
                                ->user()
                                ->reservations()
                                ->where(
                                    'status',
                                    'active'
                                )
                                ->count(),

                        'parkings' =>
                            1,

                        'availability' =>
                            $totalSpaces > 0
                                ? round(
                                    (
                                        Space::where(
                                            'status',
                                            'available'
                                        )->count()
                                        / $totalSpaces
                                    ) * 100,
                                    1
                                )
                                : 0,

                        'balance' =>
                            'S/ '.number_format(
                                (float) $request
                                    ->user()
                                    ->load(
                                        'reservations.payments'
                                    )
                                    ->reservations
                                    ->flatMap
                                    ->payments
                                    ->where(
                                        'status',
                                        'paid'
                                    )
                                    ->sum(
                                        'amount'
                                    ),
                                2,
                                '.',
                                ','
                            ),

                        'role' =>
                            $request
                                ->user()
                                ->getRoleNames()
                                ->first()
                            ?? 'driver',

                        'unpaid_reservations' =>
                            $request
                                ->user()
                                ->reservations()
                                ->where(
                                    'payment_status',
                                    'unpaid'
                                )
                                ->count(),

                        'spaces' =>
                            Space::query()
                                ->select([
                                    'id',
                                    'code',
                                    'floor',
                                    'type',
                                    'status',
                                ])
                                ->orderBy('code')
                                ->get(),

                        'parkings_list' =>
                            Parking::query()
                                ->select([
                                    'id',
                                    'name',
                                    'address',
                                    'lat',
                                    'lng',
                                ])
                                ->where(
                                    'slug',
                                    'estacionamiento-devioz'
                                )
                                ->get(),

                        'services' =>
                            Service::where(
                                'active',
                                true
                            )
                                ->orderBy('name')
                                ->get([
                                    'id',
                                    'name',
                                    'description',
                                    'price',
                                    'duration_min',
                                ]),
                    ],
                ]
            );
        }
    )->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Reservas
    |--------------------------------------------------------------------------
    */

    Route::get(
        'reservations',
        [
            ReservationController::class,
            'index',
        ]
    )->name('reservations.index');

    Route::get(
        'reservations/create',
        [
            ReservationController::class,
            'create',
        ]
    )->name('reservations.create');

    Route::post(
        'reservations',
        [
            ReservationController::class,
            'store',
        ]
    )->name('reservations.store');

    Route::post(
        'reservations/{reservation}/checkin',
        [
            ReservationController::class,
            'checkin',
        ]
    )->name('reservations.checkin');

    Route::post(
        'reservations/{reservation}/checkout',
        [
            ReservationController::class,
            'checkout',
        ]
    )->name('reservations.checkout');

    Route::post(
        'reservations/{reservation}/cancel',
        [
            ReservationController::class,
            'cancel',
        ]
    )->name('reservations.cancel');

    Route::post(
        'reservations/{reservation}/extend',
        [
            ReservationController::class,
            'extend',
        ]
    )->name('reservations.extend');

    Route::post(
        'reservations/{reservation}/payment',
        [
            PaymentController::class,
            'store',
        ]
    )->name('reservations.payment');

    /*
    |--------------------------------------------------------------------------
    | Vehículos
    |--------------------------------------------------------------------------
    */

    Route::get(
        'vehicles',
        [
            VehicleController::class,
            'index',
        ]
    )->name('vehicles.index');

    Route::post(
        'vehicles',
        [
            VehicleController::class,
            'store',
        ]
    )->name('vehicles.store');

    Route::delete(
        'vehicles/{vehicle}',
        [
            VehicleController::class,
            'destroy',
        ]
    )->name('vehicles.destroy');

    /*
    |--------------------------------------------------------------------------
    | Administración
    |--------------------------------------------------------------------------
    */

    Route::middleware(
        'role.redirect:admin'
    )->group(function () {

        Route::get(
            'admin/overview',
            [
                AdminController::class,
                'overview',
            ]
        )->name('admin.overview');

        /*
        |--------------------------------------------------------------------------
        | Usuarios
        |--------------------------------------------------------------------------
        */

        Route::get(
            'admin/users',
            [
                AdminController::class,
                'users',
            ]
        )->name('admin.users');

        Route::get(
            'admin/users/{user}',
            [
                AdminController::class,
                'showUser',
            ]
        )->name('admin.users.show');

        /*
        |--------------------------------------------------------------------------
        | Espacios
        |--------------------------------------------------------------------------
        */

        Route::get(
            'admin/spaces',
            [
                AdminController::class,
                'spaces',
            ]
        )->name('admin.spaces');

        /*
        |--------------------------------------------------------------------------
        | Pagos
        |--------------------------------------------------------------------------
        */

        Route::get(
            'admin/payments',
            [
                AdminController::class,
                'payments',
            ]
        )->name('admin.payments');

        Route::post(
            'admin/payments/{payment}/confirm',
            [
                AdminController::class,
                'confirmPayment',
            ]
        )->name('admin.payments.confirm');

        Route::post(
            'admin/payments/{payment}/reject',
            [
                AdminController::class,
                'rejectPayment',
            ]
        )->name('admin.payments.reject');

        /*
        |--------------------------------------------------------------------------
        | Auditoría
        |--------------------------------------------------------------------------
        */

        Route::get(
            'admin/audit',
            [
                AdminController::class,
                'audit',
            ]
        )->name('admin.audit');

        /*
        |--------------------------------------------------------------------------
        | Cámaras
        |--------------------------------------------------------------------------
        */

        Route::prefix('admin')
            ->name('admin.')
            ->group(function (): void {
                Route::resource(
                    'cameras',
                    CameraController::class
                );

                Route::post(
                    'cameras/{camera}/recordings',
                    [
                        CameraController::class,
                        'storeRecording',
                    ]
                )->name(
                    'cameras.recordings.store'
                );

                Route::delete(
                    'cameras/{camera}/recordings/{recording}',
                    [
                        CameraController::class,
                        'destroyRecording',
                    ]
                )->name(
                    'cameras.recordings.destroy'
                );
            });

        /*
        |--------------------------------------------------------------------------
        | Servicios
        |--------------------------------------------------------------------------
        */

        Route::get(
            'admin/services',
            [
                AdminController::class,
                'services',
            ]
        )->name('admin.services');

        Route::post(
            'admin/services',
            [
                AdminController::class,
                'storeService',
            ]
        )->name('admin.services.store');

        Route::put(
            'admin/services/{service}',
            [
                AdminController::class,
                'updateService',
            ]
        )->name('admin.services.update');

        Route::delete(
            'admin/services/{service}',
            [
                AdminController::class,
                'destroyService',
            ]
        )->name('admin.services.destroy');
    });
});

require __DIR__.'/settings.php';