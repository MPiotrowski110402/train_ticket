<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DemoAuthController extends Controller
{
    public function index(Request $request): View
    {
        $redirect = $request->query('redirect');

        if (! is_string($redirect) || $redirect === '') {
            $redirect = route('home', [], false);
        }

        return view('auth.index', [
            'redirect' => $redirect,
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        $user = User::query()
            ->orderBy('id')
            ->first();

        if (! $user) {
            return back()->with('error', 'Brak użytkownika demo w bazie.');
        }

        /*
         * Normalne logowanie zostawiamy, jeśli przeglądarka pozwala na cookies.
         * W portfolio/iframe i tak oprzemy widok na demo_logged=1 + localStorage.
         */
        Auth::login($user, remember: true);

        $request->session()->regenerate();

        $redirect = $request->input('redirect');

        if (! is_string($redirect) || $redirect === '') {
            $redirect = route('home', [], false);
        }

        $separator = str_contains($redirect, '?') ? '&' : '?';

        return redirect()->to($redirect . $separator . 'demo_logged=1');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}