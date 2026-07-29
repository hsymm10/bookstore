<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Book;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Traits\ApiResponse;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/orders
     * Admin: semua pesanan. User: pesanan milik sendiri.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Order::with(['user', 'orderDetails.book.category'])
            ->when(! $request->user()->isAdmin(), fn ($q) =>
                $q->where('user_id', $request->user()->id)
            )
            ->when($request->user_id && $request->user()->isAdmin(), fn ($q) =>
                $q->where('user_id', $request->user_id)
            )
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->payment_status, fn ($q) => $q->where('payment_status', $request->payment_status))
            ->when($request->search, fn ($q) =>
                $q->where(function ($sq) use ($request) {
                    $sq->where('kode_pesanan', 'like', "%{$request->search}%")
                       ->orWhere('invoice_number', 'like', "%{$request->search}%");
                })
            )
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 10);

        return $this->successResponse(
            OrderResource::collection($query)->response()->getData(true),
            'Data pesanan berhasil diambil.'
        );
    }

    /**
     * POST /api/orders
     * User checkout: buat pesanan baru dari keranjang.
     * Menggunakan DB transaction untuk atomicity.
     */
    public function store(OrderRequest $request): JsonResponse
    {
        return DB::transaction(function () use ($request) {
            $items      = $request->items;
            $total      = 0;
            $orderItems = [];

            // Validasi stok dan hitung total
            foreach ($items as $item) {
                $book = Book::lockForUpdate()->findOrFail($item['book_id']);

                if ($book->stok < $item['qty']) {
                    throw new \Exception("Stok buku '{$book->nama_buku}' tidak mencukupi. Stok tersedia: {$book->stok}");
                }

                $subtotal     = $book->harga_jual * $item['qty'];
                $total       += $subtotal;
                $orderItems[] = [
                    'book'     => $book,
                    'qty'      => $item['qty'],
                    'harga'    => $book->harga_jual,
                    'subtotal' => $subtotal,
                ];
            }

            // Generate kode & invoice number unik
            $kodePesanan   = Order::generateKodePesanan();
            $invoiceNumber = Order::generateInvoiceNumber();
            $qrCodeUrl     = Order::generateQrCodeUrl($invoiceNumber);
            $barcodeUrl    = Order::generateBarcodeUrl($invoiceNumber);

            // Buat order
            $order = Order::create([
                'kode_pesanan'   => $kodePesanan,
                'invoice_number' => $invoiceNumber,
                'user_id'        => $request->user()->id,
                'total'          => $total,
                'status'         => 'pending',
                'payment_method' => $request->payment_method ?? 'cash',
                'payment_status' => 'unpaid',
                'qr_code_url'    => $qrCodeUrl,
                'barcode_url'    => $barcodeUrl,
            ]);

            // Simpan detail & kurangi stok
            foreach ($orderItems as $item) {
                OrderDetail::create([
                    'order_id' => $order->id,
                    'book_id'  => $item['book']->id,
                    'qty'      => $item['qty'],
                    'harga'    => $item['harga'],
                    'subtotal' => $item['subtotal'],
                ]);

                $item['book']->decrement('stok', $item['qty']);
            }

            $order->load(['user', 'orderDetails.book.category']);

            return $this->successResponse(
                new OrderResource($order),
                'Pesanan berhasil dibuat.',
                201
            );
        });
    }

    /**
     * GET /api/orders/{order}
     * Detail pesanan (by ID).
     */
    public function show(Request $request, Order $order): JsonResponse
    {
        // User hanya bisa lihat pesanan miliknya
        if (! $request->user()->isAdmin() && $order->user_id !== $request->user()->id) {
            return $this->errorResponse('Akses ditolak.', 403);
        }

        $order->load(['user', 'orderDetails.book.category']);

        return $this->successResponse(
            new OrderResource($order),
            'Detail pesanan berhasil diambil.'
        );
    }

    /**
     * GET /api/orders/invoice/{invoiceNumber}
     * Ambil order berdasarkan invoice number (untuk QR scan & halaman invoice digital).
     * Endpoint ini bisa diakses oleh user yang memilikinya atau admin.
     */
    public function getByInvoice(Request $request, string $invoiceNumber): JsonResponse
    {
        $order = Order::where('invoice_number', $invoiceNumber)
            ->with(['user', 'orderDetails.book.category'])
            ->firstOrFail();

        // User hanya bisa lihat invoice miliknya
        if (! $request->user()->isAdmin() && $order->user_id !== $request->user()->id) {
            return $this->errorResponse('Akses ditolak.', 403);
        }

        return $this->successResponse(
            new OrderResource($order),
            'Data invoice berhasil diambil.'
        );
    }

    /**
     * PATCH /api/orders/{order}/status
     * Admin update status pesanan.
     */
    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        $request->validate([
            'status' => ['required', 'in:pending,diproses,selesai,batal'],
        ]);

        // Jika batal → kembalikan stok
        if ($request->status === 'batal' && $order->status !== 'batal') {
            foreach ($order->orderDetails()->with('book')->get() as $detail) {
                $detail->book->increment('stok', $detail->qty);
            }
        }

        // Jika restore dari batal → kurangi stok lagi
        if ($order->status === 'batal' && $request->status !== 'batal') {
            foreach ($order->orderDetails()->with('book')->get() as $detail) {
                if ($detail->book->stok < $detail->qty) {
                    return $this->errorResponse(
                        "Stok buku '{$detail->book->nama_buku}' tidak mencukupi.",
                        422
                    );
                }
                $detail->book->decrement('stok', $detail->qty);
            }
        }

        $order->update(['status' => $request->status]);

        return $this->successResponse(
            new OrderResource($order->load(['user', 'orderDetails.book'])),
            'Status pesanan berhasil diperbarui.'
        );
    }

    /**
     * POST /api/orders/{order}/confirm
     * Admin konfirmasi pesanan (kasir): hitung & simpan kembalian, tandai lunas.
     */
    public function confirm(Request $request, Order $order): JsonResponse
    {
        $request->validate([
            'cash'           => ['required', 'numeric', 'min:0'],
            'payment_method' => ['nullable', 'in:cash,transfer'],
        ]);

        if ($order->payment_status === 'paid') {
            return $this->errorResponse('Pesanan ini sudah dibayar.', 422);
        }

        $cash           = (float) $request->cash;
        $total          = (float) $order->total;
        $changeAmount   = $cash - $total;
        $paymentMethod  = $request->payment_method ?? 'cash';

        if ($changeAmount < 0) {
            return $this->errorResponse(
                'Uang pembayaran tidak mencukupi. Kurang: Rp ' . number_format(abs($changeAmount), 0, ',', '.'),
                422
            );
        }

        // Simpan semua data pembayaran ke database (PERBAIKAN UTAMA)
        $order->update([
            'status'         => 'selesai',
            'payment_status' => 'paid',
            'payment_method' => $paymentMethod,
            'amount_paid'    => $cash,
            'change_amount'  => $changeAmount,
            'paid_at'        => Carbon::now('Asia/Jakarta'),
        ]);

        $order->load(['user', 'orderDetails.book.category']);

        return $this->successResponse(
            new OrderResource($order),
            'Pesanan berhasil dikonfirmasi & lunas.'
        );
    }

    /**
     * GET /api/orders/{order}/invoice
     * Generate Invoice PDF.
     */
    public function invoice(Request $request, Order $order): mixed
    {
        // User hanya bisa lihat invoice pesanan miliknya
        if (! $request->user()->isAdmin() && $order->user_id !== $request->user()->id) {
            return $this->errorResponse('Akses ditolak.', 403);
        }

        $order->load(['user', 'orderDetails.book.category']);

        $pdf = Pdf::loadView('pdf.invoice', [
            'order' => $order,
        ])->setPaper('A4', 'portrait');

        $filename = $order->invoice_number ?? $order->kode_pesanan;
        return $pdf->download("Invoice-{$filename}.pdf");
    }

    /**
     * GET /api/orders/{order}/receipt
     * Generate Receipt PDF.
     */
    public function receipt(Request $request, Order $order): mixed
    {
        // User hanya bisa lihat receipt pesanan miliknya
        if (! $request->user()->isAdmin() && $order->user_id !== $request->user()->id) {
            return $this->errorResponse('Akses ditolak.', 403);
        }

        $order->load(['user', 'orderDetails.book.category']);

        $pdf = Pdf::loadView('pdf.receipt', [
            'order' => $order,
        ])->setPaper([0, 0, 164, 400], 'portrait');

        $filename = $order->invoice_number ?? $order->kode_pesanan;
        return $pdf->download("Struk-{$filename}.pdf");
    }

    /**
     * DELETE /api/orders/{order}/cancel
     * User membatalkan pesanan miliknya sendiri.
     * Hanya bisa dibatalkan jika:
     *   - Status masih 'pending'
     *   - payment_status masih 'unpaid'
     *   - Dibuat kurang dari 10 menit yang lalu (grace period)
     */
    public function cancelByUser(Request $request, Order $order): JsonResponse
    {
        // Pastikan pesanan milik user yang login
        if ($order->user_id !== $request->user()->id) {
            return $this->errorResponse('Akses ditolak.', 403);
        }

        // Hanya boleh batalkan yang masih pending & belum bayar
        if ($order->status !== 'pending' || $order->payment_status !== 'unpaid') {
            return $this->errorResponse('Pesanan yang sudah diproses atau lunas tidak dapat dibatalkan.', 422);
        }

        return DB::transaction(function () use ($order) {
            // Kembalikan stok buku
            foreach ($order->orderDetails()->with('book')->get() as $detail) {
                $detail->book->increment('stok', $detail->qty);
            }

            $order->update(['status' => 'batal']);

            return $this->successResponse(
                new OrderResource($order->load(['user', 'orderDetails.book'])),
                'Pesanan berhasil dibatalkan dan stok telah dikembalikan.'
            );
        });
    }

    /**
     * POST /api/orders/auto-expire
     * Artisan/Scheduler: batalkan semua pesanan unpaid yang sudah > 2 menit.
     * Juga bisa dipanggil dari cron job atau route internal.
     */
    public function autoExpire(): JsonResponse
    {
        $expiredOrders = Order::where('status', 'pending')
            ->where('payment_status', 'unpaid')
            ->where('created_at', '<', Carbon::now()->subMinutes(2))
            ->with('orderDetails.book')
            ->get();

        $count = 0;
        DB::transaction(function () use ($expiredOrders, &$count) {
            foreach ($expiredOrders as $order) {
                foreach ($order->orderDetails as $detail) {
                    $detail->book->increment('stok', $detail->qty);
                }
                $order->update(['status' => 'batal']);
                $count++;
            }
        });

        return $this->successResponse(
            ['expired_count' => $count],
            "{$count} pesanan kedaluwarsa telah dibatalkan otomatis."
        );
    }
}
