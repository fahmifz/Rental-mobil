@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Detail Pelanggan" />

    <x-common.component-card title="Informasi Pelanggan" desc="Profil singkat dan ringkasan aktivitas reservasi pelanggan.">
        <dl class="divide-y divide-gray-100 border-y border-gray-100 dark:divide-gray-800 dark:border-gray-800">
            <div class="flex flex-wrap justify-between gap-3 py-4"><dt class="text-theme-sm text-gray-500 dark:text-gray-400">Nama</dt><dd class="text-end text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $pelanggan->name }}</dd></div>
            <div class="flex flex-wrap justify-between gap-3 py-4"><dt class="text-theme-sm text-gray-500 dark:text-gray-400">Email</dt><dd class="text-end text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $pelanggan->email }}</dd></div>
            <div class="flex flex-wrap justify-between gap-3 py-4"><dt class="text-theme-sm text-gray-500 dark:text-gray-400">Terdaftar</dt><dd class="text-end text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $pelanggan->created_at?->format('d M Y H:i') ?? '-' }}</dd></div>
            <div class="flex flex-wrap justify-between gap-3 py-4"><dt class="text-theme-sm text-gray-500 dark:text-gray-400">Total reservasi</dt><dd><x-ui.badge color="primary">{{ $pelanggan->reservasis_count }} reservasi</x-ui.badge></dd></div>
        </dl>
    </x-common.component-card>

    <x-common.component-card class="mt-6" title="Riwayat Reservasi" desc="Seluruh reservasi yang dibuat oleh pelanggan ini.">
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="max-w-full overflow-x-auto custom-scrollbar">
                <table class="w-full min-w-[940px]">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <th class="px-5 py-3 text-start sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Mobil</p></th>
                            <th class="px-5 py-3 text-start sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Tanggal sewa</p></th>
                            <th class="px-5 py-3 text-start sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Durasi</p></th>
                            <th class="px-5 py-3 text-start sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Biaya</p></th>
                            <th class="px-5 py-3 text-start sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Status</p></th>
                        </tr>
                    </thead>
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
                                <td class="px-5 py-4 sm:px-6"><p class="font-medium text-gray-800 text-theme-sm dark:text-white/90">{{ $reservasi->mobil->nama_mobil }}</p><p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ $reservasi->mobil->merek }} · {{ $reservasi->mobil->plat_nomor }}</p></td>
                                <td class="px-5 py-4 text-theme-sm text-gray-600 dark:text-gray-400">{{ $reservasi->tanggal_mulai->format('d M Y') }} – {{ $reservasi->tanggal_selesai->format('d M Y') }}</td>
                                <td class="px-5 py-4 text-theme-sm text-gray-600 dark:text-gray-400">{{ $reservasi->durasi_sewa }} hari</td>
                                <td class="px-5 py-4 text-theme-sm font-medium text-gray-800 dark:text-white/90">Rp{{ number_format((float) $reservasi->total_biaya, 0, ',', '.') }}</td>
                                <td class="px-5 py-4"><x-ui.badge :color="$statusColor">{{ ucwords(str_replace('_', ' ', $reservasi->status)) }}</x-ui.badge></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-10 text-center text-theme-sm text-gray-500 dark:text-gray-400">Pelanggan ini belum memiliki riwayat reservasi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($reservasis->hasPages())
            <div class="pt-4">{{ $reservasis->links() }}</div>
        @endif

        <div class="mt-6 flex justify-end"><a href="{{ route('admin.pelanggan.index') }}" class="inline-flex items-center justify-center rounded-lg bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 transition hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:ring-gray-700">Kembali</a></div>
    </x-common.component-card>
@endsection
