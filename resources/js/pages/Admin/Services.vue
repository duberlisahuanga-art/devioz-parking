<script setup lang="ts">
import { reactive } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';

interface Service { id: number; name: string; description: string | null; price: string; duration_min: number; active: boolean; }
const props = defineProps<{ services: Service[] }>();
const form = reactive({ name: '', description: '', price: '', duration_min: 30, active: true });

function createService(): void {
    router.post('/admin/services', form, { onSuccess: () => { form.name = ''; form.description = ''; form.price = ''; form.duration_min = 30; form.active = true; } });
}
function toggleService(service: Service): void { router.put(`/admin/services/${service.id}`, { ...service, active: !service.active }); }
function removeService(id: number): void { router.delete(`/admin/services/${id}`); }
</script>

<template>
    <Head title="Servicios y promociones" />
    <div class="flex flex-1 flex-col gap-6 p-6">
        <div><p class="text-sm text-muted-foreground">Administrador</p><h1 class="text-2xl font-semibold">Servicios y promociones</h1></div>
        <div class="grid gap-6 lg:grid-cols-[360px_1fr]">
            <section class="h-fit rounded-xl border bg-card p-5"><h2 class="text-lg font-semibold">Nuevo servicio</h2><div class="mt-4 grid gap-3">
                <label class="grid gap-1 text-sm font-medium">Nombre<input v-model="form.name" required class="rounded-md border-slate-300" placeholder="Lavado express" /></label>
                <label class="grid gap-1 text-sm font-medium">Descripción<textarea v-model="form.description" class="rounded-md border-slate-300" rows="3" /></label>
                <label class="grid gap-1 text-sm font-medium">Precio PEN<input v-model="form.price" required type="number" min="0" step="0.01" class="rounded-md border-slate-300" /></label>
                <label class="grid gap-1 text-sm font-medium">Duración (minutos)<input v-model.number="form.duration_min" required type="number" min="1" class="rounded-md border-slate-300" /></label>
                <label class="flex items-center gap-2 text-sm"><input v-model="form.active" type="checkbox" class="rounded border-slate-300" /> Activo</label>
                <Button type="button" @click="createService">Crear servicio</Button>
            </div></section>
            <section class="rounded-xl border bg-card p-5"><h2 class="text-lg font-semibold">Servicios registrados</h2><div v-if="!props.services.length" class="mt-6 text-sm text-muted-foreground">No hay servicios registrados.</div><div v-else class="mt-4 grid gap-3">
                <article v-for="service in props.services" :key="service.id" class="flex flex-wrap items-center justify-between gap-4 rounded-lg border p-4"><div><p class="font-semibold">{{ service.name }} <span class="ml-2 rounded-full px-2 py-1 text-xs" :class="service.active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600'">{{ service.active ? 'Activo' : 'Inactivo' }}</span></p><p class="mt-1 text-sm text-muted-foreground">{{ service.description || 'Sin descripción' }} · {{ service.duration_min }} min</p></div><div class="flex items-center gap-3"><strong>S/ {{ Number(service.price).toFixed(2) }}</strong><Button size="sm" variant="outline" @click="toggleService(service)">{{ service.active ? 'Desactivar' : 'Activar' }}</Button><Button size="sm" variant="destructive" @click="removeService(service.id)">Eliminar</Button></div></article>
            </div></section>
        </div>
    </div>
</template>
