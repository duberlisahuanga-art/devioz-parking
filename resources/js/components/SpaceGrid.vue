<script setup lang="ts">
interface Space {
    id: number;
    code: string;
    floor: number;
    type: string;
    status: 'available' | 'occupied' | 'reserved' | 'maintenance';
}

const props = defineProps<{ spaces: Space[]; interactive?: boolean; modelValue?: number | null }>();
const emit = defineEmits<{ 'update:modelValue': [value: number] }>();

const colors: Record<Space['status'], string> = {
    available: 'border-emerald-200 bg-emerald-50 text-emerald-800',
    occupied: 'border-red-200 bg-red-50 text-red-800',
    reserved: 'border-amber-200 bg-amber-50 text-amber-800',
    maintenance: 'border-slate-300 bg-slate-100 text-slate-700',
};
const labels: Record<Space['status'], string> = { available: 'Libre', occupied: 'Ocupada', reserved: 'Reservada', maintenance: 'Mantenimiento' };
const availableCount = () => props.spaces.filter((space) => space.status === 'available').length;
</script>

<template>
    <div class="grid grid-cols-5 gap-2 sm:grid-cols-10">
        <button v-for="space in spaces" :key="space.id" type="button" class="rounded-lg border p-2 text-center text-xs transition" :class="[colors[space.status], modelValue === space.id ? 'ring-2 ring-blue-600 ring-offset-1' : '', interactive && space.status === 'available' ? 'cursor-pointer hover:-translate-y-0.5' : 'cursor-default']" :disabled="!interactive || space.status !== 'available'" @click="emit('update:modelValue', space.id)">
            <span class="block font-semibold">{{ space.code }}</span>
            <span class="mt-1 block">{{ labels[space.status] }}</span>
        </button>
    </div>
    <div class="mt-4 flex flex-wrap items-center justify-between gap-3 text-xs text-muted-foreground">
        <span>{{ availableCount() }} de {{ spaces.length }} libres</span>
        <span class="flex flex-wrap gap-3"><span v-for="(label, status) in labels" :key="status" class="flex items-center gap-1"><i class="size-2 rounded-full" :class="status === 'available' ? 'bg-emerald-500' : status === 'occupied' ? 'bg-red-500' : status === 'reserved' ? 'bg-amber-500' : 'bg-slate-500'" />{{ label }}</span></span>
    </div>
</template>
