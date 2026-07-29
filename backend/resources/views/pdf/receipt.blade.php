<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Struk {{ $order->invoice_number ?? $order->kode_pesanan }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans Mono', 'Courier New', Courier, monospace;
            font-size: 9px;
            color: #000;
            width: 164px;
            padding: 8px;
        }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        .divider-solid { border-top: 1px solid #000; margin: 4px 0; }
        .divider-dashed { border-top: 1px dashed #000; margin: 4px 0; }
        .flex-between { display: flex; justify-content: space-between; }
        .brand { font-size: 11px; font-weight: bold; text-align: center; }
        .item-name { font-weight: bold; margin-bottom: 1px; }
        .item-detail { display: flex; justify-content: space-between; padding-left: 6px; margin-bottom: 3px; }
        .total-section { margin-top: 2px; }
        .grand-total { font-weight: bold; font-size: 10px; }
        .status-ok { text-align: center; font-weight: bold; font-size: 10px; margin: 4px 0; }
        .footer-text { text-align: center; margin-top: 4px; font-size: 8px; }
    </style>
</head>
<body>
    <div class="center">
        <div class="brand">============================</div>
        <div class="brand">BOOK STORE</div>
        <div class="brand">============================</div>
    </div>

    <div style="margin-top:4px;">
        <div>No Invoice: {{ $order->invoice_number ?? $order->kode_pesanan }}</div>
        <div>Kode     : {{ $order->kode_pesanan }}</div>
        <div>Tanggal  : {{ $order->created_at?->setTimezone('Asia/Jakarta')?->format('d/m/Y H:i') }} WIB</div>
        <div>Kasir    : {{ $order->user?->nama ?? 'Pelanggan' }}</div>
        <div>Metode   : {{ $order->payment_method === 'cash' ? 'Tunai' : 'Transfer' }}</div>
    </div>

    <div class="divider-dashed"></div>

    <!-- Items -->
    @foreach($order->orderDetails as $detail)
    <div class="item-name">{{ $detail->book?->nama_buku ?? 'Buku Terhapus' }}</div>
    <div class="item-detail">
        <span>{{ $detail->qty }} x Rp {{ number_format($detail->harga, 0, ',', '.') }}</span>
        <span>Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</span>
    </div>
    @endforeach

    <div class="divider-dashed"></div>

    <!-- Totals -->
    <div class="total-section">
        <div class="flex-between">
            <span>Subtotal</span>
            <span>Rp {{ number_format($order->total, 0, ',', '.') }}</span>
        </div>
        <div class="flex-between">
            <span>Diskon</span>
            <span>Rp0</span>
        </div>
        <div class="divider-solid"></div>
        <div class="flex-between grand-total">
            <span>TOTAL</span>
            <span>Rp {{ number_format($order->total, 0, ',', '.') }}</span>
        </div>
        @if($order->payment_status === 'paid')
        <div class="flex-between" style="margin-top:2px;">
            <span>Dibayar</span>
            <span>Rp {{ number_format($order->amount_paid ?? $order->total, 0, ',', '.') }}</span>
        </div>
        <div class="flex-between">
            <span>Kembali</span>
            <span>Rp {{ number_format($order->change_amount ?? 0, 0, ',', '.') }}</span>
        </div>
        @endif
    </div>

    <div class="divider-solid"></div>

    <div class="status-ok">
        Status: {{ $order->payment_status === 'paid' ? 'LUNAS' : 'BELUM LUNAS' }}
    </div>

    <div class="footer-text">
        Terima kasih telah berbelanja<br>
        di Book Store<br>
        ============================
    </div>
</body>
</html>
