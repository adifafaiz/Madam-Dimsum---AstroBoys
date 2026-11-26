{{-- Label status stok (BR-03). Param: $status = Aman | Menipis | Habis. --}}
@php
    $gaya = [
        'Aman'    => ['bg-[#E6F3EB] text-[#2E7D4F]', '#2E8B57'],
        'Menipis' => ['bg-[#FBEBDC] text-[#A8571A]', '#D9822B'],
        'Habis'   => ['bg-[#FCE6E3] text-[#B0261D]', '#C8372D'],
    ][$status] ?? ['bg-[#F3ECE4] text-brand-secondary', '#8A7B70'];
@endphp
<span class="inline-flex items-center gap-1.5 h-7 px-2.5 rounded-full text-[13px] font-semibold {{ $gaya[0] }}">
    <span class="w-1.5 h-1.5 rounded-full" style="background: {{ $gaya[1] }}"></span>
    {{ $status }}
</span>
