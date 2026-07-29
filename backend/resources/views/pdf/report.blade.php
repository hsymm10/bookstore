<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Penjualan</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 10px; color: #1e293b; }
        .container { padding: 20px; }

        .header { text-align: center; margin-bottom: 16px; border-bottom: 3px solid #3b82f6; padding-bottom: 12px; }
        .header h1 { font-size: 20px; color: #3b82f6; font-weight: bold; }
        .header h2 { font-size: 13px; color: #1e293b; margin-top: 3px; }
        .header p { color: #64748b; font-size: 9px; margin-top: 3px; }

        .summary { display: flex; gap: 10px; margin-bottom: 14px; }
        .summary-card { flex: 1; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px 10px; }
        .summary-card .label { font-size: 9px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; }
        .summary-card .value { font-size: 12px; font-weight: bold; color: #1e293b; margin-top: 2px; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        thead tr { background: #3b82f6; color: white; }
        thead th { padding: 8px 7px; text-align: left; font-size: 9px; font-weight: bold; }
        thead th:nth-child(5),
        thead th:last-child { text-align: right; }
        tbody tr:nth-child(even) { background: #f8fafc; }
        tbody td { padding: 7px 7px; border-bottom: 1px solid #e2e8f0; font-size: 9px; }
        tbody td:nth-child(5),
        tbody td:last-child { text-align: right; }

        .footer { margin-top: 16px; text-align: center; color: #94a3b8; font-size: 8px; border-top: 1px solid #e2e8f0; padding-top: 8px; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>BookStore</h1>
        <h2>LAPORAN PENJUALAN</h2>
        <p>Periode: {{ $filter_label }} &nbsp;|&nbsp; Dicetak: {{ now()->setTimezone('Asia/Jakarta')->format('d M Y H:i') }} WIB</p>
    </div>

    <!-- Summary Cards -->
    <div class="summary">
        <div class="summary-card">
            <div class="label">Total Transaksi</div>
            <div class="value">{{ $total_transaksi }} Pesanan</div>
        </div>
        <div class="summary-card">
            <div class="label">Total Buku Terjual</div>
            <div class="value">{{ $total_buku_terjual }} Buku</div>
        </div>
        <div class="summary-card">
            <div class="label">Total Pendapatan</div>
            <div class="value">Rp {{ number_format($total_pendapatan, 0, ',', '.') }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>No Invoice</th>
                <th>Nama Pembeli</th>
                <th>Nama Buku</th>
                <th>Qty</th>
                <th>Subtotal</th>
                <th>Metode</th>
                <th>Status</th>
                <th>Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach($orders as $order)
                @foreach($order->orderDetails as $detail)
                <tr>
                    <td>{{ $no++ }}</td>
                    <td>{{ $order->invoice_number ?? $order->kode_pesanan }}</td>
                    <td>{{ $order->user?->nama ?? '-' }}</td>
                    <td>{{ $detail->book?->nama_buku ?? 'Buku Terhapus' }}</td>
                    <td style="text-align:right;">{{ $detail->qty }}</td>
                    <td>Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                    <td>{{ $order->payment_method === 'cash' ? 'Tunai' : 'Transfer' }}</td>
                    <td>{{ $order->payment_status === 'paid' ? 'Lunas' : 'Belum Lunas' }}</td>
                    <td>{{ $order->created_at?->setTimezone('Asia/Jakarta')?->format('d/m/Y') }}</td>
                </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Laporan ini dibuat secara otomatis oleh sistem BookStore.</p>
    </div>
</div>
</body>
</html>
