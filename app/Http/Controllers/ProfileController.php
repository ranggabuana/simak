<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\User;
use App\Services\ActivityLogger;
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
     * Simpan pengaturan aplikasi SIMAK ke database dan session
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            // Identitas Instansi & Wilayah
            'nama_instansi' => ['nullable', 'string', 'max:255'],
            'alamat_instansi' => ['nullable', 'string', 'max:255'],
            'telepon_instansi' => ['nullable', 'string', 'max:100'],
            'email_instansi' => ['nullable', 'string', 'max:150'],
            'website_instansi' => ['nullable', 'string', 'max:150'],
            'kota_dokumen' => ['nullable', 'string', 'max:100'],

            // Pejabat Penandatangan Laporan Resmi
            'kepala_dinas_nama' => ['nullable', 'string', 'max:255'],
            'kepala_dinas_nip' => ['nullable', 'string', 'max:50'],
            'kepala_dinas_pangkat' => ['nullable', 'string', 'max:100'],
            'kepala_dinas_jabatan' => ['nullable', 'string', 'max:255'],
            'perencana_nama' => ['nullable', 'string', 'max:255'],
            'perencana_nip' => ['nullable', 'string', 'max:50'],
            'perencana_jabatan' => ['nullable', 'string', 'max:255'],

            // Standar Kinerja & Target SPM
            'tahun_anggaran_default' => ['nullable', 'integer', 'between:2020,2035'],
            'tahun_evaluasi' => ['nullable', 'integer', 'between:2020,2035'],
            'periode_renstra' => ['nullable', 'string', 'max:50'],
            'ambang_batas_spm' => ['nullable', 'numeric', 'between:0,100'],
            'toleransi_deviasi' => ['nullable', 'numeric', 'between:0,100'],

            // Kontrol Akses & Kunci Data Historis
            'cut_off_date' => ['nullable', 'date'],
            'kunci_data_historis' => ['nullable'],

            // Pengumuman Sistem / Banner Dashboard
            'banner_aktif' => ['nullable'],
            'banner_tipe' => ['nullable', 'in:info,warning,danger,success'],
            'banner_pesan' => ['nullable', 'string', 'max:500'],
            'rumus_apk_mode' => ['nullable', 'string'],
        ]);

        if (isset($validated['tahun_evaluasi']) && empty($validated['tahun_anggaran_default'])) {
            $validated['tahun_anggaran_default'] = $validated['tahun_evaluasi'];
        }

        // Checkbox values handling
        $validated['kunci_data_historis'] = $request->boolean('kunci_data_historis') ? '1' : '0';
        $validated['banner_aktif'] = $request->boolean('banner_aktif') ? '1' : '0';

        foreach ($validated as $key => $val) {
            if ($val !== null) {
                Setting::set($key, $val);
            }
        }

        if (! empty($validated['tahun_anggaran_default'])) {
            session()->put('tahun_anggaran', (int) $validated['tahun_anggaran_default']);
        }

        session([
            'app_settings' => array_merge(session('app_settings', []), $validated),
        ]);

        ActivityLogger::log('UPDATE', 'pengaturan', 'Memperbarui konfigurasi pengaturan aplikasi SIMAK');

        return back()->with('success', 'Pengaturan aplikasi SIMAK berhasil diperbarui dan diterapkan ke seluruh sistem.');
    }
}
