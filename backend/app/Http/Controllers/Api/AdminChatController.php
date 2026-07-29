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
 * AdminChatController — Mengelola semua operasi chat dari sisi ADMIN.
 *
 * Routes (di bawah middleware auth:sanctum + role:admin):
 *   GET  /api/admin/chats               → Daftar semua user yang pernah chat
 *   GET  /api/admin/chats/{userId}      → Percakapan dengan user tertentu
 *   POST /api/admin/chats               → Kirim pesan ke user tertentu
 *   GET  /api/admin/chats/unread-count  → Jumlah total pesan belum dibaca
 */
class AdminChatController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/admin/chats
     * Ambil daftar semua user yang pernah melakukan percakapan dengan admin.
     */
    public function index(Request $request): JsonResponse
    {
        $admin = $request->user();

        // Kumpulkan semua user ID yang pernah berkirim pesan dengan admin ini
        $userIds = Chat::select('sender_id', 'receiver_id')
            ->where(fn ($q) => $q
                ->where('receiver_id', $admin->id)
                ->orWhere('sender_id', $admin->id)
            )
            ->latest()
            ->get()
            ->flatMap(fn ($chat) => [$chat->sender_id, $chat->receiver_id])
            ->unique()
            ->filter(fn ($id) => $id !== $admin->id)
            ->values();

        // Muat data user beserta info pesan terakhir & jumlah pesan belum dibaca
        $users = User::whereIn('id', $userIds)
            ->where('role', 'user')
            ->get()
            ->map(function ($u) use ($admin) {
                $lastMessage = Chat::where(fn ($q) =>
                    $q->where('sender_id', $u->id)->where('receiver_id', $admin->id)
                    ->orWhere(fn ($q2) =>
                        $q2->where('sender_id', $admin->id)->where('receiver_id', $u->id)
                    )
                )->latest()->first();

                $unreadCount = Chat::where('sender_id', $u->id)
                    ->where('receiver_id', $admin->id)
                    ->where('is_read', false)
                    ->count();

                $lastTime = null;
                if ($lastMessage?->created_at) {
                    $lastTime = $this->formatRelativeTime($lastMessage->created_at);
                }

                return [
                    'id'              => $u->id,
                    'nama'            => $u->nama,
                    'username'        => $u->username,
                    'last_message'    => $lastMessage?->message,
                    'last_time'       => $lastTime,
                    'last_time_raw'   => $lastMessage?->created_at?->toIso8601String(),
                    'unread_count'    => $unreadCount,
                    'is_online'       => $u->isOnline(),
                    'last_seen_label' => $u->getLastSeenLabel(),
                ];
            })
            // Urutkan berdasarkan pesan terakhir terbaru
            ->sortByDesc('last_time_raw')
            ->values();

        return $this->successResponse($users, 'Daftar percakapan berhasil diambil.');
    }

    /**
     * GET /api/admin/chats/{userId}
     * Ambil percakapan antara admin dan user tertentu, lalu tandai pesan
     * dari user tersebut sebagai sudah dibaca (status = 'read').
     */
    public function show(Request $request, int $userId): JsonResponse
    {
        $admin = $request->user();
        $user  = User::where('id', $userId)->where('role', 'user')->firstOrFail();

        $messages = Chat::with(['sender', 'receiver'])
            ->where(fn ($q) =>
                $q->where('sender_id', $admin->id)->where('receiver_id', $user->id)
                ->orWhere(fn ($q2) =>
                    $q2->where('sender_id', $user->id)->where('receiver_id', $admin->id)
                )
            )
            ->orderBy('created_at')
            ->get();

        // Tandai semua pesan dari user ke admin sebagai sudah dibaca
        Chat::where('sender_id', $user->id)
            ->where('receiver_id', $admin->id)
            ->where('is_read', false)
            ->update(['is_read' => true, 'status' => 'read']);

        return $this->successResponse([
            'messages'        => ChatResource::collection($messages),
            'user_status'     => [
                'is_online'       => $user->isOnline(),
                'last_seen_label' => $user->getLastSeenLabel(),
            ],
        ], 'Percakapan berhasil diambil.');
    }

    /**
     * POST /api/admin/chats
     * Admin mengirim pesan balasan kepada user tertentu.
     * Request body: { message: string, receiver_id: int }
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'message'     => ['required', 'string', 'max:1000'],
            'receiver_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $admin    = $request->user();
        $receiver = User::where('id', $request->receiver_id)
            ->where('role', 'user')
            ->firstOrFail();

        $chat = Chat::create([
            'sender_id'   => $admin->id,
            'receiver_id' => $receiver->id,
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
     * GET /api/admin/chats/unread-count
     * Total pesan belum dibaca dari semua user yang ditujukan kepada admin.
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $count = Chat::where('receiver_id', $request->user()->id)
            ->where('is_read', false)
            ->count();

        return $this->successResponse(['count' => $count], 'Jumlah pesan belum dibaca.');
    }

    /**
     * Format waktu relatif dalam Bahasa Indonesia (WIB).
     * Contoh: "08:30", "Kemarin 19:45", "20 Jul 2026 10:15"
     */
    private function formatRelativeTime($datetime): string
    {
        $dt  = Carbon::parse($datetime)->setTimezone('Asia/Jakarta');
        $now = Carbon::now('Asia/Jakarta');

        if ($dt->isToday()) {
            return $dt->format('H:i');
        } elseif ($dt->isYesterday()) {
            return 'Kemarin ' . $dt->format('H:i');
        } else {
            // Format: 20 Jul 2026
            return $dt->isoFormat('D MMM YYYY');
        }
    }
}
