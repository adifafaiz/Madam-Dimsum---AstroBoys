{{--
    Slot foto produk. Taruh foto di public/images/produk/<slug-nama>.jpg|png|webp,
    misal "Siomay Ayam" -> public/images/produk/siomay-ayam.jpg. Tanpa foto: placeholder kukusan.
    Param: $produk, $class, $ikon (ukuran ikon placeholder).
--}}
@php
    $slugFoto = \Illuminate\Support\Str::slug($produk->nama);
    $fotoProduk = null;
    foreach (['jpg', 'png', 'webp'] as $ext) {
        if (file_exists(public_path("images/produk/{$slugFoto}.{$ext}"))) { $fotoProduk = "images/produk/{$slugFoto}.{$ext}"; break; }
    }
@endphp
<div class="{{ $class ?? '' }} overflow-hidden bg-[#F5EEE6]">
    @if($fotoProduk)
        <img src="{{ asset($fotoProduk) }}" alt="{{ $produk->nama }}" class="w-full h-full object-cover" loading="lazy">
    @else
        <div class="w-full h-full flex items-center justify-center text-[#D2C2B3]">
            @include('partials.ikon-kukusan', ['class' => $ikon ?? 'w-12 h-12'])
        </div>
    @endif
</div>
