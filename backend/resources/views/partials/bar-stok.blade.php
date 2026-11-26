{{--
    Bar kecil stok vs minimum. Skala penuh = 2 x stok_minimum (target restock, BR-04);
    garis tegak menandai batas minimum (di tengah). Param: $produk.
--}}
@php
    $minimum = max(1, (int) $produk->stok_minimum);
    $persen = min(100, max(0, round($produk->stok / (2 * $minimum) * 100)));
    $warnaBar = $produk->stok <= 0 ? '#C8372D' : ($produk->stok < $produk->stok_minimum ? '#D9822B' : '#2E8B57');
@endphp
<span class="relative inline-block w-24 h-2 rounded-full bg-[#F0E7DD] align-middle"
      role="img" aria-label="Stok {{ $produk->stok }} dari minimum {{ $produk->stok_minimum }}"
      title="Stok {{ $produk->stok }} · minimum {{ $produk->stok_minimum }}">
    <span class="absolute inset-y-0 left-0 rounded-full" style="width: {{ $persen }}%; background: {{ $warnaBar }}"></span>
    <span class="absolute -top-1 -bottom-1 left-1/2 w-0.5 -ml-px bg-[#8A7B70]/60 rounded-full"></span>
</span>
