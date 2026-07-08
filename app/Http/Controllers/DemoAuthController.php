<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DemoAuthController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->intended(route('home'));
        }

        if ($request->filled('redirect')) {
            $request->session()->put('url.intended', $request->query('redirect'));
        } else {
            $previousUrl = url()->previous();

            if ($previousUrl !== route('auth.index')) {
                $request->session()->put('url.intended', $previousUrl);
            }
        }

        return view('auth.index');
    }

    public function login(Request $request): RedirectResponse
    {
        $user = User::query()
            ->orderBy('id')
            ->first();

        if (! $user) {
            return back()->with('error', 'Brak użytkownika demo w bazie.');
        }

        Auth::login($user, remember: true);

        $request->session()->regenerate();

        return redirect()->intended(route('home'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}