<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuditTrailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Tampilkan halaman login
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Proses autentikasi pengguna
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Hancurkan sesi autentikasi (Logout)
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user) {
            AuditTrailService::log('logout', $user, null, null, 'User logged out');
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
