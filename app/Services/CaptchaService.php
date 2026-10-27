<?php

namespace App\Services;

class CaptchaService
{
    /**
     * Kumpulan karakter alfanumerik yang jelas dan mudah dibaca
     * (menghindari karakter ambigu seperti 0, O, 1, I, L).
     */
    protected const CHARACTERS = '23456789ABCDEFGHJKMNPQRSTUVWXYZ';

    /**
     * Generate kode captcha baru dan simpan di session.
     *
     * @return array{code: string, svg: string}
     */
    public function generate(int $length = 5): array
    {
        $charactersLength = strlen(self::CHARACTERS);
        $code = '';

        for ($i = 0; $i < $length; $i++) {
            $code .= self::CHARACTERS[random_int(0, $charactersLength - 1)];
        }

        session([
            'login_captcha_code' => $code,
            'login_captcha_expires_at' => now()->addMinutes(10)->timestamp,
        ]);

        $svg = $this->renderSvg($code);

        return [
            'code' => $code,
            'svg' => $svg,
        ];
    }

    /**
     * Validasi kode captcha terhadap session yang tersimpan.
     * Kode di session akan langsung dihapus setelah validasi (single-use).
     */
    public function validate(?string $input): bool
    {
        $storedCode = session('login_captcha_code');
        $expiresAt = session('login_captcha_expires_at');

        // Segera bersihkan session agar kode tidak dapat digunakan kembali
        session()->forget(['login_captcha_code', 'login_captcha_expires_at']);

        if (! is_string($storedCode) || ! is_numeric($expiresAt)) {
            return false;
        }

        if (now()->timestamp > (int) $expiresAt) {
            return false;
        }

        if (empty($input)) {
            return false;
        }

        return hash_equals(strtoupper(trim($storedCode)), strtoupper(trim($input)));
    }

    /**
     * Ambil kode aktif saat ini (digunakan terutama untuk pengujian).
     */
    public function getActiveCode(): ?string
    {
        return session('login_captcha_code');
    }

    /**
     * Render SVG captcha dengan distorsi visual, garis noise, dan titik acak.
     */
    protected function renderSvg(string $code): string
    {
        $width = 150;
        $height = 44;
        $codeLength = strlen($code);

        // Palet warna kontras elegan untuk teks karakter
        $textColors = [
            '#0f172a',
            '#1e3a8a',
            '#1d4ed8',
            '#312e81',
            '#4338ca',
            '#065f46',
            '#831843',
            '#374151',
        ];

        // Garis acak melengkung (noise curves)
        $curves = '';
        $lineColors = ['#94a3b8', '#60a5fa', '#a5b4fc', '#cbd5e1'];
        for ($c = 0; $c < 4; $c++) {
            $startX = random_int(0, 30);
            $startY = random_int(5, $height - 5);
            $cp1X = random_int(30, 75);
            $cp1Y = random_int(2, $height - 2);
            $cp2X = random_int(75, 120);
            $cp2Y = random_int(2, $height - 2);
            $endX = random_int(120, $width);
            $endY = random_int(5, $height - 5);
            $strokeColor = $lineColors[$c % count($lineColors)];
            $strokeWidth = random_int(12, 22) / 10;
            $opacity = random_int(40, 70) / 100;

            $curves .= sprintf(
                '<path d="M%d,%d C%d,%d %d,%d %d,%d" stroke="%s" stroke-width="%.1f" fill="none" opacity="%.2f" stroke-linecap="round" />',
                $startX,
                $startY,
                $cp1X,
                $cp1Y,
                $cp2X,
                $cp2Y,
                $endX,
                $endY,
                $strokeColor,
                $strokeWidth,
                $opacity
            );
        }

        // Titik-titik acak (noise dots)
        $dots = '';
        for ($d = 0; $d < 25; $d++) {
            $dotX = random_int(5, $width - 5);
            $dotY = random_int(4, $height - 4);
            $dotR = random_int(10, 18) / 10;
            $dotColor = $lineColors[random_int(0, count($lineColors) - 1)];
            $opacity = random_int(30, 60) / 100;

            $dots .= sprintf(
                '<circle cx="%d" cy="%d" r="%.1f" fill="%s" opacity="%.2f" />',
                $dotX,
                $dotY,
                $dotR,
                $dotColor,
                $opacity
            );
        }

        // Karakter teks
        $charactersSvg = '';
        $charSpacing = ($width - 30) / max(1, $codeLength);

        for ($i = 0; $i < $codeLength; $i++) {
            $char = htmlspecialchars($code[$i], ENT_QUOTES, 'UTF-8');
            $charX = 18 + ($i * $charSpacing) + random_int(-2, 2);
            $charY = 30 + random_int(-3, 3);
            $angle = random_int(-14, 14);
            $fontSize = random_int(21, 25);
            $color = $textColors[random_int(0, count($textColors) - 1)];

            $charactersSvg .= sprintf(
                '<text x="%d" y="%d" font-size="%d" font-family="Consolas, Monaco, monospace" font-weight="800" fill="%s" transform="rotate(%d, %d, %d)" text-anchor="middle" letter-spacing="1">%s</text>',
                $charX,
                $charY,
                $fontSize,
                $color,
                $angle,
                $charX,
                $charY,
                $char
            );
        }

        $id = uniqid('captcha_grad_');

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="{$width}" height="{$height}" viewBox="0 0 {$width} {$height}" class="w-full h-full block rounded-xl select-none" role="img" aria-label="Kode Keamanan Captcha">
    <defs>
        <linearGradient id="{$id}" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" stop-color="#f8fafc" />
            <stop offset="100%" stop-color="#e2e8f0" />
        </linearGradient>
    </defs>
    <rect width="{$width}" height="{$height}" rx="10" fill="url(#{$id})" stroke="#cbd5e1" stroke-width="1" />
    {$curves}
    {$dots}
    {$charactersSvg}
</svg>
SVG;
    }
}
