<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Order;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/public-stats
     * Statistik untuk halaman utama (public).
     */
    public function publicStats(): JsonResponse
    {
        $totalBuku     = Book::count();
        $totalUser     = User::where('role', 'user')->count();
        $totalKategori = \App\Models\Category::count();

        // Bisa disimulasikan 99% kepuasan
        $kepuasan = 99;

        return $this->successResponse([
            'books'      => $totalBuku,
            'users'      => $totalUser,
            'categories' => $totalKategori,
            'satisfaction'=> $kepuasan
        ], 'Public stats fetched.');
    }

    /**
     * GET /api/dashboard
     * Statistik dan data grafik untuk admin dashboard.
     */
    public function index(): JsonResponse
    {
        // ─── Statistik Utama ──────────────────────────────────────
        $totalUser     = User::where('role', 'user')->count();
        $totalBuku     = Book::count();
        $totalPesanan  = Order::count();
        $totalPendapatan = Order::where('status', 'selesai')->sum('total');

        // ─── Grafik Penjualan (12 bulan terakhir) ─────────────────
        $grafikPenjualan = Order::where('status', 'selesai')
            ->whereYear('created_at', now()->year)
            ->select(
                DB::raw('MONTH(created_at) as bulan'),
                DB::raw('SUM(total) as pendapatan'),
                DB::raw('COUNT(*) as jumlah_pesanan')
            )
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get()
            ->keyBy('bulan');

        // Format 12 bulan (isi 0 jika tidak ada data)
        $bulanLabels  = [];
        $pendapatanData = [];
        $pesananData  = [];
        $namaBulan    = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
                         'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

        for ($i = 1; $i <= 12; $i++) {
            $bulanLabels[]    = $namaBulan[$i];
            $pendapatanData[] = $grafikPenjualan->get($i)?->pendapatan ?? 0;
            $pesananData[]    = $grafikPenjualan->get($i)?->jumlah_pesanan ?? 0;
        }

        // ─── Pesanan Terbaru ──────────────────────────────────────
        $pesananTerbaru = Order::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(fn ($order) => [
                'kode_pesanan' => $order->kode_pesanan,
                'user'         => $order->user?->nama,
                'total'        => (float) $order->total,
                'status'       => $order->status,
                'created_at'   => $order->created_at?->format('d M Y H:i'),
            ]);

        // ─── Buku Best Seller ─────────────────────────────────────
        $bestSeller = DB::table('order_details')
            ->join('books', 'order_details.book_id', '=', 'books.id')
            ->join('orders', 'order_details.order_id', '=', 'orders.id')
            ->where('orders.status', 'selesai')
            ->select(
                'books.id',
                'books.nama_buku',
                'books.gambar',
                'books.harga_jual',
                DB::raw('SUM(order_details.qty) as total_terjual')
            )
            ->groupBy('books.id', 'books.nama_buku', 'books.gambar', 'books.harga_jual')
            ->orderByDesc('total_terjual')
            ->limit(5)
            ->get()
            ->map(fn ($book) => [
                'id'           => $book->id,
                'nama_buku'    => $book->nama_buku,
                'gambar_url'   => $book->gambar ? asset('storage/' . $book->gambar) : null,
                'harga_jual'   => (float) $book->harga_jual,
                'total_terjual' => $book->total_terjual,
            ]);

        return $this->successResponse([
            'statistik' => [
                'total_user'       => $totalUser,
                'total_buku'       => $totalBuku,
                'total_pesanan'    => $totalPesanan,
                'total_pendapatan' => (float) $totalPendapatan,
            ],
            'grafik_penjualan' => [
                'labels'     => $bulanLabels,
                'pendapatan' => $pendapatanData,
                'pesanan'    => $pesananData,
            ],
            'pesanan_terbaru' => $pesananTerbaru,
            'best_seller'     => $bestSeller,
        ], 'Data dashboard berhasil diambil.');
    }
}
