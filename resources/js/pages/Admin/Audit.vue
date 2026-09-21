<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

interface User {
    id: number;
    name: string;
    email: string;
}

interface AuditLog {
    id: number;
    module: string;
    action: string;
    reference: string | null;
    description: string;
    created_at: string;
    user: User | null;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedLogs {
    data: AuditLog[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: PaginationLink[];
}

const props = defineProps<{
    logs: PaginatedLogs;

    filters: {
        module?: string | null;
        action?: string | null;
        user_id?: string | number | null;
        date?: string | null;
    };

    modules: string[];
    actions: string[];
    users: User[];
}>();

const moduleFilter = ref(
    props.filters.module ?? '',
);

const actionFilter = ref(
    props.filters.action ?? '',
);

const userFilter = ref(
    props.filters.user_id
        ? String(props.filters.user_id)
        : '',
);

const dateFilter = ref(
    props.filters.date ?? '',
);

function applyFilters(): void {
    router.get(
        '/admin/audit',
        {
            module:
                moduleFilter.value || undefined,

            action:
                actionFilter.value || undefined,

            user_id:
                userFilter.value || undefined,

            date:
                dateFilter.value || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
}

function clearFilters(): void {
    moduleFilter.value = '';
    actionFilter.value = '';
    userFilter.value = '';
    dateFilter.value = '';

    router.get(
        '/admin/audit',
        {},
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
}

function formatDate(
    value: string,
): string {
    return new Date(
        value,
    ).toLocaleString(
        'es-PE',
        {
            dateStyle: 'short',
            timeStyle: 'short',
        },
    );
}

/*
|--------------------------------------------------------------------------
| Mostrar historial antiguo en español
|--------------------------------------------------------------------------
*/

function displayAction(
    action: string,
): string {
    if (action === 'Check-in') {
        return 'Ingreso';
    }

    if (action === 'Check-out') {
        return 'Salida';
    }

    return action;
}

function displayDescription(
    description: string,
): string {
    return description
        .replace(
            /Check-in registrado/gi,
            'Ingreso registrado',
        )
        .replace(
            /Check-out registrado/gi,
            'Salida registrada',
        );
}

function actionClass(
    action: string,
): string {
    if (
        [
            'Creada',
            'Ingreso',
            'Check-in',
            'Aprobado',
        ].includes(action)
    ) {
        return 'border-emerald-500/40 bg-emerald-500/10 text-emerald-300';
    }

    if (
        [
            'Extendida',
            'Enviado',
        ].includes(action)
    ) {
        return 'border-amber-500/40 bg-amber-500/10 text-amber-300';
    }

    if (
        [
            'Cancelada',
            'Rechazado',
        ].includes(action)
    ) {
        return 'border-rose-500/40 bg-rose-500/10 text-rose-300';
    }

    if (
        action === 'Salida'
        || action === 'Check-out'
    ) {
        return 'border-sky-500/40 bg-sky-500/10 text-sky-300';
    }

    return 'border-slate-600 bg-slate-800 text-slate-300';
}
</script>

<template>
    <Head title="Auditoría" />

    <div class="space-y-6">
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
                        Administración
                    </p>

                    <h1
                        class="mt-1 text-2xl font-bold text-white"
                    >
                        Auditoría del sistema
                    </h1>

                    <p
                        class="mt-2 text-sm text-slate-400"
                    >
                        Consulta las acciones realizadas
                        en reservas y pagos.
                    </p>
                </div>

                <div
                    class="rounded-xl border border-slate-700 bg-slate-900/50 px-4 py-3"
                >
                    <p class="text-xs text-slate-500">
                        Registros
                    </p>

                    <p
                        class="mt-1 text-2xl font-bold text-white"
                    >
                        {{ logs.total }}
                    </p>
                </div>
            </div>
        </section>

        <section
            class="rounded-2xl border border-slate-700 bg-[#0b1826] p-5 shadow-xl"
        >
            <div
                class="grid gap-4 md:grid-cols-2 xl:grid-cols-4"
            >
                <div>
                    <label
                        class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400"
                    >
                        Módulo
                    </label>

                    <select
                        v-model="moduleFilter"
                        class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-white"
                    >
                        <option value="">
                            Todos
                        </option>

                        <option
                            v-for="module in modules"
                            :key="module"
                            :value="module"
                        >
                            {{ module }}
                        </option>
                    </select>
                </div>

                <div>
                    <label
                        class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400"
                    >
                        Acción
                    </label>

                    <select
                        v-model="actionFilter"
                        class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-white"
                    >
                        <option value="">
                            Todas
                        </option>

                        <option
                            v-for="action in actions"
                            :key="action"
                            :value="action"
                        >
                            {{ action }}
                        </option>
                    </select>
                </div>

                <div>
                    <label
                        class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400"
                    >
                        Usuario
                    </label>

                    <select
                        v-model="userFilter"
                        class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-white"
                    >
                        <option value="">
                            Todos
                        </option>

                        <option
                            v-for="user in users"
                            :key="user.id"
                            :value="String(user.id)"
                        >
                            {{ user.name }} · {{ user.email }}
                        </option>
                    </select>
                </div>

                <div>
                    <label
                        class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400"
                    >
                        Fecha
                    </label>

                    <input
                        v-model="dateFilter"
                        type="date"
                        class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-white"
                    />
                </div>
            </div>

            <div
                class="mt-4 flex flex-wrap gap-2"
            >
                <button
                    type="button"
                    class="rounded-lg bg-sky-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-sky-500"
                    @click="applyFilters"
                >
                    Aplicar filtros
                </button>

                <button
                    type="button"
                    class="rounded-lg border border-slate-600 bg-slate-900 px-4 py-2 text-sm font-semibold text-slate-300 transition hover:bg-slate-800"
                    @click="clearFilters"
                >
                    Limpiar
                </button>
            </div>
        </section>

        <section
            class="overflow-hidden rounded-2xl border border-slate-700 bg-[#0b1826] shadow-xl"
        >
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead
                        class="border-b border-slate-700 bg-slate-900/60"
                    >
                        <tr>
                            <th
                                class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-400"
                            >
                                Fecha / Hora
                            </th>

                            <th
                                class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-400"
                            >
                                Usuario
                            </th>

                            <th
                                class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-400"
                            >
                                Módulo
                            </th>

                            <th
                                class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-400"
                            >
                                Acción
                            </th>

                            <th
                                class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-400"
                            >
                                Referencia
                            </th>

                            <th
                                class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-400"
                            >
                                Descripción
                            </th>
                        </tr>
                    </thead>

                    <tbody
                        class="divide-y divide-slate-800"
                    >
                        <tr
                            v-for="log in logs.data"
                            :key="log.id"
                            class="transition hover:bg-slate-900/40"
                        >
                            <td
                                class="whitespace-nowrap px-5 py-4 text-sm text-slate-300"
                            >
                                {{ formatDate(log.created_at) }}
                            </td>

                            <td class="px-5 py-4">
                                <template
                                    v-if="log.user"
                                >
                                    <p
                                        class="text-sm font-semibold text-white"
                                    >
                                        {{ log.user.name }}
                                    </p>

                                    <p
                                        class="mt-1 text-xs text-slate-500"
                                    >
                                        {{ log.user.email }}
                                    </p>
                                </template>

                                <span
                                    v-else
                                    class="text-sm text-slate-500"
                                >
                                    Sistema
                                </span>
                            </td>

                            <td
                                class="px-5 py-4 text-sm font-medium text-slate-300"
                            >
                                {{ log.module }}
                            </td>

                            <td class="px-5 py-4">
                                <span
                                    class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold"
                                    :class="
                                        actionClass(
                                            log.action,
                                        )
                                    "
                                >
                                    {{
                                        displayAction(
                                            log.action,
                                        )
                                    }}
                                </span>
                            </td>

                            <td
                                class="whitespace-nowrap px-5 py-4 font-mono text-xs text-cyan-300"
                            >
                                {{ log.reference ?? '—' }}
                            </td>

                            <td
                                class="min-w-[280px] px-5 py-4 text-sm leading-6 text-slate-300"
                            >
                                {{
                                    displayDescription(
                                        log.description,
                                    )
                                }}
                            </td>
                        </tr>

                        <tr
                            v-if="
                                logs.data.length === 0
                            "
                        >
                            <td
                                colspan="6"
                                class="px-5 py-12 text-center text-sm text-slate-500"
                            >
                                No existen registros de auditoría con los filtros seleccionados.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div
                v-if="logs.last_page > 1"
                class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-700 px-5 py-4"
            >
                <p class="text-xs text-slate-500">
                    Página
                    {{ logs.current_page }}
                    de
                    {{ logs.last_page }}
                </p>

                <div class="flex flex-wrap gap-1">
                    <template
                        v-for="link in logs.links"
                        :key="link.label"
                    >
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            preserve-scroll
                            class="rounded-md border px-3 py-1.5 text-xs transition"
                            :class="
                                link.active
                                    ? 'border-sky-500 bg-sky-500/10 text-sky-300'
                                    : 'border-slate-700 bg-slate-900 text-slate-400 hover:bg-slate-800'
                            "
                        >
                            <span
                                v-html="link.label"
                            ></span>
                        </Link>

                        <span
                            v-else
                            class="cursor-not-allowed rounded-md border border-slate-800 px-3 py-1.5 text-xs text-slate-600"
                        >
                            <span
                                v-html="link.label"
                            ></span>
                        </span>
                    </template>
                </div>
            </div>
        </section>
    </div>
</template>