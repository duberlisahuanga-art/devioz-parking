<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

import ParkingMap from '@/components/ParkingMap.vue';
import SpaceGrid from '@/components/SpaceGrid.vue';

import { dashboard } from '@/routes';

interface ParkingMarker {
    id: number;
    name: string;
    address: string;
    lat: number | null;
    lng: number | null;
}

interface DashboardStats {
    total_spaces: number;
    occupied: number;
    active_reservations: number;
    parkings: number;
    availability: number;
    balance: string;
    role: string;
    unpaid_reservations: number;

    parkings_list: ParkingMarker[];

    spaces: {
        id: number;
        code: string;
        floor: number;
        type: string;
        status:
            | 'available'
            | 'occupied'
            | 'reserved'
            | 'maintenance';
    }[];

    services: {
        id: number;
        name: string;
        description: string | null;
        price: string;
        duration_min: number;
    }[];
}

const props = defineProps<{
    stats: DashboardStats;
}>();

const roleLabel = computed(() => {
    if (props.stats.role === 'admin') {
        return 'Administrador';
    }

    return 'Conductor';
});

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});
</script>

<template>
    <Head title="DEVIOZ PARKING" />

    <div
        class="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-6"
    >
        <!-- =====================================================
             CABECERA
             ===================================================== -->

        <header
            class="flex flex-wrap items-center justify-between gap-4"
        >
            <div class="flex items-center gap-4">
                <div
                    class="flex size-12 items-center justify-center rounded-xl bg-blue-600 text-2xl font-bold text-white shadow-sm"
                >
                    P
                </div>

                <div>
                    <p
                        class="text-xl font-bold tracking-tight"
                    >
                        DEVIOZ PARKING

                        <span
                            class="font-normal text-muted-foreground"
                        >
                            — Gestión Inteligente
                        </span>
                    </p>

                    <p
                        class="text-sm text-muted-foreground"
                    >
                        Tu lugar, en el momento exacto.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <span
                    class="rounded-full bg-blue-50 px-3 py-1.5 text-sm font-medium text-blue-700 dark:bg-blue-950 dark:text-blue-200"
                >
                    {{ roleLabel }}
                </span>

                <span
                    class="rounded-full bg-slate-900 px-3 py-1.5 text-sm font-medium text-white"
                >
                    S/ {{ stats.balance.replace('S/ ', '') }}
                </span>
            </div>
        </header>

        <!-- =====================================================
             INDICADORES PRINCIPALES
             ===================================================== -->

        <div
            class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4"
        >
            <section
                class="rounded-xl border bg-card p-5"
            >
                <p
                    class="text-sm text-muted-foreground"
                >
                    Plazas
                </p>

                <p
                    class="mt-2 text-3xl font-semibold"
                >
                    {{ stats.total_spaces }}
                </p>
            </section>

            <section
                class="rounded-xl border bg-card p-5"
            >
                <p
                    class="text-sm text-muted-foreground"
                >
                    Estacionamientos
                </p>

                <p
                    class="mt-2 text-3xl font-semibold"
                >
                    {{ stats.parkings }}
                </p>
            </section>

            <section
                class="rounded-xl border bg-card p-5"
            >
                <p
                    class="text-sm text-muted-foreground"
                >
                    Disponibilidad
                </p>

                <p
                    class="mt-2 text-3xl font-semibold"
                >
                    {{
                        stats.availability.toFixed(1)
                    }}%
                </p>
            </section>

            <section
                class="rounded-xl border bg-card p-5"
            >
                <p
                    class="text-sm text-muted-foreground"
                >
                    Monitoreo CCTV
                </p>

                <p
                    class="mt-2 text-3xl font-semibold"
                >
                    24/7
                </p>
            </section>
        </div>

        <!-- =====================================================
             ACCIONES DEL CONDUCTOR
             ===================================================== -->

        <div
            v-if="stats.role === 'driver'"
            class="flex flex-wrap gap-3"
        >
            <Link
                href="/reservations/create"
                class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700"
            >
                Reservar plaza
            </Link>

            <Link
                href="/reservations"
                class="rounded-md border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-blue-400 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"
            >
                Mis reservas
            </Link>

            <Link
                href="/vehicles"
                class="rounded-md border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-blue-400 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"
            >
                Mis vehículos
            </Link>

            <Link
                href="/reservations"
                class="rounded-md border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-blue-400 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"
            >
                Pagos pendientes

                <span
                    v-if="stats.unpaid_reservations"
                    class="ml-1 rounded-full bg-amber-100 px-1.5 py-0.5 text-xs text-amber-800"
                >
                    {{ stats.unpaid_reservations }}
                </span>
            </Link>
        </div>

        <!-- =====================================================
             ESPACIOS
             ===================================================== -->

        <section
            class="rounded-xl border bg-card p-4"
        >
            <div
                class="mb-4 flex items-center justify-between gap-4"
            >
                <div>
                    <h2
                        class="text-lg font-semibold"
                    >
                        Plazas del estacionamiento
                    </h2>

                    <p
                        class="text-sm text-muted-foreground"
                    >
                        Estacionamiento Devioz ·
                        {{ stats.total_spaces }} plazas
                    </p>
                </div>
            </div>

            <SpaceGrid
                :spaces="stats.spaces"
            />
        </section>

        <!-- =====================================================
             SERVICIOS
             ===================================================== -->

        <section
            v-if="stats.services.length"
            class="rounded-xl border bg-card p-5"
        >
            <div class="mb-4">
                <h2
                    class="text-lg font-semibold"
                >
                    Servicios adicionales
                </h2>

                <p
                    class="text-sm text-muted-foreground"
                >
                    Agrega un servicio a tu próxima
                    reserva.
                </p>
            </div>

            <div
                class="grid gap-3 sm:grid-cols-2"
            >
                <article
                    v-for="service in stats.services"
                    :key="service.id"
                    class="rounded-lg border border-blue-100 bg-blue-50 p-4 dark:border-blue-900 dark:bg-blue-950/40"
                >
                    <p
                        class="font-semibold text-blue-950 dark:text-blue-100"
                    >
                        {{ service.name }}
                    </p>

                    <p
                        class="mt-1 text-sm text-blue-900/70 dark:text-blue-200/70"
                    >
                        {{ service.description }}
                    </p>

                    <p
                        class="mt-3 font-semibold text-blue-800 dark:text-blue-200"
                    >
                        S/
                        {{
                            Number(
                                service.price,
                            ).toFixed(2)
                        }}
                        ·
                        {{ service.duration_min }}
                        min
                    </p>
                </article>
            </div>
        </section>

        <!-- =====================================================
             RESUMEN
             ===================================================== -->

        <div
            class="grid gap-4 md:grid-cols-3"
        >
            <section
                class="rounded-xl border bg-card p-5"
            >
                <p
                    class="text-sm text-muted-foreground"
                >
                    Plazas totales
                </p>

                <p
                    class="mt-2 text-3xl font-semibold"
                >
                    {{ stats.total_spaces }}
                </p>

                <p
                    class="mt-1 text-sm text-muted-foreground"
                >
                    Capacidad registrada del
                    estacionamiento.
                </p>
            </section>

            <section
                class="rounded-xl border bg-card p-5"
            >
                <p
                    class="text-sm text-muted-foreground"
                >
                    Plazas ocupadas
                </p>

                <p
                    class="mt-2 text-3xl font-semibold"
                >
                    {{ stats.occupied }}
                </p>

                <p
                    class="mt-1 text-sm text-muted-foreground"
                >
                    Ocupación actual registrada.
                </p>
            </section>

            <section
                class="rounded-xl border bg-card p-5"
            >
                <p
                    class="text-sm text-muted-foreground"
                >
                    Reservas activas
                </p>

                <p
                    class="mt-2 text-3xl font-semibold"
                >
                    {{ stats.active_reservations }}
                </p>

                <p
                    class="mt-1 text-sm text-muted-foreground"
                >
                    Reservas activas de tu cuenta.
                </p>
            </section>
        </div>

        <!-- =====================================================
             UBICACIÓN
             ===================================================== -->

        <section
            class="rounded-xl border bg-card p-4"
        >
            <div
                class="mb-4 flex items-center justify-between gap-4 px-2"
            >
                <div>
                    <h2
                        class="text-lg font-semibold"
                    >
                        Ubicación del estacionamiento
                    </h2>

                    <p
                        class="text-sm text-muted-foreground"
                    >
                        Ubicación registrada en el
                        sistema.
                    </p>
                </div>

                <span
                    class="text-sm text-muted-foreground"
                >
                    {{ stats.parkings }}
                    estacionamiento
                </span>
            </div>

            <ParkingMap
                :parkings="stats.parkings_list"
            />
        </section>
    </div>
</template>