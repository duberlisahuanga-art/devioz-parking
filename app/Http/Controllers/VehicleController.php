<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VehicleController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Vehicles/Index', [
            'vehicles' => $request->user()->vehicles()->latest()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'plate' => ['required', 'string', 'regex:/^[A-Z]{3}-?\d{3}$/'],
            'brand' => ['nullable', 'string', 'max:100'],
            'model' => ['nullable', 'string', 'max:100'],
            'color' => ['nullable', 'string', 'max:50'],
            'type' => ['required', 'in:car,moto,ev'],
        ], ['plate.regex' => 'Formato de placa peruana inválido (ej. ABC-123)']);

        $data['plate'] = strtoupper(str_replace('-', '', $data['plate']));
        $data['plate'] = substr($data['plate'], 0, 3).'-'.substr($data['plate'], 3);

        if ($request->user()->vehicles()->where('plate', $data['plate'])->exists()) {
            return back()->withErrors(['plate' => 'Ya tienes un vehículo con esta placa.']);
        }

        $request->user()->vehicles()->create($data);

        return back()->with('toast', ['type' => 'success', 'message' => 'Vehículo agregado.']);
    }

    public function destroy(Request $request, int $vehicle): RedirectResponse
    {
        $request->user()->vehicles()->whereKey($vehicle)->firstOrFail()->delete();

        return back()->with('toast', ['type' => 'success', 'message' => 'Vehículo eliminado.']);
    }
}
