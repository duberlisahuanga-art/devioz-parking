<?php

namespace App\Http\Controllers;

use App\Models\Camera;
use App\Models\Recording;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CameraController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Cameras', [
            'cameras' => Camera::with('recordings')->orderBy('id')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Camera::create($this->validated($request));

        return back()->with('toast', ['type' => 'success', 'message' => 'Cámara agregada.']);
    }

    public function update(Request $request, Camera $camera): RedirectResponse
    {
        $camera->update($this->validated($request));

        return back()->with('toast', ['type' => 'success', 'message' => 'Cámara actualizada.']);
    }

    public function destroy(Camera $camera): RedirectResponse
    {
        $camera->delete();

        return back()->with('toast', ['type' => 'success', 'message' => 'Cámara eliminada.']);
    }

    public function storeRecording(Request $request, Camera $camera): \Symfony\Component\HttpFoundation\Response
    {
        abort_unless($camera->recording_enabled, 422, 'Activa la grabación en la edición de la cámara.');
        $data = $request->validate([
            'recording' => ['required', 'file', 'mimetypes:video/webm,video/webm;codecs=vp8,video/webm;codecs=vp9', 'max:512000'],
            'started_at' => ['required', 'date'],
            'duration_sec' => ['required', 'integer', 'min:1'],
        ]);

        $path = $request->file('recording')->store("recordings/{$camera->id}", 'public');
        $sizeMb = round($request->file('recording')->getSize() / 1024 / 1024, 2);
        $startedAt = now()->parse($data['started_at']);

        $camera->recordings()->create([
            'user_id' => $request->user()->id,
            'path' => $path,
            'triggered_by' => 'manual',
            'started_at' => $startedAt,
            'ended_at' => $startedAt->copy()->addSeconds($data['duration_sec']),
            'duration_sec' => $data['duration_sec'],
            'size_mb' => $sizeMb,
            'status' => 'completed',
        ]);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Grabación guardada.'], 201);
        }

        return back()->with('toast', ['type' => 'success', 'message' => 'Grabación guardada.']);
    }

    public function destroyRecording(Camera $camera, Recording $recording): RedirectResponse
    {
        abort_unless($recording->camera_id === $camera->id, 404);
        Storage::disk('public')->delete($recording->path);
        $recording->delete();

        return back()->with('toast', ['type' => 'success', 'message' => 'Grabación eliminada.']);
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'location' => ['required', 'string', 'max:180'],
            'source_type' => ['required', 'in:webcam,rtsp,demo'],
            'stream_url' => ['nullable', 'url', 'max:500', 'required_if:source_type,rtsp,demo'],
            'active' => ['boolean'],
            'type' => ['required', 'in:entrada,salida,zona,caja'],
            'recording_enabled' => ['boolean'],
            'recording_mode' => ['required', 'in:none,manual,continuous,motion,scheduled'],
            'retention_days' => ['required', 'integer', 'between:1,30'],
            'storage_quota_mb' => ['required', 'integer', 'min:1'],
            'resolution' => ['required', 'in:640x480,1280x720,1920x1080'],
            'fps' => ['required', 'integer', 'in:15,24,30'],
            'schedule_start' => ['nullable', 'date_format:H:i'],
            'schedule_end' => ['nullable', 'date_format:H:i'],
            'flip_horizontal' => ['boolean'],
            'flip_vertical' => ['boolean'],
            'motion_detection' => ['boolean'],
            'ptz_enabled' => ['boolean'],
        ]);

        return [
            ...$data,
            'stream_url' => in_array($data['source_type'], ['rtsp', 'demo'], true) ? ($data['stream_url'] ?? null) : null,
            'type' => $data['type'],
            'status' => $data['source_type'] === 'rtsp' && empty($data['stream_url']) ? 'offline' : 'online',
            'active' => $request->boolean('active'),
            'last_heartbeat_at' => $data['source_type'] === 'rtsp' && empty($data['stream_url']) ? null : now(),
            'recording_enabled' => $request->boolean('recording_enabled'),
            'flip_horizontal' => $request->boolean('flip_horizontal'),
            'flip_vertical' => $request->boolean('flip_vertical'),
            'motion_detection' => $request->boolean('motion_detection'),
            'ptz_enabled' => $request->boolean('ptz_enabled'),
        ];
    }
}