<script setup lang="ts">
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';

interface Parking { id: number; name: string; yape_qr_path: string | null; }
const props = defineProps<{ parking: Parking; qrUrl: string | null }>();
const qrFile = ref<File | null>(null);
const previewUrl = ref<string | null>(props.qrUrl);
const processing = ref(false);

function selectQr(event: Event): void {
    const file = (event.target as HTMLInputElement).files?.[0] ?? null;
    qrFile.value = file;
    if (file) previewUrl.value = URL.createObjectURL(file);
}

function uploadQr(): void {
    if (!qrFile.value) return;
    const data = new FormData();
    data.append('yape_qr', qrFile.value);
    processing.value = true;
    router.post('/admin/yape-qr', data, {
        forceFormData: true,
        onFinish: () => { processing.value = false; },
    });
}

function removeQr(): void {
    if (window.confirm('¿Eliminar el QR actual de Yape?')) router.delete('/admin/yape-qr');
}
</script>

<template>
    <Head title="QR de Yape" />
    <div class="flex flex-1 flex-col gap-6 p-6">
        <div>
            <p class="text-sm text-muted-foreground">Administrador</p>
            <h1 class="text-2xl font-semibold">QR real de Yape</h1>
            <p class="mt-1 text-sm text-muted-foreground">Configura el código que verán los conductores al pagar.</p>
        </div>

        <section class="grid gap-6 rounded-xl border bg-card p-6 lg:grid-cols-2">
            <div>
                <h2 class="text-lg font-semibold">{{ parking.name }}</h2>
                <p class="mt-2 text-sm text-muted-foreground">Sube una imagen PNG, JPG o WebP del QR de la cuenta oficial de Yape.</p>
                <div class="mt-6 grid gap-3">
                    <label class="grid gap-2 text-sm font-medium">Imagen QR
                        <input type="file" accept="image/png,image/jpeg,image/webp" class="rounded-md border border-slate-300 p-2 text-sm" @change="selectQr" />
                    </label>
                    <p class="text-xs text-muted-foreground">Tamaño máximo: 5 MB. El archivo se guarda en `storage/app/public/qrs/`.</p>
                    <div class="flex gap-2">
                        <Button type="button" :disabled="!qrFile || processing" @click="uploadQr">{{ processing ? 'Guardando...' : 'Guardar QR' }}</Button>
                        <Button v-if="qrUrl" type="button" variant="destructive" @click="removeQr">Eliminar QR</Button>
                    </div>
                </div>
            </div>
            <div class="flex min-h-72 items-center justify-center rounded-xl border-2 border-dashed border-slate-200 bg-slate-50 p-6">
                <img v-if="previewUrl" :src="previewUrl" alt="Vista previa del QR de Yape" class="max-h-64 max-w-full rounded-lg object-contain shadow-sm" />
                <p v-else class="text-center text-sm text-muted-foreground">No hay un QR configurado.<br />El modal mostrará un aviso hasta que subas uno.</p>
            </div>
        </section>
    </div>
</template>
