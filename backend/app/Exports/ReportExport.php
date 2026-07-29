<?php

namespace App\Exports;

use App\Models\OrderDetail;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Carbon;

class ReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected Request $request;
    protected int $no = 0;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $query = OrderDetail::with(['order.user', 'book'])
            ->whereHas('order', function ($q) {
                $q->where('payment_status', 'paid');
            });

        $filter = $this->request->filter ?? 'bulan_ini';
        $now = Carbon::now('Asia/Jakarta');

        $query = match ($filter) {
            'hari_ini'   => $query->whereHas('order', fn($q) => $q->whereDate('created_at', $now->toDateString())),
            'minggu_ini' => $query->whereHas('order', fn($q) => $q->whereBetween('created_at', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()])),
            'bulan_ini'  => $query->whereHas('order', fn($q) => $q->whereYear('created_at', $now->year)->whereMonth('created_at', $now->month)),
            'tahun_ini'  => $query->whereHas('order', fn($q) => $q->whereYear('created_at', $now->year)),
            'custom'     => $query->whereHas('order', fn($q) => $q->whereBetween('created_at', [
                Carbon::parse($this->request->start_date)->startOfDay(),
                Carbon::parse($this->request->end_date)->endOfDay()
            ])),
            default      => $query->whereHas('order', fn($q) => $q->whereYear('created_at', $now->year)->whereMonth('created_at', $now->month)),
        };

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Nomor Invoice',
            'Nama Pembeli',
            'Nama Buku',
            'Jumlah Buku (Qty)',
            'Total Harga (Subtotal)',
            'Metode Pembayaran',
            'Status Pembayaran',
            'Tanggal Pembelian',
        ];
    }

    public function map($detail): array
    {
        $this->no++;
        $order = $detail->order;

        return [
            $this->no,
            $order->invoice_number ?? $order->kode_pesanan,
            $order->user?->nama ?? '-',
            $detail->book?->nama_buku ?? 'Buku Terhapus',
            $detail->qty,
            'Rp ' . number_format($detail->subtotal, 0, ',', '.'),
            $order->payment_method === 'cash' ? 'Tunai' : 'Transfer',
            $order->payment_status === 'paid' ? 'Lunas' : 'Belum Lunas',
            $order->created_at?->setTimezone('Asia/Jakarta')?->format('d/m/Y H:i') . ' WIB',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => '3B82F6']],
                'alignment' => ['horizontal' => 'center'],
            ],
        ];
    }

    public function title(): string
    {
        return 'Laporan Penjualan';
    }
}
