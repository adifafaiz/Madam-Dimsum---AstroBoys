<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk {{ $order->kode }} - Madam Dimsum</title>
    {{-- Sengaja tanpa CDN: halaman cetak harus langsung siap dicetak. --}}
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0; padding: 32px 16px; background: #FBF7F2; color: #000;
            font-family: 'Courier New', Courier, monospace; font-size: 13px; line-height: 1.45;
        }
        .kertas {
            position: relative; max-width: 320px; margin: 12px auto 0; padding: 22px 20px 26px; background: #fff;
            filter: drop-shadow(0 0 .6px #CDBBAA);
        }
        /* Tepi atas & bawah bergerigi seperti kertas struk yang disobek */
        .kertas::before, .kertas::after {
            content: ''; position: absolute; left: 0; right: 0; height: 9px;
            background-size: 14px 9px; background-repeat: repeat-x;
        }
        .kertas::before { top: -9px; background-image: radial-gradient(circle at 7px 0, transparent 5.5px, #fff 6px); }
        .kertas::after  { bottom: -9px; background-image: radial-gradient(circle at 7px 9px, transparent 5.5px, #fff 6px); }

        .tengah { text-align: center; }
        .tebal { font-weight: bold; }
        .kecil { font-size: 11px; }
        .logo { width: 56px; height: 56px; object-fit: contain; filter: grayscale(1) contrast(1.2); margin-bottom: 6px; }
        .garis { border: 0; border-top: 1px dashed #000; margin: 10px 0; }
        .baris { display: flex; justify-content: space-between; gap: 8px; }
        .item { margin-bottom: 6px; }
        .item .harga-satuan { padding-left: 3ch; font-size: 11px; color: #444; }
        .total { font-size: 15px; font-weight: bold; }
        .ulang { display: inline-block; border: 1px solid #000; padding: 1px 8px; font-size: 11px; font-weight: bold; letter-spacing: .08em; margin-bottom: 10px; }
        .info div { display: grid; grid-template-columns: 9ch 1fr; }

        .aksi { max-width: 320px; margin: 24px auto 0; display: flex; gap: 10px;
            font-family: Inter, system-ui, sans-serif; }
        .tombol { flex: 1; display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 48px;
            border-radius: 6px; font-size: 16px; font-weight: 600; cursor: pointer; border: 1px solid transparent; font-family: inherit; }
        .tombol svg { width: 18px; height: 18px; }
        .tombol-utama { background: #C8372D; color: #fff; }
        .tombol-utama:hover { background: #B22F26; }
        .tombol-kedua { background: #fff; color: #2A211C; border-color: #EADFD3; }
        .tombol-kedua:hover { background: #FBF7F2; }

        @media print {
            @page { margin: 4mm; }
            body { background: none; padding: 0; }
            .kertas { max-width: 100%; margin: 0; padding: 0; filter: none; }
            .kertas::before, .kertas::after, .aksi { display: none; }
        }
    </style>
</head>
<body>

    <div class="kertas">
        @if($isCetakUlang)
            <div class="tengah"><span class="ulang">*** [CETAK ULANG] ***</span></div>
        @endif

        <div class="tengah">
            @php $logoFile = file_exists(public_path('images/logo.png')) ? 'images/logo.png' : 'images/logo-madam-dimsum.jpg'; @endphp
            <img src="{{ asset($logoFile) }}" alt="" class="logo">
            <div class="tebal" style="font-size: 16px; letter-spacing: .06em;">MADAM DIMSUM</div>
            <div class="kecil">DimsumKu v2 - Tim Astroboys</div>
            <div class="kecil">Cabang Utama</div>
        </div>

        <hr class="garis">

        <div class="info kecil">
            <div><span>No.</span><span>{{ $order->kode }}</span></div>
            <div><span>Waktu</span><span>{{ $order->waktu ? $order->waktu->format('d/m/Y H:i') : '-' }}</span></div>
            <div><span>Kasir</span><span>{{ $order->user->name ?? 'Kasir' }}</span></div>
        </div>

        <hr class="garis">

        @foreach($order->items as $item)
            <div class="item">
                <div class="baris">
                    <span>{{ $item->qty }}x {{ $item->product->nama ?? 'Item' }}</span>
                    <span>{{ number_format($item->subtotal, 0, ',', '.') }}</span>
                </div>
                <div class="harga-satuan">@ {{ number_format($item->harga_satuan, 0, ',', '.') }}</div>
            </div>
        @endforeach

        <hr class="garis">

        <div class="baris total">
            <span>TOTAL</span>
            <span>Rp {{ number_format($order->total, 0, ',', '.') }}</span>
        </div>
        <div class="baris">
            <span>DIBAYAR</span>
            <span>Rp {{ number_format($order->dibayar, 0, ',', '.') }}</span>
        </div>
        <div class="baris">
            <span>KEMBALIAN</span>
            <span>Rp {{ number_format($order->kembalian, 0, ',', '.') }}</span>
        </div>

        <hr class="garis">

        <div class="tengah kecil" style="margin-top: 12px;">
            <div>Terima kasih atas kunjungan Anda!</div>
            <div>Produksi tertata, stok terjaga.</div>
        </div>
    </div>

    <div class="aksi">
        <button type="button" onclick="window.close()" class="tombol tombol-kedua">Tutup</button>
        <button type="button" onclick="window.print()" class="tombol tombol-utama">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                <path d="M6 9V3a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v6"/>
                <rect x="6" y="14" width="12" height="8" rx="1"/>
            </svg>
            Cetak Struk
        </button>
    </div>

</body>
</html>
