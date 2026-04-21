<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response;

class RegisterController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('Auth/Register');
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var array<string, mixed> $validated */
        $validated = Validator::make(
            $request->only(['name', 'email', 'password', 'password_confirmation']),
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'unique:users'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ]
        )->validate();

        /** @var User $user */
        $user = User::create($validated);

        Auth::login($user);

        return redirect('/dashboard');
    }
}
