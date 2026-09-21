<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import {
    LayoutDashboard,
    Calendar,
    ParkingCircle,
    Camera,
    CreditCard,
    Wrench,
    Users,
    ClipboardList,
    Car,
    Settings,
    HelpCircle,
} from '@lucide/vue';
import { computed } from 'vue';

import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
} from '@/components/ui/sidebar';

import NavMain from './NavMain.vue';
import NavFooter from './NavFooter.vue';
import NavUser from './NavUser.vue';

interface NavItem {
    title: string;
    href: string;
    icon?: any;
    isActive?: boolean;

    items?: {
        title: string;
        href: string;
    }[];
}

interface Role {
    id?: number;
    name: string;
}

interface AuthUser {
    id: number;
    name: string;
    email: string;
    roles?: Role[];
}

const page = usePage();

const user = computed(
    () => page.props.auth?.user as AuthUser | null,
);

const role = computed(() => {
    return user.value?.roles?.[0]?.name ?? 'driver';
});

const isAdmin = computed(
    () => role.value === 'admin',
);

/*
|--------------------------------------------------------------------------
| Menú del administrador
|--------------------------------------------------------------------------
*/

const adminNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutDashboard,
        isActive: true,
    },
    {
        title: 'Reservas',
        href: '/reservations',
        icon: Calendar,
        items: [
            {
                title: 'Nueva Reserva',
                href: '/reservations/create',
            },
            {
                title: 'Historial',
                href: '/reservations',
            },
        ],
    },
    {
        title: 'Espacios',
        href: '/admin/spaces',
        icon: ParkingCircle,
    },
    {
        title: 'Cámaras',
        href: '/admin/cameras',
        icon: Camera,
    },
    {
        title: 'Pagos',
        href: '/admin/payments',
        icon: CreditCard,
    },
    {
        title: 'Servicios',
        href: '/admin/services',
        icon: Wrench,
    },
    {
        title: 'Usuarios',
        href: '/admin/users',
        icon: Users,
    },
    {
        title: 'Auditoría',
        href: '/admin/audit',
        icon: ClipboardList,
    },
];

/*
|--------------------------------------------------------------------------
| Menú del conductor
|--------------------------------------------------------------------------
*/

const driverNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutDashboard,
        isActive: true,
    },
    {
        title: 'Reservas',
        href: '/reservations',
        icon: Calendar,
        items: [
            {
                title: 'Nueva Reserva',
                href: '/reservations/create',
            },
            {
                title: 'Mis Reservas',
                href: '/reservations',
            },
        ],
    },
    {
        title: 'Mis Vehículos',
        href: '/vehicles',
        icon: Car,
    },
];

/*
|--------------------------------------------------------------------------
| Menú visible según rol
|--------------------------------------------------------------------------
*/

const mainNavItems = computed<NavItem[]>(() => {
    return isAdmin.value
        ? adminNavItems
        : driverNavItems;
});

/*
|--------------------------------------------------------------------------
| Pie del menú
|--------------------------------------------------------------------------
*/

const footerNavItems: NavItem[] = [
    {
        title: 'Configuración',
        href: '/settings',
        icon: Settings,
    },
    {
        title: 'Ayuda',
        href: '/help',
        icon: HelpCircle,
    },
];
</script>

<template>
    <Sidebar
        collapsible="icon"
        class="border-r border-sidebar-border bg-sidebar text-sidebar-foreground transition-all duration-300"
    >
        <!-- LOGO -->
        <SidebarHeader
            class="border-b border-sidebar-border px-3 py-4"
        >
            <div
                class="flex items-center overflow-hidden"
            >
                <!-- Logo completo -->
                <img
                    src="/logo-devioz.png"
                    alt="DEVIOZ"
                    class="h-10 w-auto max-w-[160px] object-contain object-left transition-all duration-300 group-data-[collapsible=icon]:hidden"
                />

                <!-- Logo compacto -->
                <img
                    src="/favicon.png"
                    alt="DEVIOZ"
                    class="hidden size-8 shrink-0 object-contain group-data-[collapsible=icon]:block"
                />
            </div>
        </SidebarHeader>

        <!-- MENÚ PRINCIPAL -->
        <SidebarContent class="py-4">
            <NavMain
                :items="mainNavItems"
            />
        </SidebarContent>

        <!-- PIE -->
        <SidebarFooter
            class="border-t border-sidebar-border p-3"
        >
            <NavFooter
                :items="footerNavItems"
            />

            <NavUser />
        </SidebarFooter>

        <slot />
    </Sidebar>
</template>