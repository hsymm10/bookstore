<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    /**
     * Kirim pesan OTP ke nomor WhatsApp tujuan menggunakan Fonnte API.
     * 
     * @return array{success: bool, reason: string}
     */
    public static function sendOtp(string $target, string $otp): array
    {
        $token = config('services.fonnte.token');
        $url   = config('services.fonnte.url', 'https://api.fonnte.com/send');

        if (empty($token)) {
            Log::error('Fonnte WhatsApp Token tidak ditemukan di konfigurasi.');
            return [
                'success' => false,
                'reason'  => 'Token WhatsApp Fonnte belum dikonfigurasi.',
            ];
        }

        // Format pesan OTP
        $message = "🔐 *KODE OTP LUPA PASSWORD BOOKSTORE*\n\n"
                 . "Kode OTP Anda adalah: *{$otp}*\n\n"
                 . "Kode ini berlaku selama 5 menit. Jangan bagikan kode ini kepada siapapun demi keamanan akun Anda.";

        try {
            $response = Http::withHeaders([
                'Authorization' => $token,
            ])->post($url, [
                'target'  => self::formatPhoneNumber($target),
                'message' => $message,
            ]);

            $json = $response->json();

            Log::info('Fonnte WhatsApp Response:', [
                'target' => $target,
                'status' => $response->status(),
                'body'   => $json,
            ]);

            // Cek HTTP status dan inner status dari Fonnte JSON
            if ($response->successful() && isset($json['status']) && $json['status'] === true) {
                return [
                    'success' => true,
                    'reason'  => $json['detail'] ?? 'Pesan OTP berhasil dikirim ke antrean.',
                ];
            }

            $reason = $json['reason'] ?? $json['detail'] ?? 'Gagal mengirim pesan via WhatsApp Fonnte.';
            return [
                'success' => false,
                'reason'  => $reason,
            ];
        } catch (\Throwable $e) {
            Log::error('Gagal mengirim WhatsApp OTP via Fonnte: ' . $e->getMessage(), [
                'target' => $target,
                'trace'  => $e->getTraceAsString(),
            ]);
            return [
                'success' => false,
                'reason'  => 'Terjadi kesalahan koneksi ke server WhatsApp: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Format nomor HP agar sesuai standar (contoh: 08123456789 -> 628123456789 jika perlu).
     */
    public static function formatPhoneNumber(string $phone): string
    {
        $cleaned = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($cleaned, '0')) {
            return '62' . substr($cleaned, 1);
        }

        return $cleaned;
    }
}
