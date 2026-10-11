<?php

namespace App\Http\Controllers;

use App\Services\AuditTrailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Tampilkan form profil pengguna
     */
    public function edit(Request $request): View
    {
        return view('profile.index', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update profil nama dan telepon
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
        ]);

        $oldValues = ['name' => $user->name, 'phone' => $user->phone];
        $user->update($validated);

        AuditTrailService::log('update', $user, $oldValues, $validated, 'User updated profile details');

        return back()->with('success', 'Profil berhasil diperbarui!');
    }

    /**
     * Update kata sandi pengguna
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        AuditTrailService::log('update', $user, null, null, 'User changed password');

        return back()->with('success', 'Kata sandi berhasil diubah!');
    }
}
