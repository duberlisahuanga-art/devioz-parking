<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import {
    Car,
    CircleCheckBig,
    Clock3,
    MapPinned,
} from '@lucide/vue';

import ParkingLayout from '@/components/ParkingLayout.vue';

interface Space {
    id: number;
    parking_id: number;
    code: string;
    floor: number;
    type: string;
    status:
        | 'available'
        | 'occupied'
        | 'reserved'
        | 'maintenance';
    current_reservation_id: number | null;
}

interface Parking {
    id: number;
    name: string;
    address: string;
    capacity: number;
}

interface Summary {
    total: number;
    available: number;
    reserved: number;
    occupied: number;
    maintenance: number;
}

defineProps<{
    parking: Parking;
    spaces: Space[];
    summary: Summary;
}>();
</script>

<template>
    <Head title="Espacios / Croquis" />

    <div
        class="flex flex-1 flex-col gap-6 p-6"
    >
        <!-- Cabecera -->
        <div
            class="flex flex-wrap items-start justify-between gap-4"
        >
            <div>
                <p
                    class="text-sm text-muted-foreground"
                >
                    Administrador
                </p>

                <h1
                    class="text-2xl font-semibold"
                >
                    Espacios / Croquis
                </h1>

                <p
                    class="mt-1 text-sm text-muted-foreground"
                >
                    {{ parking.name }}
                </p>
            </div>

            <div
                class="flex items-center gap-2 rounded-lg border bg-card px-3 py-2 text-sm"
            >
                <MapPinned
                    class="size-4 text-cyan-500"
                />

                <span>
                    {{ summary.total }}
                    plazas registradas
                </span>
            </div>
        </div>

        <!-- KPIs -->
        <div
            class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4"
        >
            <section
                class="rounded-xl border bg-card p-5"
            >
                <div
                    class="flex items-center justify-between"
                >
                    <div>
                        <p
                            class="text-sm text-muted-foreground"
                        >
                            Total
                        </p>

                        <p
                            class="mt-2 text-3xl font-semibold"
                        >
                            {{ summary.total }}
                        </p>
                    </div>

                    <MapPinned
                        class="size-6 text-blue-500"
                    />
                </div>
            </section>

            <section
                class="rounded-xl border bg-card p-5"
            >
                <div
                    class="flex items-center justify-between"
                >
                    <div>
                        <p
                            class="text-sm text-muted-foreground"
                        >
                            Disponibles
                        </p>

                        <p
                            class="mt-2 text-3xl font-semibold text-emerald-600 dark:text-emerald-300"
                        >
                            {{ summary.available }}
                        </p>
                    </div>

                    <CircleCheckBig
                        class="size-6 text-emerald-500"
                    />
                </div>
            </section>

            <section
                class="rounded-xl border bg-card p-5"
            >
                <div
                    class="flex items-center justify-between"
                >
                    <div>
                        <p
                            class="text-sm text-muted-foreground"
                        >
                            Reservados
                        </p>

                        <p
                            class="mt-2 text-3xl font-semibold text-amber-600 dark:text-amber-300"
                        >
                            {{ summary.reserved }}
                        </p>
                    </div>

                    <Clock3
                        class="size-6 text-amber-500"
                    />
                </div>
            </section>

            <section
                class="rounded-xl border bg-card p-5"
            >
                <div
                    class="flex items-center justify-between"
                >
                    <div>
                        <p
                            class="text-sm text-muted-foreground"
                        >
                            Ocupados
                        </p>

                        <p
                            class="mt-2 text-3xl font-semibold text-red-600 dark:text-red-300"
                        >
                            {{ summary.occupied }}
                        </p>
                    </div>

                    <Car
                        class="size-6 text-red-500"
                    />
                </div>
            </section>
        </div>

        <!-- Croquis -->
        <ParkingLayout
            :spaces="spaces"
        />
    </div>
</template>