<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
interface Space { id: number; code: string; floor: number; type: string; status: string; }
interface Parking { id: number; name: string; address: string; spaces: Space[]; }
const props = defineProps<{ parkings: Parking[] }>();
const colors: Record<string, string> = { available: 'border-green-200 bg-green-50 text-green-800', occupied: 'border-red-200 bg-red-50 text-red-800', reserved: 'border-blue-200 bg-blue-50 text-blue-800', maintenance: 'border-slate-300 bg-slate-100 text-slate-700' };
function action(space: Space): void { router.post(`/owner/spaces/${space.id}/${space.status === 'occupied' ? 'checkout' : 'checkin'}`); }
</script>
<template>
    <Head title="Plazas" /><div class="flex flex-1 flex-col gap-6 p-6"><div><p class="text-sm text-muted-foreground">Parking Owner</p><h1 class="text-2xl font-semibold">Mis plazas</h1></div>
    <section v-for="parking in parkings" :key="parking.id" class="rounded-xl border bg-card p-5"><div class="flex flex-wrap justify-between gap-2"><div><h2 class="text-lg font-semibold">{{ parking.name }}</h2><p class="text-sm text-muted-foreground">{{ parking.address }}</p></div><span class="text-sm text-muted-foreground">{{ parking.spaces.length }} plazas</span></div><div class="mt-5 grid grid-cols-3 gap-2 sm:grid-cols-6 md:grid-cols-10 lg:grid-cols-12"><article v-for="space in parking.spaces" :key="space.id" class="rounded-lg border p-2 text-center text-xs" :class="colors[space.status]"><p class="font-semibold">{{ space.code }}</p><p>{{ space.status }}</p><Button v-if="['reserved', 'occupied'].includes(space.status)" size="sm" variant="ghost" class="mt-1 h-6 px-1 text-[10px]" @click="action(space)">{{ space.status === 'occupied' ? 'Salida' : 'Entrada' }}</Button></article></div></section>
    </div>
</template>
