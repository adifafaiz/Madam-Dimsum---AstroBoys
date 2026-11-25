<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk {{ $order->kode }} - Madam Dimsum</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            background-color: #f5f5f5;
            padding: 20px;
            color: #000;
        }
        .receipt {
            max-width: 300px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border: 1px dashed #ccc;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .divider { border-top: 1px dashed #000; margin: 10px 0; }
        .item-row { display: flex; justify-content: space-between; margin-bottom: 5px; font-size: 12px; }
        .print-btn {
            display: block;
            width: 100%;
            margin-top: 15px;
            padding: 8px;
            background: #C8372D;
            color: white;
            border: none;
            cursor: pointer;
            font-weight: bold;
            border-radius: 4px;
        }
        @media print {
            body { background: none; padding: 0; }
            .receipt { border: none; box-shadow: none; width: 100%; max-width: 100%; padding: 0; }
            .print-btn { display: none; }
        }
    </style>
</head>
<body>

    <div class="receipt">
        <!-- Cetak Ulang Indicator (BR-09) -->
        @if($isCetakUlang)
            <div class="text-center font-bold" style="font-size: 11px; margin-bottom: 8px;">
                *** [CETAK ULANG] ***
            </div>
        @endif

        <div class="text-center">
            <h2 style="margin: 0; font-size: 16px;">MADAM DIMSUM</h2>
            <p style="margin: 3px 0; font-size: 11px;">DimsumKu v2 - Tim Astroboys</p>
            <p style="margin: 3px 0; font-size: 10px;">Cabang Utama</p>
        </div>

        <div class="divider"></div>

        <div style="font-size: 11px; line-height: 1.4;">
            <div>No. Struk : {{ $order->kode }}</div>
            <div>Waktu     : {{ $order->waktu ? $order->waktu->format('d/m/Y H:i') : '-' }}</div>
            <div>Kasir     : {{ $order->user->name ?? 'Kasir' }}</div>
        </div>

        <div class="divider"></div>

        <!-- Daftar Item (BR-08) -->
        @foreach($order->items as $item)
            <div class="item-row">
                <span>{{ $item->product->nama ?? 'Item' }} x{{ $item->qty }}</span>
                <span>{{ number_format($item->subtotal, 0, ',', '.') }}</span>
            </div>
        @endforeach

        <div class="divider"></div>

        <div class="item-row font-bold">
            <span>TOTAL</span>
            <span>Rp {{ number_format($order->total, 0, ',', '.') }}</span>
        </div>
        <div class="item-row">
            <span>DIBAYAR</span>
            <span>Rp {{ number_format($order->dibayar, 0, ',', '.') }}</span>
        </div>
        <div class="item-row">
            <span>KEMBALIAN</span>
            <span>Rp {{ number_format($order->kembalian, 0, ',', '.') }}</span>
        </div>

        <div class="divider"></div>

        <div class="text-center" style="font-size: 11px; margin-top: 15px;">
            <p style="margin: 2px 0;">Terima kasih atas kunjungan Anda!</p>
            <p style="margin: 2px 0;">Produksi tertata, stok terjaga.</p>
        </div>

        <button onclick="window.print()" class="print-btn">Cetak Struk</button>
    </div>

</body>
</html>

