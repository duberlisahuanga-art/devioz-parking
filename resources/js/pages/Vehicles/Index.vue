<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import InputError from '@/components/InputError.vue';

interface Vehicle { id: number; plate: string; brand: string | null; model: string | null; color: string | null; type: string; }
const props = defineProps<{ vehicles: Vehicle[]; errors?: Record<string, string> }>();
const form = { plate: '', brand: '', model: '', color: '', type: 'car' };
function submit(): void { router.post('/vehicles', form, { onSuccess: () => { form.plate = ''; form.brand = ''; form.model = ''; form.color = ''; } }); }
function remove(id: number): void { router.delete(`/vehicles/${id}`); }
</script>

<template>
    <Head title="Mis vehículos" />
    <div class="flex flex-1 flex-col gap-6 p-6">
        <div><p class="text-sm text-muted-foreground">Conductor</p><h1 class="text-2xl font-semibold">Mis vehículos</h1></div>
        <div class="grid gap-6 lg:grid-cols-[360px_1fr]">
            <section class="h-fit rounded-xl border bg-card p-5"><h2 class="text-lg font-semibold">Agregar vehículo</h2><div class="mt-4 grid gap-3">
                <label class="grid gap-1 text-sm font-medium">Placa<input v-model="form.plate" class="rounded-md border-slate-300" placeholder="ABC-123" /></label>
                <InputError :message="errors?.plate" />
                <label class="grid gap-1 text-sm font-medium">Marca<input v-model="form.brand" class="rounded-md border-slate-300" placeholder="Toyota" /></label>
                <label class="grid gap-1 text-sm font-medium">Modelo<input v-model="form.model" class="rounded-md border-slate-300" placeholder="Corolla" /></label>
                <label class="grid gap-1 text-sm font-medium">Color<input v-model="form.color" class="rounded-md border-slate-300" placeholder="Blanco" /></label>
                <label class="grid gap-1 text-sm font-medium">Tipo<select v-model="form.type" class="rounded-md border-slate-300"><option value="car">Auto</option><option value="moto">Moto</option><option value="ev">Eléctrico</option></select></label>
                <Button type="button" @click="submit">Agregar vehículo</Button>
            </div></section>
            <section class="rounded-xl border bg-card p-5"><h2 class="text-lg font-semibold">Vehículos registrados</h2><div v-if="!vehicles.length" class="mt-6 text-sm text-muted-foreground">No tienes vehículos registrados.</div><div v-else class="mt-4 grid gap-3 sm:grid-cols-2">
                <article v-for="vehicle in vehicles" :key="vehicle.id" class="flex items-center justify-between rounded-lg border p-4"><div><p class="font-semibold">{{ vehicle.plate }}</p><p class="text-sm text-muted-foreground">{{ vehicle.brand }} {{ vehicle.model }} · {{ vehicle.color }}</p></div><Button size="sm" variant="destructive" @click="remove(vehicle.id)">Eliminar</Button></article>
            </div></section>
        </div>
    </div>
</template>
