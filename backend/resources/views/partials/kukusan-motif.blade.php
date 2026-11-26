{{-- Motif tutup kukusan bambu (dekoratif). Param: $id (unik per halaman), $class. --}}
@php $id = $id ?? 'kukusan'; @endphp
<svg viewBox="0 0 400 400" class="{{ $class ?? '' }}" aria-hidden="true" focusable="false"
     fill="none" stroke="#EADFD3" stroke-linecap="round">
    <defs>
        <pattern id="{{ $id }}-anyam" width="16" height="16" patternUnits="userSpaceOnUse" patternTransform="rotate(45)">
            <path d="M0 0V16M0 0H16" stroke="#EADFD3" stroke-width="2.5"/>
        </pattern>
        <clipPath id="{{ $id }}-isi"><circle cx="200" cy="200" r="160"/></clipPath>
    </defs>

    {{-- Bibir kukusan: dua cincin tebal --}}
    <circle cx="200" cy="200" r="194" stroke-width="7"/>
    <circle cx="200" cy="200" r="168" stroke-width="5"/>

    {{-- Ikatan bilah bambu di antara dua cincin --}}
    @for ($deg = 0; $deg < 360; $deg += 5)
        @php $r = deg2rad($deg); @endphp
        <line x1="{{ round(200 + 173 * cos($r), 2) }}" y1="{{ round(200 + 173 * sin($r), 2) }}"
              x2="{{ round(200 + 189 * cos($r), 2) }}" y2="{{ round(200 + 189 * sin($r), 2) }}" stroke-width="2.5"/>
    @endfor

    {{-- Anyaman tutup --}}
    <rect x="0" y="0" width="400" height="400" fill="url(#{{ $id }}-anyam)" stroke="none" clip-path="url(#{{ $id }}-isi)"/>
    <circle cx="200" cy="200" r="110" stroke-width="3"/>
    <circle cx="200" cy="200" r="54" stroke-width="3"/>
</svg>
