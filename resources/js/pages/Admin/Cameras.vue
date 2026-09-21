<script setup lang="ts">
import { computed, reactive, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import CameraPlayer from '@/components/CameraPlayer.vue';
import { Button } from '@/components/ui/button';

interface Camera {
    id: number;
    name: string;
    location: string;
    stream_url: string | null;
    source_type: 'rtsp' | 'webcam' | 'demo';
    type: string;
    status: 'online' | 'offline';
    active: boolean;
    last_heartbeat_at: string | null;
    recording_enabled: boolean;
    recording_mode: 'none' | 'manual' | 'continuous' | 'motion' | 'scheduled';
    retention_days: number;
    storage_quota_mb: number;
    resolution: string;
    fps: number;
    schedule_start: string | null;
    schedule_end: string | null;
    flip_horizontal: boolean;
    flip_vertical: boolean;
    motion_detection: boolean;
    ptz_enabled: boolean;
    recordings: Recording[];
}
interface Recording { id: number; path: string; started_at: string; ended_at: string | null; duration_formatted: string; size_mb: string | null; triggered_by: string; status: string; }

const props = defineProps<{ cameras: Camera[] }>();
const editingId = ref<number | null>(null);
const form = reactive({ name: '', location: '', type: 'zona' as Camera['type'], source_type: 'webcam' as Camera['source_type'], stream_url: '', active: true, recording_enabled: false, recording_mode: 'manual' as Camera['recording_mode'], retention_days: 7, storage_quota_mb: 1024, resolution: '640x480', fps: 15, schedule_start: '', schedule_end: '', flip_horizontal: false, flip_vertical: false, motion_detection: false, ptz_enabled: false });
const onlineCount = computed(() => props.cameras.filter((camera) => camera.status === 'online').length);
const sourceLabels: Record<Camera['source_type'], string> = { webcam: 'Webcam local', demo: 'Demo HLS', rtsp: 'RTSP / HLS' };
const sourceClasses: Record<Camera['source_type'], string> = { webcam: 'bg-emerald-100 text-emerald-800', demo: 'bg-amber-100 text-amber-800', rtsp: 'bg-slate-100 text-slate-700' };
const selectedRecording = ref<Recording | null>(null);

function resetForm(): void { editingId.value = null; Object.assign(form, { name: '', location: '', type: 'zona', source_type: 'webcam', stream_url: '', active: true, recording_enabled: false, recording_mode: 'manual', retention_days: 7, storage_quota_mb: 1024, resolution: '640x480', fps: 15, schedule_start: '', schedule_end: '', flip_horizontal: false, flip_vertical: false, motion_detection: false, ptz_enabled: false }); }
function editCamera(camera: Camera): void { editingId.value = camera.id; Object.assign(form, { name: camera.name, location: camera.location, type: camera.type, source_type: camera.source_type, stream_url: camera.stream_url ?? '', active: camera.active, recording_enabled: camera.recording_enabled, recording_mode: camera.recording_mode, retention_days: camera.retention_days, storage_quota_mb: camera.storage_quota_mb, resolution: camera.resolution, fps: camera.fps, schedule_start: camera.schedule_start?.slice(0, 5) ?? '', schedule_end: camera.schedule_end?.slice(0, 5) ?? '', flip_horizontal: camera.flip_horizontal, flip_vertical: camera.flip_vertical, motion_detection: camera.motion_detection, ptz_enabled: camera.ptz_enabled }); }
function saveCamera(): void {
    const data = { ...form, stream_url: form.source_type === 'webcam' ? null : form.stream_url };
    if (editingId.value) router.put(`/admin/cameras/${editingId.value}`, data, { onSuccess: resetForm });
    else router.post('/admin/cameras', data, { onSuccess: resetForm });
}
function removeCamera(id: number): void { if (window.confirm('¿Eliminar esta cámara?')) router.delete(`/admin/cameras/${id}`); }
function refresh(): void { router.reload(); }
function heartbeat(camera: Camera): string { return camera.last_heartbeat_at ? new Date(camera.last_heartbeat_at).toLocaleString('es-PE') : 'Sin heartbeat'; }
function recordingUrl(recording: Recording): string { return `/storage/${recording.path}`; }
function retentionDate(camera: Camera, recording: Recording): string { return recording.ended_at ? new Date(new Date(recording.ended_at).getTime() + camera.retention_days * 86400000).toLocaleDateString('es-PE') : 'al finalizar'; }
function deleteRecording(cameraId: number, recordingId: number): void { if (window.confirm('¿Eliminar esta grabación?')) router.delete(`/admin/cameras/${cameraId}/recordings/${recordingId}`); }
</script>

<template>
    <Head title="Monitoreo CCTV" />
    <div class="flex flex-1 flex-col gap-6 p-6">
        <div class="flex flex-wrap items-end justify-between gap-4"><div><p class="text-sm text-muted-foreground">Administrador</p><h1 class="text-2xl font-semibold">Central de Monitoreo CCTV — Estacionamiento Devioz</h1><p class="mt-1 text-sm text-muted-foreground">{{ cameras.length }} cámaras · {{ onlineCount }} en línea · {{ cameras.length - onlineCount }} offline</p></div><Button type="button" variant="outline" @click="refresh">🔄 Refrescar estados</Button></div>

        <section class="rounded-xl border bg-card p-5"><div class="mb-4 flex items-center justify-between"><div><h2 class="text-lg font-semibold">{{ editingId ? 'Editar cámara' : 'Agregar cámara' }}</h2><p class="text-sm text-muted-foreground">Configuración profesional de videovigilancia.</p></div><Button v-if="editingId" type="button" variant="ghost" @click="resetForm">Cancelar</Button></div><div class="grid gap-6 lg:grid-cols-3"><div class="grid gap-3"><h3 class="font-semibold">General</h3><input v-model="form.name" required class="rounded-md border-slate-300" placeholder="Nombre de cámara" /><input v-model="form.location" required class="rounded-md border-slate-300" placeholder="Ubicación" /><select v-model="form.type" class="rounded-md border-slate-300"><option value="entrada">Entrada</option><option value="salida">Salida</option><option value="zona">Zona</option><option value="caja">Caja</option></select><select v-model="form.source_type" class="rounded-md border-slate-300"><option value="webcam">Webcam local</option><option value="rtsp">RTSP / HLS</option><option value="demo">Demo HLS</option></select><input v-if="form.source_type !== 'webcam'" v-model="form.stream_url" required type="url" class="rounded-md border-slate-300" placeholder="https://...m3u8" /><label class="flex items-center gap-2 text-sm"><input v-model="form.active" type="checkbox" class="rounded border-slate-300" /> Activa</label></div><div class="grid content-start gap-3"><h3 class="font-semibold">Grabación</h3><label class="flex items-center gap-2 text-sm"><input v-model="form.recording_enabled" type="checkbox" class="rounded border-slate-300" /> Grabación habilitada</label><select v-model="form.recording_mode" class="rounded-md border-slate-300"><option value="none">Ninguna</option><option value="manual">Manual</option><option value="continuous">Continua</option><option value="motion">Por movimiento</option><option value="scheduled">Programada</option></select><div v-if="form.recording_mode === 'scheduled'" class="grid grid-cols-2 gap-2"><input v-model="form.schedule_start" type="time" class="rounded-md border-slate-300" /><input v-model="form.schedule_end" type="time" class="rounded-md border-slate-300" /></div><label class="grid gap-1 text-sm">Retención: {{ form.retention_days }} días<input v-model.number="form.retention_days" type="range" min="1" max="30" /><span class="text-xs text-muted-foreground">Ley 29733: retención recomendada ≤ 30 días</span></label><input v-model.number="form.storage_quota_mb" type="number" min="1" class="rounded-md border-slate-300" placeholder="Cuota MB" /><select v-model="form.resolution" class="rounded-md border-slate-300"><option value="640x480">640x480</option><option value="1280x720">1280x720</option><option value="1920x1080">1920x1080</option></select><select v-model.number="form.fps" class="rounded-md border-slate-300"><option :value="15">15 FPS</option><option :value="24">24 FPS</option><option :value="30">30 FPS</option></select></div><div class="grid content-start gap-3"><h3 class="font-semibold">Avanzado</h3><label class="flex items-center gap-2 text-sm"><input v-model="form.flip_horizontal" type="checkbox" class="rounded border-slate-300" /> Voltear horizontal</label><label class="flex items-center gap-2 text-sm"><input v-model="form.flip_vertical" type="checkbox" class="rounded border-slate-300" /> Voltear vertical</label><label class="flex items-center gap-2 text-sm"><input v-model="form.motion_detection" type="checkbox" class="rounded border-slate-300" /> Detección de movimiento <span class="text-xs text-muted-foreground">(simulado en MVP)</span></label><label class="flex items-center gap-2 text-sm opacity-60"><input v-model="form.ptz_enabled" disabled type="checkbox" class="rounded border-slate-300" /> PTZ <span class="text-xs">Requiere ONVIF (producción)</span></label></div></div><Button type="button" class="mt-5" @click="saveCamera">{{ editingId ? 'Guardar cambios' : 'Agregar cámara' }}</Button></section>

        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-3">
            <article v-for="camera in cameras" :key="camera.id" class="overflow-hidden rounded-xl border bg-card"><CameraPlayer :camera="camera" @recording-saved="refresh" /><div class="space-y-3 p-4"><div class="flex items-start justify-between gap-3"><div><h2 class="font-semibold">{{ camera.name }}</h2><p class="text-sm text-muted-foreground">{{ camera.location }}</p></div><span class="rounded-full px-2 py-1 text-xs font-semibold" :class="camera.status === 'online' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700'">{{ camera.status === 'online' ? 'Online' : 'Offline' }}</span></div><div class="flex flex-wrap items-center justify-between gap-2 text-xs text-muted-foreground"><span class="rounded-full px-2 py-1 font-medium" :class="sourceClasses[camera.source_type]">{{ sourceLabels[camera.source_type] }}</span><span>Heartbeat: {{ heartbeat(camera) }}</span></div><div class="flex gap-2"><Button size="sm" variant="outline" @click="editCamera(camera)">Editar</Button><Button size="sm" variant="destructive" @click="removeCamera(camera.id)">Eliminar</Button></div><details class="border-t pt-3"><summary class="cursor-pointer text-sm font-semibold">Grabaciones ({{ camera.recordings.length }})</summary><div v-if="camera.recordings.length" class="mt-3 overflow-x-auto"><table class="w-full text-left text-xs"><thead><tr><th class="p-2">Fecha</th><th class="p-2">Duración</th><th class="p-2">Tamaño</th><th class="p-2">Acciones</th></tr></thead><tbody><tr v-for="recording in camera.recordings" :key="recording.id" class="border-t"><td class="p-2">{{ new Date(recording.started_at).toLocaleString('es-PE') }}</td><td class="p-2">{{ recording.duration_formatted }}</td><td class="p-2">{{ recording.size_mb ?? '0.00' }} MB</td><td class="p-2"><div class="flex gap-2"><button type="button" class="text-blue-700 underline" @click="selectedRecording = recording">▶</button><a class="text-blue-700 underline" :href="recordingUrl(recording)" download>⬇</a><button type="button" class="text-red-700 underline" @click="deleteRecording(camera.id, recording.id)">🗑</button></div></td></tr></tbody></table><p class="mt-2 text-xs text-muted-foreground">Se purgará automáticamente el {{ retentionDate(camera, camera.recordings[0]) }}.</p></div><p v-else class="mt-3 text-xs text-muted-foreground">No hay grabaciones.</p></details><div class="flex gap-1 pt-2"><Button v-for="direction in ['↑', '↓', '←', '→', '+', '-']" :key="direction" size="sm" variant="outline" disabled :title="'Disponible con cámaras ONVIF en producción'">{{ direction }}</Button></div></div></article>
        </div>

        <p class="border-t pt-5 text-xs leading-relaxed text-muted-foreground">Videovigilancia conforme a la Ley 29733 de protección de datos personales (Perú). Las imágenes se usan exclusivamente para seguridad del estacionamiento.</p>
    </div>
    <div v-if="selectedRecording" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 p-4" @click.self="selectedRecording = null"><div class="w-full max-w-3xl rounded-xl bg-slate-950 p-4"><div class="mb-3 flex justify-end"><button type="button" class="text-white" aria-label="Cerrar" @click="selectedRecording = null">✕</button></div><video class="w-full" controls autoplay :src="recordingUrl(selectedRecording)" /></div></div>
</template>
