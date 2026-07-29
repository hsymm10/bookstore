<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Resources\UserResource;
use App\Models\PasswordResetOtp;
use App\Models\User;
use App\Services\WhatsAppService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use ApiResponse;

    // ─── Login ───────────────────────────────────────────────────

    /**
     * Login user dengan username dan password.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('username', $request->username)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return $this->errorResponse('Username atau password salah.', 401);
        }

        // Update online status
        $user->update([
            'is_online' => true,
            'last_seen' => now(),
        ]);

        // Buat token baru tanpa menghapus token lama
        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse([
            'user'  => new UserResource($user),
            'token' => $token,
        ], 'Login berhasil.');
    }

    // ─── Register ─────────────────────────────────────────────────

    /**
     * Register user baru.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'role'     => 'user',
            'nama'     => $request->nama,
            'username' => $request->username,
            'email'    => $request->email,
            'no_hp'    => $request->no_hp,
            'password' => Hash::make($request->password),
            'is_online' => true,
            'last_seen' => now(),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse([
            'user'  => new UserResource($user),
            'token' => $token,
        ], 'Registrasi berhasil. Selamat datang!', 201);
    }

    // ─── Logout ───────────────────────────────────────────────────

    /**
     * Logout user.
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->update([
            'is_online' => false,
            'last_seen' => now(),
        ]);

        $user->currentAccessToken()->delete();

        return $this->successResponse(null, 'Logout berhasil.');
    }

    /**
     * POST /api/auth/heartbeat
     * Keeping session online.
     */
    public function heartbeat(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->update([
            'is_online' => true,
            'last_seen' => now(),
        ]);

        return $this->successResponse(null, 'Heartbeat success.');
    }

    // ─── Profile ──────────────────────────────────────────────────

    /**
     * Get profil user yang sedang login.
     */
    public function profile(Request $request): JsonResponse
    {
        return $this->successResponse(
            new UserResource($request->user()),
            'Data profil berhasil diambil.'
        );
    }

    /**
     * Check if email exists in database.
     */
    public function checkEmail(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email'    => 'Format email tidak valid.',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return $this->errorResponse('Email tidak ditemukan.', 404);
        }

        if (empty($user->no_hp)) {
            return $this->errorResponse('Nomor WhatsApp tidak terdaftar pada akun ini.', 422);
        }

        // Masking phone number (e.g. 0812******89)
        $noHp = $user->no_hp;
        $len = strlen($noHp);
        if ($len > 6) {
            $maskedPhone = substr($noHp, 0, 4) . str_repeat('*', $len - 6) . substr($noHp, -2);
        } else {
            $maskedPhone = substr($noHp, 0, 2) . str_repeat('*', max(1, $len - 4)) . substr($noHp, -2);
        }

        return $this->successResponse([
            'email'        => $user->email,
            'masked_phone' => $maskedPhone,
        ], 'Email terdaftar.');
    }

    /**
     * Send OTP code to WhatsApp.
     */
    public function sendOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email'    => 'Format email tidak valid.',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return $this->errorResponse('Email tidak ditemukan.', 404);
        }

        if (empty($user->no_hp)) {
            return $this->errorResponse('Nomor WhatsApp tidak terdaftar pada akun ini.', 422);
        }

        // Generate 6 digit OTP
        $otp = sprintf('%06d', mt_rand(0, 999999));

        // Hapus OTP lama milik user ini
        PasswordResetOtp::where('identifier', $user->email)
            ->orWhere('identifier', $user->no_hp)
            ->delete();

        // Simpan OTP baru (berlaku 5 menit)
        PasswordResetOtp::create([
            'identifier' => $user->email,
            'no_hp'      => $user->no_hp,
            'otp'        => $otp,
            'expires_at' => now()->addMinutes(5),
        ]);

        // Kirim OTP via WhatsApp
        $otpRes = WhatsAppService::sendOtp($user->no_hp, $otp);

        if (! $otpRes['success']) {
            $reasonMsg = $otpRes['reason'] ?? 'Gagal mengirim kode OTP ke WhatsApp.';
            if ($reasonMsg === 'request invalid on disconnected device') {
                $reasonMsg = 'Perangkat WhatsApp Fonnte terputus (disconnected device). Silakan lakukan hubungkan / scan QR WhatsApp pada dashboard Fonnte (fonnte.com).';
            }
            return $this->errorResponse($reasonMsg, 400);
        }

        return $this->successResponse(null, 'Kode OTP telah dikirim ke WhatsApp Anda.');
    }

    /**
     * Verify OTP code.
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'otp'   => ['required', 'string', 'size:6'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email'    => 'Format email tidak valid.',
            'otp.required'   => 'Kode OTP wajib diisi.',
            'otp.size'       => 'Kode OTP harus 6 digit.',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return $this->errorResponse('Email tidak ditemukan.', 404);
        }

        // Cek OTP di database
        $otpRecord = PasswordResetOtp::where(function ($query) use ($user) {
                $query->where('identifier', $user->email)
                      ->orWhere('identifier', $user->no_hp);
            })
            ->where('otp', $request->otp)
            ->where('expires_at', '>=', now())
            ->first();

        if (! $otpRecord) {
            return $this->errorResponse('Kode OTP tidak valid atau sudah kedaluwarsa.', 422);
        }

        return $this->successResponse(null, 'Kode OTP valid.');
    }

    // ─── Forgot Password ──────────────────────────────────────────

    /**
     * Tahap 1: Cek apakah email atau nomor HP terdaftar, lalu kirim OTP ke WhatsApp.
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $identifier = $request->identifier;

        // Cari user berdasarkan email ATAU nomor HP
        $user = User::where('email', $identifier)
            ->orWhere('no_hp', $identifier)
            ->first();

        if (! $user) {
            return $this->errorResponse('Email atau nomor HP tidak ditemukan.', 404);
        }

        if (empty($user->no_hp)) {
            return $this->errorResponse('Nomor WhatsApp tidak terdaftar pada akun ini.', 422);
        }

        // Generate 6 digit OTP
        $otp = sprintf('%06d', mt_rand(0, 999999));

        // Hapus OTP lama milik user ini
        PasswordResetOtp::where('identifier', $user->email)
            ->orWhere('identifier', $user->no_hp)
            ->delete();

        // Simpan OTP baru (berlaku 5 menit)
        PasswordResetOtp::create([
            'identifier' => $user->email,
            'no_hp'      => $user->no_hp,
            'otp'        => $otp,
            'expires_at' => now()->addMinutes(5),
        ]);

        // Kirim OTP via WhatsApp
        $otpRes = WhatsAppService::sendOtp($user->no_hp, $otp);

        if (! $otpRes['success']) {
            $reasonMsg = $otpRes['reason'] ?? 'Gagal mengirim kode OTP ke WhatsApp.';
            if ($reasonMsg === 'request invalid on disconnected device') {
                $reasonMsg = 'Perangkat WhatsApp Fonnte terputus (disconnected device). Silakan lakukan hubungkan / scan QR WhatsApp pada dashboard Fonnte (fonnte.com).';
            }
            return $this->errorResponse($reasonMsg, 400);
        }


        // Masking nomor HP (contoh: 0812****5678)
        $noHp = $user->no_hp;
        $maskedHp = strlen($noHp) > 7
            ? substr($noHp, 0, 4) . '****' . substr($noHp, -4)
            : substr($noHp, 0, 2) . '****';

        return $this->successResponse([
            'identifier'   => $user->email,
            'masked_phone' => $maskedHp,
        ], 'Kode OTP telah dikirim ke WhatsApp ' . $maskedHp);
    }

    // ─── Reset Password ───────────────────────────────────────────

    /**
     * Tahap 2: Reset password dengan memverifikasi kode OTP.
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $identifier = $request->identifier;
        $otpCode    = $request->otp;

        // Cari user berdasarkan email ATAU nomor HP
        $user = User::where('email', $identifier)
            ->orWhere('no_hp', $identifier)
            ->first();

        if (! $user) {
            return $this->errorResponse('Identitas tidak ditemukan.', 404);
        }

        // Cek OTP di database
        $otpRecord = PasswordResetOtp::where(function ($query) use ($user) {
                $query->where('identifier', $user->email)
                      ->orWhere('identifier', $user->no_hp);
            })
            ->where('otp', $otpCode)
            ->where('expires_at', '>=', now())
            ->first();

        if (! $otpRecord) {
            return $this->errorResponse('Kode OTP tidak valid atau sudah kedaluwarsa.', 422);
        }

        // Update password user
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Hapus kode OTP agar tidak bisa dipakai ulang
        PasswordResetOtp::where('identifier', $user->email)
            ->orWhere('identifier', $user->no_hp)
            ->delete();

        // Hapus semua token (paksa login ulang)
        $user->tokens()->delete();

        return $this->successResponse(null, 'Password berhasil direset. Silakan login dengan password baru.');
    }
}

