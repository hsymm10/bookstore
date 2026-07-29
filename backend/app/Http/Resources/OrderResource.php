<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                   => $this->id,
            'kode_pesanan'         => $this->kode_pesanan,
            'invoice_number'       => $this->invoice_number,

            // User info (flat fields untuk kasir view)
            'user_id'              => $this->user_id,
            'user'                 => $this->whenLoaded('user') ? $this->user->nama : null,
            'user_email'           => $this->whenLoaded('user') ? $this->user->email : null,
            'user_detail'          => new UserResource($this->whenLoaded('user')),

            // Finansial
            'total'                => (float) $this->total,
            'amount_paid'          => $this->amount_paid !== null ? (float) $this->amount_paid : null,
            'change_amount'        => $this->change_amount !== null ? (float) $this->change_amount : null,

            // Status pesanan
            'status'               => $this->status,
            'status_label'         => $this->statusLabel(),

            // Status pembayaran
            'payment_status'       => $this->payment_status,
            'payment_status_label' => $this->paymentStatusLabel(),
            'payment_method'       => $this->payment_method,
            'payment_method_label' => $this->payment_method === 'cash' ? 'Tunai' : 'Transfer',

            // Waktu
            'paid_at'              => $this->paid_at?->setTimezone('Asia/Jakarta')?->format('d M Y H:i'),
            'paid_at_full'         => $this->paid_at?->toIso8601String(),
            'created_at'           => $this->created_at?->setTimezone('Asia/Jakarta')?->format('d M Y H:i'),
            'created_at_date'      => $this->created_at?->setTimezone('Asia/Jakarta')?->format('d M Y'),

            // QR & Barcode
            'qr_code_url'          => $this->qr_code_url,
            'barcode_url'          => $this->barcode_url,

            // Detail item
            'order_details'        => OrderDetailResource::collection($this->whenLoaded('orderDetails')),

            // Ringkasan item (untuk list view)
            'total_items'          => $this->whenLoaded('orderDetails',
                fn () => $this->orderDetails->sum('qty'),
                null
            ),
        ];
    }
}
