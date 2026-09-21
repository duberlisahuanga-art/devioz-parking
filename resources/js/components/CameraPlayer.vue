<script setup lang="ts">
import Hls from 'hls.js';
import { computed, onMounted, onUnmounted, ref } from 'vue';

interface Camera {
    id: number;
    name: string;
    location: string;
    stream_url: string | null;
    source_type: 'rtsp' | 'webcam' | 'demo';
    status: 'online' | 'offline';
    recording_enabled: boolean;
    flip_horizontal: boolean;
    flip_vertical: boolean;
}

const props = defineProps<{ camera: Camera }>();
const videoEl = ref<HTMLVideoElement | null>(null);
const canvasEl = ref<HTMLCanvasElement | null>(null);
const currentTime = ref('');
const webcamError = ref(false);
const corsError = ref(false);
const isRecording = ref(false);
const recordingSeconds = ref(0);
const zoom = ref(1);
let localStream: MediaStream | null = null;
let hls: Hls | null = null;
let clock: number | undefined;
let recorder: MediaRecorder | null = null;
let recordingStartedAt: Date | null = null;
let recordingTimer: number | undefined;
let recordingChunks: Blob[] = [];

const emit = defineEmits<{ recordingSaved: [] }>();
const videoTransform = computed(() => ({ transform: `scale(${zoom.value}) scaleX(${props.camera.flip_horizontal ? -1 : 1}) scaleY(${props.camera.flip_vertical ? -1 : 1})` }));
const canRecord = computed(() => props.camera.recording_enabled && (props.camera.source_type === 'webcam' || props.camera.source_type === 'demo'));
const recordingLabel = computed(() => `${String(Math.floor(recordingSeconds.value / 60)).padStart(2, '0')}:${String(recordingSeconds.value % 60).padStart(2, '0')}`);

function updateClock(): void {
    currentTime.value = new Intl.DateTimeFormat('es-PE', { dateStyle: 'short', timeStyle: 'medium' }).format(new Date());
}

async function startWebcam(): Promise<void> {
    if (!navigator.mediaDevices?.getUserMedia || !videoEl.value) {
        webcamError.value = true;
        return;
    }

    try {
        localStream = await navigator.mediaDevices.getUserMedia({ video: { width: 640, height: 480, facingMode: 'environment' }, audio: false });
        videoEl.value.srcObject = localStream;
    } catch {
        webcamError.value = true;
    }
}

function startHls(): void {
    if (!videoEl.value || !props.camera.stream_url) return;
    if (Hls.isSupported()) {
        hls = new Hls();
        hls.loadSource(props.camera.stream_url);
        hls.attachMedia(videoEl.value);
    } else if (videoEl.value.canPlayType('application/vnd.apple.mpegurl')) {
        videoEl.value.src = props.camera.stream_url;
    }
}

function capture(): void {
    if (!videoEl.value || !canvasEl.value) return;
    canvasEl.value.width = videoEl.value.videoWidth || 640;
    canvasEl.value.height = videoEl.value.videoHeight || 480;
    canvasEl.value.getContext('2d')?.drawImage(videoEl.value, 0, 0, canvasEl.value.width, canvasEl.value.height);
    const link = document.createElement('a');
    const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
    link.download = `captura-${props.camera.name.replace(/[^a-z0-9]+/gi, '-').toLowerCase()}-${timestamp}.png`;
    link.href = canvasEl.value.toDataURL('image/png');
    link.click();
}

function fullscreen(): void {
    void videoEl.value?.requestFullscreen();
}

function csrfToken(): string {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
}

function startRecording(): void {
    if (!canRecord.value || !videoEl.value?.captureStream) {
        if (props.camera.source_type === 'demo') corsError.value = true;
        return;
    }

    try {
        recordingChunks = [];
        recordingStartedAt = new Date();
        recorder = new MediaRecorder(videoEl.value.captureStream(), { mimeType: MediaRecorder.isTypeSupported('video/webm;codecs=vp9') ? 'video/webm;codecs=vp9' : 'video/webm' });
        recorder.ondataavailable = (event) => { if (event.data.size) recordingChunks.push(event.data); };
        recorder.onstop = () => { void uploadRecording(); };
        recorder.start(1000);
        isRecording.value = true;
        recordingSeconds.value = 0;
        recordingTimer = window.setInterval(() => { recordingSeconds.value = recordingStartedAt ? Math.floor((Date.now() - recordingStartedAt.getTime()) / 1000) : 0; }, 1000);
    } catch {
        corsError.value = props.camera.source_type === 'demo';
    }
}

function stopRecording(): void {
    if (!recorder || !isRecording.value) return;
    recorder.stop();
    isRecording.value = false;
    if (recordingTimer) window.clearInterval(recordingTimer);
}

async function uploadRecording(): Promise<void> {
    const startedAt = recordingStartedAt ?? new Date();
    const blob = new Blob(recordingChunks, { type: 'video/webm' });
    const data = new FormData();
    data.append('recording', blob, `${props.camera.id}-${Date.now()}.webm`);
    data.append('started_at', startedAt.toISOString());
    data.append('duration_sec', String(Math.max(1, recordingSeconds.value)));
    const response = await fetch(`/admin/cameras/${props.camera.id}/recordings`, { method: 'POST', body: data, headers: { 'X-CSRF-TOKEN': csrfToken(), Accept: 'application/json' } });
    if (response.ok) emit('recordingSaved');
}

onMounted(() => {
    updateClock();
    clock = window.setInterval(updateClock, 1000);
    if (props.camera.source_type === 'webcam') startWebcam();
    if (props.camera.source_type !== 'webcam') startHls();
});

onUnmounted(() => {
    if (clock) window.clearInterval(clock);
    if (recordingTimer) window.clearInterval(recordingTimer);
    hls?.destroy();
    localStream?.getTracks().forEach((track) => track.stop());
});
</script>

<template>
    <div class="relative aspect-video overflow-hidden bg-slate-950">
        <video v-if="camera.source_type !== 'rtsp' || camera.stream_url" ref="videoEl" autoplay playsinline muted :controls="false" class="size-full object-cover" :style="videoTransform" />
        <div v-if="camera.source_type === 'webcam' && !webcamError" class="absolute left-3 top-3 rounded-full bg-emerald-500 px-3 py-1 text-xs font-bold text-white shadow-lg"><span class="mr-1 inline-block animate-pulse">●</span>📷 WEBCAM EN VIVO</div>
        <div v-if="camera.source_type === 'demo'" class="absolute left-3 top-3 rounded-full bg-amber-400 px-3 py-1 text-xs font-bold text-amber-950 shadow-lg">DEMO TÉCNICA</div>
        <div v-if="isRecording" class="absolute right-3 top-3 rounded-full bg-red-600 px-3 py-1 text-xs font-bold text-white shadow-lg"><span class="mr-1 animate-pulse">●</span>REC {{ recordingLabel }}</div>
        <div v-if="camera.source_type === 'webcam' && webcamError" class="flex size-full flex-col items-center justify-center gap-3 p-6 text-center text-slate-200"><span class="text-3xl">🔒</span><p class="font-medium">Permiso de cámara denegado.</p><p class="max-w-sm text-xs text-slate-400">Ve a Configuración del navegador → Permisos → Cámara → Permitir para localhost:8000</p></div>
        <div v-if="corsError" class="absolute inset-x-3 top-14 rounded-lg bg-red-950/90 p-3 text-xs text-red-100">La grabación de este stream demo está bloqueada por CORS. Usa Webcam local para grabar.</div>
        <div v-if="camera.source_type === 'rtsp' && !camera.stream_url" class="flex size-full flex-col items-center justify-center gap-3 p-6 text-center text-slate-300"><span class="text-5xl">📹</span><p class="font-semibold">Stream RTSP no configurado</p><p class="text-xs text-slate-400">Conectar cámara IP → MediaMTX → URL HLS en producción</p></div>
        <div v-if="camera.source_type !== 'rtsp' || camera.stream_url" class="absolute inset-x-0 bottom-0 flex items-end justify-between gap-3 bg-gradient-to-t from-slate-950/90 to-transparent px-4 pb-3 pt-10 text-white"><div><p class="font-semibold">{{ camera.name }}</p><p class="text-xs text-slate-300">{{ camera.location }} · {{ currentTime }}</p></div><div class="flex flex-wrap justify-end gap-2"><button type="button" class="rounded bg-white/15 px-2 py-1 text-xs hover:bg-white/25" title="Zoom digital" @click="zoom = zoom >= 4 ? 1 : zoom + 1">🔍 {{ zoom }}x</button><button type="button" class="rounded bg-white/15 px-2 py-1 text-xs hover:bg-white/25" title="Descargar captura" @click="capture">📸</button><button v-if="!isRecording" type="button" class="rounded bg-red-600 px-2 py-1 text-xs disabled:cursor-not-allowed disabled:opacity-40" :disabled="!canRecord" title="Activa la grabación en la edición de la cámara" @click="startRecording">⏺ Grabar</button><button v-else type="button" class="rounded bg-red-700 px-2 py-1 text-xs" @click="stopRecording">⏹ Detener</button><button type="button" class="rounded bg-white/15 px-2 py-1 text-xs hover:bg-white/25" title="Pantalla completa" @click="fullscreen">⛶</button></div></div>
    <canvas ref="canvasEl" class="hidden" />
    </div>
</template>
