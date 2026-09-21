<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Role {
    id: number;
    name: string;
}

interface User {
    id: number;
    name: string;
    email: string;
    phone: string | null;

    email_verified_at:
        | string
        | null;

    two_factor_confirmed_at:
        | string
        | null;

    terms_accepted: boolean;

    created_at: string;

    vehicles_count: number;
    reservations_count: number;

    roles: Role[];
}

interface Summary {
    total: number;
    admins: number;
    drivers: number;
    verified: number;
    two_factor: number;
}

const props = defineProps<{
    users: User[];
    summary: Summary;

    filters: {
        search?: string | null;
        role?: string | null;
        verified?: string | null;
        two_factor?: string | null;
    };
}>();

const searchFilter = ref(
    props.filters.search ?? '',
);

const roleFilter = ref(
    props.filters.role ?? '',
);

const verifiedFilter = ref(
    props.filters.verified ?? '',
);

const twoFactorFilter = ref(
    props.filters.two_factor ?? '',
);

function applyFilters(): void {
    router.get(
        '/admin/users',
        {
            search:
                searchFilter.value || undefined,

            role:
                roleFilter.value || undefined,

            verified:
                verifiedFilter.value || undefined,

            two_factor:
                twoFactorFilter.value || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
}

function clearFilters(): void {
    searchFilter.value = '';
    roleFilter.value = '';
    verifiedFilter.value = '';
    twoFactorFilter.value = '';

    router.get(
        '/admin/users',
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

function roleLabel(
    user: User,
): string {
    const role =
        user.roles?.[0]?.name;

    if (role === 'admin') {
        return 'Administrador';
    }

    return 'Conductor';
}

function roleClass(
    user: User,
): string {
    const role =
        user.roles?.[0]?.name;

    if (role === 'admin') {
        return 'border-sky-500/40 bg-sky-500/10 text-sky-300';
    }

    return 'border-emerald-500/40 bg-emerald-500/10 text-emerald-300';
}
</script>

<template>
    <Head title="Usuarios" />

    <div class="space-y-6">
        <!-- CABECERA -->
        <section
            class="rounded-2xl border border-slate-700 bg-[#071525] p-6 shadow-xl"
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
                    Usuarios
                </h1>

                <p
                    class="mt-2 text-sm text-slate-400"
                >
                    Consulta los usuarios registrados,
                    sus roles y su actividad dentro
                    del sistema.
                </p>
            </div>
        </section>

        <!-- RESUMEN -->
        <section
            class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5"
        >
            <div
                class="rounded-xl border border-slate-700 bg-[#0b1826] p-4"
            >
                <p
                    class="text-xs uppercase tracking-wide text-slate-500"
                >
                    Total usuarios
                </p>

                <p
                    class="mt-2 text-2xl font-bold text-white"
                >
                    {{ summary.total }}
                </p>
            </div>

            <div
                class="rounded-xl border border-slate-700 bg-[#0b1826] p-4"
            >
                <p
                    class="text-xs uppercase tracking-wide text-slate-500"
                >
                    Administradores
                </p>

                <p
                    class="mt-2 text-2xl font-bold text-sky-300"
                >
                    {{ summary.admins }}
                </p>
            </div>

            <div
                class="rounded-xl border border-slate-700 bg-[#0b1826] p-4"
            >
                <p
                    class="text-xs uppercase tracking-wide text-slate-500"
                >
                    Conductores
                </p>

                <p
                    class="mt-2 text-2xl font-bold text-emerald-300"
                >
                    {{ summary.drivers }}
                </p>
            </div>

            <div
                class="rounded-xl border border-slate-700 bg-[#0b1826] p-4"
            >
                <p
                    class="text-xs uppercase tracking-wide text-slate-500"
                >
                    Correo verificado
                </p>

                <p
                    class="mt-2 text-2xl font-bold text-white"
                >
                    {{ summary.verified }}
                </p>
            </div>

            <div
                class="rounded-xl border border-slate-700 bg-[#0b1826] p-4"
            >
                <p
                    class="text-xs uppercase tracking-wide text-slate-500"
                >
                    2FA activo
                </p>

                <p
                    class="mt-2 text-2xl font-bold text-white"
                >
                    {{ summary.two_factor }}
                </p>
            </div>
        </section>

        <!-- FILTROS -->
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
                        Buscar
                    </label>

                    <input
                        v-model="searchFilter"
                        type="text"
                        placeholder="Nombre o correo"
                        class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-white placeholder:text-slate-500"
                        @keyup.enter="applyFilters"
                    />
                </div>

                <div>
                    <label
                        class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400"
                    >
                        Rol
                    </label>

                    <select
                        v-model="roleFilter"
                        class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-white"
                    >
                        <option value="">
                            Todos
                        </option>

                        <option value="admin">
                            Administrador
                        </option>

                        <option value="driver">
                            Conductor
                        </option>
                    </select>
                </div>

                <div>
                    <label
                        class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400"
                    >
                        Correo
                    </label>

                    <select
                        v-model="verifiedFilter"
                        class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-white"
                    >
                        <option value="">
                            Todos
                        </option>

                        <option value="yes">
                            Verificado
                        </option>

                        <option value="no">
                            Pendiente
                        </option>
                    </select>
                </div>

                <div>
                    <label
                        class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400"
                    >
                        2FA
                    </label>

                    <select
                        v-model="twoFactorFilter"
                        class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-white"
                    >
                        <option value="">
                            Todos
                        </option>

                        <option value="yes">
                            Activo
                        </option>

                        <option value="no">
                            Inactivo
                        </option>
                    </select>
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

        <!-- TABLA -->
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
                                Usuario
                            </th>

                            <th
                                class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-400"
                            >
                                Teléfono
                            </th>

                            <th
                                class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-400"
                            >
                                Rol
                            </th>

                            <th
                                class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-400"
                            >
                                Correo
                            </th>

                            <th
                                class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-400"
                            >
                                2FA
                            </th>

                            <th
                                class="px-5 py-4 text-center text-xs font-semibold uppercase tracking-wide text-slate-400"
                            >
                                Vehículos
                            </th>

                            <th
                                class="px-5 py-4 text-center text-xs font-semibold uppercase tracking-wide text-slate-400"
                            >
                                Reservas
                            </th>

                            <th
                                class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-400"
                            >
                                Registro
                            </th>

                            <th
                                class="px-5 py-4 text-center text-xs font-semibold uppercase tracking-wide text-slate-400"
                            >
                                Acciones
                            </th>
                        </tr>
                    </thead>

                    <tbody
                        class="divide-y divide-slate-800"
                    >
                        <tr
                            v-for="user in users"
                            :key="user.id"
                            class="transition hover:bg-slate-900/40"
                        >
                            <td class="px-5 py-4">
                                <p
                                    class="font-semibold text-white"
                                >
                                    {{ user.name }}
                                </p>

                                <p
                                    class="mt-1 text-xs text-slate-500"
                                >
                                    {{ user.email }}
                                </p>
                            </td>

                            <td
                                class="px-5 py-4 text-sm text-slate-300"
                            >
                                {{
                                    user.phone
                                        ?? 'No registrado'
                                }}
                            </td>

                            <td class="px-5 py-4">
                                <span
                                    class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold"
                                    :class="
                                        roleClass(
                                            user,
                                        )
                                    "
                                >
                                    {{
                                        roleLabel(
                                            user,
                                        )
                                    }}
                                </span>
                            </td>

                            <td class="px-5 py-4">
                                <span
                                    v-if="
                                        user.email_verified_at
                                    "
                                    class="text-sm font-medium text-emerald-300"
                                >
                                    Verificado
                                </span>

                                <span
                                    v-else
                                    class="text-sm text-amber-300"
                                >
                                    Pendiente
                                </span>
                            </td>

                            <td class="px-5 py-4">
                                <span
                                    v-if="
                                        user.two_factor_confirmed_at
                                    "
                                    class="text-sm font-medium text-emerald-300"
                                >
                                    Activo
                                </span>

                                <span
                                    v-else
                                    class="text-sm text-slate-500"
                                >
                                    Inactivo
                                </span>
                            </td>

                            <td
                                class="px-5 py-4 text-center font-semibold text-white"
                            >
                                {{
                                    user.vehicles_count
                                }}
                            </td>

                            <td
                                class="px-5 py-4 text-center font-semibold text-white"
                            >
                                {{
                                    user.reservations_count
                                }}
                            </td>

                            <td
                                class="whitespace-nowrap px-5 py-4 text-sm text-slate-400"
                            >
                                {{
                                    formatDate(
                                        user.created_at,
                                    )
                                }}
                            </td>

                            <!-- ACCIONES -->
                            <td
                                class="whitespace-nowrap px-5 py-4 text-center"
                            >
                                <Link
                                    :href="`/admin/users/${user.id}`"
                                    class="inline-flex items-center justify-center rounded-lg border border-sky-500/40 bg-sky-500/10 px-3 py-2 text-xs font-semibold text-sky-300 transition hover:bg-sky-500/20 hover:text-sky-200"
                                >
                                    Ver detalle
                                </Link>
                            </td>
                        </tr>

                        <tr
                            v-if="
                                users.length
                                === 0
                            "
                        >
                            <td
                                colspan="9"
                                class="px-5 py-12 text-center text-sm text-slate-500"
                            >
                                No se encontraron usuarios con los filtros seleccionados.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</template>