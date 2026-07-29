<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class ChatResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $createdAt = $this->created_at
            ? Carbon::parse($this->created_at)->setTimezone('Asia/Jakarta')
            : null;

        return [
            'id'            => $this->id,
            'sender'        => new UserResource($this->whenLoaded('sender')),
            'receiver'      => new UserResource($this->whenLoaded('receiver')),
            'sender_id'     => $this->sender_id,
            'receiver_id'   => $this->receiver_id,
            'message'       => $this->message,
            'is_read'       => $this->is_read,
            'status'        => $this->status ?? ($this->is_read ? 'read' : 'sent'),

            // Waktu server WIB (bukan waktu browser)
            'created_at'    => $createdAt?->format('d M Y H:i'),
            'time'          => $createdAt?->format('H:i'),
            'time_relative' => $this->formatRelativeTime($createdAt),
            'timestamp'     => $this->created_at?->toIso8601String(),
        ];
    }

    /**
     * Format waktu relatif dalam Bahasa Indonesia (timezone Asia/Jakarta / WIB).
     * Contoh output:
     *   - "08:30"              (hari ini)
     *   - "Kemarin 19:45"      (kemarin)
     *   - "20 Jul 2026 10:15"  (lebih dari kemarin)
     */
    private function formatRelativeTime(?Carbon $dt): ?string
    {
        if (! $dt) return null;

        $now = Carbon::now('Asia/Jakarta');

        if ($dt->isToday()) {
            return $dt->format('H:i');
        } elseif ($dt->isYesterday()) {
            return 'Kemarin ' . $dt->format('H:i');
        } else {
            return $dt->isoFormat('D MMM YYYY H:mm');
        }
    }
}
