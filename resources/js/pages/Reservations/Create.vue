<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { CalendarDays } from '@lucide/vue';

import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';

interface Space {
    id: number;
    code: string;
    floor: number;
    type:
    | 'car'
    | 'moto'
    | 'ev'
    | 'disabled'
    | 'valet';

    status:
    | 'available'
    | 'reserved'
    | 'occupied'
    | 'maintenance';
}

interface Vehicle {
    id: number;
    plate: string;
    brand: string | null;
    model: string | null;
}

type FilterStatus =
    | 'all'
    | 'available'
    | 'reserved'
    | 'occupied'
    | 'maintenance';

const props = defineProps<{
    vehicles: Vehicle[];
    spaces: Space[];
    monthlyPrice: number;
    errors?: Record<string, string>;
}>();

const activeFilter =
    ref<FilterStatus>('all');

const availableSpaces = computed(() =>
    props.spaces.filter(
        (space) =>
            space.status === 'available',
    ),
);

const filteredSpaces = computed(() => {
    if (activeFilter.value === 'all') {
        return props.spaces;
    }

    return props.spaces.filter(
        (space) =>
            space.status ===
            activeFilter.value,
    );
});

const spaceId = ref<number | null>(
    availableSpaces.value[0]?.id ??
    null,
);

const vehicleId = ref<number | null>(
    props.vehicles[0]?.id ?? null,
);

const startsOn = ref('');

const dateInput =
    ref<HTMLInputElement | null>(
        null,
    );

const selectedSpace = computed(() =>
    props.spaces.find(
        (space) =>
            space.id === spaceId.value,
    ),
);

const selectedVehicle = computed(() =>
    props.vehicles.find(
        (vehicle) =>
            vehicle.id ===
            vehicleId.value,
    ),
);

/**
 * Regla de mensualidad:
 *
 * fecha inicio
 * + 1 mes sin desbordamiento
 * - 1 día
 *
 * Ejemplo:
 * 25/09/2026 -> 24/10/2026
 */
const endsOn = computed(() => {
    if (!startsOn.value) {
        return '';
    }

    const start =
        parseIsoDate(
            startsOn.value,
        );

    const originalDay =
        start.getDate();

    let targetYear =
        start.getFullYear();

    let targetMonth =
        start.getMonth() + 1;

    if (targetMonth > 11) {
        targetMonth = 0;

        targetYear += 1;
    }

    const lastDayTargetMonth =
        new Date(
            targetYear,
            targetMonth + 1,
            0,
        ).getDate();

    const targetDay = Math.min(
        originalDay,
        lastDayTargetMonth,
    );

    const end = new Date(
        targetYear,
        targetMonth,
        targetDay,
    );

    end.setDate(
        end.getDate() - 1,
    );

    return formatIsoDate(end);
});

const total = computed(() =>
    Number(props.monthlyPrice),
);

const filters: Array<{
    label: string;
    value: FilterStatus;
}> = [
        {
            label: 'Todas',
            value: 'all',
        },
        {
            label: 'Libres',
            value: 'available',
        },
        {
            label: 'Reservadas',
            value: 'reserved',
        },
        {
            label: 'Ocupadas',
            value: 'occupied',
        },
        {
            label: 'En mantenimiento',
            value: 'maintenance',
        },
    ];

const today = computed(() =>
    formatIsoDate(
        new Date(),
    ),
);

function parseIsoDate(
    value: string,
): Date {
    const [
        year,
        month,
        day,
    ] = value
        .split('-')
        .map(Number);

    return new Date(
        year,
        month - 1,
        day,
    );
}

function formatIsoDate(
    date: Date,
): string {
    const year =
        date.getFullYear();

    const month =
        String(
            date.getMonth() + 1,
        ).padStart(2, '0');

    const day =
        String(
            date.getDate(),
        ).padStart(2, '0');

    return `${year}-${month}-${day}`;
}

function formatDisplayDate(
    value: string,
): string {
    if (!value) {
        return 'Selecciona una fecha';
    }

    return new Intl.DateTimeFormat(
        'es-PE',
        {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
        },
    ).format(
        parseIsoDate(value),
    );
}

function openDatePicker(): void {
    const input =
        dateInput.value;

    if (!input) {
        return;
    }

    if (
        typeof input.showPicker ===
        'function'
    ) {
        input.showPicker();

        return;
    }

    input.focus();
    input.click();
}

function submit(): void {
    router.post(
        '/reservations',
        {
            space_id:
                spaceId.value,

            vehicle_id:
                vehicleId.value,

            starts_on:
                startsOn.value,
        },
        {
            preserveScroll: true,
        },
    );
}

function selectSpace(
    space: Space,
): void {
    if (
        space.status !== 'available'
    ) {
        return;
    }

    spaceId.value =
        space.id;
}

function typeLabel(
    type: Space['type'],
): string {
    const labels: Record<
        Space['type'],
        string
    > = {
        car:
            '🚗 Auto',

        moto:
            '🏍 Moto',

        ev:
            '⚡ Eléctrico',

        disabled:
            '♿ Accesible',

        valet:
            '🅿️ Valet',
    };

    return labels[type];
}

function statusLabel(
    status: Space['status'],
): string {
    const labels: Record<
        Space['status'],
        string
    > = {
        available:
            'Libre',

        reserved:
            'Reservada',

        occupied:
            'Ocupada',

        maintenance:
            'Mantenimiento',
    };

    return labels[status];
}

function statusClass(
    space: Space,
): string {
    if (
        spaceId.value ===
        space.id &&
        space.status ===
        'available'
    ) {
        return 'border-cyan-400 bg-[#16304f] ring-2 ring-cyan-400/40 text-white shadow-md shadow-cyan-950/20';
    }

    const classes: Record<
        Space['status'],
        string
    > = {
        available:
            'border-blue-500/70 bg-[#10233f] text-white hover:border-cyan-400 hover:bg-[#16304f] cursor-pointer',

        reserved:
            'border-amber-400/80 bg-[#3a2b0b] text-amber-100 cursor-not-allowed',

        occupied:
            'border-red-400/80 bg-[#421919] text-red-100 cursor-not-allowed',

        maintenance:
            'border-slate-500/80 bg-[#273244] text-slate-200 cursor-not-allowed',
    };

    return classes[
        space.status
    ];
}

function badgeClass(
    status: Space['status'],
): string {
    const classes: Record<
        Space['status'],
        string
    > = {
        available:
            'bg-blue-500/20 text-blue-100 border border-blue-400/40',

        reserved:
            'bg-amber-400/20 text-amber-100 border border-amber-300/40',

        occupied:
            'bg-red-400/20 text-red-100 border border-red-300/40',

        maintenance:
            'bg-slate-400/20 text-slate-100 border border-slate-300/30',
    };

    return classes[status];
}

function countByStatus(
    status: Space['status'],
): number {
    return props.spaces.filter(
        (space) =>
            space.status === status,
    ).length;
}

function filterButtonClass(
    value: FilterStatus,
): string {
    if (
        activeFilter.value ===
        value
    ) {
        return 'bg-amber-400 text-slate-950 hover:bg-amber-300 border-amber-300';
    }

    return 'bg-transparent text-slate-200 border-slate-600 hover:bg-slate-800 hover:text-white';
}
</script>

<template>

    <Head title="Reservar plaza mensual" />

    <div class="flex flex-1 flex-col gap-6 p-6 text-slate-100">
        <!-- CABECERA -->
        <div>
            <p class="text-sm text-sky-300">
                Conductor
            </p>

            <h1 class="text-2xl font-semibold text-white">
                Reservar plaza
            </h1>

            <p class="mt-1 text-sm text-slate-300">
                Selecciona una plaza y
                registra tu mensualidad de
                estacionamiento.
            </p>
        </div>

        <div class="grid gap-6 xl:grid-cols-[1.4fr_1fr]">
            <!-- PLAZAS -->
            <section class="rounded-xl border border-slate-700 bg-[#111c2e] p-5 shadow-sm">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-white">
                            1. Elige una plaza
                        </h2>

                        <p class="mt-1 text-sm text-slate-300">
                            Consulta las plazas
                            disponibles según su
                            estado.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <Button v-for="
filter in filters
                            " :key="filter.value
                                " type="button" size="sm" variant="outline" :class="filterButtonClass(
                                    filter.value,
                                )
                                    " @click="
                                    activeFilter =
                                    filter.value
                                    ">
                            {{
                                filter.label
                            }}
                        </Button>
                    </div>
                </div>

                <!-- PLAZAS -->
                <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-5">
                    <button v-for="
space in
    filteredSpaces
                        " :key="space.id" type="button" class="rounded-lg border p-3 text-left transition duration-200"
                        :class="statusClass(
                            space,
                        )
                            " :disabled="space.status !==
                                'available'
                                " @click="
                                selectSpace(
                                    space,
                                )
                                ">
                        <div class="flex items-start justify-between gap-2">
                            <p class="font-semibold text-white">
                                {{
                                    space.code
                                }}
                            </p>

                            <span class="rounded-full px-2 py-0.5 text-[10px] font-medium" :class="badgeClass(
                                space.status,
                            )
                                ">
                                {{
                                    statusLabel(
                                        space.status,
                                    )
                                }}
                            </span>
                        </div>

                        <p class="mt-1 text-xs text-slate-300">
                            Piso
                            {{
                                space.floor
                            }}
                        </p>

                        <p class="mt-1 text-xs text-slate-200">
                            {{
                                typeLabel(
                                    space.type,
                                )
                            }}
                        </p>
                    </button>
                </div>

                <p v-if="
                    !filteredSpaces.length
                " class="mt-4 rounded-lg border border-amber-500/30 bg-amber-500/10 p-4 text-sm text-amber-100">
                    No hay plazas para este
                    filtro.
                </p>

                <!-- RESUMEN DE PLAZAS -->
                <div class="mt-5 grid gap-2 text-sm sm:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-lg border border-blue-500/40 bg-[#10233f] p-3">
                        <p class="text-xs text-blue-200">
                            Libres
                        </p>

                        <p class="text-lg font-semibold text-white">
                            {{
                                countByStatus(
                                    'available',
                                )
                            }}
                        </p>
                    </div>

                    <div class="rounded-lg border border-amber-400/40 bg-[#3a2b0b] p-3">
                        <p class="text-xs text-amber-200">
                            Reservadas
                        </p>

                        <p class="text-lg font-semibold text-white">
                            {{
                                countByStatus(
                                    'reserved',
                                )
                            }}
                        </p>
                    </div>

                    <div class="rounded-lg border border-red-400/40 bg-[#421919] p-3">
                        <p class="text-xs text-red-200">
                            Ocupadas
                        </p>

                        <p class="text-lg font-semibold text-white">
                            {{
                                countByStatus(
                                    'occupied',
                                )
                            }}
                        </p>
                    </div>

                    <div class="rounded-lg border border-slate-500/50 bg-[#273244] p-3">
                        <p class="text-xs text-slate-300">
                            Mantenimiento
                        </p>

                        <p class="text-lg font-semibold text-white">
                            {{
                                countByStatus(
                                    'maintenance',
                                )
                            }}
                        </p>
                    </div>
                </div>
            </section>

            <!-- DATOS DE LA RESERVA -->
            <section class="h-fit rounded-xl border border-slate-700 bg-[#111c2e] p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-white">
                    2. Datos de la reserva
                </h2>

                <p class="mt-1 text-sm text-slate-300">
                    La reserva corresponde a
                    una mensualidad completa.
                </p>

                <div class="mt-5 grid gap-4">
                    <!-- VEHÍCULO -->
                    <label class="grid gap-2 text-sm font-medium text-slate-100">
                        Vehículo

                        <select v-model="vehicleId
                            "
                            class="vehicle-select rounded-md border border-slate-600 bg-[#0d1728] px-3 py-2 text-white outline-none transition focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20">
                            <option :value="null" class="bg-[#0d1728] text-white">
                                Selecciona un
                                vehículo
                            </option>

                            <option v-for="
vehicle in
    props.vehicles
                                " :key="vehicle.id
                                    " :value="vehicle.id
                                        " class="bg-[#0d1728] text-white">
                                {{
                                    vehicle.plate
                                }}
                                ·
                                {{
                                    vehicle.brand ??
                                    ''
                                }}
                                {{
                                    vehicle.model ??
                                    ''
                                }}
                            </option>
                        </select>
                    </label>

                    <p v-if="
                        !props.vehicles
                            .length
                    " class="text-sm text-amber-300">
                        Agrega un vehículo
                        antes de reservar.
                    </p>

                    <!-- FECHA INICIO -->
                    <label class="grid gap-2 text-sm font-medium text-slate-100">
                        Fecha de inicio

                        <div class="relative">
                            <input ref="dateInput" v-model="startsOn
                                " type="date" :min="today
                                    "
                                class="reservation-input w-full rounded-md border border-slate-600 bg-[#0d1728] px-3 py-2 pr-12 text-white outline-none transition focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20" />

                            <button type="button"
                                class="absolute inset-y-0 right-0 flex w-11 items-center justify-center rounded-r-md text-cyan-300 transition hover:bg-cyan-400/10 hover:text-cyan-100"
                                aria-label="Seleccionar fecha de inicio" @click="
                                    openDatePicker
                                ">
                                <CalendarDays class="h-5 w-5" :stroke-width="2
                                    " />
                            </button>
                        </div>

                        <span class="text-xs font-normal text-slate-400">
                            La mensualidad
                            comienza desde el día
                            seleccionado.
                        </span>
                    </label>

                    <!-- PLAN -->
                    <div class="rounded-lg border border-cyan-500/30 bg-cyan-500/10 p-4">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="text-xs uppercase tracking-wide text-cyan-200">
                                    Plan
                                </p>

                                <p class="mt-1 font-semibold text-white">
                                    Mensual
                                </p>
                            </div>

                            <p class="text-xl font-semibold text-white">
                                S/
                                {{
                                    Number(
                                        props.monthlyPrice,
                                    ).toFixed(
                                        2,
                                    )
                                }}
                            </p>
                        </div>
                    </div>

                    <!-- VIGENCIA -->
                    <div class="rounded-lg border border-slate-700 bg-[#0b1424] p-4">
                        <p class="text-sm font-medium text-white">
                            Vigencia de la
                            mensualidad
                        </p>

                        <div class="mt-3 grid grid-cols-2 gap-3">
                            <div>
                                <p class="text-xs text-slate-400">
                                    Inicio
                                </p>

                                <p class="mt-1 text-sm font-semibold text-slate-100">
                                    {{
                                        formatDisplayDate(
                                            startsOn,
                                        )
                                    }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-slate-400">
                                    Vencimiento
                                </p>

                                <p class="mt-1 text-sm font-semibold text-slate-100">
                                    {{
                                        formatDisplayDate(
                                            endsOn,
                                        )
                                    }}
                                </p>
                            </div>
                        </div>

                        <p class="mt-3 text-xs text-slate-400">
                            La vigencia finaliza
                            un día antes de
                            cumplirse el
                            siguiente mes.
                        </p>
                    </div>

                    <!-- RESUMEN -->
                    <div class="rounded-lg border border-slate-700 bg-[#0b1424] p-4">
                        <p class="text-sm font-medium text-white">
                            Resumen
                        </p>

                        <div class="mt-3 grid gap-2 text-sm">
                            <div class="flex justify-between gap-4">
                                <span class="text-slate-300">
                                    Plaza
                                </span>

                                <strong class="text-white">
                                    {{
                                        selectedSpace
                                            ?.code ??
                                        'Sin seleccionar'
                                    }}
                                </strong>
                            </div>

                            <div class="flex justify-between gap-4">
                                <span class="text-slate-300">
                                    Vehículo
                                </span>

                                <strong class="text-white">
                                    {{
                                        selectedVehicle
                                            ?.plate ??
                                        'Sin seleccionar'
                                    }}
                                </strong>
                            </div>

                            <div class="flex justify-between gap-4">
                                <span class="text-slate-300">
                                    Mensualidad
                                </span>

                                <strong class="text-white">
                                    S/
                                    {{
                                        Number(
                                            props.monthlyPrice,
                                        ).toFixed(
                                            2,
                                        )
                                    }}
                                </strong>
                            </div>
                        </div>

                        <div class="mt-4 border-t border-slate-700 pt-4">
                            <p class="text-sm text-slate-300">
                                Total de la
                                reserva
                            </p>

                            <p class="mt-1 text-2xl font-semibold text-white">
                                S/
                                {{
                                    total.toFixed(
                                        2,
                                    )
                                }}
                            </p>

                            <p class="mt-2 text-xs text-slate-400">
                                Plan mensual del
                                estacionamiento.
                            </p>
                        </div>
                    </div>

                    <!-- ERRORES -->
                    <InputError :message="props.errors
                        ?.space_id ||
                        props.errors
                            ?.vehicle_id ||
                        props.errors
                            ?.starts_on
                        " />

                    <!-- CONFIRMAR -->
                    <Button type="button"
                        class="bg-amber-400 text-slate-950 hover:bg-amber-300 disabled:bg-amber-700/50 disabled:text-slate-300"
                        :disabled="!spaceId ||
                            !startsOn ||
                            !vehicleId
                            " @click="submit">
                        Confirmar reserva
                        mensual
                    </Button>

                    <p class="text-center text-xs text-slate-400">
                        La plaza permanecerá
                        reservada durante toda
                        la vigencia de la
                        mensualidad.
                    </p>
                </div>
            </section>
        </div>
    </div>
</template>

<style scoped>
.vehicle-select {
    color-scheme: dark;
}

.vehicle-select option {
    background-color: #0d1728;
    color: #ffffff;
}

.reservation-input {
    color-scheme: dark;
}

.reservation-input::-webkit-calendar-picker-indicator {
    opacity: 0;
    cursor: pointer;
}
</style>