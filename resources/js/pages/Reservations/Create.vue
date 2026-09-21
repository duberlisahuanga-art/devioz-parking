<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { CalendarDays } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import InputError from '@/components/InputError.vue';

interface Space {
    id: number;
    code: string;
    floor: number;
    type: 'car' | 'moto' | 'ev' | 'disabled' | 'valet';
    status: 'available' | 'reserved' | 'occupied' | 'maintenance';
}

interface Vehicle {
    id: number;
    plate: string;
    brand: string | null;
    model: string | null;
}

interface Service {
    id: number;
    name: string;
    description: string | null;
    price: string;
    duration_min: number;
}

type FilterStatus = 'all' | 'available' | 'reserved' | 'maintenance';

const props = defineProps<{
    vehicles: Vehicle[];
    spaces: Space[];
    services: Service[];
    errors?: Record<string, string>;
}>();

const activeFilter = ref<FilterStatus>('all');

const availableSpaces = computed(() =>
    props.spaces.filter((space) => space.status === 'available'),
);

const filteredSpaces = computed(() => {
    if (activeFilter.value === 'all') {
        return props.spaces;
    }

    return props.spaces.filter(
        (space) => space.status === activeFilter.value,
    );
});

const spaceId = ref<number | null>(
    availableSpaces.value[0]?.id ?? null,
);

const vehicleId = ref<number | null>(
    props.vehicles[0]?.id ?? null,
);

const checkInAt = ref('');
const dateInput = ref<HTMLInputElement | null>(null);

const durationHours = ref(1);
const selectedServiceIds = ref<number[]>([]);

const selectedServices = computed(() =>
    props.services.filter((service) =>
        selectedServiceIds.value.includes(service.id),
    ),
);

const total = computed(
    () =>
        durationHours.value * 8 +
        selectedServices.value.reduce(
            (sum, service) => sum + Number(service.price),
            0,
        ),
);

const filters = [
    {
        label: 'Todas',
        value: 'all' as FilterStatus,
    },
    {
        label: 'Libres',
        value: 'available' as FilterStatus,
    },
    {
        label: 'Reservadas',
        value: 'reserved' as FilterStatus,
    },
    {
        label: 'En mantenimiento',
        value: 'maintenance' as FilterStatus,
    },
];

function openDatePicker(): void {
    const input = dateInput.value;

    if (!input) {
        return;
    }

    if (typeof input.showPicker === 'function') {
        input.showPicker();
    } else {
        input.focus();
        input.click();
    }
}

function submit(): void {
    router.post('/reservations', {
        space_id: spaceId.value,
        vehicle_id: vehicleId.value,
        check_in_at: checkInAt.value,
        duration_hours: durationHours.value,
        service_ids: selectedServiceIds.value,
    });
}

function selectSpace(space: Space): void {
    if (space.status !== 'available') {
        return;
    }

    spaceId.value = space.id;
}

function typeLabel(type: Space['type']): string {
    return {
        car: '🚗 Auto',
        moto: '🏍 Moto',
        ev: '⚡ Eléctrico',
        disabled: '♿ Accesible',
        valet: '🅿️ Valet',
    }[type];
}

function statusLabel(status: Space['status']): string {
    return {
        available: 'Libre',
        reserved: 'Reservada',
        occupied: 'Ocupada',
        maintenance: 'Mantenimiento',
    }[status];
}

function statusClass(space: Space): string {
    if (spaceId.value === space.id) {
        return 'border-cyan-400 bg-[#16304f] ring-2 ring-cyan-400/40 text-white shadow-md shadow-cyan-950/20';
    }

    return {
        available:
            'border-blue-500/70 bg-[#10233f] text-white hover:border-cyan-400 hover:bg-[#16304f] cursor-pointer',
        reserved:
            'border-amber-400/80 bg-[#3a2b0b] text-amber-100 cursor-not-allowed',
        occupied:
            'border-red-400/80 bg-[#421919] text-red-100 cursor-not-allowed',
        maintenance:
            'border-slate-500/80 bg-[#273244] text-slate-200 cursor-not-allowed',
    }[space.status];
}

function badgeClass(status: Space['status']): string {
    return {
        available:
            'bg-blue-500/20 text-blue-100 border border-blue-400/40',
        reserved:
            'bg-amber-400/20 text-amber-100 border border-amber-300/40',
        occupied:
            'bg-red-400/20 text-red-100 border border-red-300/40',
        maintenance:
            'bg-slate-400/20 text-slate-100 border border-slate-300/30',
    }[status];
}

function countByStatus(status: Space['status']): number {
    return props.spaces.filter(
        (space) => space.status === status,
    ).length;
}

function filterButtonClass(value: FilterStatus): string {
    if (activeFilter.value === value) {
        return 'bg-amber-400 text-slate-950 hover:bg-amber-300 border-amber-300';
    }

    return 'bg-transparent text-slate-200 border-slate-600 hover:bg-slate-800 hover:text-white';
}
</script>

<template>
    <Head title="Reservar plaza" />

    <div class="flex flex-1 flex-col gap-6 p-6 text-slate-100">
        <div>
            <p class="text-sm text-sky-300">
                Conductor
            </p>

            <h1 class="text-2xl font-semibold text-white">
                Reservar plaza
            </h1>
        </div>

        <div class="grid gap-6 xl:grid-cols-[1.4fr_1fr]">
            <section
                class="rounded-xl border border-slate-700 bg-[#111c2e] p-5 shadow-sm"
            >
                <div
                    class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
                >
                    <div>
                        <h2 class="text-lg font-semibold text-white">
                            1. Elige una plaza
                        </h2>

                        <p class="mt-1 text-sm text-slate-300">
                            Consulta rápidamente las plazas según su estado.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <Button
                            v-for="filter in filters"
                            :key="filter.value"
                            type="button"
                            size="sm"
                            variant="outline"
                            :class="filterButtonClass(filter.value)"
                            @click="activeFilter = filter.value"
                        >
                            {{ filter.label }}
                        </Button>
                    </div>
                </div>

                <div
                    class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-5"
                >
                    <button
                        v-for="space in filteredSpaces"
                        :key="space.id"
                        type="button"
                        class="rounded-lg border p-3 text-left transition duration-200"
                        :class="statusClass(space)"
                        :disabled="space.status !== 'available'"
                        @click="selectSpace(space)"
                    >
                        <div class="flex items-start justify-between gap-2">
                            <p class="font-semibold text-white">
                                {{ space.code }}
                            </p>

                            <span
                                class="rounded-full px-2 py-0.5 text-[10px] font-medium"
                                :class="badgeClass(space.status)"
                            >
                                {{ statusLabel(space.status) }}
                            </span>
                        </div>

                        <p class="mt-1 text-xs text-slate-300">
                            Piso {{ space.floor }}
                        </p>

                        <p class="mt-1 text-xs text-slate-200">
                            {{ typeLabel(space.type) }}
                        </p>
                    </button>
                </div>

                <p
                    v-if="!filteredSpaces.length"
                    class="mt-4 rounded-lg border border-amber-500/30 bg-amber-500/10 p-4 text-sm text-amber-100"
                >
                    No hay plazas para este filtro.
                </p>

                <div
                    class="mt-5 grid gap-2 text-sm sm:grid-cols-2 lg:grid-cols-4"
                >
                    <div
                        class="rounded-lg border border-blue-500/40 bg-[#10233f] p-3"
                    >
                        <p class="text-xs text-blue-200">
                            Libres
                        </p>
                        <p class="text-lg font-semibold text-white">
                            {{ countByStatus('available') }}
                        </p>
                    </div>

                    <div
                        class="rounded-lg border border-amber-400/40 bg-[#3a2b0b] p-3"
                    >
                        <p class="text-xs text-amber-200">
                            Reservadas
                        </p>
                        <p class="text-lg font-semibold text-white">
                            {{ countByStatus('reserved') }}
                        </p>
                    </div>

                    <div
                        class="rounded-lg border border-red-400/40 bg-[#421919] p-3"
                    >
                        <p class="text-xs text-red-200">
                            Ocupadas
                        </p>
                        <p class="text-lg font-semibold text-white">
                            {{ countByStatus('occupied') }}
                        </p>
                    </div>

                    <div
                        class="rounded-lg border border-slate-500/50 bg-[#273244] p-3"
                    >
                        <p class="text-xs text-slate-300">
                            Mantenimiento
                        </p>
                        <p class="text-lg font-semibold text-white">
                            {{ countByStatus('maintenance') }}
                        </p>
                    </div>
                </div>
            </section>

            <section
                class="h-fit rounded-xl border border-slate-700 bg-[#111c2e] p-5 shadow-sm"
            >
                <h2 class="text-lg font-semibold text-white">
                    2. Datos de reserva
                </h2>

                <div class="mt-5 grid gap-4">
                    <label
                        class="grid gap-2 text-sm font-medium text-slate-100"
                    >
                        Vehículo

                        <select
                            v-model="vehicleId"
                            class="vehicle-select rounded-md border border-slate-600 bg-[#0d1728] px-3 py-2 text-white outline-none transition focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20"
                        >
                            <option
                                :value="null"
                                class="bg-[#0d1728] text-white"
                            >
                                Sin vehículo
                            </option>

                            <option
                                v-for="vehicle in vehicles"
                                :key="vehicle.id"
                                :value="vehicle.id"
                                class="bg-[#0d1728] text-white"
                            >
                                {{ vehicle.plate }} ·
                                {{ vehicle.brand }}
                                {{ vehicle.model }}
                            </option>
                        </select>
                    </label>

                    <p
                        v-if="!vehicles.length"
                        class="text-sm text-amber-300"
                    >
                        Agrega un vehículo antes de reservar.
                    </p>

                    <label
                        class="grid gap-2 text-sm font-medium text-slate-100"
                    >
                        Hora de inicio

                        <div class="relative">
                            <input
                                ref="dateInput"
                                v-model="checkInAt"
                                type="datetime-local"
                                class="reservation-input w-full rounded-md border border-slate-600 bg-[#0d1728] px-3 py-2 pr-12 text-white outline-none transition focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20"
                            />

                            <button
                                type="button"
                                class="absolute inset-y-0 right-0 flex w-11 items-center justify-center rounded-r-md text-cyan-300 transition hover:bg-cyan-400/10 hover:text-cyan-100"
                                aria-label="Seleccionar fecha y hora"
                                @click="openDatePicker"
                            >
                                <CalendarDays
                                    class="h-5 w-5"
                                    :stroke-width="2"
                                />
                            </button>
                        </div>
                    </label>

                    <label
                        class="grid gap-2 text-sm font-medium text-slate-100"
                    >
                        Duración (horas)

                        <input
                            v-model.number="durationHours"
                            type="number"
                            min="1"
                            max="12"
                            class="reservation-input rounded-md border border-slate-600 bg-[#0d1728] px-3 py-2 text-white outline-none transition focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20"
                        />
                    </label>

                    <div
                        v-if="services.length"
                        class="grid gap-2"
                    >
                        <p class="text-sm font-medium text-slate-100">
                            Servicios adicionales
                        </p>

                        <label
                            v-for="service in services"
                            :key="service.id"
                            class="flex items-start justify-between gap-3 rounded-lg border border-slate-700 bg-[#0d1728] p-3 text-sm"
                        >
                            <span class="flex items-start gap-2">
                                <input
                                    v-model="selectedServiceIds"
                                    type="checkbox"
                                    :value="service.id"
                                    class="mt-1 rounded border-slate-500 bg-slate-900 text-amber-400"
                                />

                                <span>
                                    <strong class="text-white">
                                        {{ service.name }}
                                    </strong>

                                    <span
                                        class="block text-xs text-slate-300"
                                    >
                                        {{ service.description }}
                                        ·
                                        {{ service.duration_min }}
                                        min
                                    </span>
                                </span>
                            </span>

                            <span class="font-semibold text-white">
                                S/
                                {{
                                    Number(
                                        service.price,
                                    ).toFixed(2)
                                }}
                            </span>
                        </label>
                    </div>

                    <div
                        class="rounded-lg border border-slate-700 bg-[#0b1424] p-4"
                    >
                        <p class="text-sm text-slate-300">
                            S/ 8.00 por hora o fracción
                        </p>

                        <p class="mt-1 text-2xl font-semibold text-white">
                            Total: S/
                            {{ total.toFixed(2) }}
                        </p>
                    </div>

                    <InputError
                        :message="
                            errors?.parking_id ||
                            errors?.space_id ||
                            errors?.check_in_at ||
                            errors?.duration_hours
                        "
                    />

                    <Button
                        type="button"
                        class="bg-amber-400 text-slate-950 hover:bg-amber-300 disabled:bg-amber-700/50 disabled:text-slate-300"
                        :disabled="
                            !spaceId ||
                            !checkInAt ||
                            !vehicleId
                        "
                        @click="submit"
                    >
                        Confirmar reserva
                    </Button>
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

.reservation-input::-webkit-inner-spin-button,
.reservation-input::-webkit-outer-spin-button {
    filter: invert(1);
    opacity: 1;
}
</style> 