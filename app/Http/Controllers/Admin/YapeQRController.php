<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Parking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class YapeQRController extends Controller
{
    public function edit(): Response
    {
        $parking = Parking::where('slug', 'estacionamiento-devioz')->firstOrFail();

        return Inertia::render('Admin/YapeQR', [
            'parking' => $parking->only(['id', 'name', 'yape_qr_path']),
            'qrUrl' => $parking->yape_qr_path ? Storage::disk('public')->url($parking->yape_qr_path) : null,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $parking = Parking::where('slug', 'estacionamiento-devioz')->firstOrFail();
        $data = $request->validate([
            'yape_qr' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ], [
            'yape_qr.required' => 'Selecciona una imagen QR.',
            'yape_qr.image' => 'El QR debe ser una imagen válida.',
            'yape_qr.max' => 'La imagen no puede superar 5 MB.',
        ]);

        if ($parking->yape_qr_path) {
            Storage::disk('public')->delete($parking->yape_qr_path);
        }

        $parking->update([
            'yape_qr_path' => $data['yape_qr']->store('qrs', 'public'),
        ]);

        return back()->with('toast', ['type' => 'success', 'message' => 'QR de Yape actualizado.']);
    }

    public function destroy(): RedirectResponse
    {
        $parking = Parking::where('slug', 'estacionamiento-devioz')->firstOrFail();

        if ($parking->yape_qr_path) {
            Storage::disk('public')->delete($parking->yape_qr_path);
            $parking->update(['yape_qr_path' => null]);
        }

        return back()->with('toast', ['type' => 'success', 'message' => 'QR de Yape eliminado.']);
    }
}