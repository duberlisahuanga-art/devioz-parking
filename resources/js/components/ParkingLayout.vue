<script setup lang="ts">
import { computed, ref } from 'vue';
import { ChevronDown, ChevronUp } from '@lucide/vue';

interface ReservationUser {
    id: number;
    name: string;
    email: string;
    phone: string | null;
}

interface ReservationVehicle {
    id: number;
    plate: string;
    brand: string | null;
    model: string | null;
    color: string | null;
    type: string | null;
}

interface CurrentReservation {
    id: number;
    status: string;
    payment_status: string;
    amount: string;
    check_in_at: string | null;
    expected_duration_min: number | null;
    expected_end_at: string | null;
    remaining_minutes: number | null;
    user: ReservationUser | null;
    vehicle: ReservationVehicle | null;
}

interface Space {
    id: number;
    code: string;
    floor: number;
    type: string;
    status: 'available' | 'occupied' | 'reserved' | 'maintenance';
    current_reservation_id?: number | null;
    reservation: CurrentReservation | null;
}

const props = defineProps<{
    spaces: Space[];
}>();

const selectedSpace = ref<Space | null>(null);

const visibleSpaces = computed(() =>
    props.spaces.filter((space) =>
        ['available', 'reserved', 'occupied'].includes(space.status),
    ),
);

function spaceNumber(space: Space): number {
    return Number(space.code.replace('P-', ''));
}

const firstRow = computed(() =>
    visibleSpaces.value
        .filter((space) => {
            const number = spaceNumber(space);
            return number >= 1 && number <= 13;
        })
        .sort((a, b) => spaceNumber(a) - spaceNumber(b)),
);

const secondRow = computed(() =>
    visibleSpaces.value
        .filter((space) => {
            const number = spaceNumber(space);
            return number >= 14 && number <= 26;
        })
        .sort((a, b) => spaceNumber(a) - spaceNumber(b)),
);

const thirdRow = computed(() =>
    visibleSpaces.value
        .filter((space) => {
            const number = spaceNumber(space);
            return number >= 27 && number <= 40;
        })
        .sort((a, b) => spaceNumber(a) - spaceNumber(b)),
);

const availableCount = computed(
    () =>
        visibleSpaces.value.filter(
            (space) => space.status === 'available',
        ).length,
);

const reservedCount = computed(
    () =>
        visibleSpaces.value.filter(
            (space) => space.status === 'reserved',
        ).length,
);

const occupiedCount = computed(
    () =>
        visibleSpaces.value.filter(
            (space) => space.status === 'occupied',
        ).length,
);

const labels = {
    available: 'Libre',
    reserved: 'Reservada',
    occupied: 'Ocupada',
} as const;

function statusLabel(space: Space): string {
    if (space.status === 'available') return 'Libre';
    if (space.status === 'reserved') return 'Reservada';
    if (space.status === 'occupied') return 'Ocupada';

    return '';
}

function statusClass(space: Space): string {
    if (space.status === 'available') {
        return [
            'border-slate-600',
            'bg-[#0d1b2a]',
            'text-emerald-300',
            'hover:border-emerald-400',
            'hover:bg-emerald-500/5',
        ].join(' ');
    }

    if (space.status === 'reserved') {
        return [
            'border-amber-500/50',
            'bg-amber-500/10',
            'text-amber-300',
            'hover:border-amber-400',
        ].join(' ');
    }

    if (space.status === 'occupied') {
        return [
            'border-rose-400/50',
            'bg-rose-500/10',
            'text-rose-300',
            'hover:border-rose-400',
        ].join(' ');
    }

    return 'border-slate-700 bg-slate-900 text-slate-400';
}

function openSpace(space: Space): void {
    selectedSpace.value = space;
}

function formatDateTime(value: string | null): string {
    if (!value) return '—';

    return new Date(value).toLocaleString('es-PE', {
        dateStyle: 'short',
        timeStyle: 'short',
    });
}

function formatRemaining(minutes: number | null): string {
    if (minutes === null) return '—';

    if (minutes <= 0) {
        return 'Tiempo cumplido';
    }

    const hours = Math.floor(minutes / 60);
    const mins = minutes % 60;

    if (hours === 0) {
        return `${mins} min`;
    }

    if (mins === 0) {
        return `${hours} h`;
    }

    return `${hours} h ${mins} min`;
}

function paymentLabel(status: string | undefined): string {
    if (status === 'paid') return 'Pagado';
    if (status === 'unpaid') return 'Pendiente';
    if (status === 'pending') return 'En revisión';
    if (status === 'rejected') return 'Rechazado';

    return status ?? '—';
}

function paymentClass(status: string | undefined): string {
    if (status === 'paid') return 'text-emerald-300';
    if (status === 'rejected') return 'text-rose-300';

    return 'text-amber-300';
}
</script>

<template>
    <div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_330px]">
        <!-- CROQUIS -->
        <section
            class="overflow-x-auto rounded-2xl border border-slate-700 bg-[#071525] shadow-xl"
        >
            <div class="min-w-[1180px]">
                <div
                    class="flex items-center justify-between gap-5 px-6 py-5"
                >
                    <div>
                        <h2 class="text-lg font-bold text-white">
                            Estacionamiento Devioz — Planta única
                        </h2>

                        <p class="mt-1 text-xs text-sky-300">
                            Lima, Perú · 40 plazas
                        </p>
                    </div>

                    <div class="flex items-center gap-2 text-xs">
                        <span
                            class="rounded-full border border-slate-600 bg-slate-900/80 px-3 py-1.5 text-slate-200"
                        >
                            Total
                            <strong class="text-white">
                                {{ visibleSpaces.length }}
                            </strong>
                        </span>

                        <span
                            class="rounded-full border border-emerald-800 bg-emerald-950/40 px-3 py-1.5 text-emerald-300"
                        >
                            Libres
                            <strong>{{ availableCount }}</strong>
                        </span>

                        <span
                            class="rounded-full border border-amber-800 bg-amber-950/40 px-3 py-1.5 text-amber-300"
                        >
                            Reservadas
                            <strong>{{ reservedCount }}</strong>
                        </span>

                        <span
                            class="rounded-full border border-rose-800 bg-rose-950/40 px-3 py-1.5 text-rose-300"
                        >
                            Ocupadas
                            <strong>{{ occupiedCount }}</strong>
                        </span>
                    </div>
                </div>

                <!-- AVENIDA -->
                <div class="px-6">
                    <div
                        class="relative overflow-hidden rounded-t-xl border border-slate-600 bg-[#303b4b]"
                    >
                        <div
                            class="flex h-[70px] items-center justify-center"
                        >
                            <div
                                class="absolute left-0 right-0 top-[17px] border-t-4 border-dashed border-slate-300/80"
                            ></div>

                            <div
                                class="absolute bottom-[17px] left-0 right-0 border-t-4 border-dashed border-slate-300/80"
                            ></div>

                            <div
                                class="relative z-10 rounded-md bg-[#303b4b] px-6 py-1"
                            >
                                <span
                                    class="text-xl font-extrabold tracking-wide text-slate-100"
                                >
                                    AV. NICOLÁS AYLLÓN
                                </span>
                            </div>
                        </div>

                        <div class="h-[3px] bg-amber-400"></div>
                    </div>
                </div>

                <!-- ENTRADA / SALIDA -->
                <div class="px-6">
                    <div
                        class="flex items-center justify-center border-x border-b border-slate-700 bg-[#162232] px-5 py-3"
                    >
                        <div
                            class="flex items-center gap-1 font-bold text-lime-300"
                        >
                            ENTRADA / SALIDA

                            <ChevronDown class="size-4" />
                        </div>
                    </div>
                </div>

                <!-- PLANO -->
                <div class="px-6">
                    <div
                        class="border-x border-b border-slate-700 bg-[#0b1826] px-5 pb-4 pt-5"
                    >
                        <!-- FILA 1 -->
                        <div
                            class="mx-auto grid w-[93%] grid-cols-13"
                        >
                            <button
                                v-for="space in firstRow"
                                :key="space.id"
                                type="button"
                                class="relative h-[112px] border transition hover:z-10 hover:-translate-y-0.5 hover:shadow-lg"
                                :class="[
                                    statusClass(space),
                                    selectedSpace?.id === space.id
                                        ? 'ring-2 ring-cyan-400 ring-inset'
                                        : '',
                                ]"
                                @click="openSpace(space)"
                            >
                                <span
                                    class="absolute left-2 top-2 text-[11px] font-bold"
                                >
                                    {{ space.code }}
                                </span>

                                <div
                                    v-if="space.status === 'reserved'"
                                    class="flex h-full items-center justify-center"
                                >
                                    <div
                                        class="flex size-9 items-center justify-center rounded-full border border-amber-500 bg-amber-500/10 text-sm font-bold text-amber-300"
                                    >
                                        R
                                    </div>
                                </div>

                                <div
                                    v-else-if="space.status === 'occupied'"
                                    class="flex h-full items-center justify-center"
                                >
                                    <div class="top-car">
                                        <span
                                            class="top-car-window top-car-window-front"
                                        ></span>

                                        <span
                                            class="top-car-window top-car-window-back"
                                        ></span>
                                    </div>
                                </div>

                                <span
                                    class="absolute bottom-2 left-0 right-0 text-center text-[9px] font-medium uppercase"
                                >
                                    {{ statusLabel(space) }}
                                </span>
                            </button>
                        </div>

                        <!-- CARRIL 1 -->
                        <div
                            class="relative flex h-[84px] items-center"
                        >
                            <div
                                class="w-full border-t-[3px] border-dashed border-lime-400/80"
                            ></div>

                            <div
                                class="absolute inset-0 flex items-center justify-around text-lime-300"
                            >
                                <ChevronDown class="size-5" />
                                <ChevronDown class="size-5" />
                                <ChevronDown class="size-5" />
                            </div>
                        </div>

                        <!-- FILA 2 -->
                        <div
                            class="mx-auto grid w-[93%] grid-cols-13"
                        >
                            <button
                                v-for="space in secondRow"
                                :key="space.id"
                                type="button"
                                class="relative h-[112px] border transition hover:z-10 hover:-translate-y-0.5 hover:shadow-lg"
                                :class="[
                                    statusClass(space),
                                    selectedSpace?.id === space.id
                                        ? 'ring-2 ring-cyan-400 ring-inset'
                                        : '',
                                ]"
                                @click="openSpace(space)"
                            >
                                <span
                                    class="absolute left-2 top-2 text-[11px] font-bold"
                                >
                                    {{ space.code }}
                                </span>

                                <div
                                    v-if="space.status === 'reserved'"
                                    class="flex h-full items-center justify-center"
                                >
                                    <div
                                        class="flex size-9 items-center justify-center rounded-full border border-amber-500 bg-amber-500/10 text-sm font-bold text-amber-300"
                                    >
                                        R
                                    </div>
                                </div>

                                <div
                                    v-else-if="space.status === 'occupied'"
                                    class="flex h-full items-center justify-center"
                                >
                                    <div class="top-car">
                                        <span
                                            class="top-car-window top-car-window-front"
                                        ></span>

                                        <span
                                            class="top-car-window top-car-window-back"
                                        ></span>
                                    </div>
                                </div>

                                <span
                                    class="absolute bottom-2 left-0 right-0 text-center text-[9px] font-medium uppercase"
                                >
                                    {{ statusLabel(space) }}
                                </span>
                            </button>
                        </div>

                        <!-- CARRIL 2 -->
                        <div
                            class="relative flex h-[84px] items-center"
                        >
                            <div
                                class="w-full border-t-[3px] border-dashed border-lime-400/80"
                            ></div>

                            <div
                                class="absolute inset-0 flex items-center justify-around text-lime-300"
                            >
                                <ChevronUp class="size-5" />
                                <ChevronUp class="size-5" />
                                <ChevronUp class="size-5" />
                            </div>
                        </div>

                        <!-- FILA 3 -->
                        <div class="grid grid-cols-14">
                            <button
                                v-for="space in thirdRow"
                                :key="space.id"
                                type="button"
                                class="relative h-[112px] border transition hover:z-10 hover:-translate-y-0.5 hover:shadow-lg"
                                :class="[
                                    statusClass(space),
                                    selectedSpace?.id === space.id
                                        ? 'ring-2 ring-cyan-400 ring-inset'
                                        : '',
                                ]"
                                @click="openSpace(space)"
                            >
                                <span
                                    class="absolute left-2 top-2 text-[11px] font-bold"
                                >
                                    {{ space.code }}
                                </span>

                                <div
                                    v-if="space.status === 'reserved'"
                                    class="flex h-full items-center justify-center"
                                >
                                    <div
                                        class="flex size-9 items-center justify-center rounded-full border border-amber-500 bg-amber-500/10 text-sm font-bold text-amber-300"
                                    >
                                        R
                                    </div>
                                </div>

                                <div
                                    v-else-if="space.status === 'occupied'"
                                    class="flex h-full items-center justify-center"
                                >
                                    <div class="top-car">
                                        <span
                                            class="top-car-window top-car-window-front"
                                        ></span>

                                        <span
                                            class="top-car-window top-car-window-back"
                                        ></span>
                                    </div>
                                </div>

                                <span
                                    class="absolute bottom-2 left-0 right-0 text-center text-[9px] font-medium uppercase"
                                >
                                    {{ statusLabel(space) }}
                                </span>
                            </button>
                        </div>

                        <!-- LEYENDA -->
                        <div
                            class="mt-4 flex items-center gap-5 border-t border-slate-800 pt-4 text-xs text-slate-300"
                        >
                            <span class="flex items-center gap-2">
                                <i
                                    class="size-3 rounded-sm bg-emerald-400"
                                ></i>
                                Libre
                            </span>

                            <span class="flex items-center gap-2">
                                <i
                                    class="size-3 rounded-sm bg-amber-400"
                                ></i>
                                Reservada
                            </span>

                            <span class="flex items-center gap-2">
                                <i
                                    class="size-3 rounded-sm bg-rose-400"
                                ></i>
                                Ocupada
                            </span>
                        </div>
                    </div>
                </div>

                <div class="h-6"></div>
            </div>
        </section>

        <!-- PANEL LATERAL -->
        <aside
            class="self-start rounded-2xl border border-slate-700 bg-[#0b1826] text-white shadow-xl xl:sticky xl:top-20"
        >
            <!-- Sin selección -->
            <div
                v-if="!selectedSpace"
                class="p-6"
            >
                <p
                    class="text-xs font-semibold uppercase tracking-wider text-slate-500"
                >
                    Detalle de plaza
                </p>

                <h3 class="mt-2 text-xl font-bold">
                    Selecciona una plaza
                </h3>

                <p class="mt-3 text-sm leading-6 text-slate-400">
                    Haz clic sobre una plaza del croquis para consultar
                    su estado y la información de la reserva actual.
                </p>
            </div>

            <!-- Con selección -->
            <template v-else>
                <div class="border-b border-slate-700 p-5">
                    <p
                        class="text-xs uppercase tracking-wider text-slate-500"
                    >
                        Detalle de plaza
                    </p>

                    <div
                        class="mt-2 flex items-center justify-between gap-3"
                    >
                        <h3 class="text-3xl font-bold">
                            {{ selectedSpace.code }}
                        </h3>

                        <span
                            class="rounded-full border px-2.5 py-1 text-xs font-semibold"
                            :class="
                                selectedSpace.status === 'available'
                                    ? 'border-emerald-500/50 bg-emerald-500/10 text-emerald-300'
                                    : selectedSpace.status === 'reserved'
                                      ? 'border-amber-500/50 bg-amber-500/10 text-amber-300'
                                      : 'border-rose-500/50 bg-rose-500/10 text-rose-300'
                            "
                        >
                            {{ statusLabel(selectedSpace) }}
                        </span>
                    </div>
                </div>

                <!-- LIBRE -->
                <div
                    v-if="
                        selectedSpace.status === 'available' ||
                        !selectedSpace.reservation
                    "
                    class="p-5"
                >
                    <div
                        class="rounded-xl border border-emerald-800/60 bg-emerald-950/20 p-4"
                    >
                        <p
                            class="font-semibold text-emerald-300"
                        >
                            Plaza disponible
                        </p>

                        <p
                            class="mt-2 text-sm leading-6 text-slate-400"
                        >
                            Esta plaza se encuentra libre y disponible
                            para una nueva reserva.
                        </p>
                    </div>
                </div>

                <!-- RESERVADA / OCUPADA -->
                <div
                    v-else
                    class="space-y-4 p-5"
                >
                    <!-- CONDUCTOR -->
                    <section
                        class="rounded-xl border border-slate-700 bg-slate-900/40 p-4"
                    >
                        <p
                            class="text-xs font-semibold uppercase tracking-wider text-slate-500"
                        >
                            Conductor
                        </p>

                        <p class="mt-2 font-semibold">
                            {{
                                selectedSpace.reservation.user?.name ??
                                '—'
                            }}
                        </p>

                        <p
                            class="mt-1 break-all text-xs text-slate-400"
                        >
                            {{
                                selectedSpace.reservation.user?.email ??
                                '—'
                            }}
                        </p>

                        <p
                            v-if="selectedSpace.reservation.user?.phone"
                            class="mt-1 text-xs text-slate-400"
                        >
                            {{
                                selectedSpace.reservation.user.phone
                            }}
                        </p>
                    </section>

                    <!-- VEHÍCULO -->
                    <section
                        class="rounded-xl border border-slate-700 bg-slate-900/40 p-4"
                    >
                        <p
                            class="text-xs font-semibold uppercase tracking-wider text-slate-500"
                        >
                            Vehículo
                        </p>

                        <p
                            class="mt-2 text-xl font-bold tracking-wide"
                        >
                            {{
                                selectedSpace.reservation.vehicle
                                    ?.plate ?? '—'
                            }}
                        </p>

                        <p class="mt-1 text-sm text-slate-300">
                            {{
                                [
                                    selectedSpace.reservation.vehicle
                                        ?.brand,
                                    selectedSpace.reservation.vehicle
                                        ?.model,
                                ]
                                    .filter(Boolean)
                                    .join(' ') || '—'
                            }}
                        </p>

                        <p
                            v-if="
                                selectedSpace.reservation.vehicle
                                    ?.color
                            "
                            class="mt-1 text-xs text-slate-400"
                        >
                            Color:
                            {{
                                selectedSpace.reservation.vehicle
                                    .color
                            }}
                        </p>
                    </section>

                    <!-- HORARIO -->
                    <section
                        class="rounded-xl border border-slate-700 bg-slate-900/40 p-4"
                    >
                        <p
                            class="text-xs font-semibold uppercase tracking-wider text-slate-500"
                        >
                            Horario
                        </p>

                        <div class="mt-3 space-y-3">
                            <div>
                                <p class="text-xs text-slate-500">
                                    {{
                                        selectedSpace.status ===
                                        'occupied'
                                            ? 'Ingreso'
                                            : 'Reserva'
                                    }}
                                </p>

                                <p class="mt-1 text-sm">
                                    {{
                                        formatDateTime(
                                            selectedSpace.reservation
                                                .check_in_at,
                                        )
                                    }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-slate-500">
                                    Salida estimada
                                </p>

                                <p class="mt-1 text-sm">
                                    {{
                                        formatDateTime(
                                            selectedSpace.reservation
                                                .expected_end_at,
                                        )
                                    }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-slate-500">
                                    Tiempo restante
                                </p>

                                <p
                                    class="mt-1 font-semibold text-cyan-300"
                                >
                                    {{
                                        formatRemaining(
                                            selectedSpace.reservation
                                                .remaining_minutes,
                                        )
                                    }}
                                </p>
                            </div>
                        </div>
                    </section>

                    <!-- PAGO -->
                    <section
                        class="rounded-xl border border-slate-700 bg-slate-900/40 p-4"
                    >
                        <div
                            class="flex items-end justify-between gap-3"
                        >
                            <div>
                                <p
                                    class="text-xs font-semibold uppercase tracking-wider text-slate-500"
                                >
                                    Pago
                                </p>

                                <p
                                    class="mt-2 font-semibold"
                                    :class="
                                        paymentClass(
                                            selectedSpace.reservation
                                                .payment_status,
                                        )
                                    "
                                >
                                    {{
                                        paymentLabel(
                                            selectedSpace.reservation
                                                .payment_status,
                                        )
                                    }}
                                </p>
                            </div>

                            <div class="text-right">
                                <p class="text-xs text-slate-500">
                                    Monto
                                </p>

                                <p class="mt-1 text-lg font-bold">
                                    {{
                                        selectedSpace.reservation
                                            .amount
                                    }}
                                </p>
                            </div>
                        </div>
                    </section>

                    <div
                        class="border-t border-slate-700 pt-4 text-xs text-slate-500"
                    >
                        <div
                            class="flex items-center justify-between gap-3"
                        >
                            <span>
                                Reserva #{{
                                    selectedSpace.reservation.id
                                }}
                            </span>

                            <span>
                                {{
                                    selectedSpace.reservation
                                        .expected_duration_min
                                }}
                                min
                            </span>
                        </div>
                    </div>
                </div>
            </template>
        </aside>
    </div>
</template>

<style scoped>
.top-car {
    position: relative;
    width: 34px;
    height: 58px;
    border: 2px solid #dbe4ee;
    border-radius: 11px;
    background: linear-gradient(
        180deg,
        #f8fafc 0%,
        #cbd5e1 45%,
        #f8fafc 100%
    );
    box-shadow:
        0 4px 10px rgba(0, 0, 0, 0.35),
        inset 0 0 0 2px rgba(255, 255, 255, 0.25);
}

.top-car::before,
.top-car::after {
    content: '';
    position: absolute;
    left: -3px;
    width: 3px;
    height: 14px;
    border-radius: 2px;
    background: #111827;
    box-shadow: 37px 0 0 #111827;
}

.top-car::before {
    top: 9px;
}

.top-car::after {
    bottom: 9px;
}

.top-car-window {
    position: absolute;
    left: 6px;
    right: 6px;
    height: 13px;
    border-radius: 5px;
    background: #263444;
}

.top-car-window-front {
    top: 10px;
}

.top-car-window-back {
    bottom: 10px;
}
</style>