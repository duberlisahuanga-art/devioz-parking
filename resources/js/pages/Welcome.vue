<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

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

function scrollToSection(id: string): void {
    const el = document.querySelector(id);

    if (el) {
        el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

onMounted(() => {
    // Blips (partículas de fondo)
    const blipsContainer = document.getElementById('blips');

    if (blipsContainer) {
        const colors = ['#ffb020', '#3ddcff', '#2ee59d'];

        for (let i = 0; i < 16; i++) {
            const b = document.createElement('div');
            b.className = 'blip';

            const size = Math.random() * 5 + 3;
            b.style.width = size + 'px';
            b.style.height = size + 'px';
            b.style.left = Math.random() * 100 + '%';
            b.style.background = colors[i % 3];
            b.style.animationDuration = Math.random() * 10 + 10 + 's';
            b.style.animationDelay = Math.random() * 10 + 's';

            blipsContainer.appendChild(b);
        }
    }

    // Fade-in al scroll
    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((e) => {
                if (e.isIntersecting) {
                    e.target.classList.add('visible');
                }
            });
        },
        { threshold: 0.1, rootMargin: '0px 0px -50px 0px' },
    );

    document.querySelectorAll('.fade-in').forEach((el) => observer.observe(el));

    // Navbar scroll con blur
    const navbar = document.getElementById('navbar');

    if (navbar) {
        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 100) {
                navbar.classList.add('nav-blur', 'bg-[#070c18]/80');
            } else {
                navbar.classList.remove('nav-blur', 'bg-[#070c18]/80');
            }
        });
    }

    // Mobile menu
    const btn = document.getElementById('mobile-menu-btn');
    const menu = document.getElementById('mobile-menu');

    if (btn && menu) {
        btn.addEventListener('click', () => menu.classList.toggle('hidden'));

        menu.querySelectorAll('a').forEach((a) =>
            a.addEventListener('click', () => menu.classList.add('hidden')),
        );
    }

    // Smooth scroll en anchors
    document.querySelectorAll('a[href^="#"]').forEach((a) => {
        a.addEventListener('click', function (this: HTMLAnchorElement, e) {
            e.preventDefault();

            const target = document.querySelector(
                this.getAttribute('href') || '',
            );

            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start',
                });
            }
        });
    });
});
</script>

<style>
* {
    box-sizing: border-box;
}

html {
    scroll-behavior: smooth;
}

.fade-in {
    opacity: 0;
    transform: translateY(36px);
    transition: opacity 0.7s ease, transform 0.7s ease;
}

.fade-in.visible {
    opacity: 1;
    transform: translateY(0);
}

.blip {
    position: absolute;
    border-radius: 50%;
    opacity: 0.55;
    animation: rise linear infinite;
    pointer-events: none;
}

@keyframes rise {
    0% {
        transform: translateY(10vh);
        opacity: 0;
    }

    12% {
        opacity: 0.6;
    }

    88% {
        opacity: 0.4;
    }

    100% {
        transform: translateY(-110vh);
        opacity: 0;
    }
}

.hero-gradient {
    background:
        linear-gradient(180deg, rgba(7, 12, 24, 0.35) 0%, rgba(7, 12, 24, 0.55) 45%, #070c18 100%),
        linear-gradient(90deg, rgba(4, 7, 14, 0.7) 0%, transparent 60%);
}

.text-glow {
    text-shadow: 0 0 40px rgba(255, 176, 32, 0.35), 0 0 80px rgba(201, 138, 18, 0.2);
}

.card-hover {
    transition: all 0.35s ease;
}

.card-hover:hover {
    transform: translateY(-6px);
    box-shadow: 0 20px 50px -20px rgba(255, 176, 32, 0.18);
}

.line-accent {
    width: 60px;
    height: 3px;
    background: linear-gradient(90deg, #ffb020, transparent);
}

.nav-blur {
    backdrop-filter: blur(18px);
    -webkit-backdrop-filter: blur(18px);
}

.quote-mark {
    font-size: 5.5rem;
    line-height: 1;
    color: #ffb020;
    opacity: 0.3;
    font-family: Georgia, serif;
}

.scroll-indicator {
    animation: bounce 2s ease infinite;
}

@keyframes bounce {

    0%,
    20%,
    50%,
    80%,
    100% {
        transform: translateY(0);
    }

    40% {
        transform: translateY(-14px);
    }

    60% {
        transform: translateY(-6px);
    }
}

.img-overlay {
    position: relative;
    overflow: hidden;
}

.img-overlay::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(255, 176, 32, 0.16), transparent 60%);
    pointer-events: none;
}

.section-divider {
    height: 1px;
    background: linear-gradient(90deg, transparent, #ffb020, transparent);
    opacity: 0.3;
}

.app-bar {
    display: flex;
    align-items: center;
    gap: 10px;
}
</style>

<template>

    <Head title="DEVIOZ PARKING — Tu lugar, en el momento exacto">
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous" />
        <link
            href="https://fonts.googleapis.com/css2?family=Archivo+Black&family=Space+Grotesk:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap"
            rel="stylesheet" />
    </Head>

    <div class="min-h-screen overflow-x-hidden bg-[#070c18] font-['Space_Grotesk'] text-[#eaf1fb]">
        <div id="blips" class="pointer-events-none fixed inset-0 z-40"></div>

        <!-- =====================================================
             NAVBAR
             ===================================================== -->
        <nav id="navbar" class="fixed top-0 right-0 left-0 z-50 transition-all duration-500">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
                <a href="#hero"
                    class="flex items-center gap-2 font-['Archivo_Black'] text-sm tracking-wider text-white">
                    <span
                        class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-[#ffb020] to-[#c98a12] text-base text-[#1a1200]">
                        D
                    </span>
                    DEVIOZ PARKING
                </a>

                <div class="hidden items-center gap-8 md:flex">
                    <a href="#about"
                        class="text-sm tracking-widest text-gray-400 uppercase transition-colors hover:text-[#ffb020]">
                        Qué es
                    </a>
                    <a href="#plot"
                        class="text-sm tracking-widest text-gray-400 uppercase transition-colors hover:text-[#ffb020]">
                        Cómo funciona
                    </a>
                    <a href="#themes"
                        class="text-sm tracking-widest text-gray-400 uppercase transition-colors hover:text-[#ffb020]">
                        Tecnología
                    </a>
                    <a href="#author"
                        class="text-sm tracking-widest text-gray-400 uppercase transition-colors hover:text-[#ffb020]">
                        Proyecto
                    </a>
                </div>

                <div class="hidden items-center gap-4 md:flex">
                    <button type="button" class="text-sm text-gray-300 transition-colors hover:text-[#ffb020]"
                        @click="openAuth('login')">
                        Iniciar sesión
                    </button>
                    <button type="button"
                        class="rounded-full bg-[#ffb020] px-5 py-2 text-sm font-semibold tracking-wide text-[#1a1200] transition-all duration-300 hover:scale-105 hover:bg-[#c98a12]"
                        @click="openAuth('register')">
                        Crear cuenta
                    </button>
                </div>

                <button id="mobile-menu-btn" type="button"
                    class="text-gray-400 transition-colors hover:text-[#ffb020] md:hidden">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>

            <div id="mobile-menu" class="nav-blur hidden border-t border-[#1c2c4d] bg-[#070c18]/90 md:hidden">
                <div class="flex flex-col gap-4 px-6 py-4">
                    <a href="#about" class="text-sm tracking-widest text-gray-400 uppercase hover:text-[#ffb020]">
                        Qué es
                    </a>
                    <a href="#plot" class="text-sm tracking-widest text-gray-400 uppercase hover:text-[#ffb020]">
                        Cómo funciona
                    </a>
                    <a href="#themes" class="text-sm tracking-widest text-gray-400 uppercase hover:text-[#ffb020]">
                        Tecnología
                    </a>
                    <a href="#author" class="text-sm tracking-widest text-gray-400 uppercase hover:text-[#ffb020]">
                        Proyecto
                    </a>
                    <div class="flex flex-col gap-3 border-t border-[#1c2c4d] pt-2">
                        <button type="button" class="text-left text-sm text-gray-300 hover:text-[#ffb020]"
                            @click="openAuth('login')">
                            Iniciar sesión
                        </button>
                        <button type="button"
                            class="rounded-full bg-[#ffb020] px-5 py-2 text-center text-sm font-semibold tracking-wide text-[#1a1200] transition-all duration-300 hover:bg-[#c98a12]"
                            @click="openAuth('register')">
                            Crear cuenta
                        </button>
                    </div>
                </div>
            </div>
        </nav>

        <!-- =====================================================
             HERO
             ===================================================== -->
        <section id="hero" class="relative flex min-h-screen items-center justify-center">
            <div class="absolute inset-0">
                <img src="/hero-estacionamiento.jpg" alt="Vista aérea del estacionamiento"
                    class="h-full w-full object-cover opacity-70" />
                <div class="hero-gradient absolute inset-0"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-4xl px-6 text-center">
                <p class="fade-in mb-6 text-sm tracking-[0.3em] text-[#ffb020] uppercase">
                    Gestión inteligente de estacionamientos
                </p>

                <h1
                    class="fade-in text-glow mb-4 font-['Archivo_Black'] text-5xl leading-tight text-white md:text-7xl lg:text-8xl">
                    TU LUGAR,<br />
                    EN EL MOMENTO<br />
                    <span class="text-[#ffb020]">EXACTO</span>
                </h1>

                <p class="fade-in mx-auto mt-6 mb-2 max-w-2xl text-lg text-gray-300 md:text-xl">
                    Reserva, paga con Yape y estaciona sin fricción.
                </p>

                <p class="fade-in mb-12 font-mono text-sm tracking-widest text-[#3ddcff] uppercase">
                    40 plazas · Red activa en tiempo real
                </p>

                <div class="fade-in flex flex-col items-center justify-center gap-4 sm:flex-row">
                    <button type="button"
                        class="rounded-full bg-[#ffb020] px-8 py-3 text-sm font-semibold tracking-widest text-[#1a1200] uppercase transition-all duration-300 hover:scale-105 hover:bg-[#c98a12]"
                        @click="scrollToSection('#plot')">
                        Cómo funciona
                    </button>
                    <button type="button"
                        class="rounded-full border border-gray-600 px-8 py-3 text-sm tracking-widest text-gray-300 uppercase transition-all duration-300 hover:border-[#ffb020] hover:text-[#ffb020]"
                        @click="scrollToSection('#author')">
                        Ver el proyecto
                    </button>
                </div>
            </div>

            <div class="scroll-indicator absolute bottom-8 left-1/2 -translate-x-1/2">
                <svg class="h-6 w-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                </svg>
            </div>
        </section>

        <!-- =====================================================
             SECCIÓN: QUÉ ES
             ===================================================== -->
        <section id="about" class="px-6 py-24 md:py-32">
            <div class="mx-auto max-w-6xl">
                <div class="grid items-center gap-12 md:grid-cols-2 md:gap-20">
                    <div class="fade-in">
                        <div class="line-accent mb-6"></div>
                        <h2 class="mb-6 font-['Archivo_Black'] text-3xl text-white md:text-4xl">
                            Qué es Devioz
                        </h2>
                        <p class="mb-6 text-lg leading-relaxed text-gray-400">
                            <span class="font-semibold text-[#ffb020]">
                                DEVIOZ PARKING
                            </span>
                            es un sistema web que digitaliza por completo la
                            operación de un estacionamiento físico: funciona
                            como un "Airbnb de estacionamientos", conectando
                            conductores con espacios disponibles en tiempo real.
                        </p>
                        <p class="leading-relaxed text-gray-500">
                            El conductor reserva, paga y accede desde su
                            celular. El administrador controla todo en tiempo
                            real desde un panel con cámaras, KPIs y auditoría
                            completa — sin planillas de Excel ni tickets
                            físicos.
                        </p>
                    </div>

                    <div class="fade-in">
                        <div class="img-overlay overflow-hidden rounded-2xl">
                            <img src="/hero-estacionamiento.jpg" alt="Estacionamiento Devioz"
                                class="h-96 w-full rounded-2xl object-cover" />
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="section-divider mx-auto max-w-4xl"></div>

        <!-- =====================================================
             SECCIÓN: CÓMO FUNCIONA
             ===================================================== -->
        <section id="plot" class="px-6 py-24 md:py-32">
            <div class="mx-auto max-w-6xl">
                <div class="fade-in mb-16 text-center">
                    <div class="line-accent mx-auto mb-6"></div>
                    <h2 class="mb-4 font-['Archivo_Black'] text-3xl text-white md:text-4xl">
                        Cómo funciona
                    </h2>
                    <p class="mx-auto max-w-2xl text-gray-500">
                        Del papel al control total en tres pasos.
                    </p>
                </div>

                <div class="relative">
                    <div
                        class="absolute top-0 bottom-0 left-4 w-px bg-gradient-to-b from-[#ffb020] via-[#1c2c4d] to-transparent md:left-1/2">
                    </div>

                    <!-- Paso 01 -->
                    <div class="fade-in relative mb-16">
                        <div class="items-center md:grid md:grid-cols-2 md:gap-12">
                            <div class="pl-12 md:pr-12 md:pl-0 md:text-right">
                                <div class="mb-3 inline-flex items-center gap-2">
                                    <span class="h-3 w-3 rounded-full bg-[#ffb020]"></span>
                                    <span class="font-mono text-sm tracking-widest text-[#ffb020] uppercase">
                                        01 · Reservar
                                    </span>
                                </div>
                                <h3 class="mb-3 text-xl font-bold text-white">
                                    Elige plaza y horario
                                </h3>
                                <p class="leading-relaxed text-gray-400">
                                    El conductor ve las 40 plazas en tiempo
                                    real y aparta la suya en menos de 30
                                    segundos, sin pisar el estacionamiento.
                                </p>
                            </div>
                            <div class="hidden md:block"></div>
                        </div>
                    </div>

                    <!-- Paso 02 -->
                    <div class="fade-in relative mb-16">
                        <div class="items-center md:grid md:grid-cols-2 md:gap-12">
                            <div class="hidden md:block"></div>
                            <div class="pl-12">
                                <div class="mb-3 inline-flex items-center gap-2">
                                    <span class="h-3 w-3 rounded-full bg-[#a855f7]"></span>
                                    <span class="font-mono text-sm tracking-widest text-[#a855f7] uppercase">
                                        02 · Pagar
                                    </span>
                                </div>
                                <h3 class="mb-3 text-xl font-bold text-white">
                                    Voucher Yape al instante
                                </h3>
                                <p class="leading-relaxed text-gray-400">
                                    Escanea el QR, sube el comprobante y el
                                    administrador lo aprueba desde el panel
                                    antes de que el conductor llegue.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Paso 03 -->
                    <div class="fade-in relative">
                        <div class="items-center md:grid md:grid-cols-2 md:gap-12">
                            <div class="pl-12 md:pr-12 md:pl-0 md:text-right">
                                <div class="mb-3 inline-flex items-center gap-2">
                                    <span class="h-3 w-3 animate-pulse rounded-full bg-[#3ddcff]"></span>
                                    <span class="font-mono text-sm tracking-widest text-[#3ddcff] uppercase">
                                        03 · Estacionar
                                    </span>
                                </div>
                                <h3 class="mb-3 text-xl font-bold text-white">
                                    Check-in por placa
                                </h3>
                                <p class="leading-relaxed text-gray-400">
                                    La cámara LPR reconoce el vehículo en el
                                    acceso y abre la barrera automáticamente,
                                    sin ticket físico.
                                </p>
                            </div>
                            <div class="hidden md:block"></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Monitoreo 24/7 -->
        <section class="px-6 py-16">
            <div class="fade-in mx-auto max-w-6xl">
                <div class="img-overlay group relative overflow-hidden rounded-2xl">
                    <img src="/camaras-vivo.jpg" alt="Panel de cámaras del estacionamiento"
                        class="h-[400px] w-full rounded-2xl object-cover transition-transform duration-700 group-hover:scale-105 md:h-[500px]" />
                    <div
                        class="absolute inset-0 flex items-center justify-center bg-[#070c18]/40 transition-colors duration-500 group-hover:bg-[#070c18]/15">
                        <div class="text-center">
                            <p class="text-glow mb-2 font-['Archivo_Black'] text-2xl text-white md:text-3xl">
                                Centro de monitoreo 24/7
                            </p>
                            <p class="font-mono text-sm tracking-wider text-gray-300">
                                Vigilancia activa en cada acceso
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="section-divider mx-auto max-w-4xl"></div>

        <!-- =====================================================
             SECCIÓN: TECNOLOGÍA
             ===================================================== -->
        <section id="themes" class="px-6 py-24 md:py-32">
            <div class="mx-auto max-w-6xl">
                <div class="fade-in mb-16 text-center">
                    <div class="line-accent mx-auto mb-6"></div>
                    <h2 class="mb-4 font-['Archivo_Black'] text-3xl text-white md:text-4xl">
                        Diferenciadores técnicos
                    </h2>
                    <p class="mx-auto max-w-2xl text-gray-500">
                        Lo que hace que Devioz funcione en tiempo real, incluso
                        sin conexión.
                    </p>
                </div>

                <div class="grid gap-8 md:grid-cols-3">
                    <div class="card-hover fade-in rounded-2xl border border-[#1c2c4d] bg-[#101c38]/40 p-8">
                        <div class="mb-6 flex h-14 w-14 items-center justify-center rounded-xl bg-[#ffb020]/10">
                            <svg class="h-7 w-7 text-[#ffb020]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </div>
                        <h3 class="mb-3 text-xl font-bold text-white">
                            Reconocimiento LPR
                        </h3>
                        <p class="text-sm leading-relaxed text-gray-400">
                            YOLOv8 + OCR para leer placas con 98.4% de
                            precisión y menos de 300ms de latencia por
                            vehículo.
                        </p>
                    </div>

                    <div class="card-hover fade-in rounded-2xl border border-[#1c2c4d] bg-[#101c38]/40 p-8">
                        <div class="mb-6 flex h-14 w-14 items-center justify-center rounded-xl bg-[#3ddcff]/10">
                            <svg class="h-7 w-7 text-[#3ddcff]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <h3 class="mb-3 text-xl font-bold text-white">
                            Edge computing
                        </h3>
                        <p class="text-sm leading-relaxed text-gray-400">
                            Lógica offline-first: las cámaras y accesos siguen
                            funcionando aunque se caiga la conexión, y
                            sincronizan al reconectar.
                        </p>
                    </div>

                    <div class="card-hover fade-in rounded-2xl border border-[#1c2c4d] bg-[#101c38]/40 p-8">
                        <div class="mb-6 flex h-14 w-14 items-center justify-center rounded-xl bg-[#2ee59d]/10">
                            <svg class="h-7 w-7 text-[#2ee59d]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                        </div>
                        <h3 class="mb-3 text-xl font-bold text-white">
                            Protocolos abiertos
                        </h3>
                        <p class="text-sm leading-relaxed text-gray-400">
                            RTSP · ONVIF · REST · WebSocket · MQTT — integra
                            cualquier cámara o sensor sin depender de un
                            fabricante.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <div class="section-divider mx-auto max-w-4xl"></div>

        <!-- =====================================================
             SECCIÓN: SEGURIDAD Y CONTROL
             ===================================================== -->
        <section class="px-6 py-24 md:py-32">
            <div class="mx-auto max-w-6xl">
                <div class="grid items-center gap-12 md:grid-cols-2 md:gap-20">
                    <div class="fade-in order-2 md:order-1">
                        <div class="overflow-hidden rounded-2xl border border-[#1c2c4d]">
                            <div class="app-bar border-b border-[#1c2c4d] bg-[#101c38] px-4 py-3">
                                <div class="flex gap-1.5">
                                    <span class="h-2.5 w-2.5 rounded-full bg-[#1c2c4d]"></span>
                                    <span class="h-2.5 w-2.5 rounded-full bg-[#1c2c4d]"></span>
                                    <span class="h-2.5 w-2.5 rounded-full bg-[#1c2c4d]"></span>
                                </div>
                                <div
                                    class="mx-3 flex-1 rounded bg-[#070c18] px-3 py-1 text-center font-mono text-xs text-gray-500">
                                    panel.devioz.pe/camaras
                                </div>
                                <span class="flex items-center gap-1.5 font-mono text-xs text-red-400">
                                    <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-red-400"></span>
                                    REC
                                </span>
                            </div>

                            <img src="/camaras-vivo.jpg" alt="Panel de cámaras en vivo"
                                class="h-[300px] w-full object-cover" />
                        </div>
                    </div>

                    <div class="fade-in order-1 md:order-2">
                        <div class="line-accent mb-6"></div>
                        <h2 class="mb-6 font-['Archivo_Black'] text-3xl text-white md:text-4xl">
                            Seguridad y control
                        </h2>
                        <p class="mb-6 text-lg leading-relaxed text-gray-400">
                            Cada acceso queda registrado. Cada acción crítica,
                            auditada. El administrador ve las cámaras, la
                            ocupación y los KPIs desde una sola pantalla.
                        </p>
                        <p class="mb-6 leading-relaxed text-gray-500">
                            La barrera solo se abre con una reserva válida. Si
                            algo falla, queda en el registro — nunca en la
                            memoria de alguien.
                        </p>

                        <div class="rounded-xl border border-[#1c2c4d] bg-[#101c38]/40 p-6">
                            <p class="text-sm text-[#ffb020] italic">
                                «Acceso controlado: la barrera solo abre con
                                reserva válida, y todo queda auditado con video
                                asociado.»
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Quote destacada -->
        <section class="relative overflow-hidden px-6 py-24 md:py-32">
            <div class="absolute inset-0 bg-gradient-to-b from-[#070c18] via-[#101c38]/30 to-[#070c18] opacity-60">
            </div>

            <div class="fade-in relative z-10 mx-auto max-w-4xl text-center">
                <span class="quote-mark">"</span>
                <blockquote
                    class="mb-8 font-['Archivo_Black'] text-2xl leading-relaxed text-white md:text-3xl lg:text-4xl">
                    Del papel al control<br />
                    total en 72 horas.
                </blockquote>
                <p class="font-mono text-sm tracking-widest text-gray-500 uppercase">
                    — Instalación completa, sin detener la operación
                </p>
            </div>
        </section>

        <div class="section-divider mx-auto max-w-4xl"></div>

        <!-- =====================================================
             SECCIÓN: SOBRE EL PROYECTO
             ===================================================== -->
        <section id="author" class="px-6 py-24 md:py-32">
            <div class="mx-auto max-w-6xl">
                <div class="grid items-center gap-12 md:grid-cols-2 md:gap-20">
                    <div class="fade-in">
                        <div class="line-accent mb-6"></div>
                        <h2 class="mb-6 font-['Archivo_Black'] text-3xl text-white md:text-4xl">
                            Sobre el proyecto
                        </h2>
                        <h3 class="mb-4 text-2xl font-bold text-[#ffb020]">
                            Estacionamiento Devioz
                        </h3>
                        <p class="mb-6 leading-relaxed text-gray-400">
                            Devioz nace para resolver un problema concreto: un
                            estacionamiento físico gestionado a mano, con
                            planillas y vouchers de papel. El sistema
                            digitaliza reservas, pagos, accesos y auditoría en
                            un solo lugar.
                        </p>
                        <p class="leading-relaxed text-gray-500">
                            Construido con Laravel + Vue 3 + Inertia, pensado
                            para escalar de un estacionamiento piloto a una red
                            de espacios conectados.
                        </p>
                    </div>

                    <div class="fade-in">
                        <div class="rounded-2xl border border-[#1c2c4d] bg-[#101c38]/30 p-8">
                            <div class="mb-6 flex items-center gap-4">
                                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-[#ffb020]/20">
                                    <svg class="h-8 w-8 text-[#ffb020]" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-bold text-white">
                                        Duber Cotrina
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        Desarrollador del proyecto
                                    </p>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div class="flex items-start gap-3">
                                    <svg class="mt-1 h-5 w-5 shrink-0 text-[#ffb020]" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <div>
                                        <p class="font-semibold text-white">
                                            Laravel + Vue 3 + Inertia
                                        </p>
                                        <p class="text-sm text-gray-500">
                                            Stack moderno con SSR y tipado
                                            estricto
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-start gap-3">
                                    <svg class="mt-1 h-5 w-5 shrink-0 text-[#3ddcff]" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <div>
                                        <p class="font-semibold text-white">
                                            SQLite + Spatie Permission
                                        </p>
                                        <p class="text-sm text-gray-500">
                                            Roles, permisos y auditoría
                                            completa
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-start gap-3">
                                    <svg class="mt-1 h-5 w-5 shrink-0 text-[#2ee59d]" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <div>
                                        <p class="font-semibold text-white">
                                            Fortify 2FA
                                        </p>
                                        <p class="text-sm text-gray-500">
                                            Autenticación segura con doble
                                            factor
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- =====================================================
             CTA FINAL
             ===================================================== -->
        <section class="relative overflow-hidden px-6 py-24 md:py-32">
            <div class="absolute inset-0">
                <img src="/hero-estacionamiento.jpg" alt="Estacionamiento Devioz"
                    class="h-full w-full object-cover opacity-20" />
                <div class="absolute inset-0 bg-gradient-to-b from-[#070c18] via-[#070c18]/80 to-[#070c18]"></div>
            </div>

            <div class="fade-in relative z-10 mx-auto max-w-3xl text-center">
                <h2 class="mb-6 font-['Archivo_Black'] text-3xl text-white md:text-5xl">
                    Tu estacionamiento,<br />
                    en <span class="text-[#ffb020]">modo control total</span>
                </h2>
                <p class="mb-10 text-lg text-gray-400">
                    Reserva, paga y accede sin fricción. El panel lo controla
                    todo.
                </p>

                <div class="flex flex-col items-center justify-center gap-4 sm:flex-row">
                    <button type="button"
                        class="rounded-full bg-[#ffb020] px-8 py-3 text-sm font-semibold tracking-widest text-[#1a1200] uppercase transition-all duration-300 hover:scale-105 hover:bg-[#c98a12]"
                        @click="openAuth('register')">
                        Crear cuenta
                    </button>
                    <button type="button"
                        class="rounded-full border border-gray-600 px-8 py-3 text-sm tracking-widest text-gray-300 uppercase transition-all duration-300 hover:border-[#ffb020] hover:text-[#ffb020]"
                        @click="openAuth('login')">
                        Iniciar sesión
                    </button>
                </div>
            </div>
        </section>

        <!-- =====================================================
             FOOTER
             ===================================================== -->
        <footer class="border-t border-[#1c2c4d] px-6 py-12">
            <div class="mx-auto max-w-6xl">
                <div class="flex flex-col items-center justify-between gap-6 md:flex-row">
                    <div class="flex items-center gap-2">
                        <span
                            class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-[#ffb020] to-[#c98a12] text-base text-[#1a1200]">
                            D
                        </span>
                        <span class="font-['Archivo_Black'] text-sm tracking-wider text-white">
                            DEVIOZ PARKING
                        </span>
                    </div>

                    <p class="font-mono text-xs text-gray-600">
                        © 2025 Devioz Parking · Todos los derechos reservados
                    </p>

                    <div class="flex items-center gap-4">
                        <a href="#hero"
                            class="text-xs tracking-widest text-gray-500 uppercase transition-colors hover:text-[#ffb020]">
                            Volver arriba
                        </a>
                    </div>
                </div>
            </div>
        </footer>

        <!-- =====================================================
             MODAL DE AUTENTICACIÓN
             ===================================================== -->
        <div v-if="authOpen"
            class="fixed inset-0 z-[100] flex items-center justify-center bg-[#070c18]/90 px-4 backdrop-blur-sm"
            @click.self="closeAuth">
            <div class="w-full max-w-md rounded-2xl border border-[#1c2c4d] bg-[#101c38] p-8">
                <div class="mb-6 flex items-center justify-between">
                    <div class="flex gap-2">
                        <button type="button"
                            class="rounded-full px-4 py-1.5 text-xs font-semibold tracking-widest uppercase transition-all"
                            :class="authMode === 'login'
                                    ? 'bg-[#ffb020] text-[#1a1200]'
                                    : 'text-gray-400 hover:text-white'
                                " @click="changeAuthMode('login')">
                            Iniciar sesión
                        </button>
                        <button type="button"
                            class="rounded-full px-4 py-1.5 text-xs font-semibold tracking-widest uppercase transition-all"
                            :class="authMode === 'register'
                                    ? 'bg-[#ffb020] text-[#1a1200]'
                                    : 'text-gray-400 hover:text-white'
                                " @click="changeAuthMode('register')">
                            Crear cuenta
                        </button>
                    </div>

                    <button type="button" class="text-gray-500 transition-colors hover:text-white" @click="closeAuth">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Formulario LOGIN -->
                <Form v-if="authMode === 'login'" v-bind="loginStore.form()" class="space-y-4" @success="closeAuth">
                    <div>
                        <label class="mb-1.5 block text-xs tracking-widest text-gray-400 uppercase">
                            Email
                        </label>
                        <input type="email" name="email" required
                            class="w-full rounded-lg border border-[#1c2c4d] bg-[#070c18] px-4 py-2.5 text-sm text-white placeholder-gray-600 outline-none transition-colors focus:border-[#ffb020]"
                            placeholder="tu@email.com" />
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs tracking-widest text-gray-400 uppercase">
                            Contraseña
                        </label>
                        <input type="password" name="password" required
                            class="w-full rounded-lg border border-[#1c2c4d] bg-[#070c18] px-4 py-2.5 text-sm text-white placeholder-gray-600 outline-none transition-colors focus:border-[#ffb020]"
                            placeholder="••••••••" />
                    </div>

                    <button type="submit"
                        class="w-full rounded-full bg-[#ffb020] py-3 text-sm font-semibold tracking-widest text-[#1a1200] uppercase transition-all duration-300 hover:bg-[#c98a12]">
                        Entrar
                    </button>
                </Form>

                <!-- Formulario REGISTER -->
                <Form v-else v-bind="registerStore.form()" class="space-y-4" @success="closeAuth">
                    <div>
                        <label class="mb-1.5 block text-xs tracking-widest text-gray-400 uppercase">
                            Nombre
                        </label>
                        <input type="text" name="name" required
                            class="w-full rounded-lg border border-[#1c2c4d] bg-[#070c18] px-4 py-2.5 text-sm text-white placeholder-gray-600 outline-none transition-colors focus:border-[#ffb020]"
                            placeholder="Tu nombre" />
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs tracking-widest text-gray-400 uppercase">
                            Email
                        </label>
                        <input type="email" name="email" required
                            class="w-full rounded-lg border border-[#1c2c4d] bg-[#070c18] px-4 py-2.5 text-sm text-white placeholder-gray-600 outline-none transition-colors focus:border-[#ffb020]"
                            placeholder="tu@email.com" />
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs tracking-widest text-gray-400 uppercase">
                            Contraseña
                        </label>
                        <input type="password" name="password" required
                            class="w-full rounded-lg border border-[#1c2c4d] bg-[#070c18] px-4 py-2.5 text-sm text-white placeholder-gray-600 outline-none transition-colors focus:border-[#ffb020]"
                            placeholder="••••••••" />
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs tracking-widest text-gray-400 uppercase">
                            Confirmar contraseña
                        </label>
                        <input type="password" name="password_confirmation" required
                            class="w-full rounded-lg border border-[#1c2c4d] bg-[#070c18] px-4 py-2.5 text-sm text-white placeholder-gray-600 outline-none transition-colors focus:border-[#ffb020]"
                            placeholder="••••••••" />
                    </div>

                    <button type="submit"
                        class="w-full rounded-full bg-[#ffb020] py-3 text-sm font-semibold tracking-widest text-[#1a1200] uppercase transition-all duration-300 hover:bg-[#c98a12]">
                        Crear cuenta
                    </button>
                </Form>

                <!-- Link a dashboard si ya está autenticado -->
                <div class="mt-4 text-center">
                    <Link :href="dashboard()" class="text-xs text-gray-500 transition-colors hover:text-[#ffb020]">
                        Ir al dashboard →
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>