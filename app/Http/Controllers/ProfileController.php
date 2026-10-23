<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Update data profil pengguna yang sedang login
     */
    public function update(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'nip' => ['nullable', 'string', 'max:30'],
            'jabatan' => ['nullable', 'string', 'max:100'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->nip = $validated['nip'] ?? null;
        $user->jabatan = $validated['jabatan'] ?? null;

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return back()->with('success', 'Profil pengguna berhasil diperbarui.');
    }

    /**
     * Simpan pengaturan aplikasi SIMAK
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_instansi' => ['required', 'string', 'max:255'],
            'periode_renstra' => ['required', 'string', 'max:50'],
            'tahun_evaluasi' => ['required', 'integer'],
            'rumus_apk_mode' => ['required', 'in:resmi_kemendikbud,agregat_total'],
        ]);

        session([
            'app_settings' => $validated,
        ]);

        return back()->with('success', 'Pengaturan aplikasi SIMAK berhasil disimpan.');
    }
}
