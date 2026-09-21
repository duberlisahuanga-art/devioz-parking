<script setup lang="ts">
import { computed } from 'vue';
import {
    Bell,
    Check,
    Moon,
    Sun,
} from '@lucide/vue';

import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import { Button } from '@/components/ui/button';

import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

import { useAppearance } from '@/composables/useAppearance';

import type { BreadcrumbItem } from '@/types';

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItem[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);

/*
|--------------------------------------------------------------------------
| Tema claro / oscuro
|--------------------------------------------------------------------------
*/

const {
    resolvedAppearance,
    updateAppearance,
} = useAppearance();

const isDark = computed(
    () => resolvedAppearance.value === 'dark',
);

function toggleTheme(): void {
    updateAppearance(
        isDark.value ? 'light' : 'dark',
    );
}

/*
|--------------------------------------------------------------------------
| Notificaciones
|--------------------------------------------------------------------------
|
| Por ahora dejamos preparado el componente visual.
| Más adelante lo conectaremos con reservas, pagos, etc.
|
*/

const notifications = computed(() => {
    return [];
});

const unreadCount = computed(
    () => notifications.value.length,
);
</script>

<template>
    <header
        class="border-sidebar-border/70 flex h-16 shrink-0 items-center border-b px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4"
    >
        <!-- Lado izquierdo -->
        <div class="flex min-w-0 items-center gap-2">
            <SidebarTrigger class="-ml-1" />

            <template
                v-if="
                    breadcrumbs &&
                    breadcrumbs.length > 0
                "
            >
                <Breadcrumbs
                    :breadcrumbs="breadcrumbs"
                />
            </template>
        </div>

        <!-- Lado derecho -->
        <div
            class="ml-auto flex items-center gap-2"
        >
            <!-- NOTIFICACIONES -->
            <DropdownMenu>
                <DropdownMenuTrigger
                    :as-child="true"
                >
                    <Button
                        type="button"
                        variant="ghost"
                        size="icon"
                        class="relative size-10 rounded-full border border-slate-700/60 bg-slate-900/30 text-slate-300 transition hover:bg-slate-800 hover:text-white dark:border-slate-700 dark:bg-slate-900/40"
                        aria-label="Notificaciones"
                    >
                        <Bell
                            class="size-5"
                            :stroke-width="2"
                        />

                        <!-- Indicador -->
                        <span
                            v-if="unreadCount > 0"
                            class="absolute right-1 top-1 flex min-h-4 min-w-4 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold leading-none text-white"
                        >
                            {{
                                unreadCount > 9
                                    ? '9+'
                                    : unreadCount
                            }}
                        </span>
                    </Button>
                </DropdownMenuTrigger>

                <DropdownMenuContent
                    align="end"
                    class="w-80 p-0"
                >
                    <!-- Cabecera -->
                    <div
                        class="flex items-center justify-between border-b px-4 py-3"
                    >
                        <div>
                            <p
                                class="font-semibold"
                            >
                                Notificaciones
                            </p>

                            <p
                                class="text-xs text-muted-foreground"
                            >
                                Actividad reciente de
                                DEVIOZ PARKING
                            </p>
                        </div>

                        <Bell
                            class="size-4 text-muted-foreground"
                        />
                    </div>

                    <!-- Sin notificaciones -->
                    <div
                        v-if="
                            notifications.length ===
                            0
                        "
                        class="flex flex-col items-center justify-center px-6 py-8 text-center"
                    >
                        <div
                            class="flex size-11 items-center justify-center rounded-full bg-emerald-500/10"
                        >
                            <Check
                                class="size-5 text-emerald-500"
                            />
                        </div>

                        <p
                            class="mt-3 text-sm font-medium"
                        >
                            Todo al día
                        </p>

                        <p
                            class="mt-1 text-xs text-muted-foreground"
                        >
                            No tienes notificaciones
                            nuevas.
                        </p>
                    </div>

                    <!-- Futuras notificaciones -->
                    <div
                        v-else
                        class="max-h-80 overflow-y-auto"
                    >
                        <div
                            v-for="(
                                notification,
                                index
                            ) in notifications"
                            :key="index"
                            class="border-b px-4 py-3 last:border-b-0"
                        >
                            {{ notification }}
                        </div>
                    </div>
                </DropdownMenuContent>
            </DropdownMenu>

            <!-- CAMBIAR TEMA -->
            <Button
                type="button"
                variant="ghost"
                size="icon"
                class="size-10 rounded-full border border-slate-700/60 bg-slate-900/30 text-slate-300 transition hover:bg-slate-800 hover:text-white dark:border-slate-700 dark:bg-slate-900/40"
                :aria-label="
                    isDark
                        ? 'Cambiar a modo claro'
                        : 'Cambiar a modo oscuro'
                "
                :title="
                    isDark
                        ? 'Modo claro'
                        : 'Modo oscuro'
                "
                @click="toggleTheme"
            >
                <Sun
                    v-if="isDark"
                    class="size-5 text-amber-400"
                    :stroke-width="2"
                />

                <Moon
                    v-else
                    class="size-5 text-slate-700"
                    :stroke-width="2"
                />
            </Button>
        </div>
    </header>
</template> 