<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Traits\ApiResponse;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReportExport;
use Illuminate\Support\Carbon;

class ReportController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/reports
     * Laporan penjualan dengan filter hari, bulan, tahun.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'filter'     => ['nullable', 'in:hari_ini,minggu_ini,bulan_ini,tahun_ini,custom'],
            'start_date' => ['nullable', 'required_if:filter,custom', 'date'],
            'end_date'   => ['nullable', 'required_if:filter,custom', 'date'],
        ]);

        $query = Order::with(['user', 'orderDetails.book'])
            ->where('payment_status', 'paid');

        $query = $this->applyFilter($query, $request);

        $orders = $query->orderBy('created_at', 'desc')->paginate($request->per_page ?? 20);

        // Summaries based on the exact same filtered query
        $summaryQuery = Order::where('payment_status', 'paid');
        $summaryQuery = $this->applyFilter($summaryQuery, $request);

        $totalTransaksi = $summaryQuery->count();
        $totalPendapatan = (float) $summaryQuery->sum('total');

        $totalBukuTerjual = \App\Models\OrderDetail::whereHas('order', function ($q) use ($request) {
            $q->where('payment_status', 'paid');
            $this->applyFilter($q, $request);
        })->sum('qty');

        // Use OrderResource so user field is a plain name string (not raw JSON object)
        $ordersData = OrderResource::collection($orders)->response()->getData(true);

        return $this->successResponse([
            'orders'             => $ordersData,
            'total_transaksi'    => $totalTransaksi,
            'total_buku_terjual' => (int) $totalBukuTerjual,
            'total_pendapatan'   => $totalPendapatan,
        ], 'Laporan berhasil diambil.');
    }

    /**
     * GET /api/reports/export/pdf
     * Export laporan ke PDF.
     */
    public function exportPdf(Request $request): mixed
    {
        $query = Order::with(['user', 'orderDetails.book'])
            ->where('payment_status', 'paid');

        $query = $this->applyFilter($query, $request);
        $orders = $query->orderBy('created_at', 'desc')->get();

        $totalPendapatan  = $orders->sum('total');
        $totalTransaksi   = $orders->count();
        $totalBukuTerjual = $orders->flatMap(fn($o) => $o->orderDetails)->sum('qty');
        $filterLabel      = $this->getFilterLabel($request);

        $pdf = Pdf::loadView('pdf.report', [
            'orders'             => $orders,
            'total_pendapatan'   => $totalPendapatan,
            'total_transaksi'    => $totalTransaksi,
            'total_buku_terjual' => $totalBukuTerjual,
            'filter_label'       => $filterLabel,
        ])->setPaper('A4', 'landscape');

        return $pdf->download("laporan-penjualan-{$filterLabel}.pdf");
    }

    /**
     * GET /api/reports/export/excel
     * Export laporan ke Excel.
     */
    public function exportExcel(Request $request): mixed
    {
        $filterLabel = $this->getFilterLabel($request);

        return Excel::download(
            new ReportExport($request),
            "laporan-penjualan-{$filterLabel}.xlsx"
        );
    }

    // ─── Private Helpers ──────────────────────────────────────────

    private function applyFilter($query, Request $request)
    {
        $filter = $request->filter ?? 'bulan_ini';
        $now = Carbon::now('Asia/Jakarta');

        return match ($filter) {
            'hari_ini'   => $query->whereDate('created_at', $now->toDateString()),
            'minggu_ini' => $query->whereBetween('created_at', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()]),
            'bulan_ini'  => $query->whereYear('created_at', $now->year)->whereMonth('created_at', $now->month),
            'tahun_ini'  => $query->whereYear('created_at', $now->year),
            'custom'     => $query->whereBetween('created_at', [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay()
            ]),
            default      => $query->whereYear('created_at', $now->year)->whereMonth('created_at', $now->month),
        };
    }

    private function getFilterLabel(Request $request): string
    {
        $filter = $request->filter ?? 'bulan_ini';
        $now = Carbon::now('Asia/Jakarta');

        return match ($filter) {
            'hari_ini'   => $now->format('Y-m-d'),
            'minggu_ini' => 'Minggu-Ini-' . $now->format('Y-W'),
            'bulan_ini'  => $now->format('F-Y'),
            'tahun_ini'  => (string) $now->year,
            'custom'     => $request->start_date . '_to_' . $request->end_date,
            default      => $now->format('F-Y'),
        };
    }
}
