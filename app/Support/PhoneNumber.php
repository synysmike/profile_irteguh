<?php

namespace App\Support;

/**
 * Normalisasi nomor HP Indonesia (termasuk paste dari kontak WhatsApp).
 * Hasil: digit saja dengan prefix 62, contoh 6281234567890.
 */
class PhoneNumber
{
    public static function normalize(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }

        $raw = trim($phone);
        if ($raw === '') {
            return null;
        }

        // Ambil bagian yang paling mungkin nomor jika ada teks tambahan.
        // Contoh: "+62 813-xxxx-xxxx Mobile" atau "tel:+62813..."
        if (preg_match('/(\+?62|0)\s*[\d\-\s().]{8,}/', $raw, $match)) {
            $raw = $match[0];
        }

        $digits = preg_replace('/\D+/', '', $raw) ?? '';
        if ($digits === '') {
            return null;
        }

        // 0062... → 62...
        if (str_starts_with($digits, '0062')) {
            $digits = substr($digits, 2);
        }

        // 08xxxx → 628xxxx
        if (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        }

        // Sudah 62...
        if (str_starts_with($digits, '62')) {
            return self::isPlausible($digits) ? $digits : null;
        }

        // 8xxxx (tanpa 0, sering dari paste WA) → 628xxxx
        if (str_starts_with($digits, '8') && strlen($digits) >= 9 && strlen($digits) <= 13) {
            $digits = '62' . $digits;

            return self::isPlausible($digits) ? $digits : null;
        }

        // Fallback: prefix 62
        $digits = '62' . $digits;

        return self::isPlausible($digits) ? $digits : null;
    }

    /**
     * Format tampilan lokal: 08xxxxxxxxxx (dari nilai tersimpan 62...).
     */
    public static function toLocal(?string $phone): ?string
    {
        $normalized = self::normalize($phone);
        if (! $normalized) {
            return null;
        }

        if (str_starts_with($normalized, '62')) {
            return '0' . substr($normalized, 2);
        }

        return $normalized;
    }

    private static function isPlausible(string $digits): bool
    {
        // 62 + minimal ~9 digit lokal, maksimal wajar untuk mobile ID
        $len = strlen($digits);

        return $len >= 11 && $len <= 15 && str_starts_with($digits, '62');
    }
}
