<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $order->kode_pesanan }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 12px; color: #1e293b; }
        .container { padding: 30px; }

        /* Header */
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 30px; border-bottom: 3px solid #3b82f6; padding-bottom: 20px; }
        .brand { }
        .brand h1 { font-size: 28px; color: #3b82f6; font-weight: bold; letter-spacing: -1px; }
        .brand p { color: #64748b; font-size: 11px; margin-top: 2px; }
        .invoice-info { text-align: right; }
        .invoice-info h2 { font-size: 20px; color: #1e293b; font-weight: bold; text-transform: uppercase; letter-spacing: 2px; }
        .invoice-info p { color: #64748b; font-size: 11px; margin-top: 4px; }

        /* Status Badge */
        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; margin-top: 6px; }
        .status-selesai { background: #dcfce7; color: #16a34a; }
        .status-pending { background: #fef9c3; color: #ca8a04; }
        .status-diproses { background: #dbeafe; color: #2563eb; }
        .status-batal { background: #fee2e2; color: #dc2626; }
        .status-paid { background: #dcfce7; color: #16a34a; }
        .status-unpaid { background: #fef9c3; color: #ca8a04; }

        /* Info Section */
        .info-grid { display: flex; gap: 20px; margin-bottom: 24px; }
        .info-box { flex: 1; background: #f8fafc; border-radius: 8px; padding: 14px; border: 1px solid #e2e8f0; }
        .info-box h3 { font-size: 10px; text-transform: uppercase; letter-spacing: 1px; color: #94a3b8; margin-bottom: 8px; font-weight: bold; }
        .info-box p { font-size: 12px; color: #1e293b; line-height: 1.6; }

        /* Table */
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table thead tr { background: #3b82f6; color: white; }
        table thead th { padding: 10px 12px; text-align: left; font-size: 11px; font-weight: bold; }
        table thead th:last-child { text-align: right; }
        table tbody tr:nth-child(even) { background: #f8fafc; }
        table tbody td { padding: 10px 12px; border-bottom: 1px solid #e2e8f0; font-size: 11px; }
        table tbody td:last-child { text-align: right; }

        /* Summary */
        .summary { margin-left: auto; width: 280px; }
        .summary-row { display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid #e2e8f0; }
        .summary-row.total { font-weight: bold; font-size: 14px; color: #3b82f6; border-bottom: 2px solid #3b82f6; }
        .summary-row.kembalian { color: #16a34a; font-weight: bold; }

        /* Footer */
        .footer { margin-top: 30px; text-align: center; color: #94a3b8; font-size: 10px; border-top: 1px solid #e2e8f0; padding-top: 14px; }
    </style>
</head>
<body>
<div class="container">
    <!-- Header -->
    <div class="header">
        <div class="brand">
            <h1>BookStore</h1>
            <p>Toko Buku Terpercaya</p>
        </div>
        <div class="invoice-info">
            <h2>Invoice</h2>
            <p><strong>No Invoice:</strong> {{ $order->invoice_number ?? $order->kode_pesanan }}</p>
            <p><strong>Kode Pesanan:</strong> {{ $order->kode_pesanan }}</p>
            <p><strong>Tanggal:</strong> {{ $order->created_at?->setTimezone('Asia/Jakarta')?->format('d M Y, H:i') }} WIB</p>
            <div class="status-badge status-{{ $order->status }}">{{ ucfirst($order->status) }}</div>
        </div>
    </div>

    <!-- Info -->
    <div class="info-grid">
        <div class="info-box">
            <h3>Data Pembeli</h3>
            <p><strong>{{ $order->user?->nama ?? '-' }}</strong></p>
            <p>{{ $order->user?->username ? '@' . $order->user->username : '' }}</p>
            <p>{{ $order->user?->email ?? '' }}</p>
            <p>{{ $order->user?->no_hp ?? '' }}</p>
        </div>
        <div class="info-box">
            <h3>Info Pesanan</h3>
            <p><strong>Kode:</strong> {{ $order->kode_pesanan }}</p>
            <p><strong>Status Pesanan:</strong> {{ ucfirst($order->status) }}</p>
            <p><strong>Metode Pembayaran:</strong> {{ $order->payment_method === 'cash' ? 'Tunai' : 'Transfer' }}</p>
            <p><strong>Status Bayar:</strong>
                <span class="status-badge status-{{ $order->payment_status }}">
                    {{ $order->payment_status === 'paid' ? 'Lunas' : 'Belum Lunas' }}
                </span>
            </p>
            @if($order->paid_at)
            <p><strong>Waktu Bayar:</strong> {{ $order->paid_at?->setTimezone('Asia/Jakarta')?->format('d M Y H:i') }} WIB</p>
            @endif
            <p><strong>Jumlah Item:</strong> {{ $order->orderDetails->count() }} jenis buku</p>
        </div>
    </div>

    <!-- Detail Buku -->
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Buku</th>
                <th>Kategori</th>
                <th>Harga Satuan</th>
                <th>Qty</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->orderDetails as $i => $detail)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $detail->book?->nama_buku ?? 'Buku Terhapus' }}</td>
                <td>{{ $detail->book?->category?->nama ?? '-' }}</td>
                <td>Rp {{ number_format($detail->harga, 0, ',', '.') }}</td>
                <td>{{ $detail->qty }}</td>
                <td>Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Summary -->
    <div class="summary">
        <div class="summary-row total">
            <span>TOTAL TAGIHAN</span>
            <span>Rp {{ number_format($order->total, 0, ',', '.') }}</span>
        </div>
        @if($order->payment_status === 'paid')
        <div class="summary-row">
            <span>Nominal Dibayar</span>
            <span>Rp {{ number_format($order->amount_paid ?? $order->total, 0, ',', '.') }}</span>
        </div>
        <div class="summary-row kembalian">
            <span>Kembalian</span>
            <span>Rp {{ number_format($order->change_amount ?? 0, 0, ',', '.') }}</span>
        </div>
        @endif
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Terima kasih telah berbelanja di <strong>BookStore</strong></p>
        <p>Dokumen ini dicetak secara otomatis pada {{ now()->setTimezone('Asia/Jakarta')->format('d M Y H:i') }} WIB</p>
    </div>
</div>
</body>
</html>
