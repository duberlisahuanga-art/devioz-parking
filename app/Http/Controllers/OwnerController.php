<?php

namespace App\Http\Controllers;

use App\Models\Space;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OwnerController extends Controller
{
    public function spaces(Request $request): Response
    {
        return Inertia::render('Owner/Spaces', [
            'parkings' => $request->user()->parkings()->with('spaces')->get(),
        ]);
    }

    public function manualCheckIn(Request $request, Space $space): RedirectResponse
    {
        $this->ensureSpaceBelongsToOwner($request, $space);
        $space->update(['status' => 'occupied']);

        return back()->with('toast', ['type' => 'success', 'message' => 'Check-in manual registrado.']);
    }

    public function manualCheckOut(Request $request, Space $space): RedirectResponse
    {
        $this->ensureSpaceBelongsToOwner($request, $space);
        $space->update(['status' => 'available', 'current_reservation_id' => null]);

        return back()->with('toast', ['type' => 'success', 'message' => 'Check-out manual registrado.']);
    }

    private function ensureSpaceBelongsToOwner(Request $request, Space $space): void
    {
        abort_unless($request->user()->parkings()->whereKey($space->parking_id)->exists(), 403);
    }
}
