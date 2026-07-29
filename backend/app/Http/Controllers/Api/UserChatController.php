<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ChatResource;
use App\Models\Chat;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * UserChatController — Mengelola semua operasi chat dari sisi USER (pelanggan).
 *
 * Routes (di bawah middleware auth:sanctum saja — tidak perlu role:admin):
 *   GET  /api/user/chats              → Ambil semua pesan percakapan dengan admin
 *   POST /api/user/chats              → Kirim pesan ke admin
 *   GET  /api/user/chats/unread-count → Jumlah pesan dari admin yang belum dibaca
 */
class UserChatController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/user/chats
     * Ambil seluruh riwayat percakapan antara user yang sedang login dengan admin.
     * Pesan dari admin ke user yang belum dibaca otomatis ditandai sebagai dibaca.
     */
    public function index(Request $request): JsonResponse
    {
        $user  = $request->user();
        $admin = User::where('role', 'admin')->first();

        if (! $admin) {
            return $this->errorResponse('Admin tidak ditemukan.', 404);
        }

        $messages = Chat::with(['sender', 'receiver'])
            ->where(fn ($q) =>
                $q->where('sender_id', $user->id)->where('receiver_id', $admin->id)
                ->orWhere(fn ($q2) =>
                    $q2->where('sender_id', $admin->id)->where('receiver_id', $user->id)
                )
            )
            ->orderBy('created_at')
            ->get();

        // Tandai semua pesan dari admin ke user ini sebagai sudah dibaca
        Chat::where('sender_id', $admin->id)
            ->where('receiver_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true, 'status' => 'read']);

        return $this->successResponse([
            'messages'     => ChatResource::collection($messages),
            'admin_status' => [
                'is_online'       => $admin->isOnline(),
                'last_seen_label' => $admin->getLastSeenLabel(),
            ],
        ], 'Pesan berhasil diambil.');
    }

    /**
     * POST /api/user/chats
     * User mengirim pesan ke admin.
     * Request body: { message: string }
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $user  = $request->user();
        $admin = User::where('role', 'admin')->firstOrFail();

        $chat = Chat::create([
            'sender_id'   => $user->id,
            'receiver_id' => $admin->id,
            'message'     => $request->message,
            'is_read'     => false,
            'status'      => 'sent',
        ]);

        $chat->load(['sender', 'receiver']);

        return $this->successResponse(
            new ChatResource($chat),
            'Pesan berhasil dikirim.',
            201
        );
    }

    /**
     * GET /api/user/chats/unread-count
     * Hitung jumlah pesan dari admin yang belum dibaca oleh user ini.
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $user  = $request->user();
        $admin = User::where('role', 'admin')->first();

        if (! $admin) {
            return $this->successResponse(['count' => 0], 'Jumlah pesan belum dibaca.');
        }

        $count = Chat::where('sender_id', $admin->id)
            ->where('receiver_id', $user->id)
            ->where('is_read', false)
            ->count();

        return $this->successResponse(['count' => $count], 'Jumlah pesan belum dibaca.');
    }
}
