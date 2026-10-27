<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthCaptchaTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Pastikan halaman login menampilkan form captcha dan SVG kode keamanan.
     */
    public function test_login_page_renders_with_captcha_svg(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Kode Keamanan (Captcha)');
        $response->assertSee('<svg', false);
        $this->assertNotNull(session('login_captcha_code'));
    }

    /**
     * Pastikan halaman login menampilkan tombol lihat/sembunyikan kata sandi.
     */
    public function test_login_page_renders_with_password_toggle(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('btn-toggle-password');
        $response->assertSee('togglePasswordVisibility');
    }

    /**
     * Pastikan endpoint refresh captcha mengembalikan JSON dengan SVG baru.
     */
    public function test_captcha_refresh_endpoint_returns_new_captcha_svg(): void
    {
        $response = $this->getJson(route('captcha.refresh'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'svg',
        ]);
        $this->assertStringContainsString('<svg', $response->json('svg'));
        $this->assertNotNull(session('login_captcha_code'));
    }

    /**
     * Pastikan proses login gagal jika captcha tidak diisi.
     */
    public function test_login_fails_without_captcha(): void
    {
        $response = $this->post(route('login.post'), [
            'email' => 'admin@dindikpora.banjarnegarakab.go.id',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('captcha');
        $this->assertGuest();
    }

    /**
     * Pastikan proses login gagal jika kode captcha salah.
     */
    public function test_login_fails_with_invalid_captcha(): void
    {
        $this->get('/login');

        $response = $this->post(route('login.post'), [
            'email' => 'admin@dindikpora.banjarnegarakab.go.id',
            'password' => 'password',
            'captcha' => 'INVALID',
        ]);

        $response->assertSessionHasErrors('captcha');
        $this->assertGuest();
    }

    /**
     * Pastikan proses login gagal jika captcha benar tetapi kata sandi salah.
     */
    public function test_login_fails_with_valid_captcha_but_wrong_password(): void
    {
        $user = User::factory()->create([
            'email' => 'user_wrongpass_'.uniqid().'@example.com',
            'password' => Hash::make('correct_password'),
        ]);

        $this->get('/login');
        $captcha = session('login_captcha_code');
        $this->assertNotNull($captcha);

        $response = $this->post(route('login.post'), [
            'email' => $user->email,
            'password' => 'wrong_password',
            'captcha' => $captcha,
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * Pastikan login berhasil ketika kredensial dan captcha valid.
     */
    public function test_login_succeeds_with_valid_credentials_and_valid_captcha(): void
    {
        $user = User::factory()->create([
            'email' => 'user_success_'.uniqid().'@example.com',
            'password' => Hash::make('password123'),
        ]);

        $this->get('/login');
        $captcha = session('login_captcha_code');
        $this->assertNotNull($captcha);

        $response = $this->post(route('login.post'), [
            'email' => $user->email,
            'password' => 'password123',
            'captcha' => strtolower($captcha), // Uji case-insensitivity
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Pastikan captcha bersifat single-use dan tidak dapat digunakan kembali (anti-replay).
     */
    public function test_captcha_cannot_be_reused(): void
    {
        $user = User::factory()->create([
            'email' => 'user_replay_'.uniqid().'@example.com',
            'password' => Hash::make('password123'),
        ]);

        $this->get('/login');
        $captcha = session('login_captcha_code');
        $this->assertNotNull($captcha);

        // Percobaan pertama (gagal password, captcha dikonsumsi)
        $this->post(route('login.post'), [
            'email' => $user->email,
            'password' => 'wrong_pass',
            'captcha' => $captcha,
        ]);

        // Percobaan kedua memakai captcha yang sama harus gagal pada validasi captcha
        $secondResponse = $this->post(route('login.post'), [
            'email' => $user->email,
            'password' => 'password123',
            'captcha' => $captcha,
        ]);

        $secondResponse->assertSessionHasErrors('captcha');
        $this->assertGuest();
    }
}
