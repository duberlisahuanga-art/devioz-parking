<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

import { dashboard } from '@/routes';
import { store as loginStore } from '@/routes/login';
import { store as registerStore } from '@/routes/register';

type AuthMode = 'login' | 'register';

const authOpen = ref(false);
const authMode = ref<AuthMode>('login');

function openAuth(mode: AuthMode): void {
    authMode.value = mode;
    authOpen.value = true;
}

function closeAuth(): void {
    authOpen.value = false;
}

function changeAuthMode(mode: AuthMode): void {
    authMode.value = mode;
}
</script>

<template>
    <Head title="DEVIOZ PARKING">
        <link
            rel="preconnect"
            href="https://fonts.googleapis.com"
        />

        <link
            rel="preconnect"
            href="https://fonts.gstatic.com"
            crossorigin="anonymous"
        />

        <link
            href="https://fonts.googleapis.com/css2?family=Archivo+Black&family=JetBrains+Mono:wght@400;600;700&family=Space+Grotesk:wght@400;500;600;700&display=swap"
            rel="stylesheet"
        />
    </Head>

    <div
        class="relative isolate min-h-screen overflow-x-hidden bg-[#071225] font-['Space_Grotesk'] text-[#eaf1fb] selection:bg-[#ffb020] selection:text-[#171003]"
    >
        <!-- Fondo cuadriculado -->
        <div
            class="pointer-events-none fixed inset-0 z-0 opacity-70 [background-image:linear-gradient(rgba(91,140,255,.06)_1px,transparent_1px),linear-gradient(90deg,rgba(91,140,255,.06)_1px,transparent_1px)] [background-size:44px_44px] [mask-image:radial-gradient(ellipse_at_50%_0%,#000_30%,transparent_80%)]"
        />

        <!-- Iluminación -->
        <div
            class="pointer-events-none fixed inset-0 z-0 bg-[radial-gradient(600px_300px_at_12%_-5%,rgba(255,176,32,.16),transparent_60%),radial-gradient(700px_340px_at_90%_0%,rgba(61,220,255,.12),transparent_60%)]"
        />

        <!-- =====================================================
             HEADER
             ===================================================== -->
        <nav
            class="relative z-20 mx-auto flex max-w-7xl items-center justify-between px-5 py-5 sm:px-8 lg:px-10"
        >
            <Link
                href="/"
                class="group flex items-center gap-3"
            >
                <span
                    class="grid size-11 rotate-[-4deg] place-items-center rounded-xl bg-[#ffb020] font-['Archivo_Black'] text-2xl text-[#171003] shadow-[0_8px_20px_rgba(255,176,32,.35)] transition group-hover:rotate-0"
                >
                    P
                </span>

                <span
                    class="font-['Archivo_Black'] text-base tracking-[.16em] sm:text-lg"
                >
                    DEVIOZ PARKING

                    <small
                        class="block font-['JetBrains_Mono'] text-[9px] tracking-[.35em] text-[#8ea2c0]"
                    >
                        GESTIÓN INTELIGENTE
                    </small>
                </span>
            </Link>

            <div class="flex items-center gap-2 sm:gap-3">
                <template v-if="$page.props.auth.user">
                    <Link
                        :href="dashboard()"
                        class="rounded-lg border border-[#2a3c63] px-4 py-2 text-sm font-semibold transition hover:border-[#ffb020] hover:text-[#ffb020]"
                    >
                        Dashboard
                    </Link>
                </template>

                <template v-else>
                    <button
                        type="button"
                        class="hidden rounded-lg px-3 py-2 text-sm font-semibold text-[#8ea2c0] transition hover:text-white sm:block"
                        @click="openAuth('login')"
                    >
                        Iniciar sesión
                    </button>

                    <button
                        type="button"
                        class="rounded-lg bg-[#ffb020] px-4 py-2 text-sm font-bold text-[#171003] shadow-[0_6px_18px_rgba(255,176,32,.3)] transition hover:-translate-y-0.5 hover:bg-[#ffcf6e]"
                        @click="openAuth('register')"
                    >
                        Crear cuenta
                    </button>
                </template>
            </div>
        </nav>

        <!-- =====================================================
             CONTENIDO PRINCIPAL
             ===================================================== -->
        <main
            class="relative z-10 mx-auto max-w-7xl px-5 pb-16 sm:px-8 lg:px-10"
        >
            <!-- HERO -->
            <section
                class="grid min-h-[590px] items-center gap-12 py-16 lg:grid-cols-[1.1fr_.9fr] lg:py-24"
            >
                <div
                    class="animate-[slideUp_.55s_ease_both]"
                >
                    <div
                        class="mb-6 inline-flex items-center gap-2 rounded-full border border-[#2a3c63] bg-[#101a30]/70 px-3 py-1.5 font-['JetBrains_Mono'] text-[10px] font-bold tracking-[.18em] text-[#3ddcff]"
                    >
                        <span
                            class="size-2 animate-pulse rounded-full bg-[#2ee59d]"
                        />

                        RED ACTIVA EN TIEMPO REAL
                    </div>

                    <h1
                        class="max-w-3xl font-['Archivo_Black'] text-5xl uppercase leading-[.98] tracking-wide sm:text-7xl"
                    >
                        Tu lugar,
                        <br />

                        en el

                        <span class="text-[#ffb020]">
                            momento exacto.
                        </span>
                    </h1>

                    <p
                        class="mt-7 max-w-xl text-base leading-7 text-[#8ea2c0] sm:text-lg"
                    >
                        Conectamos conductores con parkings
                        disponibles en tiempo real. Reserva tu
                        lugar al mejor precio y en la ubicación
                        que necesitas.
                    </p>

                    <div
                        class="mt-9 flex flex-wrap gap-3"
                    >
                        <button
                            type="button"
                            class="rounded-lg bg-[#ffb020] px-6 py-3.5 font-bold text-[#171003] shadow-[0_8px_24px_rgba(255,176,32,.3)] transition hover:-translate-y-1 hover:bg-[#ffcf6e]"
                            @click="openAuth('register')"
                        >
                            Encontrar mi lugar
                            <span aria-hidden="true">
                                →
                            </span>
                        </button>

                        <button
                            type="button"
                            class="rounded-lg border border-[#2a3c63] px-6 py-3.5 font-bold transition hover:-translate-y-1 hover:border-[#3ddcff] hover:text-[#3ddcff]"
                            @click="openAuth('login')"
                        >
                            Ya tengo cuenta
                        </button>
                    </div>
                </div>

                <!-- DISPONIBILIDAD -->
                <div
                    class="relative hidden min-h-[360px] items-center justify-center lg:flex"
                >
                    <div
                        class="absolute size-72 rounded-full border border-[#2a3c63] opacity-70"
                    />

                    <div
                        class="absolute size-56 rounded-full border border-dashed border-[#3ddcff]/40"
                    />

                    <div
                        class="relative w-full max-w-md rounded-2xl border border-[#2a3c63] bg-[#101a30]/90 p-5 shadow-2xl backdrop-blur-sm"
                    >
                        <div
                            class="mb-5 flex items-center justify-between"
                        >
                            <span class="font-bold">
                                Disponibilidad cercana
                            </span>

                            <span
                                class="rounded-full bg-[#2ee59d]/15 px-2 py-1 font-['JetBrains_Mono'] text-[10px] font-bold text-[#2ee59d]"
                            >
                                ● EN VIVO
                            </span>
                        </div>

                        <div
                            class="relative h-56 overflow-hidden rounded-xl border border-[#1e2c4a] bg-[#0c1424] [background-image:linear-gradient(rgba(42,60,99,.35)_1px,transparent_1px),linear-gradient(90deg,rgba(42,60,99,.35)_1px,transparent_1px)] [background-size:42px_42px]"
                        >
                            <div
                                class="absolute left-[18%] top-[28%] size-5 rounded-full bg-[#2ee59d] shadow-[0_0_0_9px_rgba(46,229,157,.15),0_0_22px_rgba(46,229,157,.65)]"
                            />

                            <div
                                class="absolute left-[52%] top-[52%] size-5 rounded-full bg-[#ffb020] shadow-[0_0_0_9px_rgba(255,176,32,.15),0_0_22px_rgba(255,176,32,.65)]"
                            />

                            <div
                                class="absolute right-[18%] top-[22%] size-5 rounded-full bg-[#3ddcff] shadow-[0_0_0_9px_rgba(61,220,255,.15),0_0_22px_rgba(61,220,255,.65)]"
                            />

                            <span
                                class="absolute left-[14%] top-[40%] font-['JetBrains_Mono'] text-xs text-[#2ee59d]"
                            >
                                24 libres
                            </span>

                            <span
                                class="absolute left-[47%] top-[65%] font-['JetBrains_Mono'] text-xs text-[#ffb020]"
                            >
                                8 libres
                            </span>

                            <span
                                class="absolute right-[10%] top-[34%] font-['JetBrains_Mono'] text-xs text-[#3ddcff]"
                            >
                                31 libres
                            </span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- =====================================================
                 ESTADÍSTICAS
                 ===================================================== -->
            <section
                class="grid grid-cols-1 gap-4 border-y border-[#1e2c4a] py-8 sm:grid-cols-2 lg:grid-cols-4"
            >
                <div
                    v-for="stat in [
                        {
                            icon: '▣',
                            value: '610',
                            label: 'Espacios activos',
                            tone: 'text-[#3ddcff]',
                        },
                        {
                            icon: '⌂',
                            value: '5',
                            label: 'Parkings aliados',
                            tone: 'text-[#ffb020]',
                        },
                        {
                            icon: '◉',
                            value: '99.7%',
                            label: 'Disponibilidad',
                            tone: 'text-[#2ee59d]',
                        },
                        {
                            icon: '◷',
                            value: '24/7',
                            label: 'Monitoreo',
                            tone: 'text-[#5b8cff]',
                        },
                    ]"
                    :key="stat.label"
                    class="rounded-xl border border-[#1e2c4a] bg-[#101a30]/70 p-5 transition duration-300 hover:-translate-y-1 hover:border-[#2a3c63] hover:shadow-xl"
                >
                    <div
                        class="flex items-center gap-4"
                    >
                        <span
                            :class="[
                                'text-3xl',
                                stat.tone,
                            ]"
                        >
                            {{ stat.icon }}
                        </span>

                        <div>
                            <strong
                                class="font-['JetBrains_Mono'] text-2xl"
                            >
                                {{ stat.value }}
                            </strong>

                            <span
                                class="mt-1 block text-xs uppercase tracking-[.16em] text-[#8ea2c0]"
                            >
                                {{ stat.label }}
                            </span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- =====================================================
                 CARACTERÍSTICAS
                 ===================================================== -->
            <section
                class="grid gap-4 py-16 md:grid-cols-3"
            >
                <div
                    v-for="feature in [
                        {
                            icon: '⌕',
                            title:
                                'Búsqueda inteligente',
                            text:
                                'Encuentra espacios disponibles cerca de ti en tiempo real.',
                        },
                        {
                            icon: 'ϟ',
                            title:
                                'Reservas instantáneas',
                            text:
                                'Agenda con anticipación y recibe confirmación inmediata.',
                        },
                        {
                            icon: '⌁',
                            title: 'Pago seguro',
                            text:
                                'Pagos digitales con cada transacción protegida.',
                        },
                    ]"
                    :key="feature.title"
                    class="rounded-xl border border-[#1e2c4a] bg-[#0d1526] p-6 transition hover:border-[#ffb020]"
                >
                    <span
                        class="text-3xl text-[#ffb020]"
                    >
                        {{ feature.icon }}
                    </span>

                    <h2
                        class="mt-4 font-['Archivo_Black'] text-sm uppercase tracking-wide"
                    >
                        {{ feature.title }}
                    </h2>

                    <p
                        class="mt-2 text-sm leading-6 text-[#8ea2c0]"
                    >
                        {{ feature.text }}
                    </p>
                </div>
            </section>
        </main>

        <!-- =====================================================
             MODULO ÚNICO DE AUTENTICACIÓN
             ===================================================== -->
        <div
            v-if="authOpen"
            class="fixed inset-0 z-50 grid place-items-center bg-[#050a14]/85 p-4 backdrop-blur-md"
            role="dialog"
            aria-modal="true"
            @click.self="closeAuth"
        >
            <div
                class="max-h-[94vh] w-full max-w-lg overflow-y-auto rounded-2xl border border-[#2a3c63] bg-[#101a30] shadow-[0_25px_80px_rgba(0,0,0,.5)]"
            >
                <!-- CABECERA -->
                <div
                    class="flex items-start justify-between border-b border-[#1e2c4a] px-6 py-5 sm:px-8"
                >
                    <div>
                        <p
                            class="font-['JetBrains_Mono'] text-[10px] font-bold tracking-[.22em] text-[#3ddcff]"
                        >
                            ACCESO DEVIOZ
                        </p>

                        <h2
                            class="mt-2 font-['Archivo_Black'] text-xl uppercase"
                        >
                            {{
                                authMode === 'login'
                                    ? 'Bienvenido'
                                    : 'Nueva cuenta'
                            }}
                        </h2>

                        <p
                            class="mt-1 text-xs text-[#8ea2c0]"
                        >
                            {{
                                authMode === 'login'
                                    ? 'Accede a tu cuenta para continuar'
                                    : 'Regístrate para comenzar a reservar'
                            }}
                        </p>
                    </div>

                    <button
                        type="button"
                        class="grid size-9 shrink-0 place-items-center rounded-lg text-xl text-[#8ea2c0] transition hover:bg-[#162443] hover:text-[#ff5470]"
                        aria-label="Cerrar"
                        @click="closeAuth"
                    >
                        ×
                    </button>
                </div>

                <!-- PESTAÑAS -->
                <div class="px-6 pt-6 sm:px-8">
                    <div
                        class="grid grid-cols-2 rounded-xl border border-[#2a3c63] bg-[#0c1424] p-1"
                    >
                        <button
                            type="button"
                            class="rounded-lg px-4 py-3 text-sm font-bold transition"
                            :class="
                                authMode === 'login'
                                    ? 'bg-[#ffb020] text-[#171003] shadow-[0_5px_14px_rgba(255,176,32,.18)]'
                                    : 'text-[#8ea2c0] hover:bg-[#162443] hover:text-white'
                            "
                            @click="
                                changeAuthMode(
                                    'login',
                                )
                            "
                        >
                            Iniciar sesión
                        </button>

                        <button
                            type="button"
                            class="rounded-lg px-4 py-3 text-sm font-bold transition"
                            :class="
                                authMode ===
                                'register'
                                    ? 'bg-[#ffb020] text-[#171003] shadow-[0_5px_14px_rgba(255,176,32,.18)]'
                                    : 'text-[#8ea2c0] hover:bg-[#162443] hover:text-white'
                            "
                            @click="
                                changeAuthMode(
                                    'register',
                                )
                            "
                        >
                            Registrarse
                        </button>
                    </div>
                </div>

                <div class="p-6 sm:p-8">
                    <!-- =================================================
                         LOGIN
                         ================================================= -->
                    <Form
                        v-if="authMode === 'login'"
                        v-bind="loginStore.form()"
                        v-slot="{
                            errors,
                            processing,
                        }"
                        class="space-y-5"
                    >
                        <!-- EMAIL -->
                        <label
                            class="block text-xs font-bold uppercase tracking-wider text-[#8ea2c0]"
                        >
                            Correo electrónico

                            <input
                                name="email"
                                type="email"
                                required
                                autofocus
                                autocomplete="email"
                                placeholder="usuario@correo.com"
                                class="mt-2 w-full rounded-lg border border-[#2a3c63] bg-[#0c1424] px-4 py-3 text-sm text-white outline-none transition placeholder:text-[#536783] focus:border-[#3ddcff] focus:ring-2 focus:ring-[#3ddcff]/10"
                            />

                            <span
                                v-if="errors.email"
                                class="mt-1.5 block text-xs text-[#ff5470]"
                            >
                                {{ errors.email }}
                            </span>
                        </label>

                        <!-- PASSWORD -->
                        <label
                            class="block text-xs font-bold uppercase tracking-wider text-[#8ea2c0]"
                        >
                            Contraseña

                            <input
                                name="password"
                                type="password"
                                required
                                autocomplete="current-password"
                                placeholder="••••••••"
                                class="mt-2 w-full rounded-lg border border-[#2a3c63] bg-[#0c1424] px-4 py-3 text-sm text-white outline-none transition placeholder:text-[#536783] focus:border-[#3ddcff] focus:ring-2 focus:ring-[#3ddcff]/10"
                            />

                            <span
                                v-if="errors.password"
                                class="mt-1.5 block text-xs text-[#ff5470]"
                            >
                                {{ errors.password }}
                            </span>
                        </label>

                        <div
                            class="flex flex-wrap items-center justify-between gap-3"
                        >
                            <label
                                class="flex cursor-pointer items-center gap-2 text-xs text-[#8ea2c0]"
                            >
                                <input
                                    name="remember"
                                    type="checkbox"
                                    value="1"
                                    class="rounded border-[#2a3c63] bg-[#0c1424]"
                                />

                                Recordarme
                            </label>

                            <Link
                                href="/forgot-password"
                                class="text-xs font-medium text-[#3ddcff] transition hover:text-white"
                            >
                                ¿Olvidaste tu
                                contraseña?
                            </Link>
                        </div>

                        <button
                            type="submit"
                            :disabled="processing"
                            class="w-full rounded-lg bg-[#ffb020] px-4 py-3.5 font-bold text-[#171003] shadow-[0_8px_22px_rgba(255,176,32,.22)] transition hover:-translate-y-0.5 hover:bg-[#ffcf6e] disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            {{
                                processing
                                    ? 'Ingresando...'
                                    : 'Iniciar sesión'
                            }}
                        </button>

                        <div
                            class="border-t border-[#1e2c4a] pt-5"
                        >
                            <p
                                class="text-center text-sm text-[#8ea2c0]"
                            >
                                ¿Aún no tienes una
                                cuenta?

                                <button
                                    type="button"
                                    class="font-semibold text-[#ffb020] transition hover:text-[#ffcf6e]"
                                    @click="
                                        changeAuthMode(
                                            'register',
                                        )
                                    "
                                >
                                    Regístrate
                                </button>
                            </p>
                        </div>

                        <p
                            class="text-center text-[11px] leading-5 text-[#6f82a1]"
                        >
                            Acceso protegido mediante
                            autenticación segura y
                            verificación en dos pasos.
                        </p>
                    </Form>

                    <!-- =================================================
                         REGISTRO
                         ================================================= -->
                    <Form
                        v-else
                        v-bind="registerStore.form()"
                        v-slot="{
                            errors,
                            processing,
                        }"
                        class="space-y-4"
                    >
                        <!-- NOMBRE -->
                        <label
                            class="block text-xs font-bold uppercase tracking-wider text-[#8ea2c0]"
                        >
                            Nombre completo

                            <input
                                name="name"
                                type="text"
                                required
                                autocomplete="name"
                                placeholder="Nombre y apellidos"
                                class="mt-2 w-full rounded-lg border border-[#2a3c63] bg-[#0c1424] px-4 py-3 text-sm text-white outline-none transition placeholder:text-[#536783] focus:border-[#3ddcff] focus:ring-2 focus:ring-[#3ddcff]/10"
                            />

                            <span
                                v-if="errors.name"
                                class="mt-1.5 block text-xs text-[#ff5470]"
                            >
                                {{ errors.name }}
                            </span>
                        </label>

                        <!-- EMAIL -->
                        <label
                            class="block text-xs font-bold uppercase tracking-wider text-[#8ea2c0]"
                        >
                            Correo electrónico

                            <input
                                name="email"
                                type="email"
                                required
                                autocomplete="email"
                                placeholder="usuario@correo.com"
                                class="mt-2 w-full rounded-lg border border-[#2a3c63] bg-[#0c1424] px-4 py-3 text-sm text-white outline-none transition placeholder:text-[#536783] focus:border-[#3ddcff] focus:ring-2 focus:ring-[#3ddcff]/10"
                            />

                            <span
                                v-if="errors.email"
                                class="mt-1.5 block text-xs text-[#ff5470]"
                            >
                                {{ errors.email }}
                            </span>
                        </label>

                        <!-- TELEFONO -->
                        <label
                            class="block text-xs font-bold uppercase tracking-wider text-[#8ea2c0]"
                        >
                            Teléfono

                            <input
                                name="phone"
                                type="tel"
                                required
                                autocomplete="tel"
                                placeholder="+51 999 999 999"
                                class="mt-2 w-full rounded-lg border border-[#2a3c63] bg-[#0c1424] px-4 py-3 text-sm text-white outline-none transition placeholder:text-[#536783] focus:border-[#3ddcff] focus:ring-2 focus:ring-[#3ddcff]/10"
                            />

                            <span
                                v-if="errors.phone"
                                class="mt-1.5 block text-xs text-[#ff5470]"
                            >
                                {{ errors.phone }}
                            </span>
                        </label>

                        <!-- PASSWORDS -->
                        <div
                            class="grid gap-4 sm:grid-cols-2"
                        >
                            <label
                                class="block text-xs font-bold uppercase tracking-wider text-[#8ea2c0]"
                            >
                                Contraseña

                                <input
                                    name="password"
                                    type="password"
                                    required
                                    autocomplete="new-password"
                                    placeholder="••••••••"
                                    class="mt-2 w-full rounded-lg border border-[#2a3c63] bg-[#0c1424] px-4 py-3 text-sm text-white outline-none transition placeholder:text-[#536783] focus:border-[#3ddcff] focus:ring-2 focus:ring-[#3ddcff]/10"
                                />

                                <span
                                    v-if="
                                        errors.password
                                    "
                                    class="mt-1.5 block text-xs text-[#ff5470]"
                                >
                                    {{
                                        errors.password
                                    }}
                                </span>
                            </label>

                            <label
                                class="block text-xs font-bold uppercase tracking-wider text-[#8ea2c0]"
                            >
                                Confirmar

                                <input
                                    name="password_confirmation"
                                    type="password"
                                    required
                                    autocomplete="new-password"
                                    placeholder="••••••••"
                                    class="mt-2 w-full rounded-lg border border-[#2a3c63] bg-[#0c1424] px-4 py-3 text-sm text-white outline-none transition placeholder:text-[#536783] focus:border-[#3ddcff] focus:ring-2 focus:ring-[#3ddcff]/10"
                                />
                            </label>
                        </div>

                        <!-- TERMINOS -->
                        <label
                            class="flex cursor-pointer items-start gap-2 pt-1 text-xs leading-5 text-[#8ea2c0]"
                        >
                            <input
                                name="terms_accepted"
                                type="checkbox"
                                value="1"
                                required
                                class="mt-1 rounded border-[#2a3c63] bg-[#0c1424]"
                            />

                            <span>
                                Acepto los términos,
                                condiciones y política
                                de privacidad.
                            </span>
                        </label>

                        <span
                            v-if="
                                errors.terms_accepted
                            "
                            class="block text-xs text-[#ff5470]"
                        >
                            {{
                                errors.terms_accepted
                            }}
                        </span>

                        <!-- CREAR CUENTA -->
                        <button
                            type="submit"
                            :disabled="processing"
                            class="w-full rounded-lg bg-[#ffb020] px-4 py-3.5 font-bold text-[#171003] shadow-[0_8px_22px_rgba(255,176,32,.22)] transition hover:-translate-y-0.5 hover:bg-[#ffcf6e] disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            {{
                                processing
                                    ? 'Creando cuenta...'
                                    : 'Crear cuenta'
                            }}
                        </button>

                        <div
                            class="border-t border-[#1e2c4a] pt-5"
                        >
                            <p
                                class="text-center text-sm text-[#8ea2c0]"
                            >
                                ¿Ya tienes una cuenta?

                                <button
                                    type="button"
                                    class="font-semibold text-[#ffb020] transition hover:text-[#ffcf6e]"
                                    @click="
                                        changeAuthMode(
                                            'login',
                                        )
                                    "
                                >
                                    Inicia sesión
                                </button>
                            </p>
                        </div>
                    </Form>
                </div>
            </div>
        </div>
    </div>
</template>