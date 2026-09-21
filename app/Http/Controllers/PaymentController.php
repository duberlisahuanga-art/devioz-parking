<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Reservation;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function store(
        Request $request,
        Reservation $reservation
    ): RedirectResponse {
        /*
        |--------------------------------------------------------------------------
        | Validar propietario de la reserva
        |--------------------------------------------------------------------------
        */

        abort_unless(
            $reservation->user_id === $request->user()->id,
            403
        );

        /*
        |--------------------------------------------------------------------------
        | Validar que todavía pueda enviar un pago
        |--------------------------------------------------------------------------
        */

        abort_unless(
            $reservation->payment_status === 'unpaid',
            422,
            'Esta reserva ya tiene un pago en revisión o fue pagada.'
        );

        /*
        |--------------------------------------------------------------------------
        | Validar voucher
        |--------------------------------------------------------------------------
        */

        $data = $request->validate([
            'method' => [
                'required',
                'in:yape',
            ],

            'provider_tx_id' => [
                'required',
                'string',
                'max:100',
            ],

            'voucher' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,webp,pdf',
                'max:5120',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Crear pago
        |--------------------------------------------------------------------------
        */

        $payment = DB::transaction(
            function () use (
                $data,
                $request,
                $reservation
            ): Payment {
                $path = $request
                    ->file('voucher')
                    ->store(
                        'vouchers',
                        'public'
                    );

                $payment = Payment::create([
                    'reservation_id' =>
                        $reservation->id,

                    'amount' =>
                        $reservation->amount,

                    'method' =>
                        'yape',

                    'provider' =>
                        'manual',

                    'provider_tx_id' =>
                        $data['provider_tx_id'],

                    'voucher_path' =>
                        $path,

                    'status' =>
                        'pending',
                ]);

                /*
                |--------------------------------------------------------------------------
                | Marcar reserva como pago en revisión
                |--------------------------------------------------------------------------
                */

                $reservation->update([
                    'payment_status' => 'pending',
                ]);

                return $payment;
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Cargar información para Auditoría
        |--------------------------------------------------------------------------
        */

        $reservation->refresh();

        $reservation->load([
            'space',
            'vehicle',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Auditoría: pago enviado
        |--------------------------------------------------------------------------
        */

        AuditService::log(
            request: $request,

            module: 'Pagos',

            action: 'Enviado',

            description:
                "Voucher de pago enviado para la reserva #{$reservation->id}.",

            entity: $payment,

            reference:
                "PAY-{$payment->id}",

            metadata: [
                'payment_id' =>
                    $payment->id,

                'reservation_id' =>
                    $reservation->id,

                'space' =>
                    $reservation->space?->code,

                'vehicle_plate' =>
                    $reservation->vehicle?->plate,

                'amount' =>
                    $payment->amount,

                'method' =>
                    $payment->method,

                'provider_tx_id' =>
                    $payment->provider_tx_id,

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
                    'Voucher Yape enviado. Queda pendiente de confirmación del administrador.',
            ]
        );
    }
}