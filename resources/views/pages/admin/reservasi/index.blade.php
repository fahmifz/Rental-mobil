@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Reservasi / Penyewaan" />

    @if (session('success'))
        <div class="mb-6 rounded-xl border border-success-200 bg-success-50 px-4 py-3 text-sm text-success-700 dark:border-success-500/20 dark:bg-success-500/15 dark:text-success-400" role="status">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 rounded-xl border border-error-200 bg-error-50 px-4 py-3 text-sm text-error-700 dark:border-error-500/20 dark:bg-error-500/15 dark:text-error-400" role="alert">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <x-common.component-card title="Daftar Reservasi" desc="Seluruh permintaan reservasi pelanggan.">
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="max-w-full overflow-x-auto custom-scrollbar">
                <table class="w-full min-w-[1080px]">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <th class="px-5 py-3 text-start sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Pelanggan</p></th>
                            <th class="px-5 py-3 text-start sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Mobil</p></th>
                            <th class="px-5 py-3 text-start sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Tanggal mulai</p></th>
                            <th class="px-5 py-3 text-start sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Tanggal selesai</p></th>
                            <th class="px-5 py-3 text-start sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Total biaya</p></th>
                            <th class="px-5 py-3 text-start sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Status</p></th>
                            <th class="px-5 py-3 text-end sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Aksi</p></th>
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
                                <td class="px-5 py-4 sm:px-6">
                                    <p class="font-medium text-gray-800 text-theme-sm dark:text-white/90">{{ $reservasi->user->name }}</p>
                                    <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ $reservasi->user->email }}</p>
                                </td>
                                <td class="px-5 py-4 text-theme-sm text-gray-700 dark:text-gray-300">{{ $reservasi->mobil->nama_mobil }}</td>
                                <td class="px-5 py-4 text-theme-sm text-gray-600 dark:text-gray-400">{{ $reservasi->tanggal_mulai->format('d M Y') }}</td>
                                <td class="px-5 py-4 text-theme-sm text-gray-600 dark:text-gray-400">{{ $reservasi->tanggal_selesai->format('d M Y') }}</td>
                                <td class="px-5 py-4 text-theme-sm text-gray-700 dark:text-gray-300">Rp{{ number_format((float) $reservasi->total_biaya, 0, ',', '.') }}</td>
                                <td class="px-5 py-4"><x-ui.badge :color="$statusColor">{{ ucwords(str_replace('_', ' ', $reservasi->status)) }}</x-ui.badge></td>
                                <td class="px-5 py-4 text-end sm:px-6">
                                    @if ($reservasi->status === 'menunggu_konfirmasi')
                                        <div class="inline-flex items-center gap-2">
                                            <form action="{{ route('admin.reservasi.approve', $reservasi) }}" method="POST" onsubmit="return confirm('Setujui reservasi ini?')">
                                                @csrf
                                                <button type="submit" class="rounded-lg bg-brand-500 px-3 py-2 text-sm font-medium text-white transition hover:bg-brand-600">Setujui</button>
                                            </form>
                                            <form action="{{ route('admin.reservasi.reject', $reservasi) }}" method="POST" onsubmit="return confirm('Tolak reservasi ini?')">
                                                @csrf
                                                <button type="submit" class="rounded-lg bg-error-50 px-3 py-2 text-sm font-medium text-error-700 transition hover:bg-error-100 dark:bg-error-500/15 dark:text-error-400 dark:hover:bg-error-500/25">Tolak</button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-theme-xs text-gray-400 dark:text-gray-500">Sudah diproses</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-10 text-center text-sm text-gray-500 dark:text-gray-400">Belum ada reservasi pelanggan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($reservasis->hasPages())
            <div class="pt-2">{{ $reservasis->links() }}</div>
        @endif
    </x-common.component-card>
@endsection
