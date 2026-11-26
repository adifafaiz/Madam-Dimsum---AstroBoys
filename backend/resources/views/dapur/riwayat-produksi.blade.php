@extends('dapur.layout')

@section('title', 'Riwayat Produksi - Dapur')
@section('page_title', 'Riwayat Produksi')

@section('content')
<div class="space-y-5">

    <div class="flex flex-col sm:flex-row sm:items-center gap-3">
        <form action="{{ route('dapur.riwayat-produksi') }}" method="GET" class="flex items-center gap-2">
            <div class="field-ikon w-56">
                <i data-lucide="calendar"></i>
                <input type="date" name="tanggal" value="{{ request('tanggal', date('Y-m-d')) }}" onchange="this.form.submit()" class="field" aria-label="Tanggal">
            </div>
            @if(request('tanggal'))
                <a href="{{ route('dapur.riwayat-produksi') }}" class="btn btn-ghost btn-lg text-brand-red"><i data-lucide="x" class="!w-4 !h-4"></i> Reset</a>
            @endif
        </form>

        <p class="sm:ml-auto inline-flex items-center gap-3 h-12 px-4 rounded bg-white border border-brand-border">
            <i data-lucide="chef-hat" class="text-brand-secondary"></i>
            <span class="text-brand-secondary">Total produksi</span>
            <b class="font-display text-2xl font-semibold text-brand-red">{{ $totalPorsi }} porsi</b>
        </p>
    </div>

    <div class="kartu overflow-hidden">
        <div class="overflow-x-auto">
            <table class="tabel">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Produk</th>
                        <th class="text-right">Jumlah</th>
                        <th>Sesi</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($riwayat as $item)
                        @php $sesi = $item->sesi ? ucfirst($item->sesi) : 'Sore'; @endphp
                        <tr>
                            <td class="text-brand-secondary whitespace-nowrap">{{ $item->tanggal ? $item->tanggal->format('d M H:i') : '-' }}</td>
                            <td class="text-base font-semibold">{{ $item->product->nama ?? '-' }}</td>
                            <td class="text-right text-lg font-bold text-[#2E7D4F] whitespace-nowrap">+{{ $item->qty }} porsi</td>
                            <td>
                                <span class="inline-flex items-center gap-1.5 h-7 px-2.5 rounded-full text-[13px] font-semibold {{ $sesi === 'Pagi' ? 'bg-[#FBF1D9] text-[#8A6212]' : 'bg-[#FBEBDC] text-[#A8571A]' }}">
                                    <i data-lucide="{{ $sesi === 'Pagi' ? 'sunrise' : 'sunset' }}" class="!w-3.5 !h-3.5"></i> {{ $sesi }}
                                </span>
                            </td>
                            <td class="text-brand-secondary">{{ $item->keterangan ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="kosong">
                                    @include('partials.ikon-kukusan', ['class' => 'w-14 h-14 text-[#C9BBAE]'])
                                    <p class="kosong-judul">Belum ada produksi di tanggal ini</p>
                                    <p class="kosong-sub">Setiap produksi yang Anda catat akan muncul di sini.</p>
                                    <a href="{{ route('dapur.catat-produksi') }}" class="btn btn-primary mt-4"><i data-lucide="circle-plus" class="!w-4 !h-4"></i> Catat Produksi</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
