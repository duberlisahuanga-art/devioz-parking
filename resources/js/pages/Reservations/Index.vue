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
    check_in_at: string;
    check_out_at: string | null;
    expected_duration_min: number;
    parking: { name: string };
    space: { code: string };
    vehicle: { plate: string } | null;
    services: {
        name: string;
        pivot: {
            price: string;
        };
    }[];
}

const props = defineProps<{
    reservations: Reservation[];
    paymentMethods: Record<
        string,
        {
            label: string;
            type: string;
        }
    >;
    yapeQrUrl: string | null;
}>();

const payingReservation = ref<Reservation | null>(null);
const extendingReservation = ref<Reservation | null>(null);
const selectedExtensionHours = ref(1);

const statusClasses: Record<string, string> = {
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

const statusLabels: Record<string, string> = {
    pending: 'Pendiente',
    confirmed: 'Confirmada',
    active: 'Activa',
    completed: 'Completada',
    cancelled: 'Cancelada',
    no_show: 'No asistió',
};

function action(url: string): void {
    router.post(url);
}

function openExtendModal(reservation: Reservation): void {
    extendingReservation.value = reservation;
    selectedExtensionHours.value = 1;
}

function closeExtendModal(): void {
    extendingReservation.value = null;
    selectedExtensionHours.value = 1;
}

function extendReservation(): void {
    if (!extendingReservation.value) {
        return;
    }

    router.post(
        `/reservations/${extendingReservation.value.id}/extend`,
        {
            hours: selectedExtensionHours.value,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                closeExtendModal();
            },
        },
    );
}

function durationLabel(minutes: number): string {
    const hours = Math.floor(minutes / 60);
    const remainingMinutes = minutes % 60;

    if (remainingMinutes === 0) {
        return `${hours} h`;
    }

    return `${hours} h ${remainingMinutes} min`;
}
</script>

<template>
    <Head title="Mis reservas" />

    <div class="flex flex-1 flex-col gap-6 p-6">
        <div
            class="flex flex-wrap items-end justify-between gap-4"
        >
            <div>
                <p class="text-sm text-muted-foreground">
                    Conductor
                </p>

                <h1 class="text-2xl font-semibold">
                    Mis reservas
                </h1>
            </div>

            <Button as="a" href="/reservations/create">
                📍 Reservar plaza
            </Button>
        </div>

        <section
            class="overflow-hidden rounded-xl border bg-card"
        >
            <div
                v-if="!reservations.length"
                class="p-8 text-center text-muted-foreground"
            >
                Aún no tienes reservas.
            </div>

            <div
                v-else
                class="overflow-x-auto"
            >
                <table class="w-full text-left text-sm">
                    <thead
                        class="border-b bg-slate-50 text-xs uppercase text-slate-500"
                    >
                        <tr>
                            <th class="p-4">
                                Parking
                            </th>

                            <th class="p-4">
                                Plaza
                            </th>

                            <th class="p-4">
                                Horario
                            </th>

                            <th class="p-4">
                                Duración
                            </th>

                            <th class="p-4">
                                Servicios
                            </th>

                            <th class="p-4">
                                Estado
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
                        <tr
                            v-for="reservation in reservations"
                            :key="reservation.id"
                        >
                            <td class="p-4 font-medium">
                                {{
                                    reservation.parking.name
                                }}
                            </td>

                            <td class="p-4">
                                {{
                                    reservation.space.code
                                }}

                                <br />

                                <span
                                    class="text-xs text-muted-foreground"
                                >
                                    {{
                                        reservation.vehicle
                                            ?.plate ??
                                        'Sin vehículo'
                                    }}
                                </span>
                            </td>

                            <td class="p-4">
                                {{
                                    new Date(
                                        reservation.check_in_at,
                                    ).toLocaleString(
                                        'es-PE',
                                    )
                                }}
                            </td>

                            <td class="p-4">
                                <span
                                    class="font-medium"
                                >
                                    {{
                                        durationLabel(
                                            reservation.expected_duration_min,
                                        )
                                    }}
                                </span>
                            </td>

                            <td class="p-4">
                                <span
                                    v-if="
                                        reservation.services
                                            .length
                                    "
                                >
                                    {{
                                        reservation.services
                                            .map(
                                                (
                                                    service,
                                                ) =>
                                                    service.name,
                                            )
                                            .join(', ')
                                    }}
                                </span>

                                <span
                                    v-else
                                    class="text-muted-foreground"
                                >
                                    Ninguno
                                </span>
                            </td>

                            <td class="p-4">
                                <span
                                    class="rounded-full px-2.5 py-1 text-xs font-medium"
                                    :class="
                                        statusClasses[
                                            reservation.status
                                        ]
                                    "
                                >
                                    {{
                                        statusLabels[
                                            reservation.status
                                        ] ??
                                        reservation.status
                                    }}
                                </span>
                            </td>

                            <td
                                class="p-4 font-semibold"
                            >
                                {{
                                    reservation.amount_formatted
                                }}
                            </td>

                            <td class="p-4">
                                <div
                                    class="flex flex-wrap gap-2"
                                >
                                    <Button
                                        v-if="
                                            reservation.payment_status ===
                                            'unpaid'
                                        "
                                        size="sm"
                                        variant="outline"
                                        @click="
                                            payingReservation =
                                                reservation
                                        "
                                    >
                                        💳 Pagar
                                    </Button>

                                    <Button
                                        v-if="
                                            [
                                                'confirmed',
                                                'active',
                                            ].includes(
                                                reservation.status,
                                            )
                                        "
                                        size="sm"
                                        variant="outline"
                                        @click="
                                            openExtendModal(
                                                reservation,
                                            )
                                        "
                                    >
                                        ⏱ Extender tiempo
                                    </Button>

                                    <Button
                                        v-if="
                                            reservation.status ===
                                            'confirmed'
                                        "
                                        size="sm"
                                        @click="
                                            action(
                                                `/reservations/${reservation.id}/checkin`,
                                            )
                                        "
                                    >
                                        ✅ Check-in
                                    </Button>

                                    <Button
                                        v-if="
                                            reservation.status ===
                                            'active'
                                        "
                                        size="sm"
                                        @click="
                                            action(
                                                `/reservations/${reservation.id}/checkout`,
                                            )
                                        "
                                    >
                                        🏁 Check-out
                                    </Button>

                                    <Button
                                        v-if="
                                            [
                                                'pending',
                                                'confirmed',
                                            ].includes(
                                                reservation.status,
                                            )
                                        "
                                        size="sm"
                                        variant="destructive"
                                        @click="
                                            action(
                                                `/reservations/${reservation.id}/cancel`,
                                            )
                                        "
                                    >
                                        ❌ Cancelar
                                    </Button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <PaymentModal
        v-if="payingReservation"
        :reservation-id="
            payingReservation.id
        "
        :amount="
            payingReservation.amount_formatted
        "
        :methods="paymentMethods"
        :yape-qr-url="yapeQrUrl"
        @close="payingReservation = null"
    />

    <div
        v-if="extendingReservation"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
    >
        <div
            class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl"
        >
            <h2 class="text-xl font-semibold">
                Extender tiempo
            </h2>

            <p
                class="mt-2 text-sm text-slate-600"
            >
                Plaza
                <strong>
                    {{
                        extendingReservation.space
                            .code
                    }}
                </strong>
            </p>

            <p
                class="mt-1 text-sm text-slate-600"
            >
                Duración actual:
                <strong>
                    {{
                        durationLabel(
                            extendingReservation.expected_duration_min,
                        )
                    }}
                </strong>
            </p>

            <div class="mt-5">
                <p class="text-sm font-medium">
                    Tiempo adicional
                </p>

                <div
                    class="mt-3 grid grid-cols-3 gap-3"
                >
                    <Button
                        type="button"
                        :variant="
                            selectedExtensionHours === 1
                                ? 'default'
                                : 'outline'
                        "
                        @click="
                            selectedExtensionHours = 1
                        "
                    >
                        +1 h
                    </Button>

                    <Button
                        type="button"
                        :variant="
                            selectedExtensionHours === 2
                                ? 'default'
                                : 'outline'
                        "
                        @click="
                            selectedExtensionHours = 2
                        "
                    >
                        +2 h
                    </Button>

                    <Button
                        type="button"
                        :variant="
                            selectedExtensionHours === 3
                                ? 'default'
                                : 'outline'
                        "
                        @click="
                            selectedExtensionHours = 3
                        "
                    >
                        +3 h
                    </Button>
                </div>
            </div>

            <div
                class="mt-5 rounded-lg bg-slate-50 p-4"
            >
                <p
                    class="text-sm text-slate-600"
                >
                    Costo adicional
                </p>

                <p
                    class="mt-1 text-2xl font-semibold"
                >
                    S/
                    {{
                        (
                            selectedExtensionHours *
                            8
                        ).toFixed(2)
                    }}
                </p>

                <p
                    class="mt-2 text-xs text-slate-500"
                >
                    S/ 8.00 por cada hora
                    adicional.
                </p>
            </div>

            <div
                class="mt-6 flex justify-end gap-3"
            >
                <Button
                    type="button"
                    variant="outline"
                    @click="closeExtendModal"
                >
                    Cancelar
                </Button>

                <Button
                    type="button"
                    @click="extendReservation"
                >
                    Confirmar extensión
                </Button>
            </div>
        </div>
    </div>
</template>