{{-- Logo Madam Dimsum. Pakai public/images/logo.png bila ada, selain itu logo JPG lama. --}}
@php
    $logoFile = file_exists(public_path('images/logo.png')) ? 'images/logo.png' : 'images/logo-madam-dimsum.jpg';
@endphp
<img src="{{ asset($logoFile) }}" alt="Madam Dimsum" class="{{ $class ?? 'w-10 h-10' }} object-contain bg-white rounded">
