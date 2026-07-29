<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Order extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'kode_pesanan',
        'invoice_number',
        'user_id',
        'total',
        'status',
        'payment_method',
        'payment_status',
        'amount_paid',
        'change_amount',
        'paid_at',
        'qr_code_url',
        'barcode_url',
    ];

    /**
     * Attribute casting.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'total'         => 'decimal:2',
        'amount_paid'   => 'decimal:2',
        'change_amount' => 'decimal:2',
        'paid_at'       => 'datetime',
    ];

    // ─── Helpers ─────────────────────────────────────────────────

    /**
     * Generate kode pesanan otomatis (A001, A002, dst.)
     * Menggunakan lock untuk mencegah race condition / duplicate.
     */
    public static function generateKodePesanan(): string
    {
        $last = self::lockForUpdate()->orderBy('id', 'desc')->first();

        if (! $last || ! preg_match('/^A(\d+)$/', $last->kode_pesanan ?? '', $m)) {
            return 'A001';
        }

        $number = (int) $m[1];
        return 'A' . str_pad($number + 1, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Generate invoice number unik: INV-YYYYMMDD-XXXXX
     * Format contoh: INV-20260723-00001
     */
    public static function generateInvoiceNumber(): string
    {
        $date  = Carbon::now('Asia/Jakarta')->format('Ymd');
        $count = self::whereDate('created_at', Carbon::today())->count() + 1;
        return 'INV-' . $date . '-' . str_pad($count, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Generate URL QR Code yang mengarah ke halaman invoice digital.
     * Saat QR dipindai, langsung membuka invoice di browser.
     *
     * @param string $invoiceNumber
     * @param string $frontendUrl Base URL frontend (misal: http://localhost:3000)
     */
    public static function generateQrCodeUrl(string $invoiceNumber, string $frontendUrl = ''): string
    {
        if (empty($frontendUrl)) {
            $frontendUrl = config('app.frontend_url', 'http://localhost:3000');
        }
        $invoiceUrl = rtrim($frontendUrl, '/') . '/invoice/' . $invoiceNumber;
        return 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($invoiceUrl);
    }

    /**
     * Generate URL Barcode dari invoice number.
     *
     * @param string $invoiceNumber
     */
    public static function generateBarcodeUrl(string $invoiceNumber): string
    {
        return 'https://barcode.tec-it.com/barcode.ashx?data=' . urlencode($invoiceNumber) . '&code=Code128&dpi=96';
    }

    /**
     * Label status pesanan.
     */
    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending'  => 'Menunggu Pembayaran',
            'diproses' => 'Diproses',
            'selesai'  => 'Selesai',
            'batal'    => 'Dibatalkan',
            default    => ucfirst($this->status),
        };
    }

    /**
     * Label status pembayaran.
     */
    public function paymentStatusLabel(): string
    {
        return match ($this->payment_status) {
            'paid'   => 'Lunas',
            'unpaid' => 'Belum Lunas',
            default  => ucfirst($this->payment_status),
        };
    }

    // ─── Relationships ────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class);
    }
}
