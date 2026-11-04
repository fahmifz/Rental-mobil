@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Laporan" />

    @if ($errors->any())
        <div class="mb-6 rounded-xl border border-error-200 bg-error-50 px-4 py-3 text-sm text-error-700 dark:border-error-500/20 dark:bg-error-500/15 dark:text-error-400" role="alert">
            @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
        </div>
    @endif

    <x-common.component-card title="Periode Laporan" desc="Statistik reservasi berdasarkan tanggal pembuatan reservasi; pembayaran masuk berdasarkan tanggal pembayaran; refund berdasarkan tanggal pengembalian dana yang telah dikonfirmasi.">
        <form action="{{ route('admin.laporan.index') }}" method="GET" class="flex flex-wrap items-end gap-4">
            <div class="w-full sm:w-52">
                <label for="tanggal_awal" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">Tanggal awal</label>
                <input id="tanggal_awal" type="date" name="tanggal_awal" value="{{ $tanggalAwal }}" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-theme-sm text-gray-800 shadow-theme-xs outline-hidden focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
            </div>
            <div class="w-full sm:w-52">
                <label for="tanggal_akhir" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">Tanggal akhir</label>
                <input id="tanggal_akhir" type="date" name="tanggal_akhir" value="{{ $tanggalAkhir }}" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-theme-sm text-gray-800 shadow-theme-xs outline-hidden focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
            </div>
            <div class="flex gap-3">
                <button type="submit" class="inline-flex h-11 items-center justify-center rounded-lg bg-brand-500 px-5 text-theme-sm font-medium text-white transition hover:bg-brand-600">Terapkan</button>
                @if ($tanggalAwal || $tanggalAkhir)
                    <a href="{{ route('admin.laporan.index') }}" class="inline-flex h-11 items-center justify-center rounded-lg bg-white px-4 text-theme-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 transition hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:ring-gray-700">Reset</a>
                @endif
            </div>
        </form>
    </x-common.component-card>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]"><p class="text-theme-sm text-gray-500 dark:text-gray-400">Total reservasi</p><p class="mt-2 text-title-md font-semibold text-gray-900 dark:text-white">{{ $totalReservasi }}</p></div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]"><p class="text-theme-sm text-gray-500 dark:text-gray-400">Reservasi selesai</p><p class="mt-2 text-title-md font-semibold text-success-600 dark:text-success-400">{{ $totalReservasiSelesai }}</p></div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]"><p class="text-theme-sm text-gray-500 dark:text-gray-400">Reservasi dibatalkan</p><p class="mt-2 text-title-md font-semibold text-error-600 dark:text-error-400">{{ $totalReservasiDibatalkan }}</p></div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]"><p class="text-theme-sm text-gray-500 dark:text-gray-400">Pembayaran masuk</p><p class="mt-2 text-title-md font-semibold text-gray-900 dark:text-white">Rp{{ number_format($totalPembayaranMasuk, 0, ',', '.') }}</p></div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]"><p class="text-theme-sm text-gray-500 dark:text-gray-400">Refund dikembalikan</p><p class="mt-2 text-title-md font-semibold text-error-600 dark:text-error-400">Rp{{ number_format($totalRefundDikembalikan, 0, ',', '.') }}</p></div>
        <div class="rounded-2xl border border-brand-200 bg-brand-50 p-5 dark:border-brand-500/30 dark:bg-brand-500/10"><p class="text-theme-sm text-brand-700 dark:text-brand-300">Pendapatan bersih</p><p class="mt-2 text-title-md font-semibold text-brand-700 dark:text-brand-300">Rp{{ number_format($pendapatanBersih, 0, ',', '.') }}</p></div>
    </div>

    <x-common.component-card class="mt-6" title="Rincian Reservasi" desc="Data reservasi mengikuti periode berdasarkan tanggal pembuatan reservasi.">
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="max-w-full overflow-x-auto custom-scrollbar">
                <table class="w-full min-w-[1080px]">
                    <thead><tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="px-5 py-3 text-start sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Reservasi</p></th>
                        <th class="px-5 py-3 text-start sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Pelanggan</p></th>
                        <th class="px-5 py-3 text-start sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Mobil</p></th>
                        <th class="px-5 py-3 text-start sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Tanggal penyewaan</p></th>
                        <th class="px-5 py-3 text-start sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Status</p></th>
                        <th class="px-5 py-3 text-end sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Total biaya</p></th>
                    </tr></thead>
                    <tbody>
                        @forelse ($reservasis as $reservasi)
                            @php
                                $statusColor = match ($reservasi->status) {
                                    'menunggu_konfirmasi' => 'warning',
                                    'disetujui' => 'success',
                                    'ditolak' => 'error',
                                    'dibatalkan' => 'light',
                                    'selesai' => 'primary',
                                };
                            @endphp
                            <tr class="border-b border-gray-100 last:border-b-0 dark:border-gray-800">
                                <td class="px-5 py-4 text-theme-sm font-medium text-gray-800 dark:text-white/90">#{{ $reservasi->id }}</td>
                                <td class="px-5 py-4"><p class="text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $reservasi->user->name }}</p><p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ $reservasi->user->email }}</p></td>
                                <td class="px-5 py-4 text-theme-sm text-gray-700 dark:text-gray-300">{{ $reservasi->mobil->nama_mobil }}</td>
                                <td class="px-5 py-4 text-theme-sm text-gray-600 dark:text-gray-400">{{ $reservasi->tanggal_mulai->format('d M Y') }} – {{ $reservasi->tanggal_selesai->format('d M Y') }}</td>
                                <td class="px-5 py-4"><x-ui.badge :color="$statusColor">{{ ucwords(str_replace('_', ' ', $reservasi->status)) }}</x-ui.badge></td>
                                <td class="px-5 py-4 text-end text-theme-sm font-medium text-gray-800 dark:text-white/90 sm:px-6">Rp{{ number_format((float) $reservasi->total_biaya, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-10 text-center text-theme-sm text-gray-500 dark:text-gray-400">Tidak ada reservasi pada periode yang dipilih.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($reservasis->hasPages())
            <div class="pt-4">{{ $reservasis->links() }}</div>
        @endif
    </x-common.component-card>
@endsection
