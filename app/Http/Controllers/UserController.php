<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuditTrailService;
use App\Services\DeletionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Tampilkan Daftar Pengguna / PIC
     */
    public function index(Request $request): View
    {
        $companyId = Auth::user()->company_id;

        $query = User::where('company_id', $companyId)
            ->with('roles')
            ->orderBy('name');

        if ($request->filled('role')) {
            $query->role($request->role);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(15)->withQueryString();
        $roles = Role::orderBy('name')->get();

        return view('admin.users.index', compact('users', 'roles'));
    }

    /**
     * Form Tambah Pengguna Baru
     */
    public function create(): View
    {
        $roles = Role::orderBy('name')->get();

        return view('admin.users.create', compact('roles'));
    }

    /**
     * Simpan Pengguna Baru
     */
    public function store(Request $request): RedirectResponse
    {
        $companyId = Auth::user()->company_id;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'string', 'email', 'max:150', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'role' => ['required', 'exists:roles,name'],
            'password' => ['required', Password::min(8)],
        ]);

        $user = User::create([
            'company_id' => $companyId,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'is_active' => true,
        ]);

        $user->assignRole($validated['role']);

        AuditTrailService::log(
            'create',
            $user,
            null,
            $user->toArray(),
            "Menambahkan pengguna / PIC baru: {$user->name} ({$user->email}) dengan peran {$validated['role']}"
        );

        return redirect()->route('admin.users.show', $user->id)
            ->with('success', "Pengguna \"{$user->name}\" berhasil ditambahkan.");
    }

    /**
     * Tampilkan Detail Pengguna
     */
    public function show(int $id): View
    {
        $companyId = Auth::user()->company_id;

        $targetUser = User::where('company_id', $companyId)
            ->with(['roles', 'auditLogs' => function ($q) {
                $q->orderBy('id', 'desc')->take(10);
            }])
            ->findOrFail($id);

        return view('admin.users.show', compact('targetUser'));
    }

    /**
     * Form Edit Pengguna
     */
    public function edit(int $id): View
    {
        $companyId = Auth::user()->company_id;
        $targetUser = User::where('company_id', $companyId)->with('roles')->findOrFail($id);
        $roles = Role::orderBy('name')->get();

        return view('admin.users.edit', compact('targetUser', 'roles'));
    }

    /**
     * Update Pengguna
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $companyId = Auth::user()->company_id;
        $targetUser = User::where('company_id', $companyId)->findOrFail($id);
        $oldData = $targetUser->toArray();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'string', 'email', 'max:150', 'unique:users,email,'.$id],
            'phone' => ['nullable', 'string', 'max:50'],
            'role' => ['required', 'exists:roles,name'],
            'password' => ['nullable', Password::min(8)],
            'is_active' => ['boolean'],
        ]);

        $targetUser->name = $validated['name'];
        $targetUser->email = $validated['email'];
        $targetUser->phone = $validated['phone'];
        $targetUser->is_active = $request->boolean('is_active');

        if (! empty($validated['password'])) {
            $targetUser->password = Hash::make($validated['password']);
        }

        $targetUser->save();
        $targetUser->syncRoles([$validated['role']]);

        AuditTrailService::log(
            'update',
            $targetUser,
            $oldData,
            $targetUser->toArray(),
            "Memperbarui data pengguna / PIC: {$targetUser->name}"
        );

        return redirect()->route('admin.users.show', $targetUser->id)
            ->with('success', "Data pengguna \"{$targetUser->name}\" berhasil diperbarui.");
    }

    /**
     * Hapus Pengguna (Approval Super Admin)
     */
    public function destroy(Request $request, int $id, DeletionService $deletionService): RedirectResponse
    {
        $companyId = Auth::user()->company_id;
        $targetUser = User::where('company_id', $companyId)->findOrFail($id);

        if ($targetUser->id === Auth::id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $result = $deletionService->requestOrDelete(
            Auth::user(),
            $targetUser,
            "Pengguna / PIC: {$targetUser->name} ({$targetUser->email})",
            $request->input('reason')
        );

        if ($result['status'] === 'deleted') {
            return redirect()->route('admin.users.index')->with('success', $result['message']);
        }

        return back()->with('info', $result['message']);
    }
}
