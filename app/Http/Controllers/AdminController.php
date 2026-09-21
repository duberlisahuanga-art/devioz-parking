<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Parking;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\Service;
use App\Models\Space;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AdminController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | OVERVIEW
    |--------------------------------------------------------------------------
    */

    public function overview(
        Request $request
    ): Response {
        $totalSpaces = Space::count();

        return Inertia::render(
            'Admin/Overview',
            [
                'metrics' => [
                    'total_spaces' =>
                        $totalSpaces,

                    'parkings' =>
                        Parking::count(),

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

                    'active_reservations' =>
                        Reservation::where(
                            'status',
                            'active'
                        )->count(),
                ],

                'parkings' =>
                    Parking::withCount([
                        'spaces',

                        'spaces as occupied_spaces_count' =>
                            fn ($query) =>
                                $query->where(
                                    'status',
                                    'occupied'
                                ),
                    ])
                        ->orderBy('name')
                        ->get(),

                'users' =>
                    User::with('roles')
                        ->latest()
                        ->get(),
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | USUARIOS
    |--------------------------------------------------------------------------
    */

    public function users(
        Request $request
    ): Response {
        $query = User::query()
            ->with([
                'roles:id,name',
            ])
            ->withCount([
                'vehicles',
                'reservations',
            ]);

        if ($request->filled('search')) {
            $search = $request
                ->string('search')
                ->toString();

            $query->where(
                function ($query) use ($search) {
                    $query
                        ->where(
                            'name',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'email',
                            'like',
                            "%{$search}%"
                        );
                }
            );
        }

        if ($request->filled('role')) {
            $role = $request
                ->string('role')
                ->toString();

            $query->whereHas(
                'roles',
                function ($query) use ($role) {
                    $query->where(
                        'name',
                        $role
                    );
                }
            );
        }

        if ($request->filled('verified')) {
            $verified = $request
                ->string('verified')
                ->toString();

            if ($verified === 'yes') {
                $query->whereNotNull(
                    'email_verified_at'
                );
            }

            if ($verified === 'no') {
                $query->whereNull(
                    'email_verified_at'
                );
            }
        }

        if ($request->filled('two_factor')) {
            $twoFactor = $request
                ->string('two_factor')
                ->toString();

            if ($twoFactor === 'yes') {
                $query->whereNotNull(
                    'two_factor_confirmed_at'
                );
            }

            if ($twoFactor === 'no') {
                $query->whereNull(
                    'two_factor_confirmed_at'
                );
            }
        }

        $users = $query
            ->latest()
            ->get([
                'id',
                'name',
                'email',
                'phone',
                'email_verified_at',
                'two_factor_confirmed_at',
                'terms_accepted',
                'created_at',
            ]);

        $allUsers = User::query()
            ->with('roles:id,name')
            ->get([
                'id',
                'email_verified_at',
                'two_factor_confirmed_at',
            ]);

        return Inertia::render(
            'Admin/Users',
            [
                'users' =>
                    $users,

                'filters' => [
                    'search' =>
                        $request->input('search'),

                    'role' =>
                        $request->input('role'),

                    'verified' =>
                        $request->input('verified'),

                    'two_factor' =>
                        $request->input('two_factor'),
                ],

                'summary' => [
                    'total' =>
                        $allUsers->count(),

                    'admins' =>
                        $allUsers
                            ->filter(
                                fn (User $user) =>
                                    $user
                                        ->roles
                                        ->contains(
                                            'name',
                                            'admin'
                                        )
                            )
                            ->count(),

                    'drivers' =>
                        $allUsers
                            ->filter(
                                fn (User $user) =>
                                    $user
                                        ->roles
                                        ->contains(
                                            'name',
                                            'driver'
                                        )
                            )
                            ->count(),

                    'verified' =>
                        $allUsers
                            ->whereNotNull(
                                'email_verified_at'
                            )
                            ->count(),

                    'two_factor' =>
                        $allUsers
                            ->whereNotNull(
                                'two_factor_confirmed_at'
                            )
                            ->count(),
                ],
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | DETALLE DE USUARIO
    |--------------------------------------------------------------------------
    */

    public function showUser(
        User $user
    ): Response {
        $user->load([
            'roles:id,name',

            'vehicles' => function ($query) {
                $query->orderBy('plate');
            },

            'reservations' => function ($query) {
                $query
                    ->with([
                        'space:id,code',
                        'vehicle:id,plate,brand,model',
                        'payments:id,reservation_id,amount,method,status',
                        'services:id,name',
                    ])
                    ->latest();
            },
        ]);

        return Inertia::render(
            'Admin/UserShow',
            [
                'user' => [
                    'id' =>
                        $user->id,

                    'name' =>
                        $user->name,

                    'email' =>
                        $user->email,

                    'phone' =>
                        $user->phone,

                    'email_verified_at' =>
                        $user->email_verified_at,

                    'two_factor_confirmed_at' =>
                        $user->two_factor_confirmed_at,

                    'terms_accepted' =>
                        $user->terms_accepted,

                    'created_at' =>
                        $user->created_at,

                    'role' =>
                        $user->roles
                            ->first()
                            ?->name
                        ?? 'driver',

                    'vehicles' =>
                        $user->vehicles,

                    'reservations' =>
                        $user->reservations,
                ],

                'summary' => [
                    'vehicles' =>
                        $user
                            ->vehicles
                            ->count(),

                    'reservations' =>
                        $user
                            ->reservations
                            ->count(),

                    'active_reservations' =>
                        $user
                            ->reservations
                            ->where(
                                'status',
                                'active'
                            )
                            ->count(),

                    'completed_reservations' =>
                        $user
                            ->reservations
                            ->where(
                                'status',
                                'completed'
                            )
                            ->count(),
                ],
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | ESPACIOS
    |--------------------------------------------------------------------------
    */

    public function spaces(): Response
    {
        $parking = Parking::query()
            ->where(
                'slug',
                'estacionamiento-devioz'
            )
            ->firstOrFail();

        $spaces = Space::query()
            ->where(
                'parking_id',
                $parking->id
            )
            ->with([
                'currentReservation.user:id,name,email,phone',
                'currentReservation.vehicle:id,user_id,plate,brand,model,color,type',
            ])
            ->orderBy('code')
            ->get([
                'id',
                'parking_id',
                'code',
                'floor',
                'type',
                'status',
                'current_reservation_id',
            ])
            ->map(function (
                Space $space
            ): array {
                $reservation =
                    $space->currentReservation;

                if (! $reservation) {
                    return [
                        'id' =>
                            $space->id,

                        'parking_id' =>
                            $space->parking_id,

                        'code' =>
                            $space->code,

                        'floor' =>
                            $space->floor,

                        'type' =>
                            $space->type,

                        'status' =>
                            $space->status,

                        'current_reservation_id' =>
                            null,

                        'reservation' =>
                            null,
                    ];
                }

                $startAt =
                    $reservation->check_in_at;

                $expectedEndAt =
                    $startAt
                        ? $startAt
                            ->copy()
                            ->addMinutes(
                                $reservation
                                    ->expected_duration_min
                            )
                        : null;

                $remainingMinutes =
                    $expectedEndAt
                        ? max(
                            0,
                            (int) now()
                                ->diffInMinutes(
                                    $expectedEndAt,
                                    false
                                )
                        )
                        : null;

                return [
                    'id' =>
                        $space->id,

                    'parking_id' =>
                        $space->parking_id,

                    'code' =>
                        $space->code,

                    'floor' =>
                        $space->floor,

                    'type' =>
                        $space->type,

                    'status' =>
                        $space->status,

                    'current_reservation_id' =>
                        $space
                            ->current_reservation_id,

                    'reservation' => [
                        'id' =>
                            $reservation->id,

                        'status' =>
                            $reservation->status,

                        'payment_status' =>
                            $reservation
                                ->payment_status,

                        'amount' =>
                            $reservation
                                ->amount_formatted,

                        'check_in_at' =>
                            $reservation
                                ->check_in_at
                                ?->toISOString(),

                        'expected_duration_min' =>
                            $reservation
                                ->expected_duration_min,

                        'expected_end_at' =>
                            $expectedEndAt
                                ?->toISOString(),

                        'remaining_minutes' =>
                            $remainingMinutes,

                        'user' =>
                            $reservation->user
                                ? [
                                    'id' =>
                                        $reservation
                                            ->user
                                            ->id,

                                    'name' =>
                                        $reservation
                                            ->user
                                            ->name,

                                    'email' =>
                                        $reservation
                                            ->user
                                            ->email,

                                    'phone' =>
                                        $reservation
                                            ->user
                                            ->phone,
                                ]
                                : null,

                        'vehicle' =>
                            $reservation->vehicle
                                ? [
                                    'id' =>
                                        $reservation
                                            ->vehicle
                                            ->id,

                                    'plate' =>
                                        $reservation
                                            ->vehicle
                                            ->plate,

                                    'brand' =>
                                        $reservation
                                            ->vehicle
                                            ->brand,

                                    'model' =>
                                        $reservation
                                            ->vehicle
                                            ->model,

                                    'color' =>
                                        $reservation
                                            ->vehicle
                                            ->color,

                                    'type' =>
                                        $reservation
                                            ->vehicle
                                            ->type,
                                ]
                                : null,
                    ],
                ];
            })
            ->values();

        return Inertia::render(
            'Admin/Spaces',
            [
                'parking' => [
                    'id' =>
                        $parking->id,

                    'name' =>
                        $parking->name,

                    'address' =>
                        $parking->address,

                    'capacity' =>
                        $parking->capacity,
                ],

                'spaces' =>
                    $spaces,

                'summary' => [
                    'total' =>
                        $spaces->count(),

                    'available' =>
                        $spaces
                            ->where(
                                'status',
                                'available'
                            )
                            ->count(),

                    'reserved' =>
                        $spaces
                            ->where(
                                'status',
                                'reserved'
                            )
                            ->count(),

                    'occupied' =>
                        $spaces
                            ->where(
                                'status',
                                'occupied'
                            )
                            ->count(),
                ],
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | PAGOS
    |--------------------------------------------------------------------------
    */

    public function payments(): Response
    {
        return Inertia::render(
            'Admin/Payments',
            [
                'payments' =>
                    Payment::with([
                        'reservation.user',
                        'reservation.space',
                    ])
                        ->where(
                            'status',
                            'pending'
                        )
                        ->latest()
                        ->get(),
            ]
        );
    }

    public function confirmPayment(
        Request $request,
        Payment $payment
    ): RedirectResponse {
        abort_unless(
            $payment->status === 'pending',
            422,
            'Este pago ya fue procesado.'
        );

        DB::transaction(
            function () use ($payment): void {
                $payment->update([
                    'status' =>
                        'paid',
                ]);

                $payment
                    ->reservation()
                    ->update([
                        'payment_status' =>
                            'paid',
                    ]);
            }
        );

        $payment->refresh();

        $payment->load([
            'reservation.space',
            'reservation.vehicle',
        ]);

        AuditService::log(
            request: $request,

            module: 'Pagos',

            action: 'Aprobado',

            description:
                "Pago #{$payment->id} aprobado para la reserva #{$payment->reservation_id}.",

            entity: $payment,

            reference:
                "PAY-{$payment->id}",

            metadata: [
                'payment_id' =>
                    $payment->id,

                'reservation_id' =>
                    $payment
                        ->reservation_id,

                'space' =>
                    $payment
                        ->reservation
                        ?->space
                        ?->code,

                'vehicle_plate' =>
                    $payment
                        ->reservation
                        ?->vehicle
                        ?->plate,

                'amount' =>
                    $payment->amount,

                'method' =>
                    $payment->method,

                'payment_status' =>
                    $payment->status,
            ],
        );

        return back()->with(
            'toast',
            [
                'type' =>
                    'success',

                'message' =>
                    'Pago confirmado correctamente.',
            ]
        );
    }

    public function rejectPayment(
        Request $request,
        Payment $payment
    ): RedirectResponse {
        abort_unless(
            $payment->status === 'pending',
            422,
            'Este pago ya fue procesado.'
        );

        DB::transaction(
            function () use ($payment): void {
                $payment->update([
                    'status' =>
                        'rejected',
                ]);

                $payment
                    ->reservation()
                    ->update([
                        'payment_status' =>
                            'unpaid',
                    ]);
            }
        );

        $payment->refresh();

        $payment->load([
            'reservation.space',
            'reservation.vehicle',
        ]);

        AuditService::log(
            request: $request,

            module: 'Pagos',

            action: 'Rechazado',

            description:
                "Pago #{$payment->id} rechazado para la reserva #{$payment->reservation_id}.",

            entity: $payment,

            reference:
                "PAY-{$payment->id}",

            metadata: [
                'payment_id' =>
                    $payment->id,

                'reservation_id' =>
                    $payment
                        ->reservation_id,

                'space' =>
                    $payment
                        ->reservation
                        ?->space
                        ?->code,

                'vehicle_plate' =>
                    $payment
                        ->reservation
                        ?->vehicle
                        ?->plate,

                'amount' =>
                    $payment->amount,

                'method' =>
                    $payment->method,

                'payment_status' =>
                    $payment->status,
            ],
        );

        return back()->with(
            'toast',
            [
                'type' =>
                    'warning',

                'message' =>
                    'Pago rechazado. El conductor puede enviar un nuevo voucher.',
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | AUDITORÍA
    |--------------------------------------------------------------------------
    */

    public function audit(
        Request $request
    ): Response {
        $query = AuditLog::query()
            ->with([
                'user:id,name,email',
            ])
            ->latest();

        if ($request->filled('module')) {
            $query->where(
                'module',
                $request
                    ->string('module')
                    ->toString()
            );
        }

        if ($request->filled('action')) {
            $action = $request
                ->string('action')
                ->toString();

            if ($action === 'Ingreso') {
                $query->whereIn(
                    'action',
                    [
                        'Ingreso',
                        'Check-in',
                    ]
                );
            } elseif ($action === 'Salida') {
                $query->whereIn(
                    'action',
                    [
                        'Salida',
                        'Check-out',
                    ]
                );
            } else {
                $query->where(
                    'action',
                    $action
                );
            }
        }

        if ($request->filled('user_id')) {
            $query->where(
                'user_id',
                $request->integer(
                    'user_id'
                )
            );
        }

        if ($request->filled('date')) {
            $query->whereDate(
                'created_at',
                $request->input(
                    'date'
                )
            );
        }

        $logs = $query
            ->paginate(20)
            ->withQueryString();

        $actions = AuditLog::query()
            ->select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action')
            ->map(
                function (
                    string $action
                ): string {
                    return match ($action) {
                        'Check-in' =>
                            'Ingreso',

                        'Check-out' =>
                            'Salida',

                        default =>
                            $action,
                    };
                }
            )
            ->unique()
            ->sort()
            ->values();

        return Inertia::render(
            'Admin/Audit',
            [
                'logs' =>
                    $logs,

                'filters' => [
                    'module' =>
                        $request->input(
                            'module'
                        ),

                    'action' =>
                        $request->input(
                            'action'
                        ),

                    'user_id' =>
                        $request->input(
                            'user_id'
                        ),

                    'date' =>
                        $request->input(
                            'date'
                        ),
                ],

                'modules' =>
                    AuditLog::query()
                        ->select('module')
                        ->distinct()
                        ->orderBy('module')
                        ->pluck('module'),

                'actions' =>
                    $actions,

                'users' =>
                    User::query()
                        ->select(
                            'id',
                            'name',
                            'email'
                        )
                        ->orderBy('name')
                        ->get(),
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SERVICIOS
    |--------------------------------------------------------------------------
    */

    public function services(): Response
    {
        return Inertia::render(
            'Admin/Services',
            [
                'services' =>
                    Service::orderBy(
                        'name'
                    )->get(),
            ]
        );
    }

    public function storeService(
        Request $request
    ): RedirectResponse {
        Service::create(
            $this->validatedService(
                $request
            )
        );

        return back()->with(
            'toast',
            [
                'type' =>
                    'success',

                'message' =>
                    'Servicio creado.',
            ]
        );
    }

    public function updateService(
        Request $request,
        Service $service
    ): RedirectResponse {
        $service->update(
            $this->validatedService(
                $request
            )
        );

        return back()->with(
            'toast',
            [
                'type' =>
                    'success',

                'message' =>
                    'Servicio actualizado.',
            ]
        );
    }

    public function destroyService(
        Service $service
    ): RedirectResponse {
        abort_if(
            $service
                ->reservations()
                ->exists(),
            422,
            'No puedes eliminar un servicio usado en reservas.'
        );

        $service->delete();

        return back()->with(
            'toast',
            [
                'type' =>
                    'success',

                'message' =>
                    'Servicio eliminado.',
            ]
        );
    }

    private function validatedService(
        Request $request
    ): array {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:120',
            ],

            'description' => [
                'nullable',
                'string',
                'max:500',
            ],

            'price' => [
                'required',
                'numeric',
                'min:0',
            ],

            'duration_min' => [
                'required',
                'integer',
                'min:1',
                'max:1440',
            ],

            'active' => [
                'boolean',
            ],
        ]) + [
            'active' =>
                $request->boolean(
                    'active'
                ),
        ];
    }
}