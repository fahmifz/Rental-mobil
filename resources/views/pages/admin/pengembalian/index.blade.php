@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Pengembalian Mobil" />

    @if (session('success'))
        <div class="mb-6 rounded-xl border border-success-200 bg-success-50 px-4 py-3 text-sm text-success-700 dark:border-success-500/20 dark:bg-success-500/15 dark:text-success-400" role="alert">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 rounded-xl border border-error-200 bg-error-50 px-4 py-3 text-sm text-error-700 dark:border-error-500/20 dark:bg-error-500/15 dark:text-error-400" role="alert">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 rounded-xl border border-error-200 bg-error-50 px-4 py-3 text-sm text-error-700 dark:border-error-500/20 dark:bg-error-500/15 dark:text-error-400" role="alert">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <x-common.component-card title="Pengembalian Unit Mobil" desc="Pencatatan kondisi aktual mobil, durasi keterlambatan, dan denda saat unit dikembalikan.">
        <!-- Tabs & Search Header -->
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="inline-flex rounded-xl bg-gray-100 p-1 dark:bg-gray-800">
                <a href="{{ route('admin.pengembalian.index', ['tab' => 'siap_kembali', 'search' => $search]) }}"
                    class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-theme-sm font-medium transition {{ $tab === 'siap_kembali' ? 'bg-white text-gray-900 shadow-theme-xs dark:bg-gray-900 dark:text-white' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}">
                    <span>Menunggu Pengembalian</span>
                    <span class="inline-flex items-center justify-center rounded-full bg-brand-50 px-2 py-0.5 text-theme-xs font-semibold text-brand-700 dark:bg-brand-500/15 dark:text-brand-300">
                        {{ $siapKembaliCount }}
                    </span>
                </a>
                <a href="{{ route('admin.pengembalian.index', ['tab' => 'riwayat', 'search' => $search]) }}"
                    class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-theme-sm font-medium transition {{ $tab === 'riwayat' ? 'bg-white text-gray-900 shadow-theme-xs dark:bg-gray-900 dark:text-white' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}">
                    <span>Riwayat Selesai</span>
                    <span class="inline-flex items-center justify-center rounded-full bg-gray-200 px-2 py-0.5 text-theme-xs font-semibold text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                        {{ $riwayatCount }}
                    </span>
                </a>
            </div>

            <form action="{{ route('admin.pengembalian.index') }}" method="GET" class="flex w-full max-w-sm gap-2">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <input type="search" name="search" value="{{ $search }}" placeholder="Cari pelanggan atau mobil..."
                    class="h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3.5 py-2 text-theme-sm text-gray-800 shadow-theme-xs outline-hidden placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                <button type="submit" class="inline-flex shrink-0 items-center justify-center rounded-lg bg-gray-100 px-3.5 py-2 text-theme-sm font-medium text-gray-700 transition hover:bg-gray-200 dark:bg-white/[0.05] dark:text-gray-300 dark:hover:bg-white/[0.1]">
                    Cari
                </button>
            </form>
        </div>

        <!-- Table -->
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="max-w-full overflow-x-auto custom-scrollbar">
                <table class="w-full min-w-[1100px]">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <th class="px-5 py-3 text-start sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Reservasi</p></th>
                            <th class="px-5 py-3 text-start sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Pelanggan</p></th>
                            <th class="px-5 py-3 text-start sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Mobil & Plat</p></th>
                            <th class="px-5 py-3 text-start sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Jadwal Sewa</p></th>
                            <th class="px-5 py-3 text-start sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Status Reservasi</p></th>
                            <th class="px-5 py-3 text-start sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Status Pembayaran</p></th>
                            <th class="px-5 py-3 text-end sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Aksi</p></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($reservasis as $reservasi)
                            @php
                                $paymentStatus = $reservasi->paymentStatus();
                                $paymentBadgeColor = match ($paymentStatus) {
                                    'lunas' => 'success',
                                    'dibayar_sebagian' => 'warning',
                                    default => 'error',
                                };
                                $reservasiBadgeColor = match ($reservasi->status) {
                                    'disetujui' => 'success',
                                    'selesai' => 'primary',
                                    'dibatalkan' => 'light',
                                    'ditolak' => 'error',
                                    default => 'warning',
                                };
                            @endphp
                            <tr class="border-b border-gray-100 last:border-b-0 dark:border-gray-800">
                                <td class="px-5 py-4 sm:px-6">
                                    <p class="font-semibold text-theme-sm text-gray-900 dark:text-white">#{{ $reservasi->id }}</p>
                                    <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">Rp{{ number_format((float) $reservasi->total_biaya, 0, ',', '.') }}</p>
                                </td>
                                <td class="px-5 py-4 sm:px-6">
                                    <p class="font-medium text-theme-sm text-gray-800 dark:text-white/90">{{ $reservasi->user->name }}</p>
                                    <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ $reservasi->user->email }}</p>
                                </td>
                                <td class="px-5 py-4 text-theme-sm text-gray-700 dark:text-gray-300">
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $reservasi->mobil->nama_mobil }}</p>
                                    <span class="inline-block rounded-md bg-gray-100 px-2 py-0.5 text-theme-xs font-mono font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                        {{ $reservasi->mobil->plat_nomor }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-theme-sm text-gray-600 dark:text-gray-400">
                                    <p>{{ $reservasi->tanggal_mulai->format('d M Y') }} – {{ $reservasi->tanggal_selesai->format('d M Y') }}</p>
                                    <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ $reservasi->durasi_sewa }} hari</p>
                                </td>
                                <td class="px-5 py-4">
                                    <x-ui.badge :color="$reservasiBadgeColor">
                                        {{ ucwords(str_replace('_', ' ', $reservasi->status)) }}
                                    </x-ui.badge>
                                </td>
                                <td class="px-5 py-4">
                                    <x-ui.badge :color="$paymentBadgeColor">
                                        {{ ucwords(str_replace('_', ' ', $paymentStatus)) }}
                                    </x-ui.badge>
                                </td>
                                <td class="px-5 py-4 text-end sm:px-6">
                                    @if ($reservasi->pengembalian)
                                        <a href="{{ route('admin.pengembalian.show', $reservasi) }}"
                                            class="inline-flex items-center rounded-lg px-3 py-2 text-theme-sm font-medium text-brand-600 transition hover:bg-brand-50 dark:text-brand-300 dark:hover:bg-brand-500/15">
                                            Detail Pengembalian
                                        </a>
                                    @elseif ($reservasi->canBeReturned())
                                        <a href="{{ route('admin.pengembalian.create', $reservasi) }}"
                                            class="inline-flex items-center rounded-lg bg-brand-500 px-3.5 py-2 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">
                                            Proses Pengembalian
                                        </a>
                                    @else
                                        <span class="text-theme-xs text-gray-400 dark:text-gray-500">Tidak dapat diproses</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-10 text-center text-theme-sm text-gray-500 dark:text-gray-400">
                                    @if ($tab === 'riwayat')
                                        Belum ada riwayat pengembalian mobil.
                                    @else
                                        Tidak ada reservasi yang menunggu pengembalian mobil saat ini.
                                    @endif
                                </td>
                            </tr>
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

