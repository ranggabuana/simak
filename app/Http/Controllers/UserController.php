<?php

namespace App\Http\Controllers;

use App\Models\RoleMenuPermission;
use App\Models\User;
use App\Models\WilayahKecamatan;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display user management and role menu permissions matrix.
     */
    public function index(Request $request): View
    {
        $tab = $request->query('tab', 'users');
        $search = $request->query('search');
        $roleFilter = $request->query('role');

        $usersQuery = User::with('kecamatan')->orderBy('name', 'asc');

        if ($search) {
            $usersQuery->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('nip', 'like', "%{$search}%")
                    ->orWhere('jabatan', 'like', "%{$search}%");
            });
        }

        if ($roleFilter) {
            $usersQuery->where('role', $roleFilter);
        }

        $users = $usersQuery->paginate(15)->withQueryString();

        $kecamatanList = WilayahKecamatan::orderBy('nama', 'asc')->get();
        $roles = RoleMenuPermission::getRoles();
        $menuDefinitions = RoleMenuPermission::getMenuDefinitions();

        // Current assigned permissions for each role
        $rolePermissions = RoleMenuPermission::all()
            ->groupBy('role')
            ->map(fn ($items) => $items->pluck('menu_key')->all())
            ->toArray();

        // Statistics
        $stats = [
            'total' => User::count(),
            'pimpinan' => User::where('role', 'pimpinan')->count(),
            'perencana' => User::where('role', 'perencana')->count(),
            'operator' => User::where('role', 'operator')->count(),
            'admin' => User::where('role', 'admin')->count(),
        ];

        $pimpinanUser = User::where('role', 'pimpinan')->first();
        $hasPimpinan = $pimpinanUser !== null;

        return view('users.index', compact(
            'users',
            'kecamatanList',
            'roles',
            'menuDefinitions',
            'rolePermissions',
            'tab',
            'stats',
            'search',
            'roleFilter',
            'hasPimpinan',
            'pimpinanUser'
        ));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => [
                'required',
                'string',
                'in:pimpinan,perencana,operator,admin',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($value === 'pimpinan' && User::where('role', 'pimpinan')->exists()) {
                        $pimpinan = User::where('role', 'pimpinan')->first();
                        $fail("Akun dengan peran Kepala Dinas sudah terdaftar atas nama {$pimpinan->name}. Sistem hanya memperbolehkan 1 akun Kepala Dinas.");
                    }
                },
            ],
            'nip' => ['nullable', 'string', 'max:30'],
            'jabatan' => ['nullable', 'string', 'max:150'],
            'kecamatan_id' => ['nullable', 'exists:wilayah_kecamatan,id'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'nip' => $validated['nip'] ?? null,
            'jabatan' => $validated['jabatan'] ?? null,
            'kecamatan_id' => ($validated['role'] === 'operator') ? ($validated['kecamatan_id'] ?? null) : null,
        ]);

        ActivityLogger::log('CREATE', 'users', "Menambahkan akun pengguna {$user->name} ({$user->email}) dengan peran {$user->role_label}");

        return redirect()->route('users.index', ['tab' => 'users'])
            ->with('success', "Pengguna baru '{$user->name}' dengan peran {$user->role_label} berhasil ditambahkan.");
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$id],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => [
                'required',
                'string',
                'in:pimpinan,perencana,operator,admin',
                function (string $attribute, mixed $value, \Closure $fail) use ($user): void {
                    if ($value === 'pimpinan' && User::where('role', 'pimpinan')->where('id', '!=', $user->id)->exists()) {
                        $pimpinan = User::where('role', 'pimpinan')->where('id', '!=', $user->id)->first();
                        $fail("Akun dengan peran Kepala Dinas sudah terdaftar atas nama {$pimpinan->name}. Sistem hanya memperbolehkan 1 akun Kepala Dinas.");
                    }
                },
            ],
            'nip' => ['nullable', 'string', 'max:30'],
            'jabatan' => ['nullable', 'string', 'max:150'],
            'kecamatan_id' => ['nullable', 'exists:wilayah_kecamatan,id'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];
        $user->nip = $validated['nip'] ?? null;
        $user->jabatan = $validated['jabatan'] ?? null;
        $user->kecamatan_id = ($validated['role'] === 'operator') ? ($validated['kecamatan_id'] ?? null) : null;

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        ActivityLogger::log('UPDATE', 'users', "Memperbarui data akun pengguna {$user->name} ({$user->email})");

        return redirect()->route('users.index', ['tab' => 'users'])
            ->with('success', "Data pengguna '{$user->name}' berhasil diperbarui.");
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(int $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        // Protect self or primary superadmin
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index', ['tab' => 'users'])
                ->with('error', 'Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif.');
        }

        if ($user->email === 'admin@dindikpora.banjarnegarakab.go.id') {
            return redirect()->route('users.index', ['tab' => 'users'])
                ->with('error', 'Akun Super Admin utama sistem dilindungi dan tidak dapat dihapus.');
        }

        $name = $user->name;
        $user->delete();

        ActivityLogger::log('DELETE', 'users', "Menghapus akun pengguna {$name}");

        return redirect()->route('users.index', ['tab' => 'users'])
            ->with('success', "Akun pengguna '{$name}' berhasil dihapus.");
    }

    /**
     * Update menu permissions matrix for roles.
     */
    public function updatePermissions(Request $request): RedirectResponse
    {
        // permissions format: ['pimpinan' => ['dashboard', ...], 'perencana' => [...], 'operator' => [...]]
        $submittedPermissions = $request->input('permissions', []);
        $targetRoles = ['pimpinan', 'perencana', 'operator'];

        DB::transaction(function () use ($targetRoles, $submittedPermissions): void {
            // Delete old permissions for these roles
            RoleMenuPermission::whereIn('role', $targetRoles)->delete();

            $now = now();
            $inserts = [];

            foreach ($targetRoles as $role) {
                $menuKeys = $submittedPermissions[$role] ?? [];
                if (is_array($menuKeys)) {
                    foreach ($menuKeys as $key) {
                        $inserts[] = [
                            'role' => $role,
                            'menu_key' => $key,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                }
            }

            if (! empty($inserts)) {
                RoleMenuPermission::insert($inserts);
            }
        });

        ActivityLogger::log('UPDATE', 'permissions', 'Memperbarui konfigurasi hak akses menu untuk peran Kepala Dinas, Perencana, dan Operator');

        return redirect()->route('users.index', ['tab' => 'permissions'])
            ->with('success', 'Matriks hak akses menu untuk masing-masing peran berhasil disimpan.');
    }
}
