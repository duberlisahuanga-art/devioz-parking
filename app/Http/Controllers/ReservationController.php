<?php

namespace App\Http\Controllers;

use App\Models\Parking;
use App\Models\Reservation;
use App\Models\Space;
use App\Services\AuditService;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
class ReservationController extends Controller
{
    private const MONTHLY_PRICE = 100.00;

    /**
     * Listado de reservas.
     *
     * Administrador:
     * - Ve todas las reservas.
     *
     * Conductor:
     * - Ve solamente sus reservas.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        $query = Reservation::query()
            ->with([
                'user',
                'parking',
                'space',
                'vehicle',
                'payments',
            ]);

        if (! $user->hasRole('admin')) {
            $query->where(
                'user_id',
                $user->id
            );
        }

        return Inertia::render(
            'Reservations/Index',
            [
                'reservations' => $query
                    ->latest()
                    ->get(),

                'isAdmin' =>
                    $user->hasRole('admin'),

                'paymentMethods' =>
                    config(
                        'payments.methods'
                    ),

                'yapeQrUrl' =>
                    $this->yapeQrUrl(),
            ]
        );
    }

    /**
     * URL pública del QR de Yape.
     */
    private function yapeQrUrl(): ?string
    {
        $path = Parking::query()
            ->where(
                'slug',
                'estacionamiento-devioz'
            )
            ->value(
                'yape_qr_path'
            );

        return $path
            ? Storage::disk('public')
                ->url($path)
            : null;
    }

    /**
     * Formulario de nueva mensualidad.
     *
     * Solo puede ingresar el conductor.
     */
    public function create(
        Request $request
    ): Response {
        abort_if(
            $request
                ->user()
                ->hasRole('admin'),
            403,
            'El administrador no puede crear reservas.'
        );

        $parking = Parking::query()
            ->where(
                'slug',
                'estacionamiento-devioz'
            )
            ->firstOrFail();

        return Inertia::render(
            'Reservations/Create',
            [
                'vehicles' =>
                    $request
                        ->user()
                        ->vehicles()
                        ->orderBy('plate')
                        ->get(),

                'spaces' =>
                    $parking
                        ->spaces()
                        ->orderBy('floor')
                        ->orderBy('code')
                        ->get(),

                'monthlyPrice' =>
                    self::MONTHLY_PRICE,
            ]
        );
    }

    /**
     * Crear una mensualidad.
     */
    public function store(
        Request $request
    ): RedirectResponse {
        abort_if(
            $request
                ->user()
                ->hasRole('admin'),
            403,
            'El administrador no puede crear reservas.'
        );

        $data = $request->validate([
            'space_id' => [
                'required',
                'integer',
                'exists:spaces,id',
            ],

            'vehicle_id' => [
                'required',
                'integer',
                'exists:vehicles,id',
            ],

            'starts_on' => [
                'required',
                'date',
                'after_or_equal:today',
            ],
        ]);

        /*
         * Ejemplo:
         *
         * Inicio:
         * 26/09/2026
         *
         * Fin:
         * 25/10/2026
         */
        $startsOn = CarbonImmutable::parse(
            $data['starts_on']
        )->startOfDay();

        $endsOn = $startsOn
            ->addMonthNoOverflow()
            ->subDay();

        $reservation = DB::transaction(
            function () use (
                $data,
                $request,
                $startsOn,
                $endsOn
            ): Reservation {
                /*
                 * Bloqueamos la plaza para evitar
                 * que dos usuarios reserven la misma.
                 */
                $space = Space::query()
                    ->whereKey(
                        $data['space_id']
                    )
                    ->where(
                        'status',
                        'available'
                    )
                    ->lockForUpdate()
                    ->first();

                abort_unless(
                    $space,
                    422,
                    'La plaza seleccionada ya no se encuentra disponible.'
                );

                /*
                 * El vehículo debe pertenecer
                 * al conductor autenticado.
                 */
                $vehicleExists = $request
                    ->user()
                    ->vehicles()
                    ->whereKey(
                        $data['vehicle_id']
                    )
                    ->exists();

                abort_unless(
                    $vehicleExists,
                    403,
                    'El vehículo seleccionado no te pertenece.'
                );

                /*
                 * Precio mensual:
                 * S/100
                 *
                 * + servicios iniciales.
                 */
                $amount = self::MONTHLY_PRICE;

                $reservation = $request
                    ->user()
                    ->reservations()
                    ->create([
                        'space_id' =>
                            $space->id,

                        'parking_id' =>
                            $space->parking_id,

                        'vehicle_id' =>
                            $data['vehicle_id'],

                        'starts_on' =>
                            $startsOn
                                ->toDateString(),

                        'ends_on' =>
                            $endsOn
                                ->toDateString(),

                        /*
                         * El vehículo todavía
                         * no ha ingresado.
                         */
                        'check_in_at' =>
                            null,

                        'check_out_at' =>
                            null,

                        /*
                         * Ya no trabajamos
                         * con duración por horas.
                         */
                        'expected_duration_min' =>
                            null,

                        'status' =>
                            'confirmed',

                        'amount' =>
                            $amount,

                        'currency' =>
                            'PEN',

                        'payment_status' =>
                            'unpaid',
                    ]);

                /*
                 * La plaza deja de estar
                 * disponible y queda reservada.
                 */
                $space->update([
                    'status' =>
                        'reserved',

                    'current_reservation_id' =>
                        $reservation->id,
                ]);

                return $reservation;
            }
        );

        $reservation->load([
            'space',
            'vehicle',
        ]);

        AuditService::log(
            request: $request,

            module: 'Reservas',

            action: 'Creada',

            description:
                "Reserva mensual creada para la plaza {$reservation->space?->code}.",

            entity: $reservation,

            reference:
                $reservation->space?->code,

            metadata: [
                'reservation_id' =>
                    $reservation->id,

                'space' =>
                    $reservation->space?->code,

                'vehicle_plate' =>
                    $reservation
                        ->vehicle
                        ?->plate,

                'starts_on' =>
                    $reservation
                        ->starts_on
                        ?->toDateString(),

                'ends_on' =>
                    $reservation
                        ->ends_on
                        ?->toDateString(),

                'monthly_price' =>
                    self::MONTHLY_PRICE,

                'status' =>
                    $reservation->status,

                'payment_status' =>
                    $reservation
                        ->payment_status,

                'amount' =>
                    $reservation->amount,
            ],
        );

        return to_route(
            'reservations.index'
        )->with(
            'toast',
            [
                'type' =>
                    'success',

                'message' =>
                    "Reserva mensual confirmada por {$reservation->amount_formatted}.",
            ]
        );
    }

    /**
     * Registrar ingreso físico.
     *
     * Solamente administrador.
     */
    public function checkin(
        Request $request,
        Reservation $reservation
    ): RedirectResponse {
        $this->ensureAdmin($request);

        /*
         * No debe registrarse ingreso
         * mientras se está revisando
         * una cancelación.
         */
        abort_if(
            $reservation
                ->cancellation_status
                === 'pending',
            422,
            'No se puede registrar el ingreso mientras exista una solicitud de cancelación pendiente.'
        );

        abort_unless(
            $reservation->status
                === 'confirmed',
            422,
            'La reserva no está disponible para registrar ingreso.'
        );

        DB::transaction(
            function () use (
                $reservation
            ): void {
                $reservation->update([
                    'status' =>
                        'active',

                    'check_in_at' =>
                        now(),

                    'check_out_at' =>
                        null,
                ]);

                /*
                 * Vehículo físicamente
                 * dentro de la cochera.
                 */
                $reservation
                    ->space()
                    ->update([
                        'status' =>
                            'occupied',

                        'current_reservation_id' =>
                            $reservation->id,
                    ]);
            }
        );

        $reservation->refresh();

        $reservation->load([
            'space',
            'vehicle',
        ]);

        AuditService::log(
            request: $request,

            module: 'Reservas',

            action: 'Ingreso',

            description:
                "Ingreso registrado en la plaza {$reservation->space?->code}.",

            entity: $reservation,

            reference:
                $reservation->space?->code,

            metadata: [
                'reservation_id' =>
                    $reservation->id,

                'space' =>
                    $reservation->space?->code,

                'vehicle_plate' =>
                    $reservation
                        ->vehicle
                        ?->plate,

                'status' =>
                    $reservation->status,

                'payment_status' =>
                    $reservation
                        ->payment_status,

                'check_in_at' =>
                    $reservation
                        ->check_in_at
                        ?->toDateTimeString(),
            ],
        );

        return back()->with(
            'toast',
            [
                'type' =>
                    'success',

                'message' =>
                    'Ingreso registrado correctamente.',
            ]
        );
    }

    /**
     * Registrar salida física.
     *
     * La salida NO termina la mensualidad.
     *
     * La plaza vuelve a quedar reservada
     * para el mismo conductor.
     */
    public function checkout(
        Request $request,
        Reservation $reservation
    ): RedirectResponse {
        $this->ensureAdmin($request);

        abort_unless(
            $reservation->status
                === 'active',
            422,
            'El vehículo no se encuentra registrado dentro del estacionamiento.'
        );

        DB::transaction(
            function () use (
                $reservation
            ): void {
                $reservation->update([
                    /*
                     * La mensualidad continúa.
                     */
                    'status' =>
                        'confirmed',

                    'check_out_at' =>
                        now(),
                ]);

                /*
                 * La plaza NO se libera.
                 *
                 * Continúa reservada hasta
                 * finalizar/cancelar la mensualidad.
                 */
                $reservation
                    ->space()
                    ->update([
                        'status' =>
                            'reserved',

                        'current_reservation_id' =>
                            $reservation->id,
                    ]);
            }
        );

        $reservation->refresh();

        $reservation->load([
            'space',
            'vehicle',
        ]);

        AuditService::log(
            request: $request,

            module: 'Reservas',

            action: 'Salida',

            description:
                "Salida registrada de la plaza {$reservation->space?->code}. La plaza permanece reservada por la mensualidad.",

            entity: $reservation,

            reference:
                $reservation->space?->code,

            metadata: [
                'reservation_id' =>
                    $reservation->id,

                'space' =>
                    $reservation->space?->code,

                'vehicle_plate' =>
                    $reservation
                        ->vehicle
                        ?->plate,

                'status' =>
                    $reservation->status,

                'payment_status' =>
                    $reservation
                        ->payment_status,

                'check_out_at' =>
                    $reservation
                        ->check_out_at
                        ?->toDateTimeString(),
            ],
        );

        return back()->with(
            'toast',
            [
                'type' =>
                    'success',

                'message' =>
                    'Salida registrada. La plaza continúa reservada por la mensualidad.',
            ]
        );
    }

    /**
     * El conductor solicita cancelar.
     *
     * NO se cancela inmediatamente.
     */
    public function requestCancellation(
        Request $request,
        Reservation $reservation
    ): RedirectResponse {
        /*
         * Solo el propietario.
         */
        abort_unless(
            $reservation->user_id
                === $request->user()->id,
            403,
            'No puedes solicitar la cancelación de esta reserva.'
        );

        /*
         * No permitimos cancelación
         * mientras el vehículo está dentro.
         */
        abort_unless(
            in_array(
                $reservation->status,
                [
                    'pending',
                    'confirmed',
                ],
                true
            ),
            422,
            'No puedes solicitar la cancelación en el estado actual.'
        );

        /*
         * No crear otra solicitud
         * si ya existe una pendiente.
         */
        abort_if(
            $reservation
                ->cancellation_status
                === 'pending',
            422,
            'Ya existe una solicitud de cancelación pendiente.'
        );

        $data = $request->validate([
            'reason' => [
                'required',
                'string',
                'min:5',
                'max:1000',
            ],
        ]);

        $reservation->update([
            'cancellation_status' =>
                'pending',

            'cancellation_reason' =>
                $data['reason'],

            'cancellation_requested_at' =>
                now(),

            'cancellation_reviewed_at' =>
                null,

            'cancellation_reviewed_by' =>
                null,

            'cancellation_review_note' =>
                null,
        ]);

        $reservation->loadMissing([
            'space',
            'vehicle',
        ]);

        AuditService::log(
            request: $request,

            module: 'Reservas',

            action:
                'Cancelación solicitada',

            description:
                "Se solicitó la cancelación de la mensualidad de la plaza {$reservation->space?->code}.",

            entity: $reservation,

            reference:
                $reservation->space?->code,

            metadata: [
                'reservation_id' =>
                    $reservation->id,

                'space' =>
                    $reservation->space?->code,

                'vehicle_plate' =>
                    $reservation
                        ->vehicle
                        ?->plate,

                'reason' =>
                    $data['reason'],

                'cancellation_status' =>
                    'pending',
            ],
        );

        return back()->with(
            'toast',
            [
                'type' =>
                    'success',

                'message' =>
                    'Solicitud de cancelación enviada al administrador.',
            ]
        );
    }

    /**
     * Administrador aprueba
     * una cancelación.
     */
    public function approveCancellation(
        Request $request,
        Reservation $reservation
    ): RedirectResponse {
        $this->ensureAdmin($request);

        abort_unless(
            $reservation
                ->cancellation_status
                === 'pending',
            422,
            'Esta reserva no tiene una solicitud de cancelación pendiente.'
        );

        /*
         * Nunca liberar una plaza
         * con un vehículo dentro.
         */
        abort_if(
            $reservation->status
                === 'active',
            422,
            'No se puede aprobar la cancelación mientras el vehículo esté dentro.'
        );

        $data = $request->validate([
            'note' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);

        $reservation->loadMissing([
            'space',
            'vehicle',
        ]);

        $spaceCode =
            $reservation
                ->space
                ?->code;

        $vehiclePlate =
            $reservation
                ->vehicle
                ?->plate;

        DB::transaction(
            function () use (
                $reservation,
                $request,
                $data
            ): void {
                $reservation->update([
                    'status' =>
                        'cancelled',

                    'cancellation_status' =>
                        'approved',

                    'cancellation_reviewed_at' =>
                        now(),

                    'cancellation_reviewed_by' =>
                        $request
                            ->user()
                            ->id,

                    'cancellation_review_note' =>
                        $data['note']
                            ?? null,
                ]);

                /*
                 * Solamente aquí se libera
                 * físicamente la plaza.
                 */
                $reservation
                    ->space()
                    ->update([
                        'status' =>
                            'available',

                        'current_reservation_id' =>
                            null,
                    ]);
            }
        );

        $reservation->refresh();

        AuditService::log(
            request: $request,

            module: 'Reservas',

            action:
                'Cancelación aprobada',

            description:
                "El administrador aprobó la cancelación de la mensualidad de la plaza {$spaceCode}.",

            entity: $reservation,

            reference:
                $spaceCode,

            metadata: [
                'reservation_id' =>
                    $reservation->id,

                'space' =>
                    $spaceCode,

                'vehicle_plate' =>
                    $vehiclePlate,

                'cancellation_status' =>
                    'approved',

                'review_note' =>
                    $data['note']
                        ?? null,

                'reviewed_by' =>
                    $request
                        ->user()
                        ->id,
            ],
        );

        return back()->with(
            'toast',
            [
                'type' =>
                    'success',

                'message' =>
                    'Cancelación aprobada. La plaza quedó disponible.',
            ]
        );
    }

    /**
     * Administrador rechaza
     * una cancelación.
     */
    public function rejectCancellation(
        Request $request,
        Reservation $reservation
    ): RedirectResponse {
        $this->ensureAdmin($request);

        abort_unless(
            $reservation
                ->cancellation_status
                === 'pending',
            422,
            'Esta reserva no tiene una solicitud de cancelación pendiente.'
        );

        $data = $request->validate([
            'note' => [
                'required',
                'string',
                'min:5',
                'max:1000',
            ],
        ]);

        $reservation->update([
            'cancellation_status' =>
                'rejected',

            'cancellation_reviewed_at' =>
                now(),

            'cancellation_reviewed_by' =>
                $request
                    ->user()
                    ->id,

            'cancellation_review_note' =>
                $data['note'],
        ]);

        $reservation->loadMissing([
            'space',
            'vehicle',
        ]);

        AuditService::log(
            request: $request,

            module: 'Reservas',

            action:
                'Cancelación rechazada',

            description:
                "El administrador rechazó la solicitud de cancelación de la plaza {$reservation->space?->code}.",

            entity: $reservation,

            reference:
                $reservation->space?->code,

            metadata: [
                'reservation_id' =>
                    $reservation->id,

                'space' =>
                    $reservation->space?->code,

                'vehicle_plate' =>
                    $reservation
                        ->vehicle
                        ?->plate,

                'cancellation_status' =>
                    'rejected',

                'review_note' =>
                    $data['note'],

                'reviewed_by' =>
                    $request
                        ->user()
                        ->id,
            ],
        );

        return back()->with(
            'toast',
            [
                'type' =>
                    'success',

                'message' =>
                    'Solicitud de cancelación rechazada.',
            ]
        );
    }

    /**
     * Verifica que el usuario
     * autenticado sea administrador.
     */
    private function ensureAdmin(
        Request $request
    ): void {
        abort_unless(
            $request
                ->user()
                ->hasRole('admin'),
            403,
            'Solo el administrador puede realizar esta acción.'
        );
    }
}