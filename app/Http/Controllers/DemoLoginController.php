<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class DemoLoginController extends Controller
{
    public function __invoke(string $role): RedirectResponse
    {
        abort_unless(app()->environment('local'), 404);

        $email = match ($role) {
            'admin' => 'admin@devioz.test',
            'driver' => 'driver@devioz.test',
            default => abort(404),
        };

        Auth::login(User::where('email', $email)->firstOrFail());

        return to_route('dashboard');
    }
}
