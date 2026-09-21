<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

interface Vehicle {
    id: number;
    plate: string;
    brand: string | null;
    model: string | null;
    color: string | null;
    type: string | null;
}

interface Payment {
    id: number;
    amount: string | number;
    method: string;
    status: string;
}

interface Service {
    id: number;
    name: string;
}

interface Space {
    id: number;
    code: string;
}

interface ReservationVehicle {
    id: number;
    plate: string;
    brand: string | null;
    model: string | null;
}

interface Reservation {
    id: number;
    status: string;
    payment_status: string;
    amount: string | number;
    amount_formatted?: string;
    check_in_at: string | null;
    check_out_at: string | null;
    expected_duration_min: number;
    created_at: string;

    space: Space | null;
    vehicle: ReservationVehicle | null;
    payments: Payment[];
    services: Service[];
}

interface UserDetail {
    id: number;
    name: string;
    email: string;
    phone: string | null;

    email_verified_at: string | null;
    two_factor_confirmed_at: string | null;
    terms_accepted: boolean;
    created_at: string;

    role: string;

    vehicles: Vehicle[];
    reservations: Reservation[];
}

interface Summary {
    vehicles: number;
    reservations: number;
    active_reservations: number;
    completed_reservations: number;
}

const props = defineProps<{
    user: UserDetail;
    summary: Summary;
}>();

function formatDate(value: string | null): string {
    if (!value) {
        return 'No registrado';
    }

    return new Date(value).toLocaleString('es-PE', {
        dateStyle: 'short',
        timeStyle: 'short',
    });
}

function roleLabel(role: string): string {
    return role === 'admin'
        ? 'Administrador'
        : 'Conductor';
}

function reservationStatus(status: string): string {
    const labels: Record<string, string> = {
        pending: 'Pendiente',
        confirmed: 'Confirmada',
        active: 'Activa',
        completed: 'Finalizada',
        cancelled: 'Cancelada',
    };

    return labels[status] ?? status;
}

function paymentStatus(status: string): string {
    const labels: Record<string, string> = {
        unpaid: 'Pendiente',
        pending: 'En revisión',
        paid: 'Pagado',
        rejected: 'Rechazado',
    };

    return labels[status] ?? status;
}

function reservationStatusClass(status: string): string {
    if (status === 'active') {
        return 'border-emerald-500/40 bg-emerald-500/10 text-emerald-300';
    }

    if (status === 'completed') {
        return 'border-sky-500/40 bg-sky-500/10 text-sky-300';
    }

    if (status === 'cancelled') {
        return 'border-rose-500/40 bg-rose-500/10 text-rose-300';
    }

    return 'border-amber-500/40 bg-amber-500/10 text-amber-300';
}

function paymentStatusClass(status: string): string {
    if (status === 'paid') {
        return 'text-emerald-300';
    }

    if (status === 'rejected') {
        return 'text-rose-300';
    }

    if (status === 'pending') {
        return 'text-amber-300';
    }

    return 'text-slate-400';
}
</script>

<template>
    <Head :title="`Usuario - ${user.name}`" />

    <div class="space-y-6">
        <!-- CABECERA -->
        <section
            class="rounded-2xl border border-slate-700 bg-[#071525] p-6 shadow-xl"
        >
            <div
                class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
            >
                <div>
                    <p
                        class="text-xs font-semibold uppercase tracking-wider text-sky-300"
                    >
                        Administración / Usuarios
                    </p>

                    <h1
                        class="mt-1 text-2xl font-bold text-white"
                    >
                        {{ user.name }}
                    </h1>

                    <p
                        class="mt-2 text-sm text-slate-400"
                    >
                        Información y actividad del usuario dentro del sistema.
                    </p>
                </div>

                <Link
                    href="/admin/users"
                    class="inline-flex items-center justify-center rounded-lg border border-slate-600 bg-slate-900 px-4 py-2 text-sm font-semibold text-slate-300 transition hover:bg-slate-800"
                >
                    Volver a usuarios
                </Link>
            </div>
        </section>

        <!-- RESUMEN -->
        <section
            class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4"
        >
            <div
                class="rounded-xl border border-slate-700 bg-[#0b1826] p-4"
            >
                <p
                    class="text-xs uppercase tracking-wide text-slate-500"
                >
                    Vehículos
                </p>

                <p
                    class="mt-2 text-2xl font-bold text-white"
                >
                    {{ summary.vehicles }}
                </p>
            </div>

            <div
                class="rounded-xl border border-slate-700 bg-[#0b1826] p-4"
            >
                <p
                    class="text-xs uppercase tracking-wide text-slate-500"
                >
                    Reservas
                </p>

                <p
                    class="mt-2 text-2xl font-bold text-white"
                >
                    {{ summary.reservations }}
                </p>
            </div>

            <div
                class="rounded-xl border border-slate-700 bg-[#0b1826] p-4"
            >
                <p
                    class="text-xs uppercase tracking-wide text-slate-500"
                >
                    Reservas activas
                </p>

                <p
                    class="mt-2 text-2xl font-bold text-emerald-300"
                >
                    {{ summary.active_reservations }}
                </p>
            </div>

            <div
                class="rounded-xl border border-slate-700 bg-[#0b1826] p-4"
            >
                <p
                    class="text-xs uppercase tracking-wide text-slate-500"
                >
                    Finalizadas
                </p>

                <p
                    class="mt-2 text-2xl font-bold text-sky-300"
                >
                    {{ summary.completed_reservations }}
                </p>
            </div>
        </section>

        <!-- DATOS DEL USUARIO -->
        <section
            class="rounded-2xl border border-slate-700 bg-[#0b1826] p-6 shadow-xl"
        >
            <h2
                class="text-lg font-semibold text-white"
            >
                Datos del usuario
            </h2>

            <div
                class="mt-5 grid gap-5 md:grid-cols-2 xl:grid-cols-4"
            >
                <div>
                    <p
                        class="text-xs uppercase tracking-wide text-slate-500"
                    >
                        Nombre
                    </p>

                    <p
                        class="mt-1 text-sm font-semibold text-white"
                    >
                        {{ user.name }}
                    </p>
                </div>

                <div>
                    <p
                        class="text-xs uppercase tracking-wide text-slate-500"
                    >
                        Correo
                    </p>

                    <p
                        class="mt-1 text-sm text-slate-300"
                    >
                        {{ user.email }}
                    </p>
                </div>

                <div>
                    <p
                        class="text-xs uppercase tracking-wide text-slate-500"
                    >
                        Teléfono
                    </p>

                    <p
                        class="mt-1 text-sm text-slate-300"
                    >
                        {{ user.phone ?? 'No registrado' }}
                    </p>
                </div>

                <div>
                    <p
                        class="text-xs uppercase tracking-wide text-slate-500"
                    >
                        Rol
                    </p>

                    <p
                        class="mt-1 text-sm font-semibold text-sky-300"
                    >
                        {{ roleLabel(user.role) }}
                    </p>
                </div>

                <div>
                    <p
                        class="text-xs uppercase tracking-wide text-slate-500"
                    >
                        Correo verificado
                    </p>

                    <p
                        class="mt-1 text-sm"
                        :class="
                            user.email_verified_at
                                ? 'text-emerald-300'
                                : 'text-amber-300'
                        "
                    >
                        {{
                            user.email_verified_at
                                ? 'Sí'
                                : 'No'
                        }}
                    </p>
                </div>

                <div>
                    <p
                        class="text-xs uppercase tracking-wide text-slate-500"
                    >
                        2FA
                    </p>

                    <p
                        class="mt-1 text-sm"
                        :class="
                            user.two_factor_confirmed_at
                                ? 'text-emerald-300'
                                : 'text-slate-400'
                        "
                    >
                        {{
                            user.two_factor_confirmed_at
                                ? 'Activo'
                                : 'Inactivo'
                        }}
                    </p>
                </div>

                <div>
                    <p
                        class="text-xs uppercase tracking-wide text-slate-500"
                    >
                        Términos aceptados
                    </p>

                    <p
                        class="mt-1 text-sm text-slate-300"
                    >
                        {{
                            user.terms_accepted
                                ? 'Sí'
                                : 'No'
                        }}
                    </p>
                </div>

                <div>
                    <p
                        class="text-xs uppercase tracking-wide text-slate-500"
                    >
                        Registrado
                    </p>

                    <p
                        class="mt-1 text-sm text-slate-300"
                    >
                        {{ formatDate(user.created_at) }}
                    </p>
                </div>
            </div>
        </section>

        <!-- VEHÍCULOS -->
        <section
            class="overflow-hidden rounded-2xl border border-slate-700 bg-[#0b1826] shadow-xl"
        >
            <div
                class="border-b border-slate-700 px-5 py-4"
            >
                <h2
                    class="text-lg font-semibold text-white"
                >
                    Vehículos registrados
                </h2>
            </div>

            <div
                class="overflow-x-auto"
            >
                <table
                    class="min-w-full"
                >
                    <thead
                        class="border-b border-slate-700 bg-slate-900/60"
                    >
                        <tr>
                            <th
                                class="px-5 py-4 text-left text-xs font-semibold uppercase text-slate-400"
                            >
                                Placa
                            </th>

                            <th
                                class="px-5 py-4 text-left text-xs font-semibold uppercase text-slate-400"
                            >
                                Marca
                            </th>

                            <th
                                class="px-5 py-4 text-left text-xs font-semibold uppercase text-slate-400"
                            >
                                Modelo
                            </th>

                            <th
                                class="px-5 py-4 text-left text-xs font-semibold uppercase text-slate-400"
                            >
                                Color
                            </th>

                            <th
                                class="px-5 py-4 text-left text-xs font-semibold uppercase text-slate-400"
                            >
                                Tipo
                            </th>
                        </tr>
                    </thead>

                    <tbody
                        class="divide-y divide-slate-800"
                    >
                        <tr
                            v-for="vehicle in user.vehicles"
                            :key="vehicle.id"
                        >
                            <td
                                class="px-5 py-4 font-semibold text-white"
                            >
                                {{ vehicle.plate }}
                            </td>

                            <td
                                class="px-5 py-4 text-sm text-slate-300"
                            >
                                {{ vehicle.brand ?? '—' }}
                            </td>

                            <td
                                class="px-5 py-4 text-sm text-slate-300"
                            >
                                {{ vehicle.model ?? '—' }}
                            </td>

                            <td
                                class="px-5 py-4 text-sm text-slate-300"
                            >
                                {{ vehicle.color ?? '—' }}
                            </td>

                            <td
                                class="px-5 py-4 text-sm text-slate-300"
                            >
                                {{ vehicle.type ?? '—' }}
                            </td>
                        </tr>

                        <tr
                            v-if="user.vehicles.length === 0"
                        >
                            <td
                                colspan="5"
                                class="px-5 py-8 text-center text-sm text-slate-500"
                            >
                                Este usuario no tiene vehículos registrados.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- RESERVAS -->
        <section
            class="overflow-hidden rounded-2xl border border-slate-700 bg-[#0b1826] shadow-xl"
        >
            <div
                class="border-b border-slate-700 px-5 py-4"
            >
                <h2
                    class="text-lg font-semibold text-white"
                >
                    Historial de reservas
                </h2>
            </div>

            <div
                class="overflow-x-auto"
            >
                <table
                    class="min-w-full"
                >
                    <thead
                        class="border-b border-slate-700 bg-slate-900/60"
                    >
                        <tr>
                            <th
                                class="px-5 py-4 text-left text-xs font-semibold uppercase text-slate-400"
                            >
                                Reserva
                            </th>

                            <th
                                class="px-5 py-4 text-left text-xs font-semibold uppercase text-slate-400"
                            >
                                Plaza
                            </th>

                            <th
                                class="px-5 py-4 text-left text-xs font-semibold uppercase text-slate-400"
                            >
                                Vehículo
                            </th>

                            <th
                                class="px-5 py-4 text-left text-xs font-semibold uppercase text-slate-400"
                            >
                                Estado
                            </th>

                            <th
                                class="px-5 py-4 text-left text-xs font-semibold uppercase text-slate-400"
                            >
                                Pago
                            </th>

                            <th
                                class="px-5 py-4 text-left text-xs font-semibold uppercase text-slate-400"
                            >
                                Monto
                            </th>

                            <th
                                class="px-5 py-4 text-left text-xs font-semibold uppercase text-slate-400"
                            >
                                Ingreso
                            </th>
                        </tr>
                    </thead>

                    <tbody
                        class="divide-y divide-slate-800"
                    >
                        <tr
                            v-for="reservation in user.reservations"
                            :key="reservation.id"
                            class="transition hover:bg-slate-900/40"
                        >
                            <td
                                class="px-5 py-4 font-semibold text-white"
                            >
                                #{{ reservation.id }}
                            </td>

                            <td
                                class="px-5 py-4 text-sm text-slate-300"
                            >
                                {{ reservation.space?.code ?? '—' }}
                            </td>

                            <td
                                class="px-5 py-4 text-sm text-slate-300"
                            >
                                {{ reservation.vehicle?.plate ?? '—' }}
                            </td>

                            <td
                                class="px-5 py-4"
                            >
                                <span
                                    class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold"
                                    :class="
                                        reservationStatusClass(
                                            reservation.status
                                        )
                                    "
                                >
                                    {{
                                        reservationStatus(
                                            reservation.status
                                        )
                                    }}
                                </span>
                            </td>

                            <td
                                class="px-5 py-4"
                            >
                                <span
                                    class="text-sm font-medium"
                                    :class="
                                        paymentStatusClass(
                                            reservation.payment_status
                                        )
                                    "
                                >
                                    {{
                                        paymentStatus(
                                            reservation.payment_status
                                        )
                                    }}
                                </span>
                            </td>

                            <td
                                class="px-5 py-4 text-sm font-semibold text-white"
                            >
                                {{
                                    reservation.amount_formatted
                                        ?? `S/ ${Number(reservation.amount).toFixed(2)}`
                                }}
                            </td>

                            <td
                                class="whitespace-nowrap px-5 py-4 text-sm text-slate-400"
                            >
                                {{
                                    formatDate(
                                        reservation.check_in_at
                                    )
                                }}
                            </td>
                        </tr>

                        <tr
                            v-if="user.reservations.length === 0"
                        >
                            <td
                                colspan="7"
                                class="px-5 py-8 text-center text-sm text-slate-500"
                            >
                                Este usuario todavía no tiene reservas.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</template>