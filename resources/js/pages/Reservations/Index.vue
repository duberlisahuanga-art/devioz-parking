<script setup lang="ts">
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';

import PaymentModal from '@/components/PaymentModal.vue';
import { Button } from '@/components/ui/button';

interface Reservation {
    id: number;

    status: string;
    payment_status: string;
    amount_formatted: string;

    starts_on: string | null;
    ends_on: string | null;

    check_in_at: string | null;
    check_out_at: string | null;

    created_at: string;

    cancellation_status:
    | 'none'
    | 'pending'
    | 'approved'
    | 'rejected';

    cancellation_reason: string | null;

    cancellation_requested_at:
    | string
    | null;

    cancellation_reviewed_at:
    | string
    | null;

    cancellation_reviewed_by:
    | number
    | null;

    cancellation_review_note:
    | string
    | null;

    user?: {
        id: number;
        name: string;
        email: string;
    } | null;

    parking: {
        name: string;
    };

    space: {
        code: string;
    };

    vehicle: {
        plate: string;
    } | null;
}

const props = defineProps<{
    reservations: Reservation[];

    isAdmin: boolean;

    paymentMethods: Record<
        string,
        {
            label: string;
            type: string;
        }
    >;

    yapeQrUrl: string | null;
}>();

/*
|--------------------------------------------------------------------------
| Pago
|--------------------------------------------------------------------------
*/

const payingReservation =
    ref<Reservation | null>(null);

/*
|--------------------------------------------------------------------------
| Solicitud de cancelación - conductor
|--------------------------------------------------------------------------
*/

const cancellationReservation =
    ref<Reservation | null>(null);

const cancellationReason = ref('');

const cancellationError = ref('');

const cancellationSubmitting = ref(false);

/*
|--------------------------------------------------------------------------
| Revisión de cancelación - administrador
|--------------------------------------------------------------------------
*/

const reviewReservation =
    ref<Reservation | null>(null);

const reviewNote = ref('');

const reviewError = ref('');

const reviewSubmitting = ref(false);

/*
|--------------------------------------------------------------------------
| Estados
|--------------------------------------------------------------------------
*/

const statusClasses: Record<
    string,
    string
> = {
    pending:
        'border border-amber-500 bg-amber-100 text-amber-900 dark:border-amber-400 dark:bg-amber-950 dark:text-amber-200',

    confirmed:
        'border border-blue-500 bg-blue-100 text-blue-900 dark:border-blue-400 dark:bg-blue-950 dark:text-blue-100',

    active:
        'border border-emerald-500 bg-emerald-100 text-emerald-900 dark:border-emerald-400 dark:bg-emerald-950 dark:text-emerald-100',

    completed:
        'border border-slate-500 bg-slate-200 text-slate-900 dark:border-slate-400 dark:bg-slate-700 dark:text-white',

    cancelled:
        'border border-red-500 bg-red-100 text-red-900 dark:border-red-400 dark:bg-red-950 dark:text-red-100',

    no_show:
        'border border-slate-600 bg-slate-700 text-white dark:border-slate-400 dark:bg-slate-800 dark:text-white',
};

const statusLabels: Record<
    string,
    string
> = {
    pending: 'Pendiente',
    confirmed: 'Confirmada',
    active: 'Vehículo dentro',
    completed: 'Finalizada',
    cancelled: 'Cancelada',
    no_show: 'No asistió',
};

/*
|--------------------------------------------------------------------------
| Acciones normales
|--------------------------------------------------------------------------
*/

function action(url: string): void {
    router.post(
        url,
        {},
        {
            preserveScroll: true,
        },
    );
}

/*
|--------------------------------------------------------------------------
| Fechas
|--------------------------------------------------------------------------
*/

function parseDateOnly(
    value: string,
): Date {
    const datePart =
        value.split('T')[0];

    const [
        year,
        month,
        day,
    ] = datePart
        .split('-')
        .map(Number);

    return new Date(
        year,
        month - 1,
        day,
    );
}

function formatDate(
    value: string | null,
): string {
    if (!value) {
        return 'Sin fecha';
    }

    return new Intl.DateTimeFormat(
        'es-PE',
        {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
        },
    ).format(
        parseDateOnly(value),
    );
}

function formatDateTime(
    value: string | null,
): string {
    if (!value) {
        return 'Sin registro';
    }

    return new Intl.DateTimeFormat(
        'es-PE',
        {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        },
    ).format(
        new Date(value),
    );
}

function hasMonthlyPeriod(
    reservation: Reservation,
): boolean {
    return Boolean(
        reservation.starts_on &&
        reservation.ends_on,
    );
}

/*
|--------------------------------------------------------------------------
| Modal solicitud cancelación
|--------------------------------------------------------------------------
*/

function openCancellationModal(
    reservation: Reservation,
): void {
    cancellationReservation.value =
        reservation;

    cancellationReason.value = '';

    cancellationError.value = '';
}

function closeCancellationModal(): void {
    if (
        cancellationSubmitting.value
    ) {
        return;
    }

    cancellationReservation.value =
        null;

    cancellationReason.value = '';

    cancellationError.value = '';
}

function submitCancellationRequest(): void {
    if (
        !cancellationReservation.value
    ) {
        return;
    }

    const reason =
        cancellationReason.value.trim();

    if (reason.length < 5) {
        cancellationError.value =
            'Escribe un motivo de al menos 5 caracteres.';

        return;
    }

    cancellationError.value = '';

    cancellationSubmitting.value =
        true;

    router.post(
        `/reservations/${cancellationReservation.value.id}/request-cancellation`,
        {
            reason,
        },
        {
            preserveScroll: true,

            onSuccess: () => {
                closeCancellationModal();
            },

            onError: (errors) => {
                cancellationError.value =
                    String(
                        errors.reason ??
                        'No se pudo enviar la solicitud.',
                    );
            },

            onFinish: () => {
                cancellationSubmitting.value =
                    false;
            },
        },
    );
}

/*
|--------------------------------------------------------------------------
| Modal revisión administrador
|--------------------------------------------------------------------------
*/

function openReviewModal(
    reservation: Reservation,
): void {
    reviewReservation.value =
        reservation;

    reviewNote.value = '';

    reviewError.value = '';
}

function closeReviewModal(): void {
    if (reviewSubmitting.value) {
        return;
    }

    reviewReservation.value = null;

    reviewNote.value = '';

    reviewError.value = '';
}

function approveCancellation(): void {
    if (!reviewReservation.value) {
        return;
    }

    reviewSubmitting.value = true;

    reviewError.value = '';

    router.post(
        `/reservations/${reviewReservation.value.id}/approve-cancellation`,
        {
            note:
                reviewNote.value.trim() ||
                null,
        },
        {
            preserveScroll: true,

            onSuccess: () => {
                closeReviewModal();
            },

            onError: (errors) => {
                reviewError.value =
                    String(
                        errors.note ??
                        'No se pudo aprobar la cancelación.',
                    );
            },

            onFinish: () => {
                reviewSubmitting.value =
                    false;
            },
        },
    );
}

function rejectCancellation(): void {
    if (!reviewReservation.value) {
        return;
    }

    const note =
        reviewNote.value.trim();

    if (note.length < 5) {
        reviewError.value =
            'Para rechazar la solicitud debes escribir una observación de al menos 5 caracteres.';

        return;
    }

    reviewSubmitting.value = true;

    reviewError.value = '';

    router.post(
        `/reservations/${reviewReservation.value.id}/reject-cancellation`,
        {
            note,
        },
        {
            preserveScroll: true,

            onSuccess: () => {
                closeReviewModal();
            },

            onError: (errors) => {
                reviewError.value =
                    String(
                        errors.note ??
                        'No se pudo rechazar la solicitud.',
                    );
            },

            onFinish: () => {
                reviewSubmitting.value =
                    false;
            },
        },
    );
}
</script>

<template>

    <Head :title="props.isAdmin
        ? 'Gestión de reservas'
        : 'Mis reservas'
        " />

    <div class="flex flex-1 flex-col gap-6 p-6">
        <!-- CABECERA -->
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm text-muted-foreground">
                    {{
                        props.isAdmin
                            ? 'Administrador'
                            : 'Conductor'
                    }}
                </p>

                <h1 class="text-2xl font-semibold">
                    {{
                        props.isAdmin
                            ? 'Gestión de reservas'
                            : 'Mis reservas'
                    }}
                </h1>

                <p class="mt-1 text-sm text-muted-foreground">
                    <template v-if="props.isAdmin">
                        Consulta y administra
                        las mensualidades,
                        ingresos, salidas y
                        solicitudes de
                        cancelación.
                    </template>

                    <template v-else>
                        Consulta la vigencia de
                        tu plaza, pagos y
                        solicitudes.
                    </template>
                </p>
            </div>

            <Button v-if="!props.isAdmin" as="a" href="/reservations/create">
                📍 Reservar plaza
            </Button>
        </div>

        <!-- TABLA -->
        <section class="overflow-hidden rounded-xl border bg-card">
            <div v-if="
                !props.reservations.length
            " class="p-8 text-center text-muted-foreground">
                {{
                    props.isAdmin
                        ? 'No existen reservas registradas.'
                        : 'Aún no tienes reservas.'
                }}
            </div>

            <div v-else class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead
                        class="border-b bg-slate-50 text-xs uppercase text-slate-500 dark:bg-slate-900 dark:text-slate-400">
                        <tr>
                            <th v-if="
                                props.isAdmin
                            " class="p-4">
                                Conductor
                            </th>

                            <th class="p-4">
                                Plaza / vehículo
                            </th>

                            <th class="p-4">
                                Vigencia
                            </th>

                            <th class="p-4">
                                Plan
                            </th>

                            <th class="p-4">
                                Registrada
                            </th>

                            <th class="p-4">
                                Estado
                            </th>

                            <th class="p-4">
                                Pago
                            </th>

                            <th class="p-4">
                                Monto
                            </th>

                            <th class="p-4">
                                Acciones
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y">
                        <tr v-for="
reservation in
    props.reservations
                            " :key="reservation.id
                                ">
                            <!-- CONDUCTOR -->
                            <td v-if="
                                props.isAdmin
                            " class="p-4">
                                <p class="font-medium">
                                    {{
                                        reservation
                                            .user
                                            ?.name ??
                                        'Sin conductor'
                                    }}
                                </p>

                                <p class="mt-1 text-xs text-muted-foreground">
                                    {{
                                        reservation
                                            .user
                                            ?.email ??
                                        ''
                                    }}
                                </p>
                            </td>

                            <!-- PLAZA -->
                            <td class="p-4">
                                <p class="font-medium">
                                    {{
                                        reservation
                                            .space
                                            .code
                                    }}
                                </p>

                                <p class="mt-1 text-xs text-muted-foreground">
                                    {{
                                        reservation
                                            .vehicle
                                            ?.plate ??
                                        'Sin vehículo'
                                    }}
                                </p>

                                <p class="mt-1 text-xs text-muted-foreground">
                                    {{
                                        reservation
                                            .parking
                                            .name
                                    }}
                                </p>
                            </td>

                            <!-- VIGENCIA -->
                            <td class="p-4">
                                <template v-if="
                                    hasMonthlyPeriod(
                                        reservation,
                                    )
                                ">
                                    <p class="font-medium">
                                        {{
                                            formatDate(
                                                reservation.starts_on,
                                            )
                                        }}
                                    </p>

                                    <p class="mt-1 text-xs text-muted-foreground">
                                        hasta
                                        {{
                                            formatDate(
                                                reservation.ends_on,
                                            )
                                        }}
                                    </p>
                                </template>

                                <span v-else class="text-xs text-muted-foreground">
                                    Reserva anterior
                                </span>
                            </td>

                            <!-- PLAN -->
                            <td class="p-4">
                                <span v-if="
                                    hasMonthlyPeriod(
                                        reservation,
                                    )
                                "
                                    class="rounded-full border border-cyan-500/50 bg-cyan-500/10 px-2.5 py-1 text-xs font-medium text-cyan-700 dark:text-cyan-200">
                                    Mensual
                                </span>

                                <span v-else class="text-xs text-muted-foreground">
                                    Histórico
                                </span>
                            </td>

                            <!-- REGISTRADA -->
                            <td class="p-4">
                                {{
                                    formatDateTime(
                                        reservation.created_at,
                                    )
                                }}
                            </td>
                            <!-- ESTADO -->
                            <td class="p-4">
                                <div class="flex flex-col items-start gap-2">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-medium" :class="statusClasses[
                                        reservation
                                            .status
                                    ]
                                        ">
                                        {{
                                            statusLabels[
                                            reservation
                                                .status
                                            ] ??
                                            reservation
                                                .status
                                        }}
                                    </span>

                                    <!-- CANCELACIÓN PENDIENTE -->
                                    <span v-if="
                                        reservation.cancellation_status ===
                                        'pending'
                                    "
                                        class="rounded-full border border-amber-500/60 bg-amber-500/10 px-2.5 py-1 text-xs font-medium text-amber-700 dark:text-amber-300">
                                        🕒 Cancelación
                                        pendiente
                                    </span>

                                    <!-- CANCELACIÓN RECHAZADA -->
                                    <span v-if="
                                        reservation.cancellation_status ===
                                        'rejected'
                                    "
                                        class="rounded-full border border-red-500/50 bg-red-500/10 px-2.5 py-1 text-xs font-medium text-red-700 dark:text-red-300">
                                        Solicitud
                                        rechazada
                                    </span>

                                    <!-- CANCELACIÓN APROBADA -->
                                    <span v-if="
                                        reservation.cancellation_status ===
                                        'approved'
                                    "
                                        class="rounded-full border border-slate-500/50 bg-slate-500/10 px-2.5 py-1 text-xs font-medium">
                                        Cancelación
                                        aprobada
                                    </span>
                                </div>
                            </td>

                            <!-- PAGO -->
                            <td class="p-4">
                                <span v-if="
                                    reservation.payment_status ===
                                    'paid'
                                "
                                    class="rounded-full border border-emerald-500 bg-emerald-100 px-2.5 py-1 text-xs font-medium text-emerald-900 dark:bg-emerald-950 dark:text-emerald-200">
                                    Pagado
                                </span>

                                <span v-else
                                    class="rounded-full border border-amber-500 bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-900 dark:bg-amber-950 dark:text-amber-200">
                                    Pendiente
                                </span>
                            </td>

                            <!-- MONTO -->
                            <td class="p-4 font-semibold">
                                {{
                                    reservation.amount_formatted
                                }}
                            </td>

                            <!-- ACCIONES -->
                            <td class="p-4">
                                <div class="flex flex-wrap gap-2">
                                    <!-- CONDUCTOR: PAGAR -->
                                    <Button v-if="
                                        !props.isAdmin &&
                                        reservation.payment_status ===
                                        'unpaid' &&
                                        reservation.cancellation_status !==
                                        'pending' &&
                                        ![
                                            'completed',
                                            'cancelled',
                                        ].includes(
                                            reservation.status,
                                        )
                                    " size="sm" variant="outline" @click="
                                        payingReservation =
                                        reservation
                                        ">
                                        💳 Pagar
                                    </Button>

                                    <!-- CONDUCTOR: SOLICITAR CANCELACIÓN -->
                                    <Button v-if="
                                        !props.isAdmin &&
                                        reservation.cancellation_status !==
                                        'pending' &&
                                        [
                                            'pending',
                                            'confirmed',
                                        ].includes(
                                            reservation.status,
                                        )
                                    " size="sm" variant="destructive" @click="
                                        openCancellationModal(
                                            reservation,
                                        )
                                        ">
                                        ⚠ Solicitar
                                        cancelación
                                    </Button>

                                    <!-- PENDIENTE PARA CONDUCTOR -->
                                    <span v-if="
                                        !props.isAdmin &&
                                        reservation.cancellation_status ===
                                        'pending'
                                    "
                                        class="rounded-md border border-amber-500/40 bg-amber-500/10 px-3 py-2 text-xs font-medium text-amber-700 dark:text-amber-300">
                                        🕒 Esperando
                                        revisión del
                                        administrador
                                    </span>

                                    <!-- ADMIN: REVISAR CANCELACIÓN -->
                                    <Button v-if="
                                        props.isAdmin &&
                                        reservation.cancellation_status ===
                                        'pending'
                                    " size="sm" variant="destructive" @click="
                                        openReviewModal(
                                            reservation,
                                        )
                                        ">
                                        ⚠ Revisar
                                        solicitud
                                    </Button>

                                    <!-- ADMIN: INGRESO -->
                                    <Button v-if="
                                        props.isAdmin &&
                                        reservation.status ===
                                        'confirmed' &&
                                        reservation.cancellation_status !==
                                        'pending'
                                    " size="sm" @click="
                                        action(
                                            `/reservations/${reservation.id}/checkin`,
                                        )
                                        ">
                                        🚗 Registrar
                                        ingreso
                                    </Button>

                                    <!-- ADMIN: SALIDA -->
                                    <Button v-if="
                                        props.isAdmin &&
                                        reservation.status ===
                                        'active'
                                    " size="sm" @click="
                                        action(
                                            `/reservations/${reservation.id}/checkout`,
                                        )
                                        ">
                                        🏁 Registrar
                                        salida
                                    </Button>

                                    <!-- SIN ACCIONES -->
                                    <span v-if="
                                        reservation.status ===
                                        'completed' ||
                                        reservation.status ===
                                        'cancelled'
                                    " class="text-xs text-muted-foreground">
                                        Sin acciones
                                        disponibles
                                    </span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <!-- PAGO -->
    <PaymentModal v-if="
        !props.isAdmin &&
        payingReservation
    " :reservation-id="payingReservation.id
        " :amount="payingReservation.amount_formatted
                " :methods="props.paymentMethods
                " :yape-qr-url="props.yapeQrUrl
                " @close="
                payingReservation = null
                " />

    <!-- MODAL SOLICITAR CANCELACIÓN -->
    <div v-if="
        cancellationReservation
    " class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4">
        <div class="w-full max-w-lg rounded-xl border bg-background p-6 shadow-2xl">
            <div>
                <p class="text-sm font-medium text-amber-600 dark:text-amber-400">
                    ⚠ Solicitud de
                    cancelación
                </p>

                <h2 class="mt-1 text-xl font-semibold">
                    ¿Deseas solicitar la
                    cancelación?
                </h2>
            </div>

            <div class="mt-4 rounded-lg border border-amber-500/30 bg-amber-500/10 p-4">
                <p class="text-sm">
                    Estás solicitando cancelar
                    la mensualidad de la plaza
                    <strong>
                        {{
                            cancellationReservation
                                .space
                                .code
                        }}
                    </strong>.
                </p>

                <p class="mt-2 text-sm text-muted-foreground">
                    Esta acción
                    <strong>
                        no cancelará
                        inmediatamente
                    </strong>
                    la reserva.
                </p>

                <p class="mt-1 text-sm text-muted-foreground">
                    El administrador deberá
                    revisar y aprobar tu
                    solicitud.
                </p>
            </div>

            <div class="mt-4 grid grid-cols-2 gap-3 rounded-lg border p-4 text-sm">
                <div>
                    <p class="text-xs text-muted-foreground">
                        Inicio
                    </p>

                    <p class="mt-1 font-medium">
                        {{
                            formatDate(
                                cancellationReservation.starts_on,
                            )
                        }}
                    </p>
                </div>

                <div>
                    <p class="text-xs text-muted-foreground">
                        Vencimiento
                    </p>

                    <p class="mt-1 font-medium">
                        {{
                            formatDate(
                                cancellationReservation.ends_on,
                            )
                        }}
                    </p>
                </div>
            </div>

            <label class="mt-5 grid gap-2 text-sm font-medium">
                Motivo de cancelación

                <textarea v-model="cancellationReason
                    " rows="4" maxlength="1000"
                    class="w-full resize-none rounded-md border bg-background px-3 py-2 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20"
                    placeholder="Ejemplo: Ya no utilizaré la cochera durante el próximo mes." />
            </label>

            <p class="mt-1 text-xs text-muted-foreground">
                Escribe al menos 5 caracteres.
            </p>

            <p v-if="
                cancellationError
            " class="mt-3 text-sm text-red-600 dark:text-red-400">
                {{
                    cancellationError
                }}
            </p>

            <div class="mt-6 flex justify-end gap-3">
                <Button type="button" variant="outline" :disabled="cancellationSubmitting
                    " @click="
                        closeCancellationModal
                    ">
                    Volver
                </Button>

                <Button type="button" variant="destructive" :disabled="cancellationSubmitting
                    " @click="
                        submitCancellationRequest
                    ">
                    {{
                        cancellationSubmitting
                            ? 'Enviando...'
                            : 'Enviar solicitud'
                    }}
                </Button>
            </div>
        </div>
    </div>

    <!-- MODAL REVISIÓN ADMINISTRADOR -->
    <div v-if="reviewReservation" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4">
        <div class="w-full max-w-lg rounded-xl border bg-background p-6 shadow-2xl">
            <p class="text-sm font-medium text-amber-600 dark:text-amber-400">
                ⚠ Solicitud pendiente
            </p>

            <h2 class="mt-1 text-xl font-semibold">
                Revisar cancelación
            </h2>

            <div class="mt-5 grid gap-3 rounded-lg border p-4 text-sm">
                <div>
                    <p class="text-xs text-muted-foreground">
                        Conductor
                    </p>

                    <p class="font-medium">
                        {{
                            reviewReservation
                                .user
                                ?.name ??
                            'Sin conductor'
                        }}
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <p class="text-xs text-muted-foreground">
                            Plaza
                        </p>

                        <p class="font-medium">
                            {{
                                reviewReservation
                                    .space
                                    .code
                            }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-muted-foreground">
                            Vehículo
                        </p>

                        <p class="font-medium">
                            {{
                                reviewReservation
                                    .vehicle
                                    ?.plate ??
                                'Sin vehículo'
                            }}
                        </p>
                    </div>
                </div>

                <div>
                    <p class="text-xs text-muted-foreground">
                        Motivo del conductor
                    </p>

                    <p class="mt-1 rounded-md bg-muted p-3">
                        {{
                            reviewReservation
                                .cancellation_reason ??
                            'Sin motivo registrado'
                        }}
                    </p>
                </div>

                <div>
                    <p class="text-xs text-muted-foreground">
                        Solicitud enviada
                    </p>

                    <p class="font-medium">
                        {{
                            formatDateTime(
                                reviewReservation.cancellation_requested_at,
                            )
                        }}
                    </p>
                </div>
            </div>

            <label class="mt-5 grid gap-2 text-sm font-medium">
                Observación del
                administrador

                <textarea v-model="reviewNote" rows="4" maxlength="1000"
                    class="w-full resize-none rounded-md border bg-background px-3 py-2 outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20"
                    placeholder="Escribe una observación. Es obligatoria si rechazas la solicitud." />
            </label>

            <p v-if="reviewError" class="mt-3 text-sm text-red-600 dark:text-red-400">
                {{ reviewError }}
            </p>

            <div class="mt-6 flex flex-wrap justify-end gap-3">
                <Button type="button" variant="outline" :disabled="reviewSubmitting
                    " @click="
                        closeReviewModal
                    ">
                    Cerrar
                </Button>

                <Button type="button" variant="outline" :disabled="reviewSubmitting
                    " @click="
                        rejectCancellation
                    ">
                    Rechazar
                </Button>

                <Button type="button" :disabled="reviewSubmitting
                    " @click="
                        approveCancellation
                    ">
                    Aprobar cancelación
                </Button>
            </div>
        </div>
    </div>
</template>